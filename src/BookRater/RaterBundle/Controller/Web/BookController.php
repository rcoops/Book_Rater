<?php

namespace BookRater\RaterBundle\Controller\Web;

use BookRater\RaterBundle\Entity\Book;
use BookRater\RaterBundle\Entity\Review;
use BookRater\RaterBundle\Form\BookType;
use BookRater\RaterBundle\Form\ReviewForSpecificBookType;
use DateTime;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class BookController extends EntityController
{

    /**
     * @var \BookRater\RaterBundle\Repository\BookRepository|\Doctrine\ORM\EntityRepository
     */
    protected $bookRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
        $this->bookRepository = $this->entityManager->getRepository('BookRaterRaterBundle:Book');
    }

    public function viewAction(string $title)
    {
        $book = $this->bookRepository->findOneBy(['title' => $title]);

        return $this->render('BookRaterRaterBundle:Book:view.html.twig', ['book' => $book]);
    }

    public function createAction(Request $request)
    {
        $book = new Book();

        $form = $this->createForm(BookType::class, $book, [
            'action' => $request->getUri()]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $this->entityManager->persist($book);
                $this->entityManager->flush();

                return $this->redirect($this->generateUrl('bookrater_book_view', ['title' => $book->getTitle()]));
            } catch (UniqueConstraintViolationException $e) { // thrown because of the unique constraint I put on title + edition
                $this->addFlash(
                    'error',
                    'This book already exists! Please edit the original or change the title/edition.'
                );
            }
        }
        return $this->render('BookRaterRaterBundle:Book:create.html.twig', ['form' => $form->createView()]);
    }

    public function addReviewAction(int $id, Request $request)
    {
        $book = $this->bookRepository->find($id);
        $review = new Review();
        $form = $this->createForm(ReviewForSpecificBookType::class, $review, [
                'action' => $request->getUri(),
                'book' => $book,
            ]
        );
        $form->handleRequest($request);

        if ($form->isValid()) {
            $review->setUser($this->getUser());
            $review->setCreated(new DateTime());
            $this->entityManager->persist($review);
            $this->entityManager->flush();

            return $this->redirect($this->generateUrl('bookrater_book_view', [
                'title' => $book->getTitle()
            ]));
        }
        return $this->render('@BookRaterRater/Review/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function editAction(int $id, Request $request)
    {
        $book = $this->bookRepository->find($id);

        $form = $this->createForm(BookType::class, $book, ['action' => $request->getUri()]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->entityManager->flush();

            return $this->redirect($this->generateUrl('bookrater_book_view', ['title' => $book->getTitle()]));
        }
        return $this->render('BookRaterRaterBundle:Book:edit.html.twig',
            ['form' => $form->createView(), 'book' => $book]);
    }

    public function listAction(Request $request)
    {
        $filter = $request->query->get('filter');
        $query = $this->bookRepository
            ->findAllByFilter($filter);

        $pagination = $this->paginate($query, $request);

        return $this->render('@BookRaterRater/Book/list.html.twig', [
            'pagination' => $pagination
        ]);
    }

    public function filter(string $filter = '', Request $request)
    {
        $params = $request->query->all();
        if ($filter) {
            $params = array_merge($params, ['filter' => $filter]);
        }

        $this->redirect($this->generateUrl($request->attributes->get('_route'), $params));
    }

}
