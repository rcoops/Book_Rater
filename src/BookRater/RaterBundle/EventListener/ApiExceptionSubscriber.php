<?php

namespace BookRater\RaterBundle\EventListener;

use BookRater\RaterBundle\Api\ApiProblem;
use BookRater\RaterBundle\Api\ApiProblemException;
use BookRater\RaterBundle\Api\ResponseFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionSubscriber implements EventSubscriberInterface
{

    private $debug;

    private $responseFactory;

    private $logger;

    /**
     * @param bool $debug
     * @param ResponseFactory $responseFactory
     * @param LoggerInterface $logger
     */
    public function __construct(bool $debug, ResponseFactory $responseFactory, LoggerInterface $logger)
    {
        $this->debug = $debug;
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // only reply to /api URLs
        if (strpos($event->getRequest()->getPathInfo(), '/api') !== 0) {
            return;
        }

        $e = $event->getException();

        $statusCode = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

        // allow 500 errors to be thrown
        if ($this->debug && $statusCode >= 500) {
            return;
        }

        $this->logException($e);

        if ($e instanceof ApiProblemException) {
            $apiProblem = $e->getApiProblem();
        } else {
            $apiProblem = new ApiProblem($statusCode);

            /*
             * If it's an HttpException message (e.g. for 404, 403),
             * it's probably safe for a user. Otherwise, it could be
             * some sensitive low-level exception, which they shouldn't see
             */
            if ($e instanceof HttpExceptionInterface) {
                $apiProblem->set('detail', $e->getMessage());
            }
        }

        $response = $this->responseFactory->createResponse($apiProblem);

        $event->setResponse($response);
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::EXCEPTION => 'onKernelException'];
    }

    /**
     * Adapted from the core Symfony exception handling in ExceptionListener
     *
     * @param \Exception $exception
     */
    private function logException(\Exception $exception)
    {
        $message = sprintf('Uncaught PHP Exception %s: "%s" at %s line %s', get_class($exception), $exception->getMessage(), $exception->getFile(), $exception->getLine());
        $isCritical = !$exception instanceof HttpExceptionInterface || $exception->getStatusCode() >= 500;
        $context = ['exception' => $exception];
        if ($isCritical) {
            $this->logger->critical($message, $context);
        } else {
            $this->logger->error($message, $context);
        }
    }

}
