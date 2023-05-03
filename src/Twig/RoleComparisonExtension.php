<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RoleComparisonExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('compare_roles', [$this, 'compareRoles']),
        ];
    }
    public function compareRoles(array $currentUserRoles, array $otherUserRoles): bool
    {
        //Defining role hierarchy
        $rolesHierarchy = [
            'ROLE_USER' => [],
            'ROLE_MANAGER' => ['ROLE_USER'],
            'ROLE_LINE_ADMIN' => ['ROLE_MANAGER'],
            'ROLE_ADMIN' => ['ROLE_LINE_ADMIN'],
            'ROLE_SUPER_ADMIN' => ['ROLE_ADMIN'],
        ];
        //If the current user has a role that is in the hierarchy of the other user, return true
        foreach ($currentUserRoles as $currentUserRole) {
            if (in_array($currentUserRole, $rolesHierarchy[$otherUserRoles[0]])) {

                return false;
            }
        }
        //If the current user has no role that is in the hierarchy of the other user, return false
        return true;
    }
}