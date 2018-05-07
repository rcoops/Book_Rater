<?php

namespace BookRater\RaterBundle\Controller\Web;

use BookRater\RaterBundle\Entity\Author;
use BookRater\RaterBundle\Form\AuthorType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class AuthorController extends EntityController
{

    /**
     * @var \BookRater\RaterBundle\Repository\AuthorRepository|\Doctrine\ORM\EntityRepository
     */
    protected $authorRepository;

    /**
     * AuthorController constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
        $this->authorRepository = $this->entityManager->getRepository('BookRaterRaterBundle:Author');
    }

    public function listAction(Request $request)
    {
        $filter = $request->query->get('filter');
        $query = $this->authorRepository->findAllByFilter($filter);

        $pagination = $this->paginate($query, $request);

        // parameters to template
        return $this->render('@BookRaterRater/Author/list.html.twig',[
            'pagination' => $pagination
        ]);
    }

    public function viewAction(string $lastName, string $firstName)
    {
        $author = $this->authorRepository->findOneBy([
            'lastName' => $lastName,
            'firstName' => $firstName
        ]);

        return $this->render('BookRaterRaterBundle:Author:view.html.twig', ['author' => $author]);
    }

    public function createAction(Request $request)
    {
        $author = new Author();
        $form = $this->createForm(AuthorType::class, $author, ['action' => $request->getUri()]);

        $form->handleRequest($request);

        if ($form->isValid())
        {
            $author->setLastModified(new \DateTime());
            $this->entityManager->persist($author);
            $this->entityManager->flush();

            return $this->redirect($this->generateUrl('bookrater_author_view', [
                'lastName' => $author->getLastName(),
                'firstName' => $author->getFirstName()
            ]));
        }
        return $this->render('BookRaterRaterBundle:Author:create.html.twig', ['form' => $form->createView()]);
    }

    public function editAction(string $lastName, string $firstName, Request $request)
    {
        /** @var Author $author */
        $author = $this->authorRepository->findOneBy([
            'lastName' => $lastName,
            'firstName' => $firstName
        ]);

        $form = $this->createForm(AuthorType::class, $author, [
            'action' => $request->getUri()
        ]);
        $form->handleRequest($request);

        if($form->isValid()) {
            $lastModified = new \DateTime();
            $author->setLastModified($lastModified);
            $this->entityManager->flush();

            return $this->redirect($this->generateUrl('bookrater_author_view', [
                'lastName' => $author->getLastName(),
                'firstName' => $author->getFirstName()
            ]));
        }
        return $this->render('BookRaterRaterBundle:Author:edit.html.twig',
            ['form' => $form->createView(), 'author' => $author]);
    }

}
