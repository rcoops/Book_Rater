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
     *   tags={"Authors"},
     *   summary="Create a new author",
     *   description="Creates a new author resource.
                      <strong>Requires user authorization.</strong>",
     *   @SWG\Parameter(in="body", name="author", @Model(type=Author::class, groups={"authors"},),),
     *   @SWG\Response(
     *     response=201,
     *     description="A representation of the author resource created.",
     *     @Model(type=Author::class, groups={"authors"},)
     *   ),
     *   @SWG\Response(response=401, description="An 'Unauthorized' error response, if the user is not authenticated.",),
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
        $this->setLocationHeader($response, 'api_authors_show', [
            'lastName' => $author->getLastName(),
            'firstName' => $author->getFirstName(),
        ]);
        return $response;
    }

    /**
     * @param string $lastName
     * @param string $firstName
     *
     * @return Response
     *
     * @Rest\Get("/authors/{lastName}/{firstName}", name="api_authors_show")
     *
     * @SWG\Get(
     *   tags={"Authors"},
     *   summary="Retrieve a single author",
     *   description="Retrieves a representation of the author resource queried for.",
     *   @SWG\Parameter(in="path", name="lastName", type="string", description="The author's last name."),
     *   @SWG\Parameter(in="path", name="firstName", type="string", description="The author's first name."),
     *   @SWG\Response(
     *     response=200,
     *     description="A representation of the author resource queried for.",
     *     @Model(type=Author::class, groups={"authors"},)
     *   ),
     *   @SWG\Response(response=404, description="A 'Not Found' error response, if the resource does not exist.",),
     * )
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
        $this->setLocationHeader($response, 'api_authors_show', [
            'lastName' => $author->getLastName(),
            'firstName' => $author->getFirstName(),
        ]);

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Rest\Get("/authors", name="api_authors_collection")
     *
     * @SWG\Get(
     *   tags={"Authors"},
     *   summary="List a collection of authors",
     *   description="Retrieves a (filtered) collection of author resources.",
     *   @SWG\Parameter(
     *     in="query",
     *     name="filter",
     *     type="string",
     *     description="An optional filter by first or last name."
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="A (filtered) collection of author resources.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(
     *         property="items",
     *         type="array",
     *         @Model(type=Author::class, groups={"authors"},),
     *       ),
     *     ),
     *   ),
     * )
     */
    public function listAction(Request $request)
    {
        $filter = $request->query->get('filter');

        $qb = $this->getAuthorRepository()
            ->findAllQueryBuilder($filter);
        $paginatedCollection = $this->paginationFactory
            ->createCollection($qb, $request, 'api_authors_collection');

        return $this->createApiResponse($paginatedCollection);
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
     *
     * @SWG\Put(
     *   tags={"Authors"},
     *   summary="Update an author",
     *   description="Updates an author, requiring a full representation of the resource.
                      <strong>Requires user authorization.</strong>",
     *   @SWG\Parameter(in="path", name="lastName", type="string", description="The author's last name."),
     *   @SWG\Parameter(in="path", name="firstName", type="string", description="The author's first name."),
     *   @SWG\Parameter(in="body", name="author", @Model(type=Author::class, groups={"authors"},),),
     *   @SWG\Response(
     *     response=200,
     *     description="A representation of the author resource updated.",
     *     @Model(type=Author::class, groups={"authors"},),
     *   ),
     *   @SWG\Response(response=401, description="An 'Unauthorized' error response, if the user is not authenticated.",),
     *   @SWG\Response(response=404, description="A 'Not Found' error response, if the resource does not exist.",),
     * )
     * @SWG\Patch(
     *   tags={"Authors"},
     *   summary="Update part(s) of an author",
     *   description="Updates an author, requiring only a part representation of the resource.
                      <strong>Requires user authorization.</strong>",
     *   @SWG\Parameter(in="path", name="lastName", type="string", description="The author's last name."),
     *   @SWG\Parameter(in="path", name="firstName", type="string", description="The author's first name."),
     *   @SWG\Parameter(in="body", name="author", @Model(type=Author::class, groups={"authors"},),),
     *   @SWG\Response(
     *     response=200,
     *     description="A representation of the author resource updated.",
     *     @Model(type=Author::class, groups={"authors"},),
     *   ),
     *   @SWG\Response(response=401, description="An 'Unauthorized' error response, if the user is not authenticated.",),
     *   @SWG\Response(response=404, description="A 'Not Found' error response, if the resource does not exist.",),
     * )
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

        $response = $this->createApiResponse($author);
        $this->setLocationHeader($response, 'api_authors_show', [
            'lastName' => $author->getLastName(),
            'firstName' => $author->getFirstName(),
        ]);

        return $response;
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
     *
     * @SWG\Delete(
     *   tags={"Authors"},
     *   summary="Remove an author",
     *   description="Removes an author resource from the system.
                      <strong>Requires admin authorization.</strong>",
     *   @SWG\Parameter(in="path", name="lastName", type="string", description="The author's last name."),
     *   @SWG\Parameter(in="path", name="firstName", type="string", description="The author's first name."),
     *   @SWG\Response(response=204, description="Indicates that the resource is not present on the system.",),
     * )
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
     *
     * @SWG\Get(
     *   tags={"Authors"},
     *   summary="Retrieve an author's books",
     *   description="Retrieves a collection of all of the books written by an author.",
     *   @SWG\Parameter(in="path", name="id", type="integer", description="The unique identifier of the author."),
     *   @SWG\Response(
     *     response=200,
     *     description="A collection of all of the books written by an author.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="items", type="array", @Model(type=Author::class, groups={"authors"},),),
     *     ),
     *   ),
     *   @SWG\Response(response=404, description="A 'Not Found' error response, if the resource does not exist.",),
     * )
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
