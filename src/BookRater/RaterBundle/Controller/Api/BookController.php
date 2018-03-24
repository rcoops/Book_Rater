<?php
/**
 * Created by PhpStorm.
 * User: rick
 * Date: 24/03/18
 * Time: 17:32
 */

namespace BookRater\RaterBundle\Controller\Api;


use BookRater\RaterBundle\Entity\Book;
use BookRater\RaterBundle\Pagination\PaginationFactory;
use Symfony\Component\HttpFoundation\Request;

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
     * @Rest\Post("/authors")
     *
     * @Security("is_granted('ROLE_USER')")
     *
     * @SWG\Post(
     *     responses={
     *         @SWG\Response(
     *             response=201,
     *             description="Creates a new author.",
     *             @SWG\Schema(
     *                 @Model(type=Author::class)
     *             )
     *         )
     *     }
     * )
     */
    public function newAction(Request $request)
    {
        $author = new Book();
        $form = $this->createForm(AuthorType::class, $author);
        $this->processForm($request, $form);

        if (!$form->isValid()) {
            $this->throwApiProblemValidationException($form);
        }

        $this->persistAuthor($author);
        $response = $this->createApiResponse($author, 201);

        $programmerUrl = $this->generateUrl(
            'api_authors_show', [
                'lastName' => $author->getLastName(),
                'firstName' => $author->getFirstName(),
            ]
        );
        $response->headers->set('Location', $programmerUrl);
        return $response;
    }

    /**
     * @param Book $book
     */
    private function persistAuthor(Book $book): void
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