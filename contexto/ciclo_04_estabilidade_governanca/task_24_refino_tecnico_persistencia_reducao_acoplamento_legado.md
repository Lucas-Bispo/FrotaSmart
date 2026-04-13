# Task 24 - Refino tecnico da persistencia e reducao de acoplamento legado

## Objetivo
Continuar a migracao incremental do FrotaSmart para a espinha em `src/`, reduzindo dependencias diretas do legado com `global $pdo` e concentrando leituras e escritas criticas em servicos e repositorios reutilizaveis.

## Escopo desta primeira entrega
- criada a service [VeiculoDashboardService.php](../../src/Application/Services/VeiculoDashboardService.php) para expor a frota em formato compativel com o dashboard, sem depender de `VeiculoModel`
- atualizada a view [dashboard.php](../../frontend/views/dashboard.php) para consumir `VeiculoDashboardService` com `PdoVeiculoRepository`
- preservado o comportamento operacional de filtros `ativos`, `arquivados` e `todos`, incluindo ordenacao e historico de arquivamento
- criado o teste [test-veiculo-dashboard-service.php](../../scripts/test-veiculo-dashboard-service.php) para validar ordenacao, filtros e contagem de arquivados

## Resultado tecnico desta etapa
- a leitura central da frota no dashboard deixou de depender do model legado de veiculos
- o modulo de veiculos agora usa a espinha nova tanto na escrita quanto na principal leitura operacional
- o legado `VeiculoModel` permanece apenas como compatibilidade para outros pontos ainda nao migrados

## Validacao esperada
- `php -l src/Application/Services/VeiculoDashboardService.php`
- `php -l frontend/views/dashboard.php`
- `php -l scripts/test-veiculo-dashboard-service.php`
- `php scripts/test-veiculo-dashboard-service.php`

## Proximo recorte recomendado dentro da task
- aplicar a mesma estrategia para consultas de relatorios e leituras pontuais ainda dependentes de `global $pdo`
- avaliar extracao de uma camada de query/read model para `RelatorioOperacionalModel`
