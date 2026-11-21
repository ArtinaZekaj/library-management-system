<?php

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Application\Service\UserService;
use Application\Service\RbacService;

class AuthController extends AbstractActionController
{
    private $userService;
    private $rbacService;

    public function __construct(UserService $userService, RbacService $rbacService)
    {
        $this->userService = $userService;
        $this->rbacService = $rbacService;
    }
    public function loginAction()
    {

        /** @var \Laminas\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();

        if (!$request->isPost()) {
            /** @var \Laminas\Http\PhpEnvironment\Response $response */
            $response = $this->getResponse();
            $response->setStatusCode(405);

            return new JsonModel(
                [
                    'success' => false,
                    'message' => 'Invalid request method'
                ]
            );
        }

        $content = $request->getContent();

        try {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            /** @var \Laminas\Http\PhpEnvironment\Response $response */
            $response = $this->getResponse();
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => 'Invalid JSON data'
            ]);
        }

        if (!is_array($data)) {
            /** @var \Laminas\Http\PhpEnvironment\Response $response */
            $response = $this->getResponse();
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => 'Invalid request data'
            ]);
        }

        if (!isset($data['email'], $data['password'])) {
            /** @var \Laminas\Http\PhpEnvironment\Response $response */
            $response = $this->getResponse();
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => 'Email and password are required'
            ]);
        }

        if (!is_string($data['email']) || !is_string($data['password'])) {
            /** @var \Laminas\Http\PhpEnvironment\Response $response */
            $response = $this->getResponse();
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => 'Email and password must be strings'
            ]);
        }

        if (trim($data['email']) === '' || trim($data['password']) === '') {
            /** @var \Laminas\Http\PhpEnvironment\Response $response */
            $response = $this->getResponse();
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => 'Email and password cannot be empty'
            ]);
        }

        $login = $this->userService->login($data['email'], $data['password']);

        if (!$login) {
            /** @var \Laminas\Http\PhpEnvironment\Response $response */
            $response = $this->getResponse();
            $response->setStatusCode(401);

            return new JsonModel([
                'success' => false,
                'message' => 'Invalid email or password'
            ]);
        }

        return new JsonModel([
            'success' => true,
            'message' => 'Login successful'
        ]);
    }

    public function meAction()
    {
        /** @var \Laminas\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();

        if (!$request->isGet()) {
            /** @var \Laminas\Http\PhpEnvironment\Response $response */
            $response = $this->getResponse();
            $response->setStatusCode(405);

            return new JsonModel([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $user = $this->userService->getCurrentUser();

        if (!$user) {
            /** @var \Laminas\Http\PhpEnvironment\Response $response */
            $response = $this->getResponse();
            $response->setStatusCode(401);

            return new JsonModel([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
        }

        return new JsonModel([
            'success' => true,
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'role' => $user->getRole()
            ]
        ]);
    }

    public function logoutAction()
    {
        /** @var \Laminas\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();

        if (!$request->isPost()) {
            /** @var \Laminas\Http\PhpEnvironment\Response $response */
            $response = $this->getResponse();
            $response->setStatusCode(405);

            return new JsonModel([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $this->userService->logout();

        return new JsonModel([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }
}
