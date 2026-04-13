# Progresso - FrotaSmart

## Navegacao rapida
- Roadmap atual: [roadmap_ciclo_04.md](./ciclo_04_estabilidade_governanca/roadmap_ciclo_04.md)
- Roadmap ciclo 01: [roadmap_ciclo_01.md](./ciclo_01_fundacao_arquitetura/roadmap_ciclo_01.md)
- Arquitetura: [arquitetura_projeto.md](./arquitetura_projeto.md)
- Estado atual: [estado_projeto.md](./estado_projeto.md)
- Regras de negocio: [regras_negocio.md](./regras_negocio.md)
- Guia Linux: [readme_linux.md](./readme_linux.md)

## Observacao de contexto
- registros antigos de validacao presa ao PHP do Windows refletem apenas um momento intermediario da evolucao
- o ambiente operacional oficial do projeto hoje e o Ubuntu WSL, conforme [readme_wsl_ubuntu_windows.md](./readme_wsl_ubuntu_windows.md) e [task_20_estabilizacao_wsl_validacao_integrada.md](./ciclo_04_estabilidade_governanca/task_20_estabilizacao_wsl_validacao_integrada.md)

## 2026-03-22

### Task 01 - Fundacao da arquitetura
- Criado `composer.json` na raiz do projeto
- Definido autoload PSR-4 `FrotaSmart\\` -> `src/`
- Criada a estrutura base de camadas em `src/`
- Criada a classe inicial `FrotaSmart\Domain\Entities\Veiculo`
- Criado `scripts/test-autoload.php` para validacao futura
- Atualizado o backlog em [roadmap_ciclo_01.md](./ciclo_01_fundacao_arquitetura/roadmap_ciclo_01.md)
- Registrado o diagnostico arquitetural em [estado_projeto.md](./estado_projeto.md)

### Estado atual
- A base para migracao para Clean Architecture foi iniciada
- O sistema legado em `backend/` e `frontend/` foi preservado
- Ainda nao foi feita a migracao dos controllers e models antigos

### Bloqueios
- `php` e `composer` nao estavam no PATH no inicio da sessao
- O projeto foi validado usando PHP local e `composer.phar` local

### Proximo passo recomendado
- Concluir a `Task 02`: enriquecer `Veiculo` como entidade de dominio e criar `Placa` como Value Object

## 2026-03-22 - Task 02

### Dominio inicial de veiculos
- Criado o Value Object [Placa](../src/Domain/ValueObjects/Placa.php)
- Enriquecida a entidade [Veiculo](../src/Domain/Entities/Veiculo.php)
- Criadas exceptions de dominio para falhas de placa e status
- Registrada a task em [task_02_dominio_inicial_veiculos.md](./ciclo_01_fundacao_arquitetura/task_02_dominio_inicial_veiculos.md)
- Atualizado o roadmap em [roadmap_ciclo_01.md](./ciclo_01_fundacao_arquitetura/roadmap_ciclo_01.md)
- Criado o teste de dominio em [test-domain.php](../scripts/test-domain.php)

### Regras agora no dominio
- validacao de placa no proprio dominio
- normalizacao de status legados para status oficiais
- transicoes basicas de estado do veiculo

### Proximo passo recomendado
- Iniciar a `Task 03`: criar o contrato de repositorio para o modulo de veiculos

## 2026-03-22 - Task 03

### Contrato de repositorio de veiculos
- Criado o contrato [VeiculoRepositoryInterface.php](../src/Domain/Repositories/VeiculoRepositoryInterface.php)
- Definidas operacoes de persistencia, consulta, existencia, listagem e remocao sem acoplamento com `PDO`
- Adotada [Placa](../src/Domain/ValueObjects/Placa.php) como identidade de consulta do dominio nesta etapa
- Registrada a task em [task_03_contrato_repositorio_veiculos.md](./ciclo_01_fundacao_arquitetura/task_03_contrato_repositorio_veiculos.md)
- Criado o teste de validacao do contrato em [test-repository-contract.php](../scripts/test-repository-contract.php)

### Proximo passo recomendado
- Iniciar a `Task 04`: implementar o repositorio concreto em `src/Infrastructure/Persistence`

## 2026-03-22 - Task 03.1

### Operacao Clean Linux
- Criado o guia [readme_linux.md](./readme_linux.md) com fluxo de execucao em Linux e WSL
- Criada a pasta [public](../public/index.php) como document root recomendado para servidor embutido e deploy Linux
- Ajustadas views e controllers legados para navegar por entrypoints publicos
- Removidas referencias explicitas ao ambiente legado do Windows nos scripts operacionais

### Por que isso foi feito
- reduzir acoplamento com ambiente local especifico
- evitar exposicao da raiz do projeto em servidor web
- preparar a aplicacao para Ubuntu, WSL e deploy mais profissional

### Proximo passo recomendado
- Iniciar a `Task 04`: implementar o repositorio concreto em `src/Infrastructure/Persistence` sobre a base Linux pronta

## 2026-03-22 - Revisao estrutural do projeto

### Validacao operacional da arquitetura
- Composer local baixado como ferramenta de trabalho e ignorado no Git
- `composer dump-autoload` executado com sucesso
- Teste real de autoload validado com PHP local
- `composer.json` alinhado ao PHP real da maquina com `php:^8.2`

### Limpeza arquitetural
- Removidos scripts CLI de `backend/config`
- Criados scripts operacionais em [bootstrap-db.php](../scripts/bootstrap-db.php) e [reset-password.php](../scripts/reset-password.php)
- Normalizado o nome da regra geral do Codex para [codex_regras_gerais.md](./codex_regras_gerais.md)

### Seguranca e higiene do repositorio
- Removidos caminhos absolutos com dados locais da documentacao principal
- `.gitignore` reforcado com chaves, arquivos temporarios e configuracoes locais de IDE

### Alertas encontrados na revisao
- o legado ainda usa `global $pdo`
- o CRUD de veiculos ainda faz exclusao fisica
- a integracao completa do legado com `public/` e com a nova arquitetura ainda segue em evolucao

### Proximo passo recomendado
- Implementar a `Task 04` para reduzir o acoplamento do legado com a persistencia

## 2026-04-05 - Task 04

### Persistencia PDO na nova arquitetura
- Criado o loader de ambiente [EnvLoader.php](../src/Infrastructure/Config/EnvLoader.php)
- Criada a fabrica de conexao [PdoConnectionFactory.php](../src/Infrastructure/Config/PdoConnectionFactory.php)
- Implementado o repositorio [PdoVeiculoRepository.php](../src/Infrastructure/Persistence/PdoVeiculoRepository.php)
- Criado o teste [test-repository-pdo.php](../scripts/test-repository-pdo.php)
- Atualizada a task em [task_04_persistencia_pdo_nova_arquitetura.md](./ciclo_01_fundacao_arquitetura/task_04_persistencia_pdo_nova_arquitetura.md)

### Resultado tecnico
- O fluxo novo deixou de depender de `global $pdo`
- O repositorio faz traducao entre status oficiais do dominio e status legados do banco
- A remocao ficou preparada para evoluir para soft delete quando a estrutura do banco acompanhar

### Bloqueio encontrado
- O teste real do repositorio no MySQL ainda falha com erro de acesso por credencial atual do `.env`

### Proximo passo recomendado
- Executar a `Task 05`: mover a orquestracao de veiculos para a camada de aplicacao

## 2026-04-05 - Task 05

### Service de aplicacao para veiculos
- Criado o service [VeiculoService.php](../src/Application/Services/VeiculoService.php)
- Criadas exceptions de aplicacao para duplicidade e ausencia de veiculo
- Criado o teste [test-veiculo-service.php](../scripts/test-veiculo-service.php)
- Atualizada a task em [task_05_service_aplicacao_veiculos.md](./ciclo_01_fundacao_arquitetura/task_05_service_aplicacao_veiculos.md)

### Resultado tecnico
- O fluxo de cadastro, atualizacao, consulta, listagem e remocao passou a existir fora da camada HTTP
- O service depende apenas de contrato de repositorio e objetos do dominio
- A aplicacao passou a ter um ponto unico para orquestrar o modulo de veiculos

### Validacao realizada
- `test-veiculo-service.php` executado com sucesso
- `test-domain.php` executado com sucesso

### Proximo passo recomendado
- Executar a `Task 06`: adaptar o controller legado para usar o novo service

## 2026-04-05 - Task 06

### Adaptacao do controller legado
- Reescrito [VeiculoController.php](../backend/controllers/VeiculoController.php) para consumir `VeiculoService`
- Atualizado o entrypoint [veiculos.php](../public/veiculos.php) para instanciar e despachar o controller novo
- Ajustada a dashboard [dashboard.php](../frontend/views/dashboard.php) para enviar placa na remocao e usar status oficiais no cadastro
- Criado o teste [test-veiculo-controller-flow.php](../scripts/test-veiculo-controller-flow.php)
- Atualizada a task em [task_06_adaptacao_controller_legado_veiculos.md](./ciclo_01_fundacao_arquitetura/task_06_adaptacao_controller_legado_veiculos.md)

### Resultado tecnico
- O fluxo de escrita de veiculos nao depende mais diretamente de `VeiculoModel`
- O controller ficou focado em HTTP, autorizacao, CSRF, flash e redirecionamento
- O cadastro passou a aceitar os status oficiais definidos no dominio

### Validacao realizada
- `test-veiculo-controller-flow.php` executado com sucesso
- `php -l` executado com sucesso nos arquivos alterados da task

### Proximo passo recomendado
- Executar a `Task 07`: iniciar a auditoria minima obrigatoria do modulo de veiculos

## 2026-04-05 - Task 07

### Auditoria minima obrigatoria
- Criada a estrutura de auditoria reutilizavel com [AuditEntry.php](../src/Application/Audit/AuditEntry.php)
- Criados os contratos [AuditLoggerInterface.php](../src/Application/Contracts/AuditLoggerInterface.php) e [AuditContextProviderInterface.php](../src/Application/Contracts/AuditContextProviderInterface.php)
- Criado o servico [AuditTrailService.php](../src/Application/Services/AuditTrailService.php)
- Criadas as implementacoes [ErrorLogAuditLogger.php](../src/Infrastructure/Audit/ErrorLogAuditLogger.php) e [RequestAuditContextProvider.php](../src/Infrastructure/Audit/RequestAuditContextProvider.php)
- Adaptado [VeiculoController.php](../backend/controllers/VeiculoController.php) para registrar auditoria via servico
- Criado o teste [test-audit-flow.php](../scripts/test-audit-flow.php)
- Atualizada a task em [task_07_auditoria_minima_obrigatoria.md](./ciclo_01_fundacao_arquitetura/task_07_auditoria_minima_obrigatoria.md)

### Resultado tecnico
- Criacao, atualizacao e remocao de veiculos agora geram eventos com ator, acao, alvo, IP e data
- O formato minimo de auditoria ficou padronizado e reaproveitavel para outros modulos
- A trilha de auditoria deixou de depender de detalhes espalhados no controller legado

### Validacao realizada
- `test-audit-flow.php` executado com sucesso
- `test-veiculo-controller-flow.php` executado com sucesso
- `test-veiculo-service.php` executado com sucesso
- `test-domain.php` executado com sucesso

### Proximo passo recomendado
- Executar a `Task 08`: alinhar RBAC com os perfis oficiais das regras de negocio

## 2026-04-05 - Task 08

### RBAC alinhado com regras de negocio
- Criada a politica central [Rbac.php](../src/Application/Security/Rbac.php)
- Adicionados helpers de permissao em [security.php](../backend/config/security.php)
- Adaptados [VeiculoController.php](../backend/controllers/VeiculoController.php) e [UserController.php](../backend/controllers/UserController.php) para usar a politica central
- Adaptados [dashboard.php](../frontend/views/dashboard.php), [user_management.php](../frontend/views/user_management.php) e [sidebar.php](../frontend/includes/sidebar.php) para consumir o RBAC central
- Atualizado [UserModel.php](../backend/models/UserModel.php) para aceitar os papeis oficiais, incluindo `auditor`
- Criado o teste [test-rbac-veiculos.php](../scripts/test-rbac-veiculos.php)
- Atualizada a task em [task_08_rbac_alinhado_regras_negocio.md](./ciclo_01_fundacao_arquitetura/task_08_rbac_alinhado_regras_negocio.md)

### Resultado tecnico
- O modulo de veiculos passou a usar uma base unica de permissao para leitura e escrita
- O gerenciamento de usuarios ficou restrito por permissao central, sem depender de comparacoes soltas de role
- O perfil `auditor` passou a existir como opcao valida e com acesso de leitura, conforme as regras oficiais

### Validacao realizada
- `test-rbac-veiculos.php` executado com sucesso
- `test-veiculo-controller-flow.php` executado com sucesso
- `test-audit-flow.php` executado com sucesso
- `test-veiculo-service.php` executado com sucesso

### Proximo passo recomendado
- Revisar a camada de leitura do dashboard para migrar tambem a consulta de veiculos para a nova espinha dorsal

## 2026-04-05 - Task 09

### Modulo inicial de motoristas
- Criado o model [MotoristaModel.php](../backend/models/MotoristaModel.php)
- Criado o controller [MotoristaController.php](../backend/controllers/MotoristaController.php)
- Criada a tela [motoristas.php](../frontend/views/motoristas.php)
- Criado o entrypoint [motoristas.php](../public/motoristas.php)
- Atualizado o menu lateral para expor o modulo aos perfis com leitura de frota
- Atualizado o bootstrap em [bootstrap-db.php](../scripts/bootstrap-db.php) para schema de motorista mais aderente ao ciclo 02
- Criado o teste [test-motorista-model.php](../scripts/test-motorista-model.php)
- Atualizada a task em [task_09_motoristas.md](./ciclo_02_frota_municipal/task_09_motoristas.md)

### Resultado tecnico
- o sistema agora possui cadastro, listagem e edicao basica de motoristas
- o modulo respeita RBAC ja existente
- o fluxo mutavel registra auditoria minima
- o schema ficou preparado para secretaria de lotacao e evolucao futura para viagens

### Validacao realizada
- `php scripts/bootstrap-db.php`
- `php scripts/test-motorista-model.php`

### Proximo passo recomendado
- Executar a `Task 10`: historico de manutencao por veiculo

## 2026-04-05 - Task 10

### Historico de manutencao por veiculo
- Criado o controller [ManutencaoController.php](../backend/controllers/ManutencaoController.php)
- Reestruturado o model [ManutencaoModel.php](../backend/models/ManutencaoModel.php)
- Criada a tela [manutencoes.php](../frontend/views/manutencoes.php)
- Criado o entrypoint [manutencoes.php](../public/manutencoes.php)
- Atualizado o menu lateral para expor o modulo
- Expandido o schema em [bootstrap-db.php](../scripts/bootstrap-db.php) para historico de manutencao
- Criado o teste [test-manutencao-model.php](../scripts/test-manutencao-model.php)
- Atualizada a task em [task_10_manutencao_historico.md](./ciclo_02_frota_municipal/task_10_manutencao_historico.md)

### Resultado tecnico
- o sistema agora registra manutencao com tipo, status, fornecedor, custos e observacoes
- o historico fica preservado por veiculo
- o status do veiculo e sincronizado com manutencoes abertas ou em andamento
- o modulo fica pronto para alimentar dashboard e previsao futura

### Validacao realizada
- `php scripts/bootstrap-db.php`
- `php scripts/test-manutencao-model.php`

### Proximo passo recomendado
- Executar a `Task 11`: controle de abastecimento

## 2026-04-05 - Task 11

### Controle de abastecimento
- Criado o controller [AbastecimentoController.php](../backend/controllers/AbastecimentoController.php)
- Criado o model [AbastecimentoModel.php](../backend/models/AbastecimentoModel.php)
- Criada a tela [abastecimentos.php](../frontend/views/abastecimentos.php)
- Criado o entrypoint [abastecimentos.php](../public/abastecimentos.php)
- Atualizado o menu lateral para expor o modulo
- Expandido o schema em [bootstrap-db.php](../scripts/bootstrap-db.php) para historico de abastecimentos
- Criado o teste [test-abastecimento-model.php](../scripts/test-abastecimento-model.php)
- Atualizada a task em [task_11_abastecimento.md](./ciclo_02_frota_municipal/task_11_abastecimento.md)

### Resultado tecnico
- o sistema agora registra abastecimentos com veiculo, motorista, combustivel, litros, valor e km atual
- o historico pode ser filtrado por veiculo e por periodo
- a base ficou pronta para dashboard de custo e leitura futura de consumo medio
- o modulo registra auditoria minima nas alteracoes

### Validacao realizada
- `php scripts/bootstrap-db.php`
- `php scripts/test-abastecimento-model.php`
- `php -l` executado com sucesso nos arquivos novos do modulo

### Proximo passo recomendado
- Executar a `Task 12`: dashboard operacional da frota

## 2026-04-05 - Task 12

### Dashboard operacional da frota
- Evoluida a tela [dashboard.php](../frontend/views/dashboard.php) com indicadores gerenciais e operacionais reais
- Expandido [AbastecimentoModel.php](../backend/models/AbastecimentoModel.php) com leitura de recentes e total por periodo
- Expandido [ManutencaoModel.php](../backend/models/ManutencaoModel.php) com leitura de manutencoes recentes
- Atualizada a task em [task_12_dashboard_operacional.md](./ciclo_02_frota_municipal/task_12_dashboard_operacional.md)

### Resultado tecnico
- o painel agora mostra frota, operacao, manutencao, custo do periodo, motoristas ativos e CNHs vencendo
- o dashboard passou a destacar alertas operacionais e atalhos para os modulos principais
- a pagina inicial agora exibe abastecimentos e manutencoes recentes com dados reais
- a segmentacao inicial por secretaria ficou preparada no proprio painel

### Validacao realizada
- `php -l backend/models/AbastecimentoModel.php`
- `php -l backend/models/ManutencaoModel.php`
- `php -l frontend/views/dashboard.php`
- `php -l public/dashboard.php`
- `http://127.0.0.1:8000/dashboard.php` validado com `200` apos login

### Proximo passo recomendado
- Executar a `Task 13`: operacao de uso da frota com viagens e rotas

## 2026-04-05 - Task 13

### Operacao de uso da frota com viagens e rotas
- Criado o controller [ViagemController.php](../backend/controllers/ViagemController.php)
- Criado o model [ViagemModel.php](../backend/models/ViagemModel.php)
- Criada a tela [viagens.php](../frontend/views/viagens.php)
- Criado o entrypoint [viagens.php](../public/viagens.php)
- Atualizado o menu lateral para expor o modulo
- Expandido o schema em [bootstrap-db.php](../scripts/bootstrap-db.php) para compatibilizar `viagens` com o modulo operacional
- Criado o teste [test-viagem-model.php](../scripts/test-viagem-model.php)
- Atualizada a task em [task_13_viagens_rotas.md](./ciclo_02_frota_municipal/task_13_viagens_rotas.md)

### Resultado tecnico
- o sistema agora registra o uso da frota com secretaria, solicitante, veiculo, motorista, trajeto, horario e km
- o historico pode ser filtrado por status e secretaria
- o modulo abre caminho para indicadores futuros de utilizacao, custo e produtividade
- a base ficou integrada ao fluxo operacional do ciclo 02 sem exigir framework ou cadastro extra de secretaria

### Validacao realizada
- `php scripts/bootstrap-db.php`
- `php scripts/test-viagem-model.php`
- `php -l` executado com sucesso nos arquivos novos do modulo
- `http://127.0.0.1:8000/viagens.php` validado com `200` apos login

### Proximo passo recomendado
- Executar a `Task 14`: fornecedores, oficinas e parceiros operacionais

## 2026-04-05 - Task 14

### Fornecedores, oficinas e parceiros operacionais
- Criado o controller [ParceiroOperacionalController.php](../backend/controllers/ParceiroOperacionalController.php)
- Criado o model [ParceiroOperacionalModel.php](../backend/models/ParceiroOperacionalModel.php)
- Criada a tela [parceiros.php](../frontend/views/parceiros.php)
- Criado o entrypoint [parceiros.php](../public/parceiros.php)
- Atualizado o menu lateral para expor o modulo
- Expandido o schema em [bootstrap-db.php](../scripts/bootstrap-db.php) para cadastro central de parceiros e vinculo operacional
- Integrados [ManutencaoModel.php](../backend/models/ManutencaoModel.php) e [AbastecimentoModel.php](../backend/models/AbastecimentoModel.php) com `parceiro_id`
- Criado o teste [test-parceiro-operacional-model.php](../scripts/test-parceiro-operacional-model.php)
- Atualizada a task em [task_14_fornecedores_oficinas.md](./ciclo_02_frota_municipal/task_14_fornecedores_oficinas.md)

### Resultado tecnico
- o sistema agora possui uma base unica para oficinas, postos, fornecedores de pecas e prestadores
- manutencoes e abastecimentos podem ser vinculados a parceiros reais sem perder compatibilidade com registros antigos
- o cadastro ficou pronto para relatorios futuros por parceiro e por tipo de operacao
- a rastreabilidade do gasto operacional ficou mais forte e menos dependente de texto solto

### Validacao realizada
- `php scripts/bootstrap-db.php`
- `php scripts/test-parceiro-operacional-model.php`
- `php scripts/test-manutencao-model.php`
- `php scripts/test-abastecimento-model.php`
- `php -l` executado com sucesso nos arquivos novos e integrados
- `http://127.0.0.1:8000/parceiros.php` validado com `200` apos login

### Proximo passo recomendado
- consolidar o backlog do proximo ciclo com base no que ja foi entregue no ciclo 02

## 2026-04-05 - Ciclo 03 aberto

### Planejamento do novo ciclo
- Criado o roadmap [roadmap_ciclo_03.md](./ciclo_03_consolidacao_nucleo/roadmap_ciclo_03.md)
- Criadas as tasks [task_15_cadastro_completo_veiculos.md](./ciclo_03_consolidacao_nucleo/task_15_cadastro_completo_veiculos.md), [task_16_soft_delete_veiculos.md](./ciclo_03_consolidacao_nucleo/task_16_soft_delete_veiculos.md), [task_17_manutencao_preventiva.md](./ciclo_03_consolidacao_nucleo/task_17_manutencao_preventiva.md), [task_18_consumo_alertas_abastecimento.md](./ciclo_03_consolidacao_nucleo/task_18_consumo_alertas_abastecimento.md) e [task_19_relatorios_operacionais.md](./ciclo_03_consolidacao_nucleo/task_19_relatorios_operacionais.md)

### Direcao assumida
- o ciclo 03 ficou focado em consolidacao de cadastro, prevencao e leitura gerencial
- a referencia `sete_ref` foi usada apenas para inspirar profundidade funcional
- a stack do FrotaSmart segue em PHP puro com MySQL

## 2026-04-05 - Task 15

### Consolidacao completa do cadastro de veiculos
- Expandido o schema `veiculos` em [bootstrap-db.php](../scripts/bootstrap-db.php) com os campos operacionais do cadastro municipal
- Enriquecida a entidade [Veiculo.php](../src/Domain/Entities/Veiculo.php) com dados complementares e validacoes de entrada
- Adaptados [VeiculoService.php](../src/Application/Services/VeiculoService.php), [PdoVeiculoRepository.php](../src/Infrastructure/Persistence/PdoVeiculoRepository.php) e [VeiculoController.php](../backend/controllers/VeiculoController.php)
- Reescrito o model legado [VeiculoModel.php](../backend/models/VeiculoModel.php) para respeitar `deleted_at` e ler o cadastro consolidado
- Atualizada a view [dashboard.php](../frontend/views/dashboard.php) com formulario mais completo e leitura operacional da lotacao
- Atualizada a task em [task_15_cadastro_completo_veiculos.md](./ciclo_03_consolidacao_nucleo/task_15_cadastro_completo_veiculos.md)

### Resultado tecnico
- o cadastro de veiculos passou a refletir melhor a realidade da frota por secretaria
- a base ficou pronta para evoluir `soft delete` e arquivamento sem perder rastreabilidade
- o modulo continuou compativel com MySQL no WSL e com o fluxo autenticado atual

### Validacao realizada
- `php -l` nos arquivos alterados pelo WSL
- `php scripts/test-domain.php`
- `php scripts/test-veiculo-service.php`
- `php scripts/test-veiculo-controller-flow.php`
- `php scripts/bootstrap-db.php`
- `php scripts/test-repository-pdo.php`
- login em `http://127.0.0.1:8000/auth.php` com redirecionamento final para `dashboard.php` e `200 OK`

### Proximo passo recomendado
- Executar a `Task 16`: soft delete, arquivamento e historico forte de veiculos

## 2026-04-05 - Task 16

### Soft delete, arquivamento e historico forte de veiculos
- Evoluido o contrato [VeiculoRepositoryInterface.php](../src/Domain/Repositories/VeiculoRepositoryInterface.php) com consulta expandida, listagem de arquivados e restauracao
- Enriquecida a entidade [Veiculo.php](../src/Domain/Entities/Veiculo.php) com `arquivadoEm()` e `estaArquivado()`
- Adaptados [VeiculoService.php](../src/Application/Services/VeiculoService.php), [PdoVeiculoRepository.php](../src/Infrastructure/Persistence/PdoVeiculoRepository.php) e [VeiculoController.php](../backend/controllers/VeiculoController.php) para arquivar e restaurar
- Atualizado o legado [VeiculoModel.php](../backend/models/VeiculoModel.php) com filtros `ativos`, `arquivados` e `todos`
- Atualizada a view [dashboard.php](../frontend/views/dashboard.php) com leitura de historico, contador de arquivados e acao de restauracao
- Atualizados os testes [test-veiculo-service.php](../scripts/test-veiculo-service.php), [test-veiculo-controller-flow.php](../scripts/test-veiculo-controller-flow.php), [test-repository-pdo.php](../scripts/test-repository-pdo.php) e [test-repository-contract.php](../scripts/test-repository-contract.php)

### Resultado tecnico
- arquivamento e restauracao passaram a ser operacoes explicitas e auditaveis
- placas arquivadas continuam protegidas contra reaproveitamento silencioso
- a consulta operacional consegue separar frota ativa do historico arquivado

### Validacao realizada
- revisao local das assinaturas e dos fluxos alterados
- tentativa de executar `php -l` e scripts de teste bloqueada porque `php` nao esta disponivel no `PATH` do PowerShell atual
- tentativa de validar pelo `wsl` tambem bloqueada por `E_ACCESSDENIED` neste ambiente

### Proximo passo recomendado
- Executar a `Task 17`: manutencao preventiva por km e por data

## 2026-04-05 - Task 17

### Manutencao preventiva por km e por data
- Expandido o bootstrap [bootstrap-db.php](../scripts/bootstrap-db.php) com campos de plano preventivo na tabela `manutencoes`
- Evoluido [ManutencaoModel.php](../backend/models/ManutencaoModel.php) para calcular previsao por data e por km, km atual do veiculo e alertas preventivos
- Atualizado [ManutencaoController.php](../backend/controllers/ManutencaoController.php) com validacao de regras preventivas
- Atualizadas as views [manutencoes.php](../frontend/views/manutencoes.php) e [dashboard.php](../frontend/views/dashboard.php) com contadores e alertas de preventivas vencidas e proximas
- Atualizado o teste [test-manutencao-model.php](../scripts/test-manutencao-model.php) para cobrir a leitura preventiva
- Atualizada a task em [task_17_manutencao_preventiva.md](./ciclo_03_consolidacao_nucleo/task_17_manutencao_preventiva.md)

### Resultado tecnico
- o sistema agora suporta plano preventivo por data, por km e por recorrencia
- o dashboard e a tela de manutencoes conseguem destacar itens vencidos e proximos
- o km operacional passa a alimentar a leitura preventiva sem exigir nova stack

### Validacao realizada
- `php -l backend/models/ManutencaoModel.php`
- `php -l backend/controllers/ManutencaoController.php`
- `php -l frontend/views/manutencoes.php`
- `php -l frontend/views/dashboard.php`
- `php -l scripts/bootstrap-db.php`
- `php -l scripts/test-manutencao-model.php`
- tentativa de executar `scripts/bootstrap-db.php` e `scripts/test-manutencao-model.php` bloqueada por acesso negado ao MySQL (`SQLSTATE[HY000] [1045]`)

### Proximo passo recomendado
- Executar a `Task 18`: consumo medio e alertas de abastecimento

## 2026-04-05 - Task 18

### Consumo medio e alertas de abastecimento
- Evoluido [AbastecimentoModel.php](../backend/models/AbastecimentoModel.php) com calculo de consumo por km/L, custo por litro, custo por km, ranking de eficiencia e leitura automatica de anomalias
- Atualizadas as views [abastecimentos.php](../frontend/views/abastecimentos.php) e [dashboard.php](../frontend/views/dashboard.php) com indicadores de consumo e alertas de suspeita
- Atualizado o teste [test-abastecimento-model.php](../scripts/test-abastecimento-model.php) para cobrir resumo consolidado e ranking
- Atualizada a task em [task_18_consumo_alertas_abastecimento.md](./ciclo_03_consolidacao_nucleo/task_18_consumo_alertas_abastecimento.md)

### Resultado tecnico
- cada abastecimento agora pode ser comparado com o anterior para medir variacao de litros, valor e consumo
- o sistema consegue sinalizar registros de atencao e criticos por comportamento fora do padrao
- a leitura de eficiencia por veiculo ficou pronta para consolidacao em relatorios

### Validacao realizada
- `php -l backend/models/AbastecimentoModel.php`
- `php -l frontend/views/abastecimentos.php`
- `php -l frontend/views/dashboard.php`
- `php -l scripts/test-abastecimento-model.php`
- tentativa de executar `scripts/test-abastecimento-model.php` bloqueada por acesso negado ao MySQL (`SQLSTATE[HY000] [1045]`)

### Proximo passo recomendado
- Executar a `Task 19`: relatorios operacionais com exportacao

## 2026-04-05 - Task 19

### Relatorios operacionais com exportacao
- Criado [RelatorioOperacionalModel.php](../backend/models/RelatorioOperacionalModel.php) para consolidar abastecimentos, manutencoes, viagens e disponibilidade
- Criada a pagina [relatorios.php](../frontend/views/relatorios.php) com filtros por periodo, secretaria, veiculo e status
- Criada a rota publica [relatorios.php](../public/relatorios.php) com exportacao inicial em CSV
- Integrado o acesso ao modulo pelo menu em [sidebar.php](../frontend/includes/sidebar.php)
- Atualizada a task em [task_19_relatorios_operacionais.md](./ciclo_03_consolidacao_nucleo/task_19_relatorios_operacionais.md)

### Resultado tecnico
- o sistema passou a ter uma camada unica de consulta gerencial para operacao e transparencia
- a exportacao CSV fecha o ciclo 03 com uma saida simples e reutilizavel
- a modelagem ficou pronta para evoluir depois para PDF e relatorios mais formais

### Validacao realizada
- `php -l backend/models/RelatorioOperacionalModel.php`
- `php -l frontend/views/relatorios.php`
- `php -l public/relatorios.php`
- `php -l frontend/includes/sidebar.php`

### Fechamento do ciclo
- ciclo 03 concluido com cadastro consolidado, arquivamento, manutencao preventiva, leitura de abastecimento e relatorios operacionais

## 2026-04-05 - Estabilizacao WSL apos ciclo 03

### Validacao real no Ubuntu WSL
- confirmada a conexao do projeto no Linux com `DB_HOST=127.0.0.1`, `DB_NAME=frota_smart`, `DB_USER=frota_user`
- executado com sucesso `php scripts/bootstrap-db.php` no Ubuntu WSL
- executado com sucesso `php scripts/test-repository-pdo.php` no Ubuntu WSL
- executado com sucesso `php scripts/test-manutencao-model.php` no Ubuntu WSL
- corrigido [AbastecimentoModel.php](../backend/models/AbastecimentoModel.php) para que `findById()` preserve a leitura analitica baseada no historico do veiculo
- executado com sucesso `php scripts/test-abastecimento-model.php` no Ubuntu WSL

### Resultado tecnico
- o bloqueio de validacao integrada deixou de ser um problema estrutural do projeto
- o caminho principal de desenvolvimento ficou confirmado no Ubuntu WSL, alinhado com os guias do repositorio
- a base agora esta pronta para um ciclo 04 mais orientado a governanca, automacao e uso continuo

## 2026-04-05 - Ciclo 04 proposto

### Novo roadmap sugerido
- criado [roadmap_ciclo_04.md](./ciclo_04_estabilidade_governanca/roadmap_ciclo_04.md)

### Direcao recomendada
- estabilizar completamente o fluxo Linux/WSL como ambiente padrao
- reforcar regras automaticas de bloqueio e alerta
- subir o nivel do painel executivo e da auditoria

## 2026-04-05 - Task 20

### Estabilizacao definitiva do ambiente WSL e validacao integrada
- Ajustado [security.php](../backend/config/security.php) para salvar sessoes em `runtime/sessions`, sem depender de configuracao externa do PHP
- Simplificado [db.php](../backend/config/db.php) para reaproveitar `EnvLoader` e reduzir duplicacao de bootstrap de ambiente
- Criado [test-wsl-stack.php](../scripts/test-wsl-stack.php) como health check unico do ambiente Linux/WSL
- Adicionado o script Composer `test:wsl-stack` em [composer.json](../composer.json)
- Atualizado o guia [readme_wsl_ubuntu_windows.md](./readme_wsl_ubuntu_windows.md) com o novo fluxo de validacao integrada
- Atualizada a task em [task_20_estabilizacao_wsl_validacao_integrada.md](./ciclo_04_estabilidade_governanca/task_20_estabilizacao_wsl_validacao_integrada.md)

### Validacao realizada
- `php scripts/test-wsl-stack.php` executado com sucesso no Ubuntu WSL
- validacoes internas do health check:
- conexao PDO
- `bootstrap-db.php`
- `test-repository-pdo.php`
- `test-manutencao-model.php`
- `test-abastecimento-model.php`

### Proximo passo recomendado
- Executar a `Task 21`: regras operacionais automaticas de bloqueio e alerta

## 2026-04-05 - Task 21

### Regras operacionais automaticas de bloqueio e alerta
- Criado o guard [OperacaoFrotaGuard.php](../backend/models/OperacaoFrotaGuard.php) para avaliar bloqueios e alertas de viagem e abastecimento
- Evoluido [VeiculoModel.php](../backend/models/VeiculoModel.php) com `findById()` para leitura pontual de estado operacional
- Evoluido [ManutencaoModel.php](../backend/models/ManutencaoModel.php) com avaliacao preventiva por veiculo, data e km de referencia
- Adaptados [ViagemController.php](../backend/controllers/ViagemController.php) e [AbastecimentoController.php](../backend/controllers/AbastecimentoController.php) para barrar operacoes criticas e devolver avisos no flash de sucesso
- Criado o teste [test-operacao-frota-guard.php](../scripts/test-operacao-frota-guard.php)
- Atualizados [test-wsl-stack.php](../scripts/test-wsl-stack.php) e [composer.json](../composer.json) para incluir a nova validacao
- Atualizada a task em [task_21_regras_operacionais_bloqueio_alerta.md](./ciclo_04_estabilidade_governanca/task_21_regras_operacionais_bloqueio_alerta.md)

### Resultado tecnico
- viagens agora podem ser bloqueadas automaticamente por veiculo arquivado, em manutencao, baixado, em viagem, CNH vencida ou preventiva vencida
- abastecimentos agora ganham alertas operacionais em casos de preventiva vencida ou proxima, CNH proxima do vencimento e situacao de manutencao do veiculo
- a leitura das regras saiu do dashboard passivo e passou a agir direto no fluxo de registro

### Validacao realizada
- `php -l backend/models/OperacaoFrotaGuard.php`
- `php -l backend/models/ManutencaoModel.php`
- `php -l backend/controllers/ViagemController.php`
- `php -l backend/controllers/AbastecimentoController.php`
- `php -l scripts/test-operacao-frota-guard.php`

### Proximo passo recomendado
- Executar a `Task 22`: painel executivo por secretaria e por veiculo

## 2026-04-11 - Task 22

### Painel executivo por secretaria e por veiculo
- Evoluido [RelatorioOperacionalModel.php](../backend/models/RelatorioOperacionalModel.php) com agregacoes executivas por secretaria e por veiculo
- Enriquecidos [AbastecimentoModel.php](../backend/models/AbastecimentoModel.php) e [ManutencaoModel.php](../backend/models/ManutencaoModel.php) com dados adicionais de lotacao para consolidacao
- Atualizada a view [dashboard.php](../frontend/views/dashboard.php) com cards executivos, leitura por secretaria e ranking por veiculo
- Criado o teste [test-relatorio-executivo.php](../scripts/test-relatorio-executivo.php)
- Atualizados [test-wsl-stack.php](../scripts/test-wsl-stack.php) e [composer.json](../composer.json) para incluir a nova validacao
- Atualizada a task em [task_22_painel_executivo_secretaria_veiculo.md](./ciclo_04_estabilidade_governanca/task_22_painel_executivo_secretaria_veiculo.md)

### Resultado tecnico
- o dashboard passou a cruzar custo, disponibilidade, viagens, abastecimentos e preventivas por secretaria
- a leitura por veiculo agora destaca os itens mais sensiveis do periodo, em vez de depender apenas da consulta transacional
- o ciclo 04 ganhou uma camada executiva reaproveitavel para auditoria e exportacao futura

### Validacao realizada
- `php -l backend/models/AbastecimentoModel.php`
- `php -l backend/models/ManutencaoModel.php`
- `php -l backend/models/RelatorioOperacionalModel.php`
- `php -l frontend/views/dashboard.php`
- `php -l scripts/test-relatorio-executivo.php`
- tentativa de executar `php scripts/test-relatorio-executivo.php` bloqueada por acesso negado ao MySQL (`SQLSTATE[HY000] [1045]`)

### Proximo passo recomendado
- Executar a `Task 23`: auditoria expandida e trilha de exportacao

## 2026-04-13 - Task 23

### Auditoria expandida e trilha de exportacao
- Criados [CompositeAuditLogger.php](../src/Infrastructure/Audit/CompositeAuditLogger.php) e [PdoAuditLogger.php](../src/Infrastructure/Audit/PdoAuditLogger.php)
- Evoluido [security.php](../backend/config/security.php) para centralizar `audit_log()` em auditoria estruturada e persistente
- Adaptado [VeiculoController.php](../backend/controllers/VeiculoController.php) para usar a mesma trilha combinada de log tecnico e banco
- Expandido [bootstrap-db.php](../scripts/bootstrap-db.php) com a tabela `audit_logs`
- Evoluido [RelatorioOperacionalModel.php](../backend/models/RelatorioOperacionalModel.php) com leitura, resumo e exportacao CSV da auditoria
- Atualizada a tela [relatorios.php](../frontend/views/relatorios.php) com aba de auditoria e filtros especificos
- Criado o teste [test-auditoria-relatorio.php](../scripts/test-auditoria-relatorio.php)
- Atualizados [test-wsl-stack.php](../scripts/test-wsl-stack.php), [composer.json](../composer.json) e a task [task_23_auditoria_expandida_trilha_exportacao.md](./ciclo_04_estabilidade_governanca/task_23_auditoria_expandida_trilha_exportacao.md)

### Resultado tecnico
- a auditoria agora pode ser consultada por ator, evento, modulo, acao e periodo
- exportacoes CSV passaram a gerar seu proprio rastro auditavel
- a governanca operacional ficou menos dependente de leitura manual de logs tecnicos

### Validacao realizada
- validacao de sintaxe executada com sucesso nos arquivos alterados da task
- tentativa de executar `scripts/bootstrap-db.php` e `scripts/test-auditoria-relatorio.php` fora do ambiente Linux oficial bloqueada por acesso negado do MySQL (`SQLSTATE[HY000] [1045]`)

### Proximo passo recomendado
- Executar a `Task 24`: refino tecnico da persistencia e reducao de acoplamento legado

## 2026-04-13 - Task 24

### Refino tecnico da persistencia e reducao de acoplamento legado
- Criado o service [VeiculoDashboardService.php](../src/Application/Services/VeiculoDashboardService.php) para expor a frota do dashboard sobre `PdoVeiculoRepository`
- Atualizada a view [dashboard.php](../frontend/views/dashboard.php) para consumir a leitura nova em `src/`, sem depender de [VeiculoModel.php](../backend/models/VeiculoModel.php) na consulta principal
- Criado o teste [test-veiculo-dashboard-service.php](../scripts/test-veiculo-dashboard-service.php) para validar filtros, ordenacao e contagem de arquivados
- Atualizada a task em [task_24_refino_tecnico_persistencia_reducao_acoplamento_legado.md](./ciclo_04_estabilidade_governanca/task_24_refino_tecnico_persistencia_reducao_acoplamento_legado.md)

### Resultado tecnico
- a principal leitura de frota da pagina inicial passou a usar a mesma espinha de persistencia moderna aplicada na escrita
- a reducao de acoplamento deixou de ser apenas conceitual e passou a remover uma dependencia concreta de `global $pdo` no fluxo mais visivel do sistema
- o modulo de relatorios agora tambem aceita conexao explicita nos entrypoints principais, reduzindo a dependencia de `global $pdo`
- o SQL de leitura mais sensivel do modulo de relatorios passou a ser concentrado em `RelatorioOperacionalQueryService`, preparando uma camada de read model mais limpa

### Validacao realizada
- validacao de sintaxe executada com sucesso nos arquivos alterados da task
- `php scripts/test-veiculo-dashboard-service.php` executado com sucesso em validacao local
- `php scripts/test-auditoria-relatorio.php` executado com sucesso no Ubuntu WSL
- `php scripts/test-relatorio-executivo.php` executado com sucesso no Ubuntu WSL

### Proximo passo recomendado
- continuar a `Task 24` extraindo consultas e agregacoes restantes de `RelatorioOperacionalModel` para uma camada de leitura mais dedicada

## 2026-04-13 - Padrao Clean Code

### Guia de adocao do projeto
- Criado o guia [padrao_clean_code_frotasmart.md](../engenharia/padrao_clean_code_frotasmart.md) com diretrizes praticas para regra do escoteiro, tamanhos pequenos, comentarios, nomes significativos, formatacao, refatoracao, complexidade ciclomática, excecoes, consistencia, testes de unidade, booleanos, nulos e funcoes puras
- Registrada uma analise objetiva dos hotspots atuais do FrotaSmart, com foco em views grandes, controllers com validacao extensa e pontos de acoplamento residual

### Primeira execucao no codigo
- Refatorado [ViagemController.php](../backend/controllers/ViagemController.php) para reduzir complexidade em `validatedPayload()`
- Extraidas validacoes para metodos pequenos e nomeados, como `assertRequiredSelections()`, `assertRequiredTextFields()`, `assertDateFields()`, `assertKilometers()` e `assertStatus()`
- Padronizado o uso de constante para status aceitos e helper para campos textuais opcionais
- Refatorados [ManutencaoController.php](../backend/controllers/ManutencaoController.php) e [AbastecimentoController.php](../backend/controllers/AbastecimentoController.php) seguindo o mesmo padrao
- As validacoes agora foram separadas em blocos menores e sem concentrar toda a regra de entrada em um unico metodo grande

### Validacao realizada
- `php -l backend/controllers/ViagemController.php`
- `php -l public/viagens.php`
- `php -l frontend/views/viagens.php`
- `php -l backend/controllers/ManutencaoController.php`
- `php -l backend/controllers/AbastecimentoController.php`
- `php -l public/manutencoes.php`
- `php -l public/abastecimentos.php`
- `php -l frontend/views/manutencoes.php`
- `php -l frontend/views/abastecimentos.php`
- `php scripts/test-wsl-stack.php` executado com sucesso no Ubuntu WSL apos a rodada de refatoracao
- observacao: o projeto ainda nao possui testes automatizados especificos para esses controllers

### Proximo passo recomendado
- aplicar o mesmo padrao em views grandes e revisar contratos que ainda dependem de booleanos e `null`
