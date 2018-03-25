<?php

namespace BookRater\RaterBundle\Controller\Api;

use BookRater\RaterBundle\Entity\Book;
use BookRater\RaterBundle\Form\Api\BookType;
use BookRater\RaterBundle\Pagination\PaginationFactory;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BookController extends BaseApiController
{

    private const GROUPS = ["books"];

    /** @var PaginationFactory */
    private $paginationFactory;

    /**
     * @param PaginationFactory $paginationFactory
     */
    public function __construct(PaginationFactory $paginationFactory)
    {
        $this->paginationFactory = $paginationFactory;
    }

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
     *     responses={
     *         @SWG\Response(
     *             response=201,
     *             description="Creates a new book.",
     *             @SWG\Schema(
     *                 @Model(type=Book::class, groups="books")
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

        $bookUrl = $this->generateUrl(
            'api_books_show', [
                'id' => $book->getId(),
            ]
        );
        $response->headers->set('Location', $bookUrl);
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
     *     responses={
     *         @SWG\Response(
     *             response=200,
     *             description="Returns the representation of a book resource.",
     *             @SWG\Schema(
     *                 @Model(type=Book::class, groups="books")
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

        return $this->createApiResponse($book);
    }

    /**
     * @param Book $book
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/books/{id}/books", name="api_books_authors_list")
     */
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