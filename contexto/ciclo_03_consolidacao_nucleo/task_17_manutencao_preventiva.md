# Task 17 - Manutencao preventiva por km e por data

## Objetivo
Evoluir o modulo de manutencoes do historico reativo para a prevencao operacional.

## Escopo sugerido
- planos preventivos por km, data ou recorrencia
- leitura de proximidade de vencimento
- bloqueio ou alerta de veiculo com revisao vencida
- indicadores no dashboard

## Referencia conceitual
O `sete_ref` reforca o valor de previsao de manutencao, mas a implementacao aqui deve permanecer simples e em PHP puro.

## Entrega realizada em 2026-04-05
- schema de `manutencoes` expandido com `km_referencia`, `km_proxima_preventiva`, `data_proxima_preventiva`, `recorrencia_dias` e `recorrencia_km`
- `ManutencaoModel` evoluido para calcular previsao por data e por km, lendo km atual do veiculo a partir da operacao registrada
- criados indicadores `preventivas vencidas` e `preventivas proximas`, alem de listagem de alertas preventivos
- `ManutencaoController` reforcado para validar plano preventivo quando o tipo for `preventiva`
- tela de manutencoes atualizada com painel preventivo, campos de agendamento e leitura do status preventivo
- dashboard passou a mostrar alertas e contadores de manutencao preventiva

## Resultado observado
- o modulo saiu do historico puramente reativo para uma leitura operacional de previsao
- a equipe passa a enxergar manutencao vencida ou em janela de atencao por data e por km
- a base fica pronta para a proxima task de alertas e consolidacao gerencial
