<?php

namespace Application\Controller;

use Application\Service\SystemLogService;
use Application\Service\UserService;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Throwable;

class SystemLogController extends AbstractActionController
{
    public function __construct(
        private SystemLogService $systemLogService,
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

        $user = $this->userService->getCurrentUser();

        if ($user === null) {
            $response->setStatusCode(401);

            return new JsonModel([
                'success' => false,
                'message' => 'You must be logged in',
            ]);
        }

        if ($user->getRole() !== 'super_admin') {
            $response->setStatusCode(403);

            return new JsonModel([
                'success' => false,
                'message' => 'Access denied',
            ]);
        }

        try {
            return new JsonModel([
                'success' => true,
                'logs' => $this->systemLogService->getLogs(),
            ]);
        } catch (Throwable $e) {
            $response->setStatusCode(500);

            return new JsonModel([
                'success' => false,
                'message' => 'Unable to retrieve system logs',
            ]);
        }
    }
}
