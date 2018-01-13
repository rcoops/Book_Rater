<?php

namespace BookRater\RaterBundle\Controller;

use BookRater\RaterBundle\Entity\Contact;
use BookRater\RaterBundle\Form\ContactType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends EntityController
{

    /**
     * @var \BookRater\RaterBundle\Repository\ContactRepository|\Doctrine\ORM\EntityRepository
     */
    protected $contactRepository;

    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager);
        $this->contactRepository = $this->entityManager->getRepository('BookRaterRaterBundle:Contact');
    }

    public function createAction(Request $request)
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact, ['action' => $request->getUri()]);
        # Handle form response
        $form->handleRequest($request);

        if ($form->isValid()) {
            $contact->setUser($this->getUser());
            dump($contact); die;
            $this->entityManager->persist($contact);
            $this->entityManager->flush();

            return new Response('<html><body>success!</body></html>');
        }
        return $this->render('BookRaterRaterBundle:Contact:contact.html.twig', ['form' => $form->createView()]);
    }

}