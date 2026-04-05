<?php

declare(strict_types=1);

$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';

if (! file_exists($autoloadPath)) {
    fwrite(STDERR, "Autoload nao encontrado. Rode `composer dump-autoload` antes do teste." . PHP_EOL);
    exit(1);
}

require_once $autoloadPath;
require_once dirname(__DIR__) . '/backend/config/security.php';

use FrotaSmart\Application\Security\Rbac;

function assertTrue(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

assertTrue(in_array('auditor', valid_roles(), true), 'Perfil auditor deveria ser valido.');

assertTrue(user_can(Rbac::PERMISSION_FLEET_MANAGE, 'admin'), 'Admin deveria gerenciar frota.');
assertTrue(user_can(Rbac::PERMISSION_FLEET_MANAGE, 'gerente'), 'Gerente deveria gerenciar frota.');
assertTrue(! user_can(Rbac::PERMISSION_FLEET_MANAGE, 'motorista'), 'Motorista nao deveria gerenciar frota.');
assertTrue(! user_can(Rbac::PERMISSION_FLEET_MANAGE, 'auditor'), 'Auditor nao deveria gerenciar frota.');

assertTrue(user_can(Rbac::PERMISSION_FLEET_READ, 'admin'), 'Admin deveria ler frota.');
assertTrue(user_can(Rbac::PERMISSION_FLEET_READ, 'gerente'), 'Gerente deveria ler frota.');
assertTrue(user_can(Rbac::PERMISSION_FLEET_READ, 'motorista'), 'Motorista deveria ler frota.');
assertTrue(user_can(Rbac::PERMISSION_FLEET_READ, 'auditor'), 'Auditor deveria ler frota.');

assertTrue(user_can(Rbac::PERMISSION_USERS_MANAGE, 'admin'), 'Admin deveria gerenciar usuarios.');
assertTrue(! user_can(Rbac::PERMISSION_USERS_MANAGE, 'gerente'), 'Gerente nao deveria gerenciar usuarios.');
assertTrue(! user_can(Rbac::PERMISSION_USERS_MANAGE, 'motorista'), 'Motorista nao deveria gerenciar usuarios.');
assertTrue(! user_can(Rbac::PERMISSION_USERS_MANAGE, 'auditor'), 'Auditor nao deveria gerenciar usuarios.');

echo "RBAC de veiculos validado com sucesso." . PHP_EOL;
