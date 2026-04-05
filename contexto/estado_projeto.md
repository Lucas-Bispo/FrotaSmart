# Estado atual do projeto

## Navegacao rapida
- Arquitetura: [arquitetura_projeto.md](./arquitetura_projeto.md)
- Regras de negocio: [regras_negocio.md](./regras_negocio.md)
- Roadmap: [roadmap_tasks.md](./tasks/roadmap_tasks.md)
- Progresso: [progresso.md](./progresso.md)
- Contexto de transicao: [contexto_transicao.md](./contexto_transicao.md)

## Data de referencia
2026-04-05

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
- O projeto ja possui `public/` como document root recomendado para Linux/WSL

## Achados tecnicos
- `backend/models/VeiculoModel.php` usa `global $pdo`
- `backend/controllers/VeiculoController.php` ja delega a escrita para o service, mas a listagem do dashboard ainda vem do model legado
- A auditoria de veiculos agora usa servico e contratos proprios em `src/`
- O legado de autorizacao passou a consumir uma politica unica de permissoes por perfil
- O modulo de motoristas ainda usa model legado, mas ja esta integrado ao fluxo principal da aplicacao
- `backend/config/db.php` centraliza conexao e leitura do `.env`
- Ja existem `src/` e `composer.json`
- O ambiente possui PHP local funcional para validacao do projeto
- O Composer foi baixado localmente como `composer.phar`

## Conclusao de viabilidade
A `task_01` era viavel e foi executada como fundacao arquitetural, nao como refactor total do projeto.

## Estrategia recomendada
- Criar a nova base em paralelo ao legado
- Migrar modulo por modulo
- Comecar por veiculos, porque ja existe fluxo funcional e ele e o centro do dominio

## Riscos atuais
- O legado ainda depende de `global $pdo` e `require_once`
- O CRUD legado de veiculos ainda faz `DELETE` fisico, enquanto a regra de negocio pede soft delete
- A leitura do dashboard ainda depende de `VeiculoModel`, mesmo com a escrita ja migrada
- A proxima frente funcional do ciclo 02 e historico de manutencao por veiculo

## Decisao atual
- Manter `composer.phar` apenas como ferramenta local, fora do versionamento
- Evoluir o modulo de veiculos por migracao incremental a partir do dominio novo
- Consolidar a migracao de leitura e finalizar o alinhamento de persistencia com soft delete e banco real
- Preservar a compatibilidade com Linux/WSL e com a publicacao via `public/`
- Evoluir o ciclo 02 mantendo foco em frota municipal, sem desviar para transporte escolar
