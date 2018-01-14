<?php

namespace BookRater\RaterBundle\Controller;

use BookRater\RaterBundle\Entity\Contact;
use BookRater\RaterBundle\Form\ContactType;
use DateTime;
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

    public function listAction()
    {
//        $this->contactRepository->createQueryBuilder('message')
//            ->addOrderBy('message.created', 'DESC');
//
//        return new Response();
    }

    public function createAction(Request $request)
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact, ['action' => $request->getUri()]);
        # Handle form response
        $form->handleRequest($request);

        if ($form->isValid()) {
            $contact->setUser($this->getUser());
            $contact->setCreated(new DateTime());

            $this->entityManager->persist($contact);
            $this->entityManager->flush();

            $this->sendResponse($contact);

            return $this->render('BookRaterRaterBundle:Contact:success.html.twig');
        }
        return $this->render('BookRaterRaterBundle:Contact:contact.html.twig', ['form' => $form->createView()]);
    }

    private function sendResponse(Contact $contact)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($contact->getSubject())
            ->setFrom('noreply@bookrater.co.uk')
            ->setTo($contact->getUser()->getEmailCanonical())
            ->setBody($this->renderView('@BookRaterRater/Contact/email.html.twig',[
                'name' => $contact->getUser()->getUsername(),
                'message' => $contact->getMessage()
            ]),'text/html');

        $this->get('mailer')->send($message);
    }

}