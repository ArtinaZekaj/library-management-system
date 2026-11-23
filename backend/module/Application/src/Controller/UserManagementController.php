<?php

namespace Application\Controller;

use Application\Service\UserService;
use DomainException;
use InvalidArgumentException;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Throwable;

class UserManagementController extends AbstractActionController
{
    public function __construct(
        private UserService $userService
    ) {}

    private function checkAccess(): ?JsonModel
    {
        /** @var Response $response */
        $response = $this->getResponse();

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

        return null;
    }

    private function getJsonData(): array
    {
        /** @var Request $request */
        $request = $this->getRequest();

        $data = json_decode(
            $request->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (!is_array($data) || array_is_list($data)) {
            throw new InvalidArgumentException(
                'Invalid user data'
            );
        }

        return $data;
    }

    private function validateCreate(array $data): void
    {
        foreach (['name', 'email', 'password', 'role'] as $field) {
            if (
                !isset($data[$field]) ||
                !is_string($data[$field]) ||
                trim($data[$field]) === ''
            ) {
                throw new InvalidArgumentException(
                    'Invalid or missing field: ' . $field
                );
            }
        }

        if (count($data) !== 4) {
            throw new InvalidArgumentException(
                'Unknown user field'
            );
        }

        if (
            mb_strlen(trim($data['name'])) > 255 ||
            strlen($data['email']) > 255 ||
            !filter_var($data['email'], FILTER_VALIDATE_EMAIL)
        ) {
            throw new InvalidArgumentException(
                'Invalid name or email'
            );
        }

        if (!in_array(
            $data['role'],
            ['student', 'operator'],
            true
        )) {
            throw new InvalidArgumentException(
                'Invalid role'
            );
        }
    }

    public function indexAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();

        /** @var Response $response */
        $response = $this->getResponse();

        if (!$request->isGet() && !$request->isPost()) {
            $response->setStatusCode(405);

            return new JsonModel([
                'success' => false,
                'message' => 'Method not allowed',
            ]);
        }

        $accessError = $this->checkAccess();

        if ($accessError !== null) {
            return $accessError;
        }

        try {
            if ($request->isGet()) {
                return new JsonModel([
                    'success' => true,
                    'users' => $this->userService->listUsers(),
                ]);
            }

            $data = $this->getJsonData();
            $this->validateCreate($data);

            $user = $this->userService->addUser($data);

            $response->setStatusCode(201);

            return new JsonModel([
                'success' => true,
                'message' => 'User created successfully',
                'user' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole(),
                ],
            ]);
        } catch (DomainException $e) {
            $response->setStatusCode(
                $e->getCode() === 409 ? 409 : 400
            );

            return new JsonModel([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (\JsonException | InvalidArgumentException $e) {
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => 'Invalid user data',
            ]);
        } catch (Throwable $e) {
            $response->setStatusCode(500);

            return new JsonModel([
                'success' => false,
                'message' => 'Unable to process users',
            ]);
        }
    }

    public function detailAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();

        /** @var Response $response */
        $response = $this->getResponse();

        if (!$request->isPut() && !$request->isDelete()) {
            $response->setStatusCode(405);

            return new JsonModel([
                'success' => false,
                'message' => 'Method not allowed',
            ]);
        }

        $accessError = $this->checkAccess();

        if ($accessError !== null) {
            return $accessError;
        }

        $id = (int) $this->params()->fromRoute('id');

        try {
            if ($request->isPut()) {
                $data = $this->getJsonData();

                $user = $this->userService
                    ->updateUser($id, $data);

                return new JsonModel([
                    'success' => true,
                    'message' => 'User updated successfully',
                    'user' => $user,
                ]);
            }

            $this->userService->deleteUser($id);

            return new JsonModel([
                'success' => true,
                'message' => 'User deleted successfully',
            ]);
        } catch (DomainException $e) {
            $status = (int) $e->getCode();

            $response->setStatusCode(
                in_array($status, [403, 404, 409], true)
                    ? $status
                    : 400
            );

            return new JsonModel([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (\JsonException | InvalidArgumentException $e) {
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => 'Invalid user data',
            ]);
        } catch (Throwable $e) {
            $response->setStatusCode(500);

            return new JsonModel([
                'success' => false,
                'message' => 'Unable to process user',
            ]);
        }
    }
}
