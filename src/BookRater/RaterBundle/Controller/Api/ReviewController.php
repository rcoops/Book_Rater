<?php

namespace BookRater\RaterBundle\Controller\Api;

use BookRater\RaterBundle\Entity\Review;
use BookRater\RaterBundle\Form\Api\ReviewType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Psr\Http\Message\ResponseInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

/**
 * Class ReviewController
 * @package BookRater\RaterBundle\Controller\Api
 * @SWG\Swagger()
 */
class ReviewController extends BaseApiController
{

    private const GROUPS = ['reviews'];

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
