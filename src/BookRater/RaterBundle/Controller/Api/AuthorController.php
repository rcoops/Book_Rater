<?php

namespace BookRater\RaterBundle\Controller\Api;

use BookRater\RaterBundle\Form\Api\AuthorType;
use BookRater\RaterBundle\Form\Api\Update\UpdateAuthorType;
use BookRater\RaterBundle\Pagination\PaginationFactory;
use Doctrine\ORM\NonUniqueResultException;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use BookRater\RaterBundle\Entity\Author;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorController extends BaseApiController
{

    private const GROUPS = ['authors'];

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
        $author = new Author();
        $form = $this->createForm(AuthorType::class, $author);
        $this->processForm($request, $form);

        if (!$form->isValid()) {
            $this->throwApiProblemValidationException($form);
        }

        $this->persistAuthor($author);
        $response = $this->createApiResponse($author, 201);

        $authorUrl = $this->generateUrl(
            'api_authors_show', [
                'lastName' => $author->getLastName(),
                'firstName' => $author->getFirstName(),
            ]
        );
        $response->headers->set('Location', $authorUrl);
        return $response;
    }

    /**
     * @param string $lastName
     * @param string $firstName
     *
     * @return Response
     *
     * @Rest\Get("/authors/{lastName}/{firstName}", name="api_authors_show")
     */
    public function showAction(string $lastName, string $firstName)
    {
        $author = $this->getAuthorRepository()->findOneBy([
            'lastName' => $lastName,
            'firstName' => $firstName
        ]);

        if (!$author) {
            $this->throwAuthorNotFoundException($lastName, $firstName);
        }

        $response = $this->createApiResponse($author);

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Rest\Get("/authors", name="api_authors_collection")
     */
    public function listAction(Request $request)
    {
        $filter = $request->query->get('filter');

        $qb = $this->getAuthorRepository()
            ->findAllQueryBuilder($filter);
        $paginatedCollection = $this->paginationFactory
            ->createCollection($qb, $request, 'api_authors_collection');

        $response = $this->createApiResponse($paginatedCollection, 200);

        return $response;
    }

    /**
     * @param string $lastName
     * @param string $firstName
     * @param Request $request
     *
     * @return Response
     *
     * @throws NonUniqueResultException
     *
     * @Route("/authors/{lastName}/{firstName}")
     * @Method({"PUT", "PATCH"})
     *
     * @Security("is_granted('ROLE_USER')")
     */
    public function updateAction(string $lastName, string $firstName, Request $request)
    {
        $author = $this->getAuthorRepository()
            ->findOneByName($lastName, $firstName);

        if (!$author) {
            $this->throwAuthorNotFoundException($lastName, $firstName);
        }
        $form = $this->createForm(UpdateAuthorType::class, $author);
        $this->processForm($request, $form);

        if (!$form->isValid()) {
            $this->throwApiProblemValidationException($form);
        }

        $this->persistAuthor($author);

        return $this->createApiResponse($author);
    }


    /**
     * @param string $lastName
     * @param string $firstName
     *
     * @return Response
     *
     * @throws NonUniqueResultException
     *
     * @Rest\Delete("/authors/{lastName}/{firstName}")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteAction(string $lastName, string $firstName)
    {
        $author = $this->getAuthorRepository()
            ->findOneByName($lastName, $firstName);

        if ($author) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($author);
            $em->flush();
        }

        // Doesn't matter if the author was there or not - because it isn't now which is what we wanted
        return $this->createApiResponse(null, 204);
    }

    /**
     * @param Author $author
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/authors/{lastName}/{firstName}/books", name="api_authors_books_list")
     */
    public function booksListAction(Author $author, Request $request)
    {
        $qb = $this->getBookRepository()
            ->createQueryBuilderForAuthor($author);
        $collection = $this->paginationFactory->createCollection(
            $qb,
            $request,
            'api_authors_books_list',
            [
                'lastName' => $author->getLastName(),
                'firstName' => $author->getFirstName(),
            ]
        );
        return $this->createApiResponse($collection);
    }

    /**
     * @param Author $author
     */
    private function persistAuthor(Author $author): void
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($author);
        foreach ($author->getBooksAuthored() as $book) {
            $book->addAuthor($author);
            $em->persist($book);
        }
        $em->flush();
    }

    /**
     * @param string $lastName
     * @param string $firstName
     */
    private function throwAuthorNotFoundException(string $lastName, string $firstName): void
    {
        throw $this->createNotFoundException(sprintf(
            'No author found with last name: "%s" & first name: "%s"',
            $lastName, $firstName
        ));
    }

    protected function getGroups()
    {
        return self::GROUPS;
    }

}
