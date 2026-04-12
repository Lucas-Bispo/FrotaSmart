# Roadmap do Ciclo 01 - Fundacao da Arquitetura

## Visao geral
Este arquivo e o quadro de andamento real do projeto.
Cada task precisa ser executavel, ter criterio de aceite e respeitar a arquitetura oficial definida em `contexto/`.

## Navegacao rapida
- Progresso geral: [../progresso.md](../progresso.md)
- Arquitetura: [../arquitetura_projeto.md](../arquitetura_projeto.md)
- Estado atual: [../estado_projeto.md](../estado_projeto.md)
- Regras de negocio: [../regras_negocio.md](../regras_negocio.md)
- Transicao Linux: [../contexto_transicao.md](../contexto_transicao.md)

## Estado atual do projeto
- Arquitetura atual: MVC simples com `backend/` e `frontend/`
- Arquitetura alvo: Clean Architecture adaptada com `src/`
- Estrategia: migracao incremental, sem reescrever tudo de uma vez

## Backlog principal

### Task 01 - Fundacao da arquitetura com Composer e PSR-4
- Status: concluida
- Objetivo: criar a base `src/`, `composer.json` e autoload PSR-4
- Documento detalhado: [task_01_fundacao_arquitetura_composer_psr4.md](./task_01_fundacao_arquitetura_composer_psr4.md)

### Task 02 - Dominio inicial de veiculos
- Status: concluida
- Objetivo: evoluir `Veiculo` para entidade rica e criar `Placa` como Value Object
- Documento detalhado: [task_02_dominio_inicial_veiculos.md](./task_02_dominio_inicial_veiculos.md)

### Task 03 - Contrato de repositorio
- Status: concluida
- Objetivo: criar interface de repositorio em `Domain/Repositories`
- Documento detalhado: [task_03_contrato_repositorio_veiculos.md](./task_03_contrato_repositorio_veiculos.md)

### Task 03.1 - Operacao Clean Linux
- Status: concluida
- Objetivo: preparar o projeto para execucao padronizada em Linux Ubuntu e WSL sem dependencias legadas de Windows
- Documento detalhado: [task_03_1_operacao_clean_linux.md](./task_03_1_operacao_clean_linux.md)

### Task 04 - Persistencia PDO na nova arquitetura
- Status: concluida
- Objetivo: criar repositorio concreto em `Infrastructure/Persistence`
- Documento detalhado: [task_04_persistencia_pdo_nova_arquitetura.md](./task_04_persistencia_pdo_nova_arquitetura.md)

### Task 05 - Service de aplicacao para veiculos
- Status: concluida
- Objetivo: mover regras de orquestracao para `Application/Services`
- Documento detalhado: [task_05_service_aplicacao_veiculos.md](./task_05_service_aplicacao_veiculos.md)

### Task 06 - Adaptacao do controller legado
- Status: concluida
- Objetivo: fazer `backend/controllers/VeiculoController.php` consumir o novo service
- Documento detalhado: [task_06_adaptacao_controller_legado_veiculos.md](./task_06_adaptacao_controller_legado_veiculos.md)

### Task 07 - Auditoria minima obrigatoria
- Status: concluida
- Objetivo: iniciar trilha de auditoria para operacoes mutaveis
- Documento detalhado: [task_07_auditoria_minima_obrigatoria.md](./task_07_auditoria_minima_obrigatoria.md)

### Task 08 - RBAC alinhado com regras de negocio
- Status: concluida
- Objetivo: alinhar perfis atuais com as regras oficiais de [regras_negocio.md](../regras_negocio.md)
- Documento detalhado: [task_08_rbac_alinhado_regras_negocio.md](./task_08_rbac_alinhado_regras_negocio.md)
