# Task 07 - Auditoria minima obrigatoria

## Navegacao rapida
- Roadmap: [roadmap_ciclo_01.md](./roadmap_ciclo_01.md)
- Progresso geral: [../progresso.md](../progresso.md)
- Regras de negocio: [../regras_negocio.md](../regras_negocio.md)
- Seguranca: [../seguranca.md](../seguranca.md)
- Task anterior: [task_06_adaptacao_controller_legado_veiculos.md](./task_06_adaptacao_controller_legado_veiculos.md)
- Task seguinte: [task_08_rbac_alinhado_regras_negocio.md](./task_08_rbac_alinhado_regras_negocio.md)

## Status
- Estado atual: concluida
- Dependencia anterior: [task_06_adaptacao_controller_legado_veiculos.md](./task_06_adaptacao_controller_legado_veiculos.md)

## Objetivo
Iniciar a trilha minima de auditoria para operacoes mutaveis do modulo de veiculos, alinhando implementacao com a regra oficial de rastreabilidade do projeto.

## Escopo minimo
- Garantir registro consistente para criacao, atualizacao e remocao logica
- Definir payload minimo auditavel com usuario, acao, alvo e data
- Preparar o fluxo para expansao futura a outros modulos
- Evitar que a auditoria dependa de detalhes do controller legado

## Criterios de aceite
- Toda operacao mutavel de veiculos deve gerar trilha auditavel
- O formato minimo do evento deve ser consistente entre operacoes
- A solucao deve ser reutilizavel para outros modulos
- Deve existir validacao pratica dos eventos gerados

## Validacao pratica
```bash
php scripts/test-audit-flow.php
```
