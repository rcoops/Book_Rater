<?php

namespace BookRater\RaterBundle\Controller;

use BookRater\RaterBundle\Entity\Author;
use BookRater\RaterBundle\Entity\Book;
use BookRater\RaterBundle\Form\BookType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

class BookController extends EntityController
{

    /**
     * @var \BookRater\RaterBundle\Repository\ReviewRepository|\Doctrine\ORM\EntityRepository
     */
    protected $bookRepository;

    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager);
        $this->bookRepository = $this->entityManager->getRepository('BookRaterRaterBundle:Book');
    }

    public function viewAction(int $id)
    {
        $book = $this->bookRepository->find($id);

        return $this->render('BookRaterRaterBundle:Book:view.html.twig', ['book' => $book]);
    }

    public function createAction(Request $request)
    {
        $book = new Book();

        $author = new Author();
        $book->getAuthors()->add($author);

        $form = $this->createForm(BookType::class, $book, [
            'action' => $request->getUri()]);

        $form->handleRequest($request);

        if ($form->isValid())
        {
            $this->entityManager->persist($book);
            $this->entityManager->flush();

            return $this->redirect($this->generateUrl('bookrater_book_view', ['id' => $book->getId()]));
        }
        return $this->render('BookRaterRaterBundle:Book:create.html.twig', ['form' => $form->createView()]);
    }

    public function editAction(int $id, Request $request)
    {
        $book = $this->bookRepository->find($id);

        $form = $this->createForm(BookType::class, $book, ['action' => $request->getUri()]);
        $form->handleRequest($request);

        if($form->isValid()) {
            $this->entityManager->flush();
        }
        return $this->render('BookRaterRaterBundle:Book:edit.html.twig',
            ['form' => $form->createView(), 'book' => $book]);
    }

}
