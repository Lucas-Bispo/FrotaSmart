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
- iniciado o fracionamento de [dashboard.php](../../frontend/views/dashboard.php) com o helper [dashboard_view_helpers.php](../../frontend/views/helpers/dashboard_view_helpers.php), extraindo sumarizacao, cards e atalhos para funcoes puras de apoio
- iniciado o fracionamento de [relatorios.php](../../frontend/views/relatorios.php) com o helper [relatorios_view_helpers.php](../../frontend/views/helpers/relatorios_view_helpers.php), extraindo labels, cards, cabecalhos e renderizacao de linhas por tipo
- evoluido [relatorios_view_helpers.php](../../frontend/views/helpers/relatorios_view_helpers.php) para tambem centralizar campos de filtro, opcoes, tabs e query de exportacao do modulo de relatorios
- evoluidos [AbastecimentoModel.php](../../backend/models/AbastecimentoModel.php) e [ManutencaoModel.php](../../backend/models/ManutencaoModel.php) para aceitar `PDO` explicito, reduzindo dependencia obrigatoria de `global $pdo`
- ajustado [RelatorioOperacionalModel.php](../../backend/models/RelatorioOperacionalModel.php) para compartilhar a mesma conexao com os modelos auxiliares reutilizados nas leituras executivas e operacionais
- criados [AbastecimentoReadModel.php](../../src/Infrastructure/ReadModels/AbastecimentoReadModel.php) e [ManutencaoReadModel.php](../../src/Infrastructure/ReadModels/ManutencaoReadModel.php) para deslocar leituras analiticas e preventivas do apoio legado para `src/Infrastructure/ReadModels`
- simplificado [RelatorioOperacionalModel.php](../../backend/models/RelatorioOperacionalModel.php) para consumir os novos read models dedicados, reduzindo o uso de `AbastecimentoModel` e `ManutencaoModel` dentro dos relatorios
- criado [RelatorioExecutiveSummaryService.php](../../src/Application/Services/RelatorioExecutiveSummaryService.php) para concentrar a consolidacao executiva por secretaria e por veiculo fora do model legado
- simplificado novamente [RelatorioOperacionalModel.php](../../backend/models/RelatorioOperacionalModel.php) para delegar o painel executivo ao novo service, mantendo a fachada mais enxuta
- criados [RelatorioAuditSummaryService.php](../../src/Application/Services/RelatorioAuditSummaryService.php) e [RelatorioCsvExporterService.php](../../src/Application/Services/RelatorioCsvExporterService.php) para deslocar do model legado a consolidacao do resumo de auditoria e a exportacao CSV
- adicionado o teste [test-relatorio-support-services.php](../../scripts/test-relatorio-support-services.php) para validar o recorte novo em componentes menores e mais testaveis

## Resultado tecnico desta etapa
- a leitura central da frota no dashboard deixou de depender do model legado de veiculos
- o modulo de veiculos agora usa a espinha nova tanto na escrita quanto na principal leitura operacional
- o legado `VeiculoModel` permanece apenas como compatibilidade para outros pontos ainda nao migrados
- o modulo de relatorios passou a aceitar conexao explicita e deixou de depender implicitamente do estado global para os seus entrypoints principais
- a leitura SQL mais critica de relatorios saiu do model legado e passou a viver em uma camada dedicada de read model dentro de `src/Infrastructure`
- o modulo de veiculos ficou mais aderente ao padrao de Clean Code do projeto ao remover uma flag booleana que escondia dois comportamentos diferentes no mesmo metodo
- o dashboard principal ficou menos acoplado a calculos e a blocos repetidos de markup, preparando a view para novos recortes menores
- o modulo de relatorios tambem passou a centralizar variacoes de apresentacao em helpers, reduzindo condicionais distribuicionais dentro da view principal
- o modulo de relatorios reduziu ainda mais o tamanho e a responsabilidade da view principal ao deslocar montagem de filtros, navegacao e exportacao para helpers
- a leitura reutilizada de abastecimentos e manutencoes no modulo de relatorios passou a operar com a mesma conexao explicita do fluxo principal, deixando menos espaco para dependencias implicitas de bootstrap global
- os relatorios executivos e operacionais agora leem abastecimentos e manutencoes por read models dedicados em `src/`, aproximando melhor o modulo do padrao arquitetural definido para o projeto
- a montagem executiva do dashboard tambem passou a morar em `src/Application/Services`, reduzindo mais uma concentracao de regra dentro do model legado
- o resumo de auditoria e a serializacao CSV do modulo de relatorios agora tambem ficaram em services dedicados, reduzindo mais responsabilidade local do `RelatorioOperacionalModel`

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
- `php -l frontend/views/helpers/dashboard_view_helpers.php`
- `php -l frontend/views/helpers/relatorios_view_helpers.php`
- `php -l frontend/views/relatorios.php`
- `php scripts/test-auditoria-relatorio.php`
- `php scripts/test-relatorio-executivo.php`
- `php -l backend/models/AbastecimentoModel.php`
- `php -l backend/models/ManutencaoModel.php`
- `php -l backend/models/RelatorioOperacionalModel.php`
- `php -l src/Infrastructure/ReadModels/AbastecimentoReadModel.php`
- `php -l src/Infrastructure/ReadModels/ManutencaoReadModel.php`
- `php -l src/Application/Services/RelatorioExecutiveSummaryService.php`
- `php -l src/Application/Services/RelatorioAuditSummaryService.php`
- `php -l src/Application/Services/RelatorioCsvExporterService.php`
- `php -l scripts/test-relatorio-support-services.php`
- `php scripts/test-relatorio-support-services.php`

## Proximo recorte recomendado dentro da task
- continuar deslocando agregacoes e regras de montagem ainda presas ao `RelatorioOperacionalModel`
- avaliar se `AbastecimentoModel` e `ManutencaoModel` devem gradualmente delegar leituras compartilhadas para os novos read models, evitando duplicacao de regra analitica
- continuar deslocando pos-processamentos e agregacoes restantes do `RelatorioOperacionalModel`, especialmente resumo operacional e exportacao, para componentes menores e mais testaveis
- continuar o fracionamento de `relatorios.php` para reduzir responsabilidade de view e aproximar a apresentacao do padrao de Clean Code definido para o projeto
