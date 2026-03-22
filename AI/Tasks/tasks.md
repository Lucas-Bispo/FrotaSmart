# Roadmap de Tasks - FrotaSmart

## Visao geral
Este arquivo e o quadro de andamento real do projeto.
Cada task precisa ser executavel, ter criterio de aceite e respeitar a arquitetura oficial definida em `AI/Contexto`.

## Estado atual do projeto
- Arquitetura atual: MVC simples com `backend/` e `frontend/`
- Arquitetura alvo: Clean Architecture adaptada com `src/`
- Estrategia: migracao incremental, sem reescrever tudo de uma vez

## Backlog principal

### Task 01 - Fundacao da arquitetura com Composer e PSR-4
- Status: em andamento
- Objetivo: criar a base `src/`, `composer.json` e autoload PSR-4
- Concluido:
  - estrutura `src/` criada
  - `composer.json` criado
  - classe inicial namespaced criada
  - progresso documentado
- Pendente:
  - instalar/configurar PHP no PATH
  - instalar/configurar Composer no PATH
  - rodar `composer dump-autoload`
  - executar teste real do autoload
- Bloqueio atual: ambiente sem `php` e `composer` acessiveis

### Task 02 - Dominio inicial de veiculos
- Status: planejada
- Objetivo: evoluir `Veiculo` para entidade rica e criar `Placa` como Value Object
- Criterio de aceite:
  - validacao de placa no dominio
  - entidade sem dependencia de banco ou HTTP
  - testes de regra de negocio preparados

### Task 03 - Contrato de repositorio
- Status: planejada
- Objetivo: criar interface de repositorio em `Domain/Repositories`
- Criterio de aceite:
  - contrato desacoplado de PDO
  - assinatura compativel com casos de uso futuros

### Task 04 - Persistencia PDO na nova arquitetura
- Status: planejada
- Objetivo: criar repositorio concreto em `Infrastructure/Persistence`
- Criterio de aceite:
  - injecao de dependencias
  - sem `global $pdo`
  - prepared statements

### Task 05 - Service de aplicacao para veiculos
- Status: planejada
- Objetivo: mover regras de orquestracao para `Application/Services`
- Criterio de aceite:
  - validacoes centralizadas
  - ponto unico para casos de uso de veiculo

### Task 06 - Adaptacao do controller legado
- Status: planejada
- Objetivo: fazer `backend/controllers/VeiculoController.php` consumir o novo service
- Criterio de aceite:
  - controller fino
  - sem regra de negocio relevante no controller

### Task 07 - Auditoria minima obrigatoria
- Status: planejada
- Objetivo: iniciar trilha de auditoria para operacoes mutaveis
- Criterio de aceite:
  - definicao tecnica da auditoria
  - pontos de captura mapeados

### Task 08 - RBAC alinhado com regras de negocio
- Status: planejada
- Objetivo: alinhar perfis atuais com as regras oficiais de `AI/Contexto/Regras-Negocio.md`
- Criterio de aceite:
  - perfis consistentes
  - restricoes aplicadas no fluxo de veiculos

## Regras de execucao
- Sempre validar o contexto em `AI/Contexto` antes de implementar
- Sempre registrar status e decisoes aqui
- Sempre preferir migracao incremental em vez de reescrita ampla
- Toda task nova deve ter objetivo, criterio de aceite e bloqueios claros
