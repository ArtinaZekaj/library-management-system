<?php

namespace Application\Controller;

use Application\Service\SettingsService;
use Application\Service\UserService;
use InvalidArgumentException;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Throwable;

class SettingsController extends AbstractActionController
{
    public function __construct(
        private SettingsService $settingsService,
        private UserService $userService
    ) {}

    public function indexAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();

        /** @var Response $response */
        $response = $this->getResponse();

        if (!$request->isGet() && !$request->isPut()) {
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

        if ($currentUser->getRole() !== 'super_admin') {
            $response->setStatusCode(403);

            return new JsonModel([
                'success' => false,
                'message' => 'Access denied',
            ]);
        }

        try {
            if ($request->isGet()) {
                return new JsonModel([
                    'success' => true,
                    'settings' => $this->settingsService->getSettings(),
                ]);
            }

            $data = json_decode(
                $request->getContent(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            if (!is_array($data) || array_is_list($data)) {
                throw new InvalidArgumentException(
                    'Invalid settings data'
                );
            }

            $settings = $this->settingsService->updateSettings($data);

            return new JsonModel([
                'success' => true,
                'message' => 'Settings updated successfully',
                'settings' => $settings,
            ]);
        } catch (\JsonException | InvalidArgumentException $e) {
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => $e instanceof \JsonException
                    ? 'Invalid JSON'
                    : $e->getMessage(),
            ]);
        } catch (Throwable $e) {
            $response->setStatusCode(500);

            return new JsonModel([
                'success' => false,
                'message' => 'Unable to process settings',
            ]);
        }
    }

    public function borrowingFeeAction()
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

        if ($currentUser->getRole() !== 'student') {
            $response->setStatusCode(403);

            return new JsonModel([
                'success' => false,
                'message' => 'Access denied',
            ]);
        }

        try {
            $settings = $this->settingsService->getSettings();

            return new JsonModel([
                'success' => true,
                'borrow_fee' => $settings['borrow_fee'],
            ]);
        } catch (Throwable $e) {
            $response->setStatusCode(500);

            return new JsonModel([
                'success' => false,
                'message' => 'Unable to retrieve borrowing fee',
            ]);
        }
    }
}
