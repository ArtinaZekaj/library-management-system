<?php

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Application\Service\AuthorizationService;
use Laminas\Http\Response;

class BookController extends AbstractActionController
{
    private $authorizationService;

    public function __construct(AuthorizationService $authorizationService)
    {
        $this->authorizationService = $authorizationService;
    }

    public function indexAction()
{
    $permission = $this->authorizationService->checkPermission('books.view');

    if ($permission === 'unauthenticated') {
        /** @var Response $response */
        $response = $this->getResponse();
        $response->setStatusCode(401);

        return new JsonModel([
            'success' => false,
            'message' => 'You must be logged in',
        ]);
    }

    if ($permission === 'forbidden') {
        /** @var Response $response */
        $response = $this->getResponse();
        $response->setStatusCode(403);

        return new JsonModel([
            'success' => false,
            'message' => 'You do not have permission to view books',
        ]);
    }

    return new JsonModel([
        'success' => true,
        'message' => 'You have permission to view books',
    ]);
}
}