<?php

namespace BookRater\RaterBundle\Controller\Api;

use BookRater\RaterBundle\Entity\Book;
use BookRater\RaterBundle\Form\Api\BookType;
use BookRater\RaterBundle\Form\Api\Update\UpdateBookType;
use BookRater\RaterBundle\Pagination\PaginationFactory;
use Doctrine\ORM\NonUniqueResultException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BookController extends BaseApiController
{

    private const GROUPS = ["books"];

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Rest\Post("/books")
     *
     * @Security("is_granted('ROLE_USER')")
     *
     * @SWG\Post(
     *     tags={"Books"},
     *     description="Creates a new book resource.",
     *     responses={
     *         @SWG\Response(
     *             response=201,
     *             description="A representation of the book resource created.",
     *             @SWG\Schema(
     *                 @Model(type=Book::class, groups={"books"})
     *             )
     *         )
     *     }
     * )
     */
    public function newAction(Request $request)
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $this->processForm($request, $form);

        if (!$form->isValid()) {
            $this->throwApiProblemValidationException($form);
        }

        $this->persistBook($book);

        $response = $this->createApiResponse($book, 201);
        $this->setLocationHeader($response, 'api_books_show', [
            'id' => $book->getId(),
        ]);

        return $response;
    }

    /**
     * @param int $id
     *
     * @return Response
     *
     * @Rest\Get("/books/{id}", name="api_books_show")
     *
     * @SWG\Get(
     *     tags={"Books"},
     *     description="Returns a representation of the book resource queried for.",
     *     responses={
     *         @SWG\Response(
     *             response=200,
     *             description="A representation of the book resource queried for.",
     *             @SWG\Schema(
     *                 @Model(type=Book::class, groups={"books"})
     *             )
     *         )
     *     }
     * )
     */
    public function showAction(int $id)
    {
        $book = $this->getBookRepository()->find($id);

        if (!$book) {
            $this->throwBookNotFoundException($id);
        }

        $response = $this->createApiResponse($book);
        $this->setLocationHeader($response, 'api_books_show', [
            'id' => $book->getId(),
        ]);

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Rest\Get("/books", name="api_books_collection")
     *
     * @SWG\Get(
     *     tags={"Books"},
     *     description="Returns a (filtered) collection of book resources.",
     *     parameters={
     *         @SWG\Parameter(
     *             in="query",
     *             name="filter",
     *             type="string",
     *             description="An optional filter by book title or author first/last name."),
     *     },
     *     responses={
     *         @SWG\Response(
     *             response=200,
     *             description="A (filtered) collection of book resources.",
     *             @SWG\Schema(
     *                 @Model(type=Book::class, groups={"books"})
     *             )
     *         )
     *     }
     * )
     */
    public function listAction(Request $request)
    {
        $filter = $request->query->get('filter');

        $qb = $this->getBookRepository()
            ->findAllByFilterQueryBuilder($filter);
        $paginatedCollection = $this->paginationFactory
            ->createCollection($qb, $request, 'api_books_collection');

        return $this->createApiResponse($paginatedCollection);
    }

    /**
     * @param int $id
     * @param Request $request
     *
     * @return Response
     *
     * @Route("/books/{id}")
     * @Method({"PUT", "PATCH"})
     *
     * @Security("is_granted('ROLE_USER')")
     *
     * @SWG\Put(
     *     tags={"Books"},
     *     description="Updates a book, requiring a full representation of the resource.",
     *     responses={
     *         @SWG\Response(
     *             response=200,
     *             description="A representation of the book resource updated.",
     *             @SWG\Schema(
     *                 @Model(type=Book::class, groups={"books"})
     *             )
     *         )
     *     }
     * )
     * @SWG\Patch(
     *     tags={"Books"},
     *     description="Updates a book, requiring only a part representation of the resource.",
     *     responses={
     *         @SWG\Response(
     *             response=200,
     *             description="A representation of the book resource updated.",
     *             @SWG\Schema(
     *                 @Model(type=Book::class, groups={"books"})
     *             )
     *         )
     *     }
     * )
     */
    public function updateAction(int $id, Request $request)
    {
        /** @var Book|null $book */
        $book = $this->getBookRepository()->find($id);

        if (!$book) {
            $this->throwBookNotFoundException($id);
        }

        $form = $this->createForm(UpdateBookType::class, $book);
        $this->processForm($request, $form);
        if (!$form->isValid()) {
            $this->throwApiProblemValidationException($form);
        }

        $this->persistBook($book);

        $response = $this->createApiResponse($book);
        $this->setLocationHeader($response, 'api_books_show', [
            'id' => $book->getId(),
        ]);

        return $response;
    }

    /**
     * @param int $id
     * @return Response
     *
     * @Rest\Delete("/books/{id}")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @SWG\Delete(
     *     tags={"Books"},
     *     description="Removes a book resource from the system.",
     *     responses={
     *         @SWG\Response(response=204, description="Indicates that the resource is not present on the system.")
     *     }
     * )
     */
    public function deleteAction(int $id)
    {
        $book = $this->getBookRepository()->find($id);

        if ($book) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($book);
            $em->flush();
        }

        // Doesn't matter if the book was there or not - because it isn't now which is what we wanted
        return $this->createApiResponse(null, 204);
    }

    /**
     * @param Book $book
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/books/{id}/authors", name="api_books_authors_list")
     *
     * @SWG\Get(
     *     tags={"Books"},
     *     description="Returns a collection of the book's authors.",
     *     responses={
     *         @SWG\Response(
     *             response=200,
     *             description="A collection of the book's authors.",
     *         )
     *     }
     * )
     */
    // TODO schema for collection?
    public function authorsListAction(Book $book, Request $request)
    {
        $qb = $this->getAuthorRepository()
            ->createQueryBuilderForBook($book);
        $collection = $this->paginationFactory->createCollection(
            $qb,
            $request,
            'api_books_authors_list',
            [
                'id' => $book->getId(),
            ]
        );
        return $this->createApiResponse($collection);
    }

    /**
     * @param Book $book
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/books/{id}/reviews", name="api_books_reviews_list")
     *
     * @SWG\Get(
     *     tags={"Books"},
     *     description="Returns a collection of the book's reviews.",
     *     parameters={
     *         @SWG\Parameter(in="path", name="id", type="integer", description="The unique identifier of the book resource."),
     *     },
     *     responses={
     *         @SWG\Response(
     *             response=200,
     *             description="A collection of the book's reviews.",
     *         )
     *     }
     * )
     */
    public function reviewsListAction(Book $book, Request $request)
    {
        $qb = $this->getReviewRepository()
            ->createQueryBuilderForBook($book);
        $collection = $this->paginationFactory->createCollection(
            $qb,
            $request,
            'api_books_reviews_list',
            [
                'id' => $book->getId(),
            ]
        );
        return $this->createApiResponse($collection);
    }

    /**
     * @param Book $book
     */
    private function persistBook(Book $book): void
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($book);
        foreach ($book->getAuthors() as $author) {
            $author->addBooksAuthored($book);
            $em->persist($author);
        }
        $em->flush();
    }

    /**
     * @param int $id
     */
    private function throwBookNotFoundException(int $id) : void
    {
        throw $this->createNotFoundException(sprintf('No book found with id: "%s"', $id));
    }

    protected function getGroups()
    {
        return self::GROUPS;
    }

}