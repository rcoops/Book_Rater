<?php

namespace BookRater\RaterBundle\Controller\Api;

use BookRater\RaterBundle\Entity\Review;
use BookRater\RaterBundle\Form\Api\ReviewType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

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
     *     responses={
     *         @SWG\Response(
     *             response=201,
     *             description="Creates a new review.",
     *             @SWG\Schema(
     *                 @Model(type=Review::class, groups="reviews")
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

//        $bookUrl = $this->generateUrl(
//            'api_reviews_show', [
//                'id' => $review->getId(),
//            ]
//        );
//        $response->headers->set('Location', $bookUrl);
        return $response;
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

    protected function getGroups()
    {
        return self::GROUPS;
    }

}
