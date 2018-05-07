<?php

namespace BookRater\RaterBundle\Controller\Web;

use BookRater\RaterBundle\Entity\Message;
use BookRater\RaterBundle\Form\MessageType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class MessageController extends EntityController
{

    /**
     * @var \BookRater\RaterBundle\Repository\MessageRepository|\Doctrine\ORM\EntityRepository
     */
    protected $messageRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
        $this->messageRepository = $this->entityManager->getRepository('BookRaterRaterBundle:Message');
    }

    public function listAction(Request $request)
    {
        $query = $this->messageRepository->findUnreadMessages();

        $pagination = $this->paginate($query, $request);

        return $this->render('@BookRaterRater/Message/list.html.twig',[
            'pagination' => $pagination
        ]);
    }

    public function createAction(Request $request)
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message, ['action' => $request->getUri()]);
        # Handle form response
        $form->handleRequest($request);

        if ($form->isValid()) {
            $lastModified = new DateTime();
            $message->setUser($this->getUser());
            $message->setCreated($lastModified);
            $message->setLastModified($lastModified);
            $message->setIsRead();

            $this->entityManager->persist($message);
            $this->entityManager->flush();

            $this->sendResponse($message);

            return $this->render('BookRaterRaterBundle:Message:success.html.twig');
        }
        return $this->render('BookRaterRaterBundle:Message:contact.html.twig', ['form' => $form->createView()]);
    }

    private function sendResponse(Message $contact)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($contact->getSubject())
            ->setFrom('noreply@bookrater.co.uk')
            ->setTo($contact->getUser()->getEmailCanonical())
            ->setBody($this->renderView('@BookRaterRater/Message/email.html.twig',[
                'name' => $contact->getUser()->getUsername(),
                'message' => $contact->getMessage()
            ]),'text/html');

        $this->get('mailer')->send($message);
    }

}
