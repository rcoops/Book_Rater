<?php

namespace BookRater\RaterBundle\Controller;

use BookRater\RaterBundle\Entity\Review;
use BookRater\RaterBundle\Form\ReviewType;
use DateTime;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

class ReviewController extends EntityController
{

    /**
     * @var \BookRater\RaterBundle\Repository\ReviewRepository|\Doctrine\ORM\EntityRepository
     */
    protected $reviewRepository;

    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager);
        $this->reviewRepository = $entityManager->getRepository('BookRaterRaterBundle:Review');
    }

    public function listAction(Request $request)
    {
        $query = $this->reviewRepository
            ->getLatestByBookTitleLike("");

        // parameters to template
        return $this->render('@BookRaterRater/Review/list.html.twig', [
            'pagination' => $this->paginate($query, $request)
        ]);
    }

    public function viewAction(int $id)
    {
        $review = $this->reviewRepository->find($id);

        return $this->render('BookRaterRaterBundle:Review:view.html.twig', ['review' => $review]);
    }

    public function editAction(int $id, Request $request)
    {
        $review = $this->reviewRepository->find($id);

        $form = $this->createForm(ReviewType::class, $review, [
            'action' => $request->getUri()
        ]);
        $form->handleRequest($request);

        if($form->isValid()) {
            $review->setEdited(new DateTime());

            $this->entityManager->flush();

            return $this->redirect($this->generateUrl('bookrater_review_view', ['id' => $review->getId()]));
        }
        return $this->render('BookRaterRaterBundle:Review:edit.html.twig',
            ['form' => $form->createView(), 'review' => $review]);
    }

    public function createAction(Request $request)
    {
        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review, ['action' => $request->getUri()]);

        $form->handleRequest($request);

        if ($form->isValid())
        {
            $review->setUser($this->getUser());
            $review->setCreated(new DateTime());
            $this->entityManager->persist($review);
            $this->entityManager->flush();

            return $this->redirect($this->generateUrl('bookrater_review_edit', ['id' => $review->getId()]));
        }
        return $this->render('BookRaterRaterBundle:Review:create.html.twig', ['form' => $form->createView()]);
    }

    public function deleteAction(int $id, Request $request)
    {
        return $this->render('BookRaterRaterBundle:Review:delete.html.twig', array(
            // ...
        ));
    }

}
