<?php

namespace Application\Service;

use Laminas\Permissions\Rbac\Rbac;
use Laminas\Permissions\Rbac\Role;

class RbacService
{
    private Rbac $rbac;

    public function __construct()
    {
        $this->rbac = new Rbac();

        $student = new Role('student');

        $student->addPermission('books.view');
        $student->addPermission('loans.create');
        $student->addPermission('loans.view_own');

        $operator = new Role('operator');

        $operator->addPermission('books.view');
        $operator->addPermission('books.manage');
        $operator->addPermission('loans.manage');
        $operator->addPermission('penalties.manage');

        $superAdmin = new Role('super_admin');

        $superAdmin->addPermission('books.view');
        $superAdmin->addPermission('loans.create');
        $superAdmin->addPermission('loans.view_own');
        $superAdmin->addPermission('books.manage');
        $superAdmin->addPermission('loans.manage');
        $superAdmin->addPermission('penalties.manage');
        $superAdmin->addPermission('users.manage');
        $superAdmin->addPermission('settings.manage');
        $superAdmin->addPermission('logs.view');

        $this->rbac->addRole($student);
        $this->rbac->addRole($operator);
        $this->rbac->addRole($superAdmin);
    }

    public function isGranted(string $role, string $permission): bool
    {
        if (!$this->rbac->hasRole($role)) {
            return false;
        }

        return $this->rbac->isGranted($role, $permission);
    }
}
