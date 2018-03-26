<?php

namespace BookRater\RaterBundle\Controller\Api;

use BookRater\RaterBundle\Entity\Review;
use BookRater\RaterBundle\Form\Api\ReviewType;
use BookRater\RaterBundle\Form\Api\Update\UpdateReviewType;
use BookRater\RaterBundle\Pagination\PaginationFactory;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Psr\Http\Message\ResponseInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;
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
     *     tags={"Reviews"},
     *     responses={
     *         @SWG\Response(
     *             response=201,
     *             description="Creates a new review.",
     *             @SWG\Schema(
     *                 @Model(type=Review::class, groups={"reviews"})
     *             )
     *         )
     *     }
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

        $response = $this->createApiResponse($review, 201);
        $this->setLocationHeader($response, 'api_reviews_show', ['id' => $review->getId()]);

        return $response;
    }

    /**
     * @param int $id
     *
     * @return Response
     *
     * @Rest\Get("/reviews/{id}", name="api_reviews_show")
     *
     * @SWG\Get(
     *     tags={"Reviews"},
     *     responses={
     *         @SWG\Response(
     *             response=200,
     *             description="Returns a representation of a review resource.",
     *             @SWG\Schema(
     *                 @Model(type=Review::class, groups={"reviews"})
     *             )
     *         )
     *     }
     * )
     */
    public function showAction(int $id)
    {
        $review = $this->getReviewRepository()->find($id);

        if (!$review) {
            $this->throwReviewNotFoundException($id);
        }

        $response = $this->createApiResponse($review);
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
     *     tags={"Reviews"},
     *     responses={
     *         @SWG\Response(
     *             response=200,
     *             description="Returns a (filtered) collection of review representations.",
     *             @SWG\Schema(
     *                 @Model(type=Review::class, groups={"reviews"})
     *             )
     *         )
     *     }
     * )
     */
    public function listAction(Request $request)
    {
        $filter = $request->query->get('filter');

        $qb = $this->getReviewRepository()
            ->findAllQueryBuilder($filter);
        $paginatedCollection = $this->paginationFactory
            ->createCollection($qb, $request, 'api_reviews_collection');

        return $this->createApiResponse($paginatedCollection);
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
     *     tags={"Reviews"},
     *     description="Updates a review, requiring a full representation of the resource.",
     *     responses={
     *         @SWG\Response(
     *             ref="update_review_response",
     *             response=200,
     *             description="A representation of the review resource updated.",
     *             @SWG\Schema(
     *                 @Model(type=Review::class, groups={"reviews"})
     *             )
     *         )
     *     }
     * )
     * @SWG\Patch(
     *     tags={"Review"},
     *     description="Updates a review, requiring only a part representation of the resource.",
     *     responses={
     *         @SWG\Response(
     *             response=200,
     *             description="A representation of the review resource updated.",
     *             @SWG\Schema(
     *                 @Model(type=Review::class, groups={"reviews"})
     *             )
     *         )
     *     }
     * )
     */
    public function updateAction(int $id, Request $request)
    {
        /** @var Review $review */
        $review = $this->getReviewRepository()->find($id);

        if (!$review) {
            $this->throwReviewNotFoundException($id);
        } else if (!$this->authorizationChecker->isGranted('OWNER', $review)) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->createForm(UpdateReviewType::class, $review);
        $this->processForm($request, $form);
        if (!$form->isValid()) {
            $this->throwApiProblemValidationException($form);
        }

        $this->persistReview($review);

        $response = $this->createApiResponse($review);
        $this->setLocationHeader($response, 'api_books_show', [
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
     *     tags={"Reviews"},
     *     description="Removes a review resource from the system.",
     *     responses={
     *         @SWG\Response(response=204, description="Indicates that the resource is not present on the system.")
     *     }
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

        return $this->createApiResponse(null, 204);
    }

    /**
     * @param Review $review
     */
    private function persistReview(Review $review): void
    {
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

    protected function getGroups()
    {
        return self::GROUPS;
    }

}
