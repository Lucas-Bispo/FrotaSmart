<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Security;

final class Rbac
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_GERENTE = 'gerente';
    public const ROLE_MOTORISTA = 'motorista';
    public const ROLE_AUDITOR = 'auditor';

    public const PERMISSION_FLEET_READ = 'fleet.read';
    public const PERMISSION_FLEET_MANAGE = 'fleet.manage';
    public const PERMISSION_USERS_MANAGE = 'users.manage';

    /**
     * @return list<string>
     */
    public static function validRoles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_GERENTE,
            self::ROLE_MOTORISTA,
            self::ROLE_AUDITOR,
        ];
    }

    public static function allows(?string $role, string $permission): bool
    {
        if ($role === null || ! in_array($role, self::validRoles(), true)) {
            return false;
        }

        return in_array($permission, self::permissionMap()[$role] ?? [], true);
    }

    /**
     * @return array<string, list<string>>
     */
    private static function permissionMap(): array
    {
        return [
            self::ROLE_ADMIN => [
                self::PERMISSION_FLEET_READ,
                self::PERMISSION_FLEET_MANAGE,
                self::PERMISSION_USERS_MANAGE,
            ],
            self::ROLE_GERENTE => [
                self::PERMISSION_FLEET_READ,
                self::PERMISSION_FLEET_MANAGE,
            ],
            self::ROLE_MOTORISTA => [
                self::PERMISSION_FLEET_READ,
            ],
            self::ROLE_AUDITOR => [
                self::PERMISSION_FLEET_READ,
            ],
        ];
    }
}
