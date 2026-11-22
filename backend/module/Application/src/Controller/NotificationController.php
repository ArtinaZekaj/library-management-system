<?php

namespace Application\Controller;

use Application\Service\NotificationService;
use Application\Service\UserService;
use DomainException;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Throwable;

class NotificationController extends AbstractActionController
{
    public function __construct(
        private NotificationService $notificationService,
        private UserService $userService
    ) {}

    public function indexAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();

        /** @var Response $response */
        $response = $this->getResponse();

        if (!$request->isGet()) {
            $response->setStatusCode(405);

            return new JsonModel([
                'success' => false,
                'message' => 'Method not allowed',
            ]);
        }

        $currentUser = $this->userService->getCurrentUser();

        if ($currentUser === null) {
            $response->setStatusCode(401);

            return new JsonModel([
                'success' => false,
                'message' => 'You must be logged in',
            ]);
        }

        try {
            $notifications = $this->notificationService
                ->getNotifications($currentUser);

            return new JsonModel([
                'success' => true,
                'notifications' => $notifications,
            ]);
        } catch (DomainException $e) {
            $response->setStatusCode(403);

            return new JsonModel([
                'success' => false,
                'message' => 'Access denied',
            ]);
        } catch (Throwable $e) {
            $response->setStatusCode(500);

            return new JsonModel([
                'success' => false,
                'message' => 'Unable to retrieve notifications',
            ]);
        }
    }
}
