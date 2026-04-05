# Progresso - FrotaSmart

## Navegacao rapida
- Roadmap: [roadmap_tasks.md](./tasks/roadmap_tasks.md)
- Arquitetura: [arquitetura_projeto.md](./arquitetura_projeto.md)
- Estado atual: [estado_projeto.md](./estado_projeto.md)
- Regras de negocio: [regras_negocio.md](./regras_negocio.md)
- Guia Linux: [readme_linux.md](./readme_linux.md)

## 2026-03-22

### Task 01 - Fundacao da arquitetura
- Criado `composer.json` na raiz do projeto
- Definido autoload PSR-4 `FrotaSmart\\` -> `src/`
- Criada a estrutura base de camadas em `src/`
- Criada a classe inicial `FrotaSmart\Domain\Entities\Veiculo`
- Criado `scripts/test-autoload.php` para validacao futura
- Atualizado o backlog em [roadmap_tasks.md](./tasks/roadmap_tasks.md)
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
- Registrada a task em [task_02.md](./tasks/task_02.md)
- Atualizado o roadmap em [roadmap_tasks.md](./tasks/roadmap_tasks.md)
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
- Registrada a task em [task_03.md](./tasks/task_03.md)
- Criado o teste de validacao do contrato em [test-repository-contract.php](../scripts/test-repository-contract.php)

### Proximo passo recomendado
- Iniciar a `Task 04`: implementar o repositorio concreto em `src/Infrastructure/Persistence`

## 2026-03-22 - Task 03.1

### Operacao Clean Linux
- Criado o guia [readme_linux.md](./readme_linux.md) com fluxo de execucao em Linux e WSL
- Criada a pasta [public](../public/index.php) como document root recomendado para servidor embutido e deploy Linux
- Ajustadas views e controllers legados para navegar por entrypoints publicos
- Removidas referencias explicitas a XAMPP dos scripts operacionais

### Por que isso foi feito
- reduzir acoplamento com Windows
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
- Atualizada a task em [task_04.md](./tasks/task_04.md)

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
- Atualizada a task em [task_05.md](./tasks/task_05.md)

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
- Atualizada a task em [task_06.md](./tasks/task_06.md)

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
- Atualizada a task em [task_07.md](./tasks/task_07.md)

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
- Atualizada a task em [task_08.md](./tasks/task_08.md)

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
