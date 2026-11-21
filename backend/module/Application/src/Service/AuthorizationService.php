<?php

namespace Application\Service;

class AuthorizationService
{
    private $userService;
    private $rbacService;

    public function __construct(UserService $userService, RbacService $rbacService)
    {
        $this->userService = $userService;
        $this->rbacService = $rbacService;
    }

    public function checkPermission(string $permission): string
    {
        $user = $this->userService->getCurrentUser();

        if (!$user) {
            return 'unauthenticated';
        }

        $role = $user->getRole();

        if ($this->rbacService->isGranted($role, $permission)) {
            return 'allowed';
        }

        return 'forbidden';
    }
}
