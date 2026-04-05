# Task 10 - Historico de manutencao por veiculo

## Navegacao rapida
- Roadmap do ciclo: [roadmap_ciclo_02.md](./roadmap_ciclo_02.md)
- Arquitetura: [../arquitetura_projeto.md](../arquitetura_projeto.md)
- Task anterior: [task_09_motoristas.md](./task_09_motoristas.md)

## Status
- Estado atual: planejada
- Dependencia anterior: [task_09_motoristas.md](./task_09_motoristas.md)

## Objetivo
Transformar a manutencao de um simples status do veiculo em um historico auditavel e util para a gestao municipal.

## Escopo minimo
- abrir registro de manutencao para um veiculo
- registrar motivo ou defeito informado
- registrar data de entrada e saida
- registrar situacao da manutencao
- registrar oficina ou fornecedor associado
- registrar custo estimado e custo final

## Campos minimos sugeridos
- placa ou identificador do veiculo
- tipo: preventiva ou corretiva
- descricao do problema
- data de abertura
- data de conclusao
- status: aberta, em andamento, concluida, cancelada
- fornecedor ou oficina
- custo
- observacoes

## Criterios de aceite
- cada veiculo deve poder ter multiplos registros de manutencao
- a historizacao deve ser preservada sem apagar passado
- deve ser possivel listar manutencoes em aberto e concluidas
- operacoes devem gerar trilha auditavel
- a tela de dashboard deve poder consumir esses dados no futuro

## Observacoes de negocio
- esse modulo sera base para previsao de manutencao
- esse modulo tambem ajuda a medir indisponibilidade da frota
