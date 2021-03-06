<?php


namespace App\KernelFoundation;


use Exception;

class Security
{
    const SECRET_SALT = ",^GH'7hq}LJgL`CU";
    const ROLE_CLIENT = "ROLE_CLIENT";
    const ROLE_ADMIN = "ROLE_ADMIN";
    const ROLE_SUPER_ADMIN = "ROLE_SUPER_ADMIN";
    static $permissions = [
        self::ROLE_CLIENT => ["AUTHENTICATED"],
        self::ROLE_ADMIN => ["AUTHENTICATED"],
        self::ROLE_SUPER_ADMIN => [self::ROLE_ADMIN, "AUTHENTICATED"]
    ];

    static function hasPermission(string $role)
    {
        $userRole = $_SESSION['role'] ?? "";
        if (key_exists($userRole, self::$permissions)) {
            // User has the right if he inherit of the role or if he has directly the role
            return $role == $userRole || in_array($role, self::$permissions[$userRole]);
        }
        return false;
    }

    static function getInheritedRoles(string $role){
        $roles[] = $role;
        foreach (self::$permissions as $r => $inherited){
            if(in_array($role, $inherited)){
                $roles[] = $r;
            }
        }
        return $roles;
    }
}