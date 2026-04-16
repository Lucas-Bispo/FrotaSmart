# Estado atual do projeto

## Navegacao rapida
- Arquitetura: [arquitetura_projeto.md](./arquitetura_projeto.md)
- Regras de negocio: [regras_negocio.md](./regras_negocio.md)
- Roadmap atual: [roadmap_ciclo_04.md](./ciclo_04_estabilidade_governanca/roadmap_ciclo_04.md)
- Roadmap ciclo 01: [roadmap_ciclo_01.md](./ciclo_01_fundacao_arquitetura/roadmap_ciclo_01.md)
- Progresso: [progresso.md](./progresso.md)
- Contexto de transicao: [contexto_transicao.md](./contexto_transicao.md)

## Data de referencia
2026-04-14

## Leitura rapida
- O FrotaSmart hoje esta organizado em `backend/` e `frontend/`
- A base atual segue um MVC simples
- Ja existe autenticacao, dashboard e CRUD basico de veiculos
- A arquitetura alvo oficial e Clean Architecture adaptada em `src/`
- O dominio novo de veiculos ja possui `Veiculo` e `Placa` em `src/Domain`
- O fluxo de escrita de veiculos agora passa por `VeiculoService` e por um repositorio PDO novo
- O modulo de veiculos agora possui trilha minima de auditoria reutilizavel
- O RBAC do modulo de veiculos e do gerenciamento de usuarios agora usa uma base central em `src/`
- O modulo inicial de motoristas agora existe com cadastro, listagem e edicao operacional
- O historico inicial de manutencoes por veiculo agora existe com abertura, andamento e conclusao
- O modulo inicial de abastecimentos agora existe com filtros, custo total e vinculo com motorista
- O dashboard agora consolidou leitura operacional com alertas, custos e atalhos da rotina
- O modulo inicial de viagens agora conecta secretaria, motorista e veiculo no uso administrativo da frota
- O modulo inicial de parceiros operacionais agora centraliza oficinas, postos e fornecedores com vinculo real nas operacoes
- O backlog do ciclo 03 foi aberto para consolidar o nucleo do sistema, com foco em veiculos, prevencao e relatorios
- O ciclo 03 foi concluido com cadastro completo, arquivamento, manutencao preventiva, inteligencia de abastecimento e relatorios CSV
- O ambiente WSL Ubuntu voltou a validar bootstrap e testes integrados diretamente no Linux, sem depender do PHP do Windows
- O projeto ja possui `public/` como document root recomendado para Linux/WSL
- O ciclo 04 ja comecou a reagir com bloqueios e alertas automaticos no fluxo de viagens e abastecimentos
- O dashboard agora tambem entrega leitura executiva por secretaria e por veiculo no proprio painel principal
- A auditoria agora tambem possui persistencia em banco e leitura exportavel no modulo de relatorios
- A listagem principal da frota no dashboard agora sai de `VeiculoDashboardService` em `src/`, reutilizando o repositorio PDO novo
- O modulo de relatorios ja aceita `PDO` explicito nos entrypoints principais, reduzindo dependencia de estado global
- O FrotaSmart agora possui uma camada dedicada de read model para relatorios em `src/Infrastructure/ReadModels`
- O modulo de relatorios agora tambem usa read models dedicados para abastecimentos e manutencoes analiticas, reduzindo apoio direto dos models legados nesse fluxo
- o painel executivo agora tambem possui montagem dedicada em `src/Application/Services/RelatorioExecutiveSummaryService.php`
- o resumo de auditoria e a exportacao CSV dos relatorios agora tambem usam services dedicados em `src/Application/Services`
- o resumo operacional e a selecao de datasets dos relatorios agora tambem usam services dedicados em `src/Application/Services`
- as transformacoes de linhas de relatorios agora tambem usam service dedicado em `src/Application/Services`
- o fluxo completo de abastecimentos do modulo de relatorios agora tambem usa um service dedicado em `src/Application/Services`
- o fluxo de auditoria do modulo de relatorios agora tambem usa um service dedicado em `src/Application/Services`
- os fluxos de manutencoes, viagens e disponibilidade do modulo de relatorios agora tambem usam um service operacional dedicado em `src/Application/Services`
- a exportacao e a montagem das dependencias do modulo de relatorios agora tambem usam componentes dedicados em `src/`
- o dashboard principal agora tambem centraliza no helper o pacote principal de dados da tela, incluindo cards executivos e tabs de filtro
- o `bootstrap-db.php` agora tambem separa a evolucao de schema por modulos e usa helpers menores para colunas, indices e execucao de statements
- O projeto agora possui um guia formal de Clean Code em `engenharia/padrao_clean_code_frotasmart.md`
- Os controllers operacionais principais ja comecaram a migrar para validacoes menores e mais nomeadas
- O modulo de veiculos agora tambem usa contratos mais explicitos para separar leitura ativa de leitura historica
- O dashboard principal iniciou um fracionamento de view com helpers puros para cards, alertas e sumarizacao operacional
- O modulo de relatorios agora tambem iniciou fracionamento de view com helpers para labels, cards, cabecalhos e renderizacao de linhas
- O modulo de relatorios ja avancou para um segundo recorte de view, movendo tambem filtros, exportacao e abas para helpers dedicados

## Achados tecnicos
- `backend/models/VeiculoModel.php` usa `global $pdo`
- `backend/controllers/VeiculoController.php` ja delega a escrita para o service, e a listagem principal do dashboard agora tambem passa pela espinha nova em `src/`
- A auditoria de veiculos agora usa servico e contratos proprios em `src/`
- O legado de autorizacao passou a consumir uma politica unica de permissoes por perfil
- Os modulos de motoristas e manutencoes ainda usam models legados, mas ja estao integrados ao fluxo principal da aplicacao
- O modulo de abastecimentos segue o mesmo padrao incremental dos modulos operacionais do ciclo 02
- O dashboard ainda e uma view legacy, mas agora usa dados reais dos modulos operacionais do ciclo
- O dashboard tambem passou a consumir consolidacoes executivas do `RelatorioOperacionalModel` para leitura por secretaria e por veiculo
- O modulo de viagens reutiliza schema legado compatibilizado, sem exigir cadastro formal de secretaria nesta fase
- manutencoes e abastecimentos continuam compativeis com texto livre, mas agora aceitam vinculo estruturado com parceiro cadastrado
- viagens e abastecimentos agora contam com um guard operacional central para CNH, preventiva e estado do veiculo
- `backend/config/db.php` centraliza conexao e leitura do `.env`
- `backend/config/security.php` passou a centralizar a emissao de auditoria estruturada para log e banco
- `backend/models/RelatorioOperacionalModel.php` foi ajustado para receber conexao explicita, embora ainda concentre consultas legadas
- parte importante das consultas do `RelatorioOperacionalModel` ja foi extraida para `RelatorioOperacionalQueryService`
- parte da leitura analitica reutilizada por relatorios saiu de `AbastecimentoModel` e `ManutencaoModel` e passou a ser atendida por read models dedicados em `src/Infrastructure/ReadModels`
- a montagem do painel executivo tambem saiu do `RelatorioOperacionalModel` e passou a ser orquestrada por um service dedicado em `src/Application/Services`
- a consolidacao do resumo de auditoria e a serializacao CSV tambem comecaram a sair do `RelatorioOperacionalModel`, reduzindo mais responsabilidade nessa fachada legada
- o resumo operacional e a selecao do dataset por tipo de relatorio tambem comecaram a sair do `RelatorioOperacionalModel`, reduzindo mais ramificacao nesse hotspot legacy
- o pos-processamento de linhas de viagem, disponibilidade e auditoria tambem comecou a sair do `RelatorioOperacionalModel`, reduzindo mais loops e transformacoes locais
- a normalizacao de criterios base de abastecimento tambem comecou a sair do `RelatorioOperacionalModel`, reduzindo mais responsabilidade transacional nessa fachada legacy
- a orquestracao completa do relatorio de abastecimentos tambem comecou a sair do `RelatorioOperacionalModel`, reduzindo mais acoplamento entre criterios, leitura e filtro residual
- a normalizacao compartilhada dos filtros de consulta tambem comecou a sair do `RelatorioOperacionalQueryService`, reduzindo repeticao e ramificacao entre relatorios transacionais
- a preparacao de estado da tela de relatorios tambem comecou a sair da propria view, reduzindo montagem de request e selecao de dataset dentro de `frontend/views/relatorios.php`
- a view de relatorios tambem comecou a consolidar a composicao dos dados de apresentacao em helper dedicado, reduzindo mais variaveis locais e passos de montagem na pagina principal
- a leitura, transformacao e sumarizacao da auditoria tambem comecaram a sair do `RelatorioOperacionalModel`, reduzindo mais composicao residual nessa fachada legacy
- a leitura e as transformacoes operacionais de manutencoes, viagens e disponibilidade tambem comecaram a sair do `RelatorioOperacionalModel`, reduzindo mais responsabilidade nessa fachada legacy
- a exportacao CSV e a montagem dos colaboradores do modulo tambem comecaram a sair do `RelatorioOperacionalModel`, reduzindo mais a funcao de mini-container nessa fachada legacy
- o `dashboard.php` tambem comecou a consolidar mais composicao de apresentacao em helper dedicado, reduzindo mais atribuicoes locais e repeticao na view principal
- o `bootstrap-db.php` tambem comecou a quebrar sua evolucao de schema em funcoes menores por modulo, reduzindo a concentracao de condicionais no script de ambiente
- `ViagemController` iniciou uma rodada de reducao de complexidade com extracao de validacoes em metodos menores
- `ManutencaoController` e `AbastecimentoController` seguiram a mesma direcao para reduzir complexidade local
- `MotoristaController` e `ParceiroOperacionalController` agora tambem delegam normalizacao e validacao de entrada para services pequenos e testaveis em `src/Application/Services`
- `VeiculoRepositoryInterface`, `VeiculoService` e `PdoVeiculoRepository` deixaram de usar a flag `includeArchived` e passaram a expor metodos com intencao explicita
- `dashboard.php` agora reaproveita `dashboard_view_helpers.php` para organizar parte da camada de apresentacao em funcoes puras e estruturas declarativas
- `dashboard.php` agora tambem reaproveita o helper para preparar linhas prontas das tabelas executivas e de abastecimentos recentes, reduzindo mais formatacao inline na view
- `relatorios.php` agora reaproveita `relatorios_view_helpers.php` para reduzir condicionais de apresentacao e centralizar variacoes por tipo de relatorio
- `relatorios.php` tambem passou a reaproveitar helpers para campos de filtro, tabs de navegacao e query de exportacao
- Ja existem `src/` e `composer.json`
- O ambiente possui PHP local funcional para validacao do projeto
- O Composer foi baixado localmente como `composer.phar`

## Conclusao de viabilidade
A [task_01_fundacao_arquitetura_composer_psr4.md](./ciclo_01_fundacao_arquitetura/task_01_fundacao_arquitetura_composer_psr4.md) era viavel e foi executada como fundacao arquitetural, nao como refactor total do projeto.

## Estrategia recomendada
- Criar a nova base em paralelo ao legado
- Migrar modulo por modulo
- Comecar por veiculos, porque ja existe fluxo funcional e ele e o centro do dominio

## Riscos atuais
- O legado ainda depende de `global $pdo` e `require_once`
- O CRUD legado de veiculos ainda faz `DELETE` fisico, enquanto a regra de negocio pede soft delete
- outras leituras legadas ainda dependem de `global $pdo`, mas o nucleo de relatorios ja comecou a migrar para uma camada dedicada
- O ciclo 02 planejado foi concluido e o proximo passo natural e consolidar backlog do ciclo seguinte
- O cadastro de veiculos ainda precisava de dados mais aderentes a frota municipal real
- a trilha de auditoria foi fortalecida, mas a persistencia geral ainda mistura models legados, `global $pdo` e servicos novos
- views grandes como `dashboard.php` e `relatorios.php` ainda concentram muita responsabilidade de apresentacao

## Decisao atual
- Manter `composer.phar` apenas como ferramenta local, fora do versionamento
- Evoluir o modulo de veiculos por migracao incremental a partir do dominio novo
- Consolidar a migracao de leitura e finalizar o alinhamento de persistencia com soft delete e banco real
- Preservar a compatibilidade com Linux/WSL e com a publicacao via `public/`
- Manter o WSL Ubuntu como ambiente principal e repetivel de desenvolvimento
- Avancar o ciclo 04 priorizando refino tecnico da persistencia, governanca operacional, compliance e transparencia de dados nao pessoais
