<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;


// This class is used to compare the roles of two users in twig files to serve the right content in a select box
// Finally is not useful because user creation is a monopol and only the super admin can create users
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
            'ROLE_LINE_ADMIN' => ['ROLE_MANAGER', 'ROLE_USER'],
            'ROLE_LINE_ADMIN_VALIDATOR' => ['ROLE_LINE_ADMIN', 'ROLE_MANAGER', 'ROLE_USER'],
            'ROLE_ADMIN' => ['ROLE_LINE_ADMIN', 'ROLE_LINE_ADMIN_VALIDATOR', 'ROLE_MANAGER', 'ROLE_USER'],
            'ROLE_ADMIN_VALIDATOR' => ['ROLE_ADMIN', 'ROLE_LINE_ADMIN_VALIDATOR', 'ROLE_LINE_ADMIN', 'ROLE_MANAGER', 'ROLE_USER'],
            'ROLE_SUPER_ADMIN' => ['ROLE_VALIDATOR', 'ROLE_ADMIN', 'ROLE_LINE_ADMIN', 'ROLE_MANAGER', 'ROLE_USER'],
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