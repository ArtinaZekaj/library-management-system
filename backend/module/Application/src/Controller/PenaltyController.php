<?php

namespace Application\Controller;

use Application\Service\PenaltyService;
use Application\Service\UserService;
use DomainException;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Throwable;

class PenaltyController extends AbstractActionController
{
    public function __construct(
        private PenaltyService $penaltyService,
        private UserService $userService
    ) {}

    public function payAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();

        /** @var Response $response */
        $response = $this->getResponse();

        if (!$request->isPost()) {
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

        if (!in_array(
            $currentUser->getRole(),
            ['student', 'operator', 'super_admin'],
            true
        )) {
            $response->setStatusCode(403);

            return new JsonModel([
                'success' => false,
                'message' => 'Access denied',
            ]);
        }

        $penaltyId = filter_var(
            $this->params()->fromRoute('id'),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($penaltyId === false) {
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => 'Invalid penalty ID',
            ]);
        }

        try {
            $data = json_decode(
                $request->getContent(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => 'Invalid JSON data',
            ]);
        }

        if (
            !is_array($data) ||
            ($data['payment_confirmed'] ?? null) !== true
        ) {
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => 'Payment confirmation required',
            ]);
        }

        try {
            $result = $this->penaltyService->payPenalty(
                $currentUser,
                $penaltyId
            );

            return new JsonModel([
                'success' => true,
                'message' => 'Penalty payment recorded successfully',
                'penalty' => $result,
            ]);
        } catch (DomainException $e) {
            $status = $e->getCode();

            if (!in_array($status, [403, 404, 409], true)) {
                $status = 400;
            }

            $response->setStatusCode($status);

            return new JsonModel([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (Throwable $e) {
            $response->setStatusCode(500);

            return new JsonModel([
                'success' => false,
                'message' => 'Unable to record penalty payment',
            ]);
        }
    }
}
