<?php

namespace BookRater\RaterBundle\Controller;

use BookRater\RaterBundle\Entity\Author;
use BookRater\RaterBundle\Form\AuthorType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ObjectManager;

class AuthorController extends Controller
{

    /**
     * @var ObjectManager
     */
    private $entityManager;

    /**
     * @var \BookRater\RaterBundle\Repository\AuthorRepository|\Doctrine\ORM\EntityRepository
     */
    private $entryRepository;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->entryRepository = $entityManager->getRepository('BookRaterRaterBundle:Author');
    }

    public function viewAction(int $id)
    {
        $author = $this->find($id);

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

            return $this->redirect($this->generateUrl('bookrater_author_edit', ['id' => $author->getId()]));
        }
        return $this->render('BookRaterRaterBundle:Author:create.html.twig', ['form' => $form->createView()]);
    }

    public function editAction(int $id, Request $request)
    {

        $author = $this->find($id);

        $form = $this->createForm(AuthorType::class, $author, [
            'action' => $request->getUri()
        ]);
        $form->handleRequest($request);

        if($form->isValid()) {
            $this->entityManager->flush();
        }
        return $this->render('BookRaterRaterBundle:Author:edit.html.twig',
            ['form' => $form->createView(), 'author' => $author]);
    }

    private function persistAndFlush(Author $author)
    {
    }

    private function find(int $id)
    {
        return $this->entryRepository->find($id);
    }

}
