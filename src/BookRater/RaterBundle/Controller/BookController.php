<?php

namespace BookRater\RaterBundle\Controller;

use BookRater\RaterBundle\Entity\Book;
use BookRater\RaterBundle\Form\BookType;
use BookRater\RaterBundle\Repository\AuthorRepository;
use BookRater\RaterBundle\Repository\BookRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BookController extends Controller
{

    /**
     * @var ObjectManager
     */
    private $entityManager;

    /**
     * @var BookRepository|EntityRepository
     */
    private $bookRepository;

    /**
     * @var AuthorRepository|EntityRepository
     */
    private $authorRepository;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->bookRepository = $entityManager->getRepository('BookRaterRaterBundle:Book');
        $this->authorRepository = $entityManager->getRepository('BookRaterRaterBundle:Author');
    }

    public function viewAction(int $id)
    {
        $book = $this->bookRepository->find($id);

        return $this->render('BookRaterRaterBundle:Book:view.html.twig', ['book' => $book]);
    }

    public function createAction(Request $request)
    {
        $book = new Book();

        $form = $this->createForm(BookType::class, $book, [
            'action' => $request->getUri()]);
//                'authors' => $this->authorRepository->findAll()]);

        $form->handleRequest($request);

        if ($form->isValid())
        {
            $this->entityManager->persist($book);
            $this->entityManager->flush();

            return $this->redirect($this->generateUrl('bookrater_book_edit', ['id' => $book->getId()]));
        }
        return $this->render('BookRaterRaterBundle:Book:create.html.twig', ['form' => $form->createView()]);
    }

//    private function buildAuthorList() {
//        $authors = $this->authorRepository->findAll();
//        $authorList = [];
//        foreach ($authors as $author) {
//            array_push($authorList, [$author->getFullName() => $author]);
//        }
//        array_push($authorList, ['Create...' => null]);
//        return $authorList;
//    }

    public function editAction(int $id, Request $request)
    {

        $book = $this->bookRepository->find($id);

        $form = $this->createForm(BookType::class, $book, [
            'action' => $request->getUri()
        ]);
        $form->handleRequest($request);

        if($form->isValid()) {
            $this->entityManager->flush();
        }
        return $this->render('BookRaterRaterBundle:Book:edit.html.twig',
            ['form' => $form->createView(), 'book' => $book]);
    }

}
