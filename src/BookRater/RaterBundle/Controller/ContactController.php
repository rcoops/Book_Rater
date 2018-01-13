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

            $this->entityManager->persist($contact);
            $this->entityManager->flush();

            $this->sendResponse($contact);

            return $this->render('BookRaterRaterBundle:Contact:success.html.twig');
        }
        return $this->render('BookRaterRaterBundle:Contact:contact.html.twig', ['form' => $form->createView()]);
    }

    private function sendResponse(Contact $contact)
    {
        $mailer = \Swift_Mailer::newInstance(
            \Swift_SmtpTransport::newInstance()
                ->setUsername($this->getParameter('mailer_user'))
                ->setPassword($this->getParameter('mailer_password'))
                ->setHost($this->getParameter('mailer_host'))
                ->setPort($this->getParameter('mailer_port'))
                ->setEncryption('tls')
        );
        $message = \Swift_Message::newInstance()
            ->setSubject($contact->getSubject())
            ->setFrom('noreply@bookrater.co.uk')
            ->setTo('rcoops84@hotmail.com')//$contact->getUser()->getEmailCanonical())
            ->setBody($this->renderView('@BookRaterRater/Contact/email.html.twig',[
                'name' => $contact->getUser()->getUsername()
            ]),'text/html');

        $this->get('mailer')->send($message);
    }

}