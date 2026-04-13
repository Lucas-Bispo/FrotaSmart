# Task 23 - Auditoria expandida e trilha de exportacao

## Objetivo
Transformar a auditoria do FrotaSmart em uma trilha persistida, consultavel e exportavel, deixando de depender apenas de `error_log` e abrindo caminho para compliance, transparencia e revisao operacional.

## Entregas desta task
- criados [CompositeAuditLogger.php](../../src/Infrastructure/Audit/CompositeAuditLogger.php) e [PdoAuditLogger.php](../../src/Infrastructure/Audit/PdoAuditLogger.php) para combinar log tecnico e persistencia em banco
- evoluido [security.php](../../backend/config/security.php) para centralizar `audit_log()` em uma trilha estruturada com acao, alvo, ator, IP e contexto enriquecido
- adaptado [VeiculoController.php](../../backend/controllers/VeiculoController.php) para usar a mesma espinha dorsal persistente da auditoria
- expandido [bootstrap-db.php](../../scripts/bootstrap-db.php) com a tabela `audit_logs` e indices de consulta
- evoluido [RelatorioOperacionalModel.php](../../backend/models/RelatorioOperacionalModel.php) com leitura, resumo e exportacao CSV da auditoria
- atualizada a tela [relatorios.php](../../frontend/views/relatorios.php) com a nova aba de auditoria, filtros por ator, evento, modulo e acao, alem da trilha de exportacao
- criado o teste integrado [test-auditoria-relatorio.php](../../scripts/test-auditoria-relatorio.php)
- integrado o novo teste ao health check [test-wsl-stack.php](../../scripts/test-wsl-stack.php) e ao [composer.json](../../composer.json)

## Resultado tecnico
- eventos de autenticacao, veiculos, viagens, abastecimentos, manutencoes, parceiros e exportacoes agora podem ser persistidos no banco
- a auditoria deixou de ser apenas um rastro tecnico e passou a compor o modulo de relatorios com leitura operacional
- exportacoes CSV passam a gerar seu proprio evento auditavel, fortalecendo a trilha de governanca do ciclo 04

## Validacao esperada
- `php -l backend/config/security.php`
- `php -l backend/models/RelatorioOperacionalModel.php`
- `php -l frontend/views/relatorios.php`
- `php -l src/Infrastructure/Audit/CompositeAuditLogger.php`
- `php -l src/Infrastructure/Audit/PdoAuditLogger.php`
- `php -l scripts/test-auditoria-relatorio.php`
- `php scripts/bootstrap-db.php`
- `php scripts/test-auditoria-relatorio.php`

## Observacao de ambiente
- no PowerShell Windows desta retomada, a execucao com banco continuou bloqueada por `SQLSTATE[HY000] [1045]` para `frota_user`
- a validacao integrada recomendada segue sendo no Ubuntu WSL, apos alinhar a credencial real do `.env`

## Proximo passo sugerido
Executar a `Task 24 - Refino tecnico da persistencia e reducao de acoplamento legado`.
