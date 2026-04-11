# Task 22 - Painel executivo por secretaria e por veiculo

## Objetivo
Subir o nivel do dashboard para uma leitura realmente executiva, permitindo acompanhar custo, uso, disponibilidade e risco da frota por secretaria e tambem por veiculo.

## Entregas desta task
- evoluido [RelatorioOperacionalModel.php](../../backend/models/RelatorioOperacionalModel.php) com agregacoes executivas por secretaria e por veiculo
- enriquecidos [AbastecimentoModel.php](../../backend/models/AbastecimentoModel.php) e [ManutencaoModel.php](../../backend/models/ManutencaoModel.php) com dados adicionais de lotacao e contexto para consolidacao
- atualizada a view [dashboard.php](../../frontend/views/dashboard.php) com:
- cards de leitura executiva
- painel executivo por secretaria
- painel executivo por veiculo
- criado o teste integrado [test-relatorio-executivo.php](../../scripts/test-relatorio-executivo.php)
- integrado o novo teste ao health check [test-wsl-stack.php](../../scripts/test-wsl-stack.php) e ao [composer.json](../../composer.json)

## Resultado tecnico
- o dashboard deixou de mostrar apenas indicadores gerais e passou a cruzar disponibilidade, viagens, abastecimentos, manutencoes e preventivas por secretaria
- a leitura por veiculo agora evidencia os itens mais sensiveis do periodo, com custo total, uso consolidado e alertas preventivos
- a consolidacao foi mantida em model reutilizavel, evitando espalhar regra de negocio na view

## Validacao esperada
- `php -l backend/models/RelatorioOperacionalModel.php`
- `php -l frontend/views/dashboard.php`
- `php -l scripts/test-relatorio-executivo.php`
- `php scripts/test-relatorio-executivo.php`

## Proximo passo sugerido
Executar a `Task 23 - Auditoria expandida e trilha de exportacao`.
