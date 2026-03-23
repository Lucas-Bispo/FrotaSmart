# Estado atual do projeto

## Navegacao rapida
- Arquitetura: [Arquitetura-Projeto.md](./Arquitetura-Projeto.md)
- Regras de negocio: [Regras-Negocio.md](./Regras-Negocio.md)
- Roadmap: [tasks.md](../Tasks/tasks.md)
- Progresso: [PROGRESSO.MD](../../PROGRESSO.MD)
- Contexto de transicao: [ContextodeTransicao.md](./ContextodeTransicao.md)

## Data de referencia
2026-03-22

## Leitura rapida
- O FrotaSmart hoje esta organizado em `backend/` e `frontend/`
- A base atual segue um MVC simples
- Ja existe autenticacao, dashboard e CRUD basico de veiculos
- A arquitetura alvo oficial e Clean Architecture adaptada em `src/`
- O dominio novo de veiculos ja possui `Veiculo` e `Placa` em `src/Domain`
- O projeto ja possui `public/` como document root recomendado para Linux/WSL

## Achados tecnicos
- `backend/models/VeiculoModel.php` usa `global $pdo`
- `backend/controllers/VeiculoController.php` mistura validacao, autorizacao e fluxo HTTP
- `backend/config/db.php` centraliza conexao e leitura do `.env`
- Ja existem `src/` e `composer.json`
- O ambiente possui PHP local funcional para validacao do projeto
- O Composer foi baixado localmente como `composer.phar`

## Conclusao de viabilidade
A `task01` era viavel e foi executada como fundacao arquitetural, nao como refactor total do projeto.

## Estrategia recomendada
- Criar a nova base em paralelo ao legado
- Migrar modulo por modulo
- Comecar por veiculos, porque ja existe fluxo funcional e ele e o centro do dominio

## Riscos atuais
- O legado ainda depende de `global $pdo` e `require_once`
- O CRUD legado de veiculos ainda faz `DELETE` fisico, enquanto a regra de negocio pede soft delete
- O dominio novo ainda nao governa o fluxo completo do CRUD em producao

## Decisao atual
- Manter `composer.phar` apenas como ferramenta local, fora do versionamento
- Evoluir o modulo de veiculos por migracao incremental a partir do dominio novo
- Avancar para repositorio concreto, service de aplicacao e adaptacao gradual dos controllers legados
- Preservar a compatibilidade com Linux/WSL e com a publicacao via `public/`

## Guias relacionados
- Arquitetura alvo: [Arquitetura-Projeto.md](./Arquitetura-Projeto.md)
- Regras de negocio: [Regras-Negocio.md](./Regras-Negocio.md)
- Roadmap: [tasks.md](../Tasks/tasks.md)
- Guia Linux: [README_LINUX.md](../../README_LINUX.md)
