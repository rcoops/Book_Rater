<?php

namespace BookRater\RaterBundle\Controller\Api;

use BookRater\RaterBundle\Entity\Review;
use BookRater\RaterBundle\Form\Api\ReviewType;
use BookRater\RaterBundle\Form\Api\Update\UpdateReviewType;
use BookRater\RaterBundle\Pagination\PaginationFactory;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ReviewController extends BaseApiController
{

    private const GROUPS = ['reviews'];

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(PaginationFactory $paginationFactory, AuthorizationCheckerInterface $authorizationChecker)
    {
        parent::__construct($paginationFactory);

        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Rest\Post("/reviews")
     *
     * @Security("is_granted('ROLE_USER')")
     *
     * @SWG\Post(
     *   tags={"Reviews"},
     *   summary="Create a new review",
     *   description="Creates a new review resource.
                      <strong>Requires user authorization.</strong>",
     *   produces={"application/hal+json"},
     *   @SWG\Parameter(in="body", name="review", @Model(type=Review::class, groups={"reviews_send"}),),
     *   @SWG\Response(
     *     response=201,
     *     description="Creates a new review.",
     *     @SWG\Schema(@Model(type=Review::class, groups={"reviews"},),),
     *   ),
     *   @SWG\Response(
     *     response=400,
     *     description="A 'Bad Request' error response, if the request body is not correctly formatted.",
     *   ),
     *   @SWG\Response(response=401, description="An 'Unauthorized' error response, if the user is not authenticated.",),
     *   @SWG\Response(response=404, description="A 'Not Found' error response, if the review resource does not exist.",),
     * )
     */
    public function newAction(Request $request)
    {
        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);
        $this->processForm($request, $form);

        if (!$form->isValid()) {
            $this->throwApiProblemValidationException($form);
        }

        $review->setUser($this->getUser());
        $review->setCreated(new \DateTime('now'));

        $this->persistReview($review);

        $response = $this->createApiResponse($review, Response::HTTP_CREATED);
        $this->setLocationHeader($response, 'api_reviews_show', ['id' => $review->getId()]);

        return $response;
    }

    /**
     * @param int $id
     *
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/reviews/{id}", name="api_reviews_show")
     *
     * @SWG\Get(
     *   tags={"Reviews"},
     *   summary="Retrieve a single review",
     *   description="Retrieves a representation of the review resource queried for.",
     *   produces={"application/hal+json", "application/hal+xml"},
     *   @SWG\Parameter(in="path", name="id", type="integer", description="The unique identifier of the review."),
     *   @SWG\Parameter(in="query", name="format", type="string", enum={"xml", "json"}),
     *   @SWG\Response(
     *     response=200,
     *     description="A representation of the book resource queried for.",
     *     @Model(type=Review::class, groups={"reviews"},),
     *   ),
     *   @SWG\Response(response=404, description="A 'Not Found' error response, if the resource does not exist.",),
     * )
     */
    public function showAction(int $id, Request $request)
    {
        $review = $this->getReviewIfExists($id);

        $response = $this->getCachedResponseIfExistent($request, $review->getLastModified());
        if ($response) {
            return $response;
        }

        $response = $this->createApiResponseUsingRequestedFormat($review, $request);
        $this->setLocationHeader($response, 'api_reviews_show', ['id' => $review->getId()]);

        return $response;
    }


    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Rest\Get("/reviews", name="api_reviews_collection")
     *
     * @SWG\Get(
     *   tags={"Reviews"},
     *   summary="List a collection of reviews",
     *   description="Retrieves a (filtered) collection of review resources.",
     *   produces={"application/hal+json", "application/hal+xml"},
     *   @SWG\Parameter(
     *     in="query",
     *     name="filter",
     *     type="string",
     *     description="An optional filter by book title, username, or review rating."
     *   ),
     *   @SWG\Parameter(in="query", name="format", type="string", enum={"xml", "json"}),
     *   @SWG\Parameter(
     *     in="query",
     *     name="page",
     *     type="integer",
     *     description="An optional page number for paginated results."
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="A (filtered) collection of review resources.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(
     *         property="items",
     *         type="array",
     *         @Model(type=Review::class, groups={"reviews"},),
     *       ),
     *       @SWG\Property(property="total", type="integer", description="The total number of items in the pagination.",),
     *       @SWG\Property(property="count", type="integer", description="The total number of items in the current page.",),
     *       @SWG\Property(
     *         property="_links",
     *         @SWG\Property(property="self", type="string", description="A relative url to the current page of the pagination.",),
     *         @SWG\Property(property="first", type="string", description="A relative url to the first page of the pagination.",),
     *         @SWG\Property(property="last", type="string", description="A relative url to the current page of the pagination.",),
     *         @SWG\Property(property="next", type="string", description="A relative url to the current page of the pagination.",),
     *       ),
     *     ),
     *   ),
     * )
     */
    public function listAction(Request $request)
    {
        $filter = $request->query->get('filter');

        $qb = $this->getReviewRepository()
            ->findAllQueryBuilder($filter);
        $reviews = $qb->getQuery()->getResult();
        if ($reviews) {
            $response = $this->getCachedResponseIfExistent($request, $this->getMostRecentModified($reviews));
            if ($response) {
                return $response;
            }
        }
        $paginatedCollection = $this->paginationFactory
            ->createCollection($qb, $request, 'api_reviews_collection');

        return $this->createApiPaginationResponse($paginatedCollection, $request);
    }

    /**
     * @param int $id
     * @param Request $request
     *
     * @return Response
     *
     * @Route("/reviews/{id}")
     * @Method({"PUT", "PATCH"})
     *
     * @SWG\Put(
     *   tags={"Reviews"},
     *   summary="Update a review",
     *   description="Updates a review, requiring a full representation of the resource.
     *                <strong>Requires owner or admin authorization.</strong>",
     *   produces={"application/hal+json"},
     *   @SWG\Parameter(in="path", name="id", type="integer", description="The unique identifier of the review."),
     *   @SWG\Parameter(in="body", name="book", @Model(type=Review::class, groups={"reviews_send"},),),
     *   @SWG\Response(
     *     response=200,
     *     description="A representation of the review resource updated.",
     *     @Model(type=Review::class, groups={"reviews"},),
     *   ),
     *   @SWG\Response(
     *     response=400,
     *     description="A 'Bad Request' error response, if the request body is not correctly formatted.",
     *   ),
     *   @SWG\Response(
     *     response=401,
     *     description="An 'Unauthorized' error response, if the user is not the owner of the resource or an admin.",
     *   ),
     *   @SWG\Response(response=404, description="A 'Not Found' error response, if the resource does not exist.",),
     * )
     * @SWG\Patch(
     *   tags={"Reviews"},
     *   summary="Update part(s) of a review",
     *   description="Updates a review, requiring a full representation of the resource.
     *                <strong>Requires owner or admin authorization.</strong>",
     *   produces={"application/hal+json"},
     *   @SWG\Parameter(in="path", name="id", type="integer", description="The unique identifier of the review."),
     *   @SWG\Parameter(in="body", name="book", @Model(type=Review::class, groups={"reviews_send"},),),
     *   @SWG\Response(
     *     response=200,
     *     description="A representation of the review resource updated.",
     *     @Model(type=Review::class, groups={"reviews"},),
     *   ),
     *   @SWG\Response(
     *     response=400,
     *     description="A 'Bad Request' error response, if the request body is not correctly formatted.",
     *   ),
     *   @SWG\Response(
     *     response=401,
     *     description="An 'Unauthorized' error response, if the user is not the owner of the resource or an admin.",
     *   ),
     *   @SWG\Response(response=404, description="A 'Not Found' error response, if the resource does not exist.",),
     * )
     */
    public function updateAction(int $id, Request $request)
    {
        /** @var Review $review */
        $review = $this->getReviewIfExists($id);

        if (!$this->authorizationChecker->isGranted('OWNER', $review)) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->createForm(UpdateReviewType::class, $review);
        $this->processForm($request, $form);
        if (!$form->isValid()) {
            $this->throwApiProblemValidationException($form);
        }

        $this->persistReview($review);

        $response = $this->createApiResponse($review);
        $this->setLocationHeader($response, 'api_reviews_show', [
            'id' => $review->getId(),
        ]);

        return $response;
    }

    /**
     * @param int $id
     * @return Response
     *
     * @Rest\Delete("/reviews/{id}")
     *
     * @SWG\Delete(
     *   tags={"Reviews"},
     *   summary="Remove a review",
     *   description="Removes a review resource from the system.
     *                <strong>Requires owner or admin authorization.</strong>",
     *   produces={"text/html"},
     *   @SWG\Parameter(in="path", name="id", type="integer", description="The unique identifier of the review."),
     *   @SWG\Response(response=204, description="Indicates that the resource is not present on the system.",),
     * )
     */
    public function deleteAction(int $id)
    {
        $review = $this->getReviewRepository()->find($id);
        if ($review) {
            if (!$this->authorizationChecker->isGranted('OWNER', $review)) {
                throw new AccessDeniedHttpException();
            }
            $em = $this->getDoctrine()->getManager();
            $em->remove($review);
            $em->flush();
        }

        return $this->createApiResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Review $review
     */
    private function persistReview(Review $review): void
    {
        $review->setLastModified(new \DateTime());
        $em = $this->getDoctrine()->getManager();
        $em->persist($review);
        $em->flush();
    }

    /**
     * @param int $id
     */
    private function throwReviewNotFoundException(int $id) : void
    {
        throw $this->createNotFoundException(sprintf('No review found with id: "%s"', $id));
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function getReviewIfExists(int $id)
    {
        $review = $this->getReviewRepository()->find($id);

        if (!$review) {
            $this->throwReviewNotFoundException($id);
        }
        return $review;
    }

    protected function getGroups()
    {
        return self::GROUPS;
    }

}
