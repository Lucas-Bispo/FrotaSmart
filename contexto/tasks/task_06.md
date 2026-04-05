# Task 06 - Adaptacao do controller legado

## Navegacao rapida
- Roadmap: [roadmap_tasks.md](./roadmap_tasks.md)
- Progresso geral: [../progresso.md](../progresso.md)
- Arquitetura: [../arquitetura_projeto.md](../arquitetura_projeto.md)
- Estado atual: [../estado_projeto.md](../estado_projeto.md)
- Task anterior: [task_05.md](./task_05.md)
- Task seguinte: [task_07.md](./task_07.md)

## Status
- Estado atual: concluida
- Dependencia anterior: [task_05.md](./task_05.md)

## Objetivo
Adaptar `backend/controllers/VeiculoController.php` para consumir o novo service de aplicacao, reduzindo o acoplamento com model legado e afinando o controller.

## Escopo minimo
- Substituir chamadas diretas ao model legado por chamadas ao service
- Preservar autenticacao, CSRF, flashes e redirecionamentos existentes
- Reduzir validacoes duplicadas que ja existam no dominio ou na aplicacao
- Alinhar os status aceitos com as regras oficiais do projeto

## Criterios de aceite
- O controller deve manter o comportamento HTTP esperado
- O fluxo de escrita nao deve mais depender diretamente de `VeiculoModel`
- O controller deve ficar focado em entrada, saida e autorizacao
- Deve existir validacao pratica do fluxo principal de cadastro, edicao e remocao

## Validacao pratica
```bash
php scripts/test-veiculo-controller-flow.php
```
