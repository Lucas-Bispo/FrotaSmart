# Task 24 - Refino tecnico da persistencia e reducao de acoplamento legado

## Objetivo
Continuar a migracao incremental do FrotaSmart para a espinha em `src/`, reduzindo dependencias diretas do legado com `global $pdo` e concentrando leituras e escritas criticas em servicos e repositorios reutilizaveis.

## Escopo desta primeira entrega
- criada a service [VeiculoDashboardService.php](../../src/Application/Services/VeiculoDashboardService.php) para expor a frota em formato compativel com o dashboard, sem depender de `VeiculoModel`
- atualizada a view [dashboard.php](../../frontend/views/dashboard.php) para consumir `VeiculoDashboardService` com `PdoVeiculoRepository`
- preservado o comportamento operacional de filtros `ativos`, `arquivados` e `todos`, incluindo ordenacao e historico de arquivamento
- criado o teste [test-veiculo-dashboard-service.php](../../scripts/test-veiculo-dashboard-service.php) para validar ordenacao, filtros e contagem de arquivados
- evoluido [RelatorioOperacionalModel.php](../../backend/models/RelatorioOperacionalModel.php) para receber `PDO` explicitamente, reduzindo a dependencia direta de `global $pdo`
- atualizados [relatorios.php](../../frontend/views/relatorios.php), [dashboard.php](../../frontend/views/dashboard.php), [test-auditoria-relatorio.php](../../scripts/test-auditoria-relatorio.php) e [test-relatorio-executivo.php](../../scripts/test-relatorio-executivo.php) para instanciar o model com a fabrica nova
- criada a camada [RelatorioOperacionalQueryService.php](../../src/Infrastructure/ReadModels/RelatorioOperacionalQueryService.php) para concentrar consultas SQL de secretarias, veiculos, relatorios transacionais, auditoria e agregacoes executivas iniciais
- simplificado [RelatorioOperacionalModel.php](../../backend/models/RelatorioOperacionalModel.php) para atuar mais como fachada de composicao e pos-processamento
- refinado o contrato de veiculos em [VeiculoRepositoryInterface.php](../../src/Domain/Repositories/VeiculoRepositoryInterface.php), [VeiculoService.php](../../src/Application/Services/VeiculoService.php) e [PdoVeiculoRepository.php](../../src/Infrastructure/Persistence/PdoVeiculoRepository.php) para trocar a flag `includeArchived` por metodos explicitos de leitura ativa e historica
- ajustados [VeiculoController.php](../../backend/controllers/VeiculoController.php) e os testes de repositorio e service para acompanhar a nova intencao explicita do contrato

## Resultado tecnico desta etapa
- a leitura central da frota no dashboard deixou de depender do model legado de veiculos
- o modulo de veiculos agora usa a espinha nova tanto na escrita quanto na principal leitura operacional
- o legado `VeiculoModel` permanece apenas como compatibilidade para outros pontos ainda nao migrados
- o modulo de relatorios passou a aceitar conexao explicita e deixou de depender implicitamente do estado global para os seus entrypoints principais
- a leitura SQL mais critica de relatorios saiu do model legado e passou a viver em uma camada dedicada de read model dentro de `src/Infrastructure`
- o modulo de veiculos ficou mais aderente ao padrao de Clean Code do projeto ao remover uma flag booleana que escondia dois comportamentos diferentes no mesmo metodo

## Validacao esperada
- `php -l src/Application/Services/VeiculoDashboardService.php`
- `php -l frontend/views/dashboard.php`
- `php -l scripts/test-veiculo-dashboard-service.php`
- `php scripts/test-veiculo-dashboard-service.php`
- `php -l backend/models/RelatorioOperacionalModel.php`
- `php -l src/Infrastructure/ReadModels/RelatorioOperacionalQueryService.php`
- `php -l frontend/views/relatorios.php`
- `php scripts/test-auditoria-relatorio.php`
- `php scripts/test-relatorio-executivo.php`
- `php scripts/test-veiculo-service.php`
- `php scripts/test-veiculo-controller-flow.php`
- `php scripts/test-repository-pdo.php`
- `php scripts/test-repository-contract.php`
- `php scripts/test-wsl-stack.php`

## Proximo recorte recomendado dentro da task
- continuar deslocando agregacoes e regras de montagem ainda presas ao `RelatorioOperacionalModel`
- avaliar se `AbastecimentoModel` e `ManutencaoModel` tambem devem ganhar portas de leitura mais explicitas para compor os relatorios
- fracionar `dashboard.php` e `relatorios.php` para reduzir responsabilidade de view e aproximar a apresentacao do padrao de Clean Code definido para o projeto
