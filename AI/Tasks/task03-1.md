# Task 03.1 - Operacao Clean Linux

## Status
- Estado atual: concluida
- Dependencia anterior: [Task 03](./task03.md)
- Roadmap principal: [tasks.md](./tasks.md)

## Navegacao rapida
- Progresso geral: [PROGRESSO.MD](../../PROGRESSO.MD)
- Guia Linux: [README_LINUX.md](../../README_LINUX.md)
- Contexto de transicao: [ContextodeTransicao.md](../Contexto/ContextodeTransicao.md)
- Task anterior: [task03.md](./task03.md)

## Objetivo
Eliminar o acoplamento explicito com Windows/XAMPP e preparar o FrotaSmart para execucao padronizada em Linux Ubuntu e WSL.

## Arquivos envolvidos
- Guia operacional: [README_LINUX.md](../../README_LINUX.md)
- Entrypoints publicos:
  - [index.php](../../public/index.php)
  - [login.php](../../public/login.php)
  - [dashboard.php](../../public/dashboard.php)
  - [user_management.php](../../public/user_management.php)
  - [auth.php](../../public/auth.php)
  - [veiculos.php](../../public/veiculos.php)
  - [users.php](../../public/users.php)
- Ajustes de navegacao:
  - [login.php](../../frontend/views/login.php)
  - [dashboard.php](../../frontend/views/dashboard.php)
  - [user_management.php](../../frontend/views/user_management.php)
  - [sidebar.php](../../frontend/includes/sidebar.php)
- Ajustes de controllers:
  - [AuthController.php](../../backend/controllers/AuthController.php)
  - [VeiculoController.php](../../backend/controllers/VeiculoController.php)
  - [UserController.php](../../backend/controllers/UserController.php)
- Scripts CLI:
  - [reset-password.php](../../scripts/reset-password.php)
  - [test-domain.php](../../scripts/test-domain.php)
  - [test-repository-contract.php](../../scripts/test-repository-contract.php)

## Decisoes de engenharia
- O projeto passou a ter `public/` como document root recomendado para Linux
- Views e controllers legados foram ajustados para navegar por rotas publicas do projeto
- Referencias explicitas a XAMPP foram removidas dos scripts operacionais
- O fluxo local padrao foi definido com `composer install`, `php scripts/bootstrap-db.php` e `php -S 0.0.0.0:8000 -t public`

## Criterio de aceite atendido
- scripts sem referencia a XAMPP
- `.env` mantido como fonte de verdade do banco
- guia Linux criado
- `public/` preparado como document root

## Validacao pratica
Comandos esperados:

```bash
composer install
php scripts/bootstrap-db.php
php -S 0.0.0.0:8000 -t public
```

## Observacoes para a proxima task
- A `Task 04` deve implementar a persistencia concreta ja assumindo `public/` como ponto de entrada da aplicacao
- O legado ainda usa `global $pdo`, entao a prontidao Linux ficou operacional, nao arquitetural completa
