<?php

namespace BookRater\RaterBundle\Controller;

use BookRater\RaterBundle\Entity\Author;
use BookRater\RaterBundle\Form\AuthorType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

class AuthorController extends EntityController
{

    /**
     * @var \BookRater\RaterBundle\Repository\AuthorRepository|\Doctrine\ORM\EntityRepository
     */
    protected $authorRepository;

    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager);
        $this->authorRepository = $this->entityManager->getRepository('BookRaterRaterBundle:Author');
    }

    public function listAction(Request $request)
    {
        $query = $this->authorRepository
            ->findAllWhereNameLike("");

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
            $this->entityManager->persist($author);
            $this->entityManager->flush();

            return $this->redirect($this->generateUrl('bookrater_author_view', ['id' => $author->getId()]));
        }
        return $this->render('BookRaterRaterBundle:Author:create.html.twig', ['form' => $form->createView()]);
    }

    public function editAction(int $id, Request $request)
    {
        $author = $this->authorRepository->find($id);

        $form = $this->createForm(AuthorType::class, $author, [
            'action' => $request->getUri()
        ]);
        $form->handleRequest($request);

        if($form->isValid()) {
            $this->entityManager->flush();

            return $this->redirect($this->generateUrl('bookrater_author_view', ['id' => $author->getId()]));
        }
        return $this->render('BookRaterRaterBundle:Author:edit.html.twig',
            ['form' => $form->createView(), 'author' => $author]);
    }

}
