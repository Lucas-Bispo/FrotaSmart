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
- O projeto ja possui `public/` como document root recomendado para Linux/WSL

## Achados tecnicos
- `backend/models/VeiculoModel.php` usa `global $pdo`
- `backend/controllers/VeiculoController.php` ja delega a escrita para o service, mas a listagem do dashboard ainda vem do model legado
- A auditoria de veiculos agora usa servico e contratos proprios em `src/`
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
- A validacao real do repositorio PDO ainda depende de ajustar as credenciais atuais do banco

## Decisao atual
- Manter `composer.phar` apenas como ferramenta local, fora do versionamento
- Evoluir o modulo de veiculos por migracao incremental a partir do dominio novo
- Consolidar a migracao de leitura e RBAC apos a escrita e a auditoria passarem a usar a nova espinha dorsal
- Preservar a compatibilidade com Linux/WSL e com a publicacao via `public/`
