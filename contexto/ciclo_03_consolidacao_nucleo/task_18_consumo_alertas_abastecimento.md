# Task 18 - Consumo medio e alertas de abastecimento

## Objetivo
Usar a base de abastecimentos ja pronta para gerar leitura automatica de eficiencia e suspeitas.

## Escopo sugerido
- calcular consumo medio por veiculo
- comparar variacao entre abastecimentos
- gerar alertas de anomalia por km, litros e valor
- apoiar visao de custo por secretaria

## Valor de negocio
Ataca diretamente uma das regras mais fortes de controle de gasto e prevencao de fraude.

## Entrega realizada em 2026-04-05
- `AbastecimentoModel` evoluido para calcular consumo por km/L, custo por litro, custo por km e variacoes entre abastecimentos
- criada deteccao automatica de anomalias por km, litros, valor total e desvio de consumo frente ao historico do veiculo
- implementados resumo consolidado de consumo e ranking de eficiencia por veiculo
- tela de abastecimentos atualizada com painel de alertas, ranking e coluna analitica por registro
- dashboard passou a destacar consumo medio do periodo e quantidade de abastecimentos com anomalia
- teste de abastecimento ajustado para cobrir consumo consolidado e ranking

## Resultado observado
- o modulo passou a gerar leitura automatica de eficiencia sem depender de nova stack
- o sistema agora apoia identificacao precoce de gastos suspeitos ou comportamento fora do padrao
- a base fica pronta para a task 19 de relatorios operacionais
