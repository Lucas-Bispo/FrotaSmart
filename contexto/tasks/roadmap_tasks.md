# Roadmap de tasks - FrotaSmart

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
- Documento detalhado: [task_01.md](./task_01.md)

### Task 02 - Dominio inicial de veiculos
- Status: concluida
- Objetivo: evoluir `Veiculo` para entidade rica e criar `Placa` como Value Object
- Documento detalhado: [task_02.md](./task_02.md)

### Task 03 - Contrato de repositorio
- Status: concluida
- Objetivo: criar interface de repositorio em `Domain/Repositories`
- Documento detalhado: [task_03.md](./task_03.md)

### Task 03.1 - Operacao Clean Linux
- Status: concluida
- Objetivo: preparar o projeto para execucao padronizada em Linux Ubuntu e WSL sem XAMPP
- Documento detalhado: [task_03_1.md](./task_03_1.md)

### Task 04 - Persistencia PDO na nova arquitetura
- Status: planejada
- Objetivo: criar repositorio concreto em `Infrastructure/Persistence`
- Documento detalhado: [task_04.md](./task_04.md)

### Task 05 - Service de aplicacao para veiculos
- Status: planejada
- Objetivo: mover regras de orquestracao para `Application/Services`
- Documento detalhado: [task_05.md](./task_05.md)

### Task 06 - Adaptacao do controller legado
- Status: planejada
- Objetivo: fazer `backend/controllers/VeiculoController.php` consumir o novo service
- Documento detalhado: [task_06.md](./task_06.md)

### Task 07 - Auditoria minima obrigatoria
- Status: planejada
- Objetivo: iniciar trilha de auditoria para operacoes mutaveis
- Documento detalhado: [task_07.md](./task_07.md)

### Task 08 - RBAC alinhado com regras de negocio
- Status: planejada
- Objetivo: alinhar perfis atuais com as regras oficiais de [regras_negocio.md](../regras_negocio.md)
- Documento detalhado: [task_08.md](./task_08.md)
