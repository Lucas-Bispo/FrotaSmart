# Task 21 - Regras operacionais automaticas de bloqueio e alerta

## Objetivo
Impedir operacoes incoerentes no uso da frota e deixar alertas mais claros no proprio fluxo de cadastro, sem depender apenas da leitura passiva do dashboard.

## Entregas desta task
- criado o guard operacional [OperacaoFrotaGuard.php](../../backend/models/OperacaoFrotaGuard.php) para concentrar regras automaticas de viagem e abastecimento
- evoluido [VeiculoModel.php](../../backend/models/VeiculoModel.php) com `findById()` para leitura pontual do estado do veiculo
- evoluido [ManutencaoModel.php](../../backend/models/ManutencaoModel.php) com avaliacao preventiva por veiculo e por data/km de referencia
- evoluidos [ViagemController.php](../../backend/controllers/ViagemController.php) e [AbastecimentoController.php](../../backend/controllers/AbastecimentoController.php) para bloquear operacoes criticas e devolver alertas de contexto ao usuario
- adicionado o teste integrado [test-operacao-frota-guard.php](../../scripts/test-operacao-frota-guard.php)
- integrado o novo teste ao health check [test-wsl-stack.php](../../scripts/test-wsl-stack.php) e ao [composer.json](../../composer.json)

## Regras aplicadas
- bloquear viagem para veiculo arquivado
- bloquear viagem para veiculo em manutencao, em viagem ou baixado
- bloquear operacao para motorista fora do status `ativo`
- bloquear operacao quando a CNH estiver vencida na data da acao
- bloquear viagem quando a preventiva do veiculo estiver vencida
- alertar quando a CNH estiver proxima do vencimento
- alertar quando a preventiva estiver proxima ou vencida no abastecimento
- alertar quando abastecimento for registrado em veiculo que ainda esta em manutencao

## Resultado tecnico
- o projeto passou a reagir a situacoes criticas antes de gravar viagem ou abastecimento
- a regra deixou de ficar espalhada entre telas e ficou reaproveitavel para ciclos futuros
- o health check Linux/WSL agora cobre tambem a validacao das regras operacionais

## Validacao esperada
- `php -l backend/models/OperacaoFrotaGuard.php`
- `php -l backend/models/ManutencaoModel.php`
- `php -l backend/controllers/ViagemController.php`
- `php -l backend/controllers/AbastecimentoController.php`
- `php scripts/test-operacao-frota-guard.php`

## Proximo passo sugerido
Executar a `Task 22 - Painel executivo por secretaria e por veiculo`.
