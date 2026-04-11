# Estado atual do projeto

## Navegacao rapida
- Arquitetura: [arquitetura_projeto.md](./arquitetura_projeto.md)
- Regras de negocio: [regras_negocio.md](./regras_negocio.md)
- Roadmap atual: [roadmap_ciclo_04.md](./ciclo_04_estabilidade_governanca/roadmap_ciclo_04.md)
- Roadmap ciclo 01: [roadmap_ciclo_01.md](./ciclo_01_fundacao_arquitetura/roadmap_ciclo_01.md)
- Progresso: [progresso.md](./progresso.md)
- Contexto de transicao: [contexto_transicao.md](./contexto_transicao.md)

## Data de referencia
2026-04-11

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

## Achados tecnicos
- `backend/models/VeiculoModel.php` usa `global $pdo`
- `backend/controllers/VeiculoController.php` ja delega a escrita para o service, mas a listagem do dashboard ainda vem do model legado
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
- A leitura do dashboard ainda depende de `VeiculoModel`, mesmo com a escrita ja migrada
- O ciclo 02 planejado foi concluido e o proximo passo natural e consolidar backlog do ciclo seguinte
- O cadastro de veiculos ainda precisava de dados mais aderentes a frota municipal real
- a trilha executiva ja existe, mas a camada de auditoria expandida ainda e o gap funcional mais visivel do ciclo 04

## Decisao atual
- Manter `composer.phar` apenas como ferramenta local, fora do versionamento
- Evoluir o modulo de veiculos por migracao incremental a partir do dominio novo
- Consolidar a migracao de leitura e finalizar o alinhamento de persistencia com soft delete e banco real
- Preservar a compatibilidade com Linux/WSL e com a publicacao via `public/`
- Manter o WSL Ubuntu como ambiente principal e repetivel de desenvolvimento
- Avancar o ciclo 04 priorizando auditoria expandida, governanca operacional, compliance e transparencia de dados nao pessoais
