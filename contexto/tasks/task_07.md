# Task 07 - Auditoria minima obrigatoria

## Navegacao rapida
- Roadmap: [roadmap_tasks.md](./roadmap_tasks.md)
- Progresso geral: [../progresso.md](../progresso.md)
- Regras de negocio: [../regras_negocio.md](../regras_negocio.md)
- Seguranca: [../seguranca.md](../seguranca.md)
- Task anterior: [task_06.md](./task_06.md)
- Task seguinte: [task_08.md](./task_08.md)

## Status
- Estado atual: planejada
- Dependencia anterior: [task_06.md](./task_06.md)

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
