<?php

namespace BookRater\RaterBundle\Controller\Api;

use BookRater\RaterBundle\Api\ApiProblem;
use BookRater\RaterBundle\Api\ApiProblemException;
use BookRater\RaterBundle\Pagination\PaginatedCollection;
use BookRater\RaterBundle\Pagination\PaginationFactory;
use BookRater\RaterBundle\Repository\ApiTokenRepository;
use BookRater\RaterBundle\Repository\AuthorRepository;
use BookRater\RaterBundle\Repository\BookRepository;
use BookRater\RaterBundle\Repository\MessageRepository;
use BookRater\RaterBundle\Repository\ReviewRepository;
use BookRater\RaterBundle\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Request;
use BookRater\RaterBundle\Entity\User;

abstract class BaseApiController extends Controller
{

    /** @var PaginationFactory */
    protected $paginationFactory;

    /**
     * @param PaginationFactory $paginationFactory
     */
    public function __construct(PaginationFactory $paginationFactory)
    {
        $this->paginationFactory = $paginationFactory;
    }

    /**
     * Is the current user logged in?
     *
     * @return boolean
     */
    public function isUserLoggedIn()
    {
        return $this->container->get('security.authorization_checker')
            ->isGranted('IS_AUTHENTICATED_FULLY');
    }

    /**
     * Logs this user into the system
     *
     * @param User $user
     */
    public function loginUser(User $user)
    {
        $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());

        $this->container->get('security.token_storage')->setToken($token);
    }

    public function addFlash($message, $positiveNotice = true)
    {
        /** @var Request $request */
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $noticeKey = $positiveNotice ? 'notice_happy' : 'notice_sad';
        /** @var Session $session */
        $session = $request->getSession();
        $session->getFlashbag()->add($noticeKey, $message);
    }

    /**
     * Used to find the fixtures user - I use it to cheat in the beginning
     *
     * @param string $username
     * @return User
     */
    public function findUserByUsername(string $username)
    {
        return $this->getUserRepository()
            ->findUserByUsername($username);
    }

    /**
     * @return UserRepository|ObjectRepository
     */
    protected function getUserRepository()
    {
        return $this->getDoctrine()
            ->getRepository('BookRaterRaterBundle:User');
    }

    /**
     * @return BookRepository|ObjectRepository
     */
    protected function getBookRepository()
    {
        return $this->getDoctrine()
            ->getRepository('BookRaterRaterBundle:Book');
    }

    /**
     * @return AuthorRepository|ObjectRepository
     */
    protected function getAuthorRepository()
    {
        return $this->getDoctrine()
            ->getRepository('BookRaterRaterBundle:Author');
    }

    /**
     * @return MessageRepository|ObjectRepository
     */
    protected function getMessageRepository()
    {
        return $this->getDoctrine()
            ->getRepository('BookRaterRaterBundle:Message');
    }

    /**
     * @return ReviewRepository|ObjectRepository
     */
    protected function getReviewRepository()
    {
        return $this->getDoctrine()
            ->getRepository('BookRaterRaterBundle:Review');
    }

    /**
     * @return ApiTokenRepository|ObjectRepository
     */
    protected function getApiTokenRepository()
    {
        return $this->getDoctrine()
            ->getRepository('BookRaterRaterBundle:ApiToken');
    }

    protected function createApiResponseWithGroups($data, $statusCode = Response::HTTP_OK, $format = 'json',
                                                   array $groups = [])
    {
        $json = $this->serialize($data, $format, $groups);

        $contentType = $format == 'xml' ? 'application/hal+xml' : 'application/hal+json';

        return new Response($json, $statusCode, ['Content-Type' => $contentType]);
    }

    protected function serialize($data, $format, array $groups = [])
    {
        $context = new SerializationContext();
        $context->setSerializeNull(true);

        $groups[] = 'Default';
        $context->setGroups($groups);

        return $this->container->get('jms_serializer')
            ->serialize($data, $format, $context);
    }

    protected function processForm(Request $request, FormInterface $form)
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            $apiProblem = new ApiProblem(
                Response::HTTP_BAD_REQUEST,
                ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT
            );

            throw new ApiProblemException($apiProblem);
        }

        $clearMissing = $request->getMethod() != 'PATCH';
        $form->submit($data, $clearMissing);
    }

    protected function getErrorsFromForm(FormInterface $form)
    {
        $errors = [];
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }

    protected function throwApiProblemValidationException(FormInterface $form)
    {
        $errors = $this->getErrorsFromForm($form);

        $apiProblem = new ApiProblem(
            Response::HTTP_BAD_REQUEST,
            ApiProblem::TYPE_VALIDATION_ERROR
        );
        $apiProblem->set('errors', $errors);

        throw new ApiProblemException($apiProblem);
    }

    protected abstract function getGroups();

    /**
     * @param $data
     * @param int $statusCode
     * @param string $format
     * @return Response
     */
    protected function createApiResponse($data, $statusCode = Response::HTTP_OK, $format = 'json')
    {
        return $this->createApiResponseWithGroups($data, $statusCode, $format, $this->getGroups());
    }

    /**
     * @param Response $response
     * @param string $route
     * @param array $routeParams
     */
    protected function setLocationHeader(Response $response, string $route, array $routeParams): void
    {
        $response->headers->set('Location', $this->generateUrl($route, $routeParams));
    }

    /**
     * @param Request $request
     * @return mixed|string
     */
    private function getFormatFromRequest(Request $request)
    {
        $queryFormat = $request->query->get('format');
        return $queryFormat ? $queryFormat : 'json';
    }

    protected function createApiPaginationResponse(PaginatedCollection $paginatedCollection, Request $request)
    {
        return $this->createApiResponseUsingRequestedFormat($paginatedCollection, $request);
    }

    /**
     * @param mixed $data
     * @param Request $request
     * @param int $statusCode
     * @return Response
     */
    protected function createApiResponseUsingRequestedFormat($data, Request $request,
                                                             int $statusCode = Response::HTTP_OK)
    {
        return $this->createApiResponse($data, $statusCode, $this->getFormatFromRequest($request));
    }

}
