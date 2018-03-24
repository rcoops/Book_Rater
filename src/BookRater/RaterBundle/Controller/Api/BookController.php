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
     *                 @Model(type=Book::class)
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

//        $bookUrl = $this->generateUrl(
//            'api_books_show', [
//                'lastName' => $book->getLastName(),
//                'firstName' => $book->getFirstName(),
//            ]
//        );
//        $response->headers->set('Location', $bookUrl);
        return $response;
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

    protected function getGroups()
    {
        return self::GROUPS;
    }

}