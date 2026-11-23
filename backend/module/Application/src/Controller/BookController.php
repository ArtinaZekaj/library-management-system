<?php

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Application\Service\AuthorizationService;
use Laminas\Http\Response;
use Application\Entity\Book;
use Doctrine\ORM\EntityManager;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\StringLength;
use Laminas\Filter\StringTrim;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\Digits;
use Application\Service\UserService;
use Application\Service\SystemLogService;

class BookController extends AbstractActionController
{
    private AuthorizationService $authorizationService;
    private EntityManager $entityManager;

    public function __construct(
        AuthorizationService $authorizationService,
        EntityManager $entityManager,
        private UserService $userService,
        private SystemLogService $systemLogService
    ) {
        $this->authorizationService = $authorizationService;
        $this->entityManager = $entityManager;
    }

    public function indexAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            return $this->createAction();
        }

        if (!$request->isGet()) {
            /** @var Response $response */
            $response = $this->getResponse();
            $response->setStatusCode(405);

            return new JsonModel([
                'success' => false,
                'message' => 'Method not allowed',
            ]);
        }

        $permission = $this->authorizationService
            ->checkPermission('books.view');

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

        $books = $this->entityManager
            ->getRepository(Book::class)
            ->findAll();

        $data = [];

        foreach ($books as $book) {
            $data[] = [
                'id' => $book->getId(),
                'title' => $book->getTitle(),
                'author' => $book->getAuthor(),
                'isbn' => $book->getIsbn(),
                'category' => $book->getCategory(),
                'stock' => $book->getStock(),
                'status' => $book->getStatus(),
            ];
        }

        return new JsonModel([
            'success' => true,
            'books' => $data,
        ]);
    }

    public function viewAction()
    {
        $permission = $this->authorizationService
            ->checkPermission('books.view');

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
        /** @var Request $request */
        $request = $this->getRequest();

        if ($request->isPut()) {
            return $this->updateAction();
        }

        if ($request->isDelete()) {
            return $this->deleteAction();
        }

        if (!$request->isGet()) {
            /** @var Response $response */
            $response = $this->getResponse();
            $response->setStatusCode(405);

            return new JsonModel([
                'success' => false,
                'message' => 'Method not allowed',
            ]);
        }

        $id = $this->params()->fromRoute('id');

        if (
            !is_string($id) ||
            filter_var(
                $id,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            ) === false
        ) {
            /** @var Response $response */
            $response = $this->getResponse();
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => 'Invalid book ID',
            ]);
        }

        $book = $this->entityManager
            ->getRepository(Book::class)
            ->find((int) $id);

        if ($book === null) {
            /** @var Response $response */
            $response = $this->getResponse();

            $response->setStatusCode(404);

            $response->getHeaders()->addHeaderLine(
                'Content-Type',
                'application/json'
            );

            $response->setContent(json_encode([
                'success' => false,
                'message' => 'Book not found',
            ], JSON_THROW_ON_ERROR));

            return $response;
        }

        return new JsonModel([
            'success' => true,
            'book' => [
                'id' => $book->getId(),
                'title' => $book->getTitle(),
                'author' => $book->getAuthor(),
                'isbn' => $book->getIsbn(),
                'category' => $book->getCategory(),
                'stock' => $book->getStock(),
                'status' => $book->getStatus(),
            ],
        ]);
    }

    public function updateAction()
    {
        /** @var Response $response */
        $response = $this->getResponse();

        $permission = $this->authorizationService
            ->checkPermission('books.manage');

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
                'message' => 'You do not have permission to update books',
            ]);
        }

        /** @var Request $request */
        $request = $this->getRequest();

        if (!$request->isPut()) {
            $response->setStatusCode(405);

            return new JsonModel([
                'success' => false,
                'message' => 'Method not allowed',
            ]);
        }

        $id = $this->params()->fromRoute('id');

        if (
            !is_string($id) ||
            filter_var(
                $id,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            ) === false
        ) {
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => 'Invalid book ID',
            ]);
        }

        $book = $this->entityManager
            ->getRepository(Book::class)
            ->find((int) $id);

        if ($book === null) {
            $response->setStatusCode(404);
            $response->getHeaders()->addHeaderLine(
                'Content-Type',
                'application/json'
            );

            $response->setContent(json_encode([
                'success' => false,
                'message' => 'Book not found',
            ], JSON_THROW_ON_ERROR));

            return $response;
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

        $inputFilter = new InputFilter();

        foreach (['title', 'author', 'isbn', 'category'] as $field) {
            $inputFilter->add([
                'name' => $field,
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                ],
                'validators' => [
                    ['name' => NotEmpty::class],
                    [
                        'name' => StringLength::class,
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 255,
                        ],
                    ],
                ],
            ]);
        }

        $inputFilter->add([
            'name' => 'stock',
            'required' => true,
            'validators' => [
                ['name' => Digits::class],
            ],
        ]);

        foreach (['title', 'author', 'isbn', 'category'] as $field) {
            if (isset($data[$field]) && !is_string($data[$field])) {
                $response->setStatusCode(400);

                return new JsonModel([
                    'success' => false,
                    'message' => 'Invalid field type: ' . $field,
                ]);
            }
        }

        if (
            isset($data['stock']) &&
            !is_int($data['stock']) &&
            !is_string($data['stock'])
        ) {
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => 'Invalid field type: stock',
            ]);
        }

        $inputFilter->setData($data);

        if (!$inputFilter->isValid()) {
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $inputFilter->getMessages(),
            ]);
        }

        $validatedData = $inputFilter->getValues();

        if (
            filter_var(
                $validatedData['stock'],
                FILTER_VALIDATE_INT,
                [
                    'options' => [
                        'min_range' => 0,
                        'max_range' => 2147483647,
                    ],
                ]
            ) === false
        ) {
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => 'Stock must be an integer between 0 and 2147483647',
            ]);
        }

        $existingBook = $this->entityManager
            ->getRepository(Book::class)
            ->findOneBy([
                'isbn' => $validatedData['isbn'],
            ]);

        if (
            $existingBook !== null &&
            $existingBook->getId() !== $book->getId()
        ) {
            $response->setStatusCode(409);

            return new JsonModel([
                'success' => false,
                'message' => 'A book with this ISBN already exists',
            ]);
        }

        $book->setTitle($validatedData['title']);
        $book->setAuthor($validatedData['author']);
        $book->setIsbn($validatedData['isbn']);
        $book->setCategory($validatedData['category']);
        $book->setStock((int) $validatedData['stock']);

        $actor = $this->userService->getCurrentUser();

        if ($actor === null) {
            throw new \RuntimeException('Authenticated user not found');
        }

        $this->systemLogService->log(
            $actor,
            'BOOK_UPDATED',
            'Book',
            $book->getId(),
            'Book updated'
        );

        $this->entityManager->flush();

        return new JsonModel([
            'success' => true,
            'message' => 'Book updated successfully',
            'book' => [
                'id' => $book->getId(),
                'title' => $book->getTitle(),
                'author' => $book->getAuthor(),
                'isbn' => $book->getIsbn(),
                'category' => $book->getCategory(),
                'stock' => $book->getStock(),
                'status' => $book->getStatus(),
            ],
        ]);
    }

    public function deleteAction()
    {
        /** @var Response $response */
        $response = $this->getResponse();

        $permission = $this->authorizationService
            ->checkPermission('books.manage');

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
                'message' => 'You do not have permission to delete books',
            ]);
        }

        /** @var Request $request */
        $request = $this->getRequest();

        if (!$request->isDelete()) {
            $response->setStatusCode(405);

            return new JsonModel([
                'success' => false,
                'message' => 'Method not allowed',
            ]);
        }

        $id = $this->params()->fromRoute('id');

        if (
            !is_string($id) ||
            filter_var(
                $id,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            ) === false
        ) {
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => 'Invalid book ID',
            ]);
        }

        $book = $this->entityManager
            ->getRepository(Book::class)
            ->find((int) $id);

        if ($book === null) {
            $response->setStatusCode(404);

            $response->getHeaders()->addHeaderLine(
                'Content-Type',
                'application/json'
            );

            $response->setContent(json_encode([
                'success' => false,
                'message' => 'Book not found',
            ], JSON_THROW_ON_ERROR));

            return $response;
        }

        $loanCount = $this->entityManager
            ->getConnection()
            ->fetchOne(
                'SELECT COUNT(*) FROM loans WHERE book_id = ?',
                [(int) $id]
            );

        if ((int) $loanCount > 0) {
            $response->setStatusCode(409);

            return new JsonModel([
                'success' => false,
                'message' => 'Cannot delete a book with loan history',
            ]);
        }

        $actor = $this->userService->getCurrentUser();

        if ($actor === null) {
            throw new \RuntimeException('Authenticated user not found');
        }

        $this->systemLogService->log(
            $actor,
            'BOOK_DELETED',
            'Book',
            $book->getId(),
            'Book deleted'
        );

        $actor = $this->userService->getCurrentUser();

        if ($actor === null) {
            throw new \RuntimeException('Authenticated user not found');
        }

        $this->systemLogService->log(
            $actor,
            'BOOK_DELETED',
            'Book',
            $book->getId(),
            'Book deleted'
        );

        $this->entityManager->remove($book);
        $this->entityManager->flush();

        return new JsonModel([
            'success' => true,
            'message' => 'Book deleted successfully',
        ]);
    }

    public function createAction()
    {
        $permission = $this->authorizationService
            ->checkPermission('books.manage');

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
                'message' => 'You do not have permission to create books',
            ]);
        }
        /** @var Request $request */
        $request = $this->getRequest();

        if (!$request->isPost()) {
            /** @var Response $response */
            $response = $this->getResponse();
            $response->setStatusCode(405);

            return new JsonModel([
                'success' => false,
                'message' => 'Method not allowed',
            ]);
        }

        $content = $request->getContent();

        try {
            $data = json_decode(
                $content,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            /** @var Response $response */
            $response = $this->getResponse();
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => 'Invalid JSON data',
            ]);
        }
        if (!is_array($data)) {
            /** @var Response $response */
            $response = $this->getResponse();
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => 'Invalid request data',
            ]);
        }
        $inputFilter = new InputFilter();

        $inputFilter->add([
            'name' => 'title',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => NotEmpty::class,
                ],
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 1,
                        'max' => 255,
                    ],
                ],
            ],
        ]);

        $inputFilter->add([
            'name' => 'author',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => NotEmpty::class,
                ],
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 1,
                        'max' => 255,
                    ],
                ],
            ],
        ]);

        $inputFilter->add([
            'name' => 'isbn',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => NotEmpty::class,
                ],
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 1,
                        'max' => 255,
                    ],
                ],
            ],
        ]);

        $inputFilter->add([
            'name' => 'category',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => NotEmpty::class,
                ],
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 1,
                        'max' => 255,
                    ],
                ],
            ],
        ]);

        $inputFilter->add([
            'name' => 'stock',
            'required' => true,
            'validators' => [
                [
                    'name' => Digits::class,
                ],
            ],
        ]);
        $inputFilter->setData($data);

        if (!$inputFilter->isValid()) {
            /** @var Response $response */
            $response = $this->getResponse();
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $inputFilter->getMessages(),
            ]);
        }
        $validatedData = $inputFilter->getValues();

        if (
            filter_var(
                $validatedData['stock'],
                FILTER_VALIDATE_INT,
                [
                    'options' => [
                        'min_range' => 0,
                        'max_range' => 2147483647,
                    ],
                ]
            ) === false
        ) {
            /** @var Response $response */
            $response = $this->getResponse();
            $response->setStatusCode(400);

            return new JsonModel([
                'success' => false,
                'message' => 'Stock must be an integer between 0 and 2147483647',
            ]);
        }
        $existingBook = $this->entityManager
            ->getRepository(Book::class)
            ->findOneBy([
                'isbn' => $validatedData['isbn'],
            ]);

        if ($existingBook !== null) {
            /** @var Response $response */
            $response = $this->getResponse();
            $response->setStatusCode(409);

            return new JsonModel([
                'success' => false,
                'message' => 'A book with this ISBN already exists',
            ]);
        }
        $book = new Book();

        $book->setTitle($validatedData['title']);
        $book->setAuthor($validatedData['author']);
        $book->setIsbn($validatedData['isbn']);
        $book->setCategory($validatedData['category']);
        $book->setStock((int) $validatedData['stock']);

        $actor = $this->userService->getCurrentUser();

        if ($actor === null) {
            throw new \RuntimeException('Authenticated user not found');
        }

        $this->entityManager->wrapInTransaction(
            function () use ($book, $actor): void {
                $this->entityManager->persist($book);
                $this->entityManager->flush();

                $this->systemLogService->log(
                    $actor,
                    'BOOK_CREATED',
                    'Book',
                    $book->getId(),
                    'Book created'
                );
            }
        );

        /** @var Response $response */
        $response = $this->getResponse();
        $response->setStatusCode(201);

        return new JsonModel([
            'success' => true,
            'message' => 'Book created successfully',
            'book' => [
                'id' => $book->getId(),
                'title' => $book->getTitle(),
                'author' => $book->getAuthor(),
                'isbn' => $book->getIsbn(),
                'category' => $book->getCategory(),
                'stock' => $book->getStock(),
                'status' => $book->getStatus(),
            ],
        ]);
    }
}
