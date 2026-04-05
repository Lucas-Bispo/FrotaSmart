# Task 13 - Operacao de uso da frota com viagens e rotas

## Navegacao rapida
- Roadmap do ciclo: [roadmap_ciclo_02.md](./roadmap_ciclo_02.md)
- Task anterior: [task_12_dashboard_operacional.md](./task_12_dashboard_operacional.md)
- Arquitetura: [../arquitetura_projeto.md](../arquitetura_projeto.md)

## Status
- Estado atual: concluida
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

## Entrega realizada
- modulo inicial de viagens implementado com cadastro, listagem, filtros e edicao
- vinculo entre secretaria solicitante, veiculo, motorista e trajeto preservado no banco
- historico de uso da frota agora fica consultavel por status e por secretaria
- fluxo mutavel registra auditoria minima para rastreabilidade

## Escopo entregue nesta fase
- tela publica em `public/viagens.php`
- controller dedicado com validacao de horario, km e status
- model legado para consulta e persistencia das viagens
- filtros por status e secretaria
- indicadores basicos de operacao em curso, concluidas e km percorridos
- compatibilizacao do schema existente de `viagens` com o novo modulo

## Validacao realizada
- `php -l backend/models/ViagemModel.php`
- `php -l backend/controllers/ViagemController.php`
- `php -l frontend/views/viagens.php`
- `php -l public/viagens.php`
- `php -l scripts/test-viagem-model.php`
- `php scripts/bootstrap-db.php`
- `php scripts/test-viagem-model.php`
- acesso autenticado em `http://127.0.0.1:8000/viagens.php` com `200 OK`
