<?php

namespace BookRater\RaterBundle\Controller\Api;

use BookRater\RaterBundle\Api\ApiProblem;
use BookRater\RaterBundle\Api\ApiProblemException;
use BookRater\RaterBundle\Entity\Book;
use BookRater\RaterBundle\Entity\Review;
use BookRater\RaterBundle\Entity\Author;
use BookRater\RaterBundle\Form\Api\BookType;
use BookRater\RaterBundle\Form\Api\ReviewType;
use BookRater\RaterBundle\Form\Api\Update\UpdateBookType;
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
     *   tags={"Books"},
     *   summary="Create a new book",
     *   description="Creates a new book resource.
                      <strong>Requires user authorization.</strong>",
     *   produces={"application/hal+json"},
     *   @SWG\Parameter(in="body", name="book", @Model(type=Book::class, groups={"books_send"},),),
     *   @SWG\Response(
     *     response=201,
     *     description="A representation of the book resource created.",
     *     @Model(type=Book::class, groups={"books"},),
     *   ),
     *   @SWG\Response(
     *     response=400,
     *     description="A 'Bad Request' error response, if the request body is not correctly formatted.",
     *   ),
     *   @SWG\Response(response=401, description="An 'Unauthorized' error response, if the user is not authenticated.",),
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

        $response = $this->createApiResponse($book, Response::HTTP_CREATED);
        $this->setLocationHeader($response, 'api_books_show', [
            'id' => $book->getId(),
        ]);

        return $response;
    }

    /**
     * @param int $id
     *
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/books/{id}", name="api_books_show")
     *
     * @SWG\Get(
     *   tags={"Books"},
     *   summary="Retrieve a single book",
     *   description="Retrieves a representation of the book resource queried for.",
     *   produces={"application/hal+json", "application/hal+xml"},
     *   @SWG\Parameter(in="path", name="id", type="integer", description="The unique identifier of the book."),
     *   @SWG\Parameter(in="query", name="format", type="string", enum={"xml", "json"}),
     *   @SWG\Response(
     *     response=200,
     *     description="A representation of the book resource queried for.",
     *     @Model(type=Book::class, groups={"books"},)
     *   ),
     *   @SWG\Response(response=404, description="A 'Not Found' error response, if the resource does not exist.",),
     * )
     */
    public function showAction(int $id, Request $request)
    {
        /** @var Book $book */
        $book = $this->getBookIfExists($id);

        $response = $this->getCachedResponseIfExistent($request, $book->getLastModified());
        if ($response) {
            return $response;
        }

        $response = $this->createApiResponseUsingRequestedFormat($book, $request);
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
     *   tags={"Books"},
     *   summary="List a collection of books",
     *   description="Retrieves a (filtered) collection of book resources.",
     *   produces={"application/hal+json", "application/hal+xml"},
     *   @SWG\Parameter(
     *     in="query",
     *     name="filter",
     *     type="string",
     *     description="An optional filter by book title or author first/last name."
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
     *     description="A (filtered) collection of book resources.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(
     *         property="items",
     *         type="array",
     *         @Model(type=Book::class, groups={"books"},),
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

        $qb = $this->getBookRepository()
            ->findAllByFilterQueryBuilder($filter);
        $books = $qb->getQuery()->getResult();
        if ($books) {
            $response = $this->getCachedResponseIfExistent($request, $this->getMostRecentModified($books));
            if ($response) {
                return $response;
            }
        }
        $paginatedCollection = $this->paginationFactory
            ->createCollection($qb, $request, 'api_books_collection');

        return $this->createApiPaginationResponse($paginatedCollection, $request);
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
     *   tags={"Books"},
     *   summary="Update a book",
     *   description="Updates a book, requiring a full representation of the resource.
                      <strong>Requires user authorization.</strong>",
     *   produces={"application/hal+json"},
     *   @SWG\Parameter(in="path", name="id", type="integer", description="The unique identifier of the book."),
     *   @SWG\Parameter(in="body", name="book", @Model(type=Book::class, groups={"books_send"},),),
     *   @SWG\Response(
     *     response=200,
     *     description="A representation of the book resource updated.",
     *     @Model(type=Book::class, groups={"books"},),
     *   ),
     *   @SWG\Response(
     *     response=400,
     *     description="A 'Bad Request' error response, if the request body is not correctly formatted.",
     *   ),
     *   @SWG\Response(response=401, description="An 'Unauthorized' error response, if the user is not authenticated.",),
     *   @SWG\Response(response=404, description="A 'Not Found' error response, if the resource does not exist.",),
     * )
     * @SWG\Patch(
     *   tags={"Books"},
     *   summary="Update part(s) of a book",
     *   description="Updates a book, requiring only a part representation of the resource.
                      <strong>Requires user authorization.</strong>",
     *   produces={"application/hal+json"},
     *   @SWG\Parameter(in="path", name="id", type="integer", description="The unique identifier of the book."),
     *   @SWG\Parameter(in="body", name="book", @Model(type=Book::class, groups={"books_send"},),),
     *   @SWG\Response(
     *     response=200,
     *     description="A representation of the book resource updated.",
     *     @Model(type=Book::class, groups={"books"},),
     *   ),
     *   @SWG\Response(
     *     response=400,
     *     description="A 'Bad Request' error response, if the request body is not correctly formatted.",
     *   ),
     *   @SWG\Response(response=401, description="An 'Unauthorized' error response, if the user is not authenticated.",),
     *   @SWG\Response(response=404, description="A 'Not Found' error response, if the resource does not exist.",),
     * )
     */
    public function updateAction(int $id, Request $request)
    {
        /** @var Book|null $book */
        $book = $this->getBookIfExists($id);

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
     *   tags={"Books"},
     *   summary="Remove a book",
     *   description="Removes a book resource from the system.
                      <strong>Requires admin authorization.</strong>",
     *   produces={"text/html"},
     *   @SWG\Parameter(in="path", name="id", type="integer", description="The unique identifier of the book."),
     *   @SWG\Response(response=204, description="Indicates that the resource is not present on the system.",),
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
        return $this->createApiResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Book $book
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/books/{id}/authors", name="api_books_authors_list")
     *
     * @SWG\Get(
     *   tags={"Books"},
     *   summary="Retrieve a book's authors",
     *   description="Retrieves a collection of all of the book's authors.",
     *   produces={"application/hal+json", "application/hal+xml"},
     *   @SWG\Parameter(in="path", name="id", type="integer", description="The unique identifier of the book."),
     *   @SWG\Parameter(in="query", name="format", type="string", enum={"xml", "json"}),
     *   @SWG\Response(
     *     response=200,
     *     description="A collection of the book's authors.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="items", type="array", @Model(type=Author::class, groups={"books"},),),
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
     *   @SWG\Response(response=404, description="A 'Not Found' error response, if the resource does not exist.",),
     * )
     */
    public function authorsListAction(Book $book, Request $request)
    {
        $qb = $this->getAuthorRepository()
            ->createQueryBuilderForBook($book);
        $paginatedCollection = $this->paginationFactory->createCollection(
            $qb,
            $request,
            'api_books_authors_list',
            [
                'id' => $book->getId(),
            ]
        );

        return $this->createApiPaginationResponse($paginatedCollection, $request);
    }

    /**
     * @param Book $book
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/books/{id}/reviews", name="api_books_reviews_list")
     *
     * @SWG\Get(
     *   tags={"Books"},
     *   summary="Retrieves a book's reviews",
     *   description="Retrieves a collection of all reviews made for this book.",
     *   produces={"application/hal+json", "application/hal+xml"},
     *   @SWG\Parameter(in="path", name="id", type="integer", description="The unique identifier of the book resource."),
     *   @SWG\Parameter(in="query", name="format", type="string", enum={"xml", "json"}),
     *   @SWG\Response(
     *     response=200,
     *     description="A collection of the book's reviews.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="items", type="array", @Model(type=Review::class, groups={"books"},),),
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
     *   @SWG\Response(response=404, description="A 'Not Found' error response, if the resource does not exist.",),
     * )
     */
    public function reviewsListAction(Book $book, Request $request)
    {
        $qb = $this->getReviewRepository()
            ->createQueryBuilderForBook($book);
        $paginatedCollection = $this->paginationFactory->createCollection(
            $qb,
            $request,
            'api_books_reviews_list',
            [
                'id' => $book->getId(),
            ]
        );

        return $this->createApiPaginationResponse($paginatedCollection, $request);
    }

    /**
     * @param Book $book
     * @param Request $request
     *
     * @return Response
     *
     * @Rest\Post("/books/{id}/reviews")
     *
     * @Security("is_granted('ROLE_USER')")
     *
     * @SWG\Post(
     *   tags={"Books"},
     *   summary="Create a new review",
     *   description="Creates a new review resource assigned to the specified book resource.
                      <strong>Requires user authorization.</strong>",
     *   produces={"application/hal+json"},
     *   @SWG\Parameter(in="path", name="id", type="integer", description="The unique identifier of the book."),
     *   @SWG\Parameter(in="body", name="review", @Model(type=Review::class, groups={"reviews_send"}),),
     *   @SWG\Response(
     *     response=201,
     *     description="Returns a respresentation of the review created.",
     *     @SWG\Schema(@Model(type=Review::class, groups={"reviews"},),),
     *   ),
     *   @SWG\Response(
     *     response=400,
     *     description="A 'Bad Request' error response, if the request body is not correctly formatted,
                        or if the book id in the post body does not match that in the path.",
     *   ),
     *   @SWG\Response(response=401, description="An 'Unauthorized' error response, if the user is not authenticated.",),
     *   @SWG\Response(response=404, description="A 'Not Found' error response, if the (book) resource does not exist.",),
     * )
     */
    public function reviewsNewAction(Book $book, Request $request)
    {
        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);
        $this->processForm($request, $form);
        if ($review->getBook() !== $book) {
            $apiProblem = new ApiProblem(400, ApiProblem::TYPE_NON_MATCHING_PATH_BODY);
            throw new ApiProblemException($apiProblem);
        }
        return $this->forward('BookRaterRaterBundle:Api\Review:new');
    }

    /**
     * @param Book $book
     */
    private function persistBook(Book $book): void
    {
        $book->setLastModified(new \DateTime());
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

    /**
     * @param int $id
     * @return Book
     */
    public function getBookIfExists(int $id): Book
    {
        $book = $this->getBookRepository()->find($id);

        if (!$book) {
            $this->throwBookNotFoundException($id);
        }
        return $book;
    }

    protected function getGroups()
    {
        return self::GROUPS;
    }

}