# Task 13 - Operacao de uso da frota com viagens e rotas

## Navegacao rapida
- Roadmap do ciclo: [roadmap_ciclo_02.md](./roadmap_ciclo_02.md)
- Task anterior: [task_12_dashboard_operacional.md](./task_12_dashboard_operacional.md)
- Arquitetura: [../arquitetura_projeto.md](../arquitetura_projeto.md)

## Status
- Estado atual: planejada
- Dependencia anterior: [task_12_dashboard_operacional.md](./task_12_dashboard_operacional.md)

## Objetivo
Criar o primeiro modulo de operacao real da frota, conectando veiculo, motorista e uso administrativo pelas secretarias.

## Escopo minimo
- cadastrar viagem ou rota operacional
- vincular veiculo
- vincular motorista
- registrar secretaria solicitante
- registrar origem, destino e finalidade
- registrar data e horario
- registrar km inicial e final

## Campos minimos sugeridos
- secretaria
- solicitante ou responsavel
- veiculo
- motorista
- origem
- destino
- objetivo da viagem
- data e hora de saida
- data e hora de retorno
- km inicial
- km final
- observacoes

## Criterios de aceite
- deve ser possivel registrar o uso da frota por secretaria
- cada viagem deve manter historico consultavel
- a operacao deve abrir caminho para indicadores de uso e custo
- o modelo deve se integrar naturalmente com manutencao e abastecimento

## Observacoes de negocio
- esse modulo e um divisor de aguas entre cadastro de frota e gestao de frota
- o foco nao e roteirizacao avancada neste momento
- o foco e controle administrativo e operacional da utilizacao dos veiculos
