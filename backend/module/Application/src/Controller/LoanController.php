<?php

namespace Application\Controller;

use Application\Service\AuthorizationService;
use Application\Service\LoanService;
use Application\Service\UserService;
use DomainException;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Throwable;

class LoanController extends AbstractActionController
{
    public function __construct(
        private LoanService $loanService,
        private UserService $userService,
        private AuthorizationService $authorizationService
    ) {}

    public function indexAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();

        /** @var Response $response */
        $response = $this->getResponse();

        // List loans and borrowing history.
        if ($request->isGet()) {
            $currentUser = $this->userService->getCurrentUser();

            if ($currentUser === null) {
                $response->setStatusCode(401);

                return new JsonModel([
                    'success' => false,
                    'message' => 'You must be logged in',
                ]);
            }

            try {
                $loans = $this->loanService
                    ->listLoans($currentUser);

                return new JsonModel([
                    'success' => true,
                    'loans' => $loans,
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
                    'message' => 'Unable to retrieve loans',
                ]);
            }
        }

        if (!$request->isPost()) {
            $response->setStatusCode(405);

            return new JsonModel([
                'success' => false,
                'message' => 'Method not allowed',
            ]);
        }

        // Check permission to borrow books.
        $permission = $this->authorizationService
            ->checkPermission('loans.create');

        if ($permission === 'unauthenticated') {
            $response->setStatusCode(401);

            return new JsonModel([
                'success' => false,
                'message' => 'You must be logged in',
            ]);
        }

        if ($permission === 'forbidden') {
            $response->setStatusCode(403);

            return new JsonModel([
                'success' => false,
                'message' => 'You cannot borrow books',
            ]);
        }

        $student = $this->userService->getCurrentUser();

        if ($student === null) {
            $response->setStatusCode(401);

            return new JsonModel([
                'success' => false,
                'message' => 'You must be logged in',
            ]);
        }

        if ($student->getRole() !== 'student') {
            $response->setStatusCode(403);

            return new JsonModel([
                'success' => false,
                'message' => 'Only students can borrow books',
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

        if (!is_array($data)) {
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => 'Invalid request data',
            ]);
        }

        $bookId = filter_var(
            $data['book_id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($bookId === false) {
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => 'Invalid book ID',
            ]);
        }

        if (($data['fee_confirmed'] ?? null) !== true) {
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => 'Borrowing fee confirmation required',
            ]);
        }

        try {
            $result = $this->loanService
                ->borrowBook($student, $bookId);

            $response->setStatusCode(201);

            return new JsonModel([
                'success' => true,
                'message' => 'Book borrowed successfully',
                'loan' => $result,
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
                'message' => 'Unable to process borrowing request',
            ]);
        }
    }

    public function returnAction()
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

        $permission = $this->authorizationService
            ->checkPermission('loans.manage');

        if ($permission === 'unauthenticated') {
            $response->setStatusCode(401);

            return new JsonModel([
                'success' => false,
                'message' => 'You must be logged in',
            ]);
        }

        if ($permission !== 'allowed') {
            $response->setStatusCode(403);

            return new JsonModel([
                'success' => false,
                'message' => 'You cannot return books',
            ]);
        }

        $loanId = filter_var(
            $this->params()->fromRoute('id'),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($loanId === false) {
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => 'Invalid loan ID',
            ]);
        }

        try {
            $result = $this->loanService->returnBook($loanId);

            $response->setStatusCode(200);

            return new JsonModel([
                'success' => true,
                'message' => 'Book returned successfully',
                'loan' => $result,
            ]);
        } catch (DomainException $e) {
            $status = $e->getCode();

            if (!in_array($status, [404, 409], true)) {
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
                'message' => 'Unable to return book',
            ]);
        }
    }
}
