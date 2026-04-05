# Task 11 - Controle de abastecimento

## Navegacao rapida
- Roadmap do ciclo: [roadmap_ciclo_02.md](./roadmap_ciclo_02.md)
- Task anterior: [task_10_manutencao_historico.md](./task_10_manutencao_historico.md)
- Regras de negocio: [../regras_negocio.md](../regras_negocio.md)

## Status
- Estado atual: concluida
- Dependencia anterior: [task_10_manutencao_historico.md](./task_10_manutencao_historico.md)

## Objetivo
Registrar abastecimentos da frota para permitir controle de custo, consumo e anomalias operacionais.

## Escopo minimo
- cadastrar abastecimento
- listar abastecimentos por veiculo
- listar abastecimentos por periodo
- preparar indicadores de consumo medio
- preparar base para relatorios de gasto por secretaria

## Campos minimos sugeridos
- veiculo
- motorista
- data do abastecimento
- posto ou fornecedor
- tipo de combustivel
- litros
- valor total
- km atual do veiculo
- observacoes

## Criterios de aceite
- deve ser possivel consultar historico de abastecimento por veiculo
- o sistema deve permitir calculo futuro de km por litro
- registros nao devem depender de logica apenas visual
- operacoes mutaveis devem ser auditaveis

## Observacoes de negocio
- esse modulo ajuda a detectar desperdicio
- esse modulo prepara o FrotaSmart para custo operacional real

## Entrega realizada
- modulo de abastecimento implementado com cadastro, listagem, filtros e edicao
- vinculo entre abastecimento, veiculo e motorista preservado no banco
- base preparada para leitura futura de consumo medio e gasto por secretaria
- trilha minima de auditoria adicionada para operacoes mutaveis

## Escopo entregue nesta fase
- tela publica em `public/abastecimentos.php`
- controller dedicado para validacao e persistencia
- model legado para consulta e escrita do historico
- filtros por veiculo e por periodo
- indicadores operacionais basicos de litros, gasto total e ticket medio
- teste automatizado do model e bootstrap de schema

## Validacao realizada
- `php -l backend/models/AbastecimentoModel.php`
- `php -l backend/controllers/AbastecimentoController.php`
- `php -l frontend/views/abastecimentos.php`
- `php -l public/abastecimentos.php`
- `php -l scripts/test-abastecimento-model.php`
- `php scripts/bootstrap-db.php`
- `php scripts/test-abastecimento-model.php`
