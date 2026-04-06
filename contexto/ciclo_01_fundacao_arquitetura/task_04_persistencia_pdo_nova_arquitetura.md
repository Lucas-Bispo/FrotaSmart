# Task 04 - Persistencia PDO na nova arquitetura

## Navegacao rapida
- Roadmap: [roadmap_ciclo_01.md](./roadmap_ciclo_01.md)
- Progresso geral: [../progresso.md](../progresso.md)
- Arquitetura: [../arquitetura_projeto.md](../arquitetura_projeto.md)
- Estado atual: [../estado_projeto.md](../estado_projeto.md)
- Regras de negocio: [../regras_negocio.md](../regras_negocio.md)
- Task anterior: [task_03_contrato_repositorio_veiculos.md](./task_03_contrato_repositorio_veiculos.md)
- Task seguinte: [task_05_service_aplicacao_veiculos.md](./task_05_service_aplicacao_veiculos.md)

## Status
- Estado atual: concluida
- Dependencia anterior: [task_03_contrato_repositorio_veiculos.md](./task_03_contrato_repositorio_veiculos.md)

## Objetivo
Implementar o repositorio concreto de veiculos em `src/Infrastructure/Persistence`, usando `PDO` por injecao de dependencia e respeitando o contrato definido no dominio.

## Escopo minimo
- Criar uma implementacao concreta de `VeiculoRepositoryInterface`
- Remover dependencia direta de `global $pdo` do fluxo novo
- Mapear entidade de dominio para persistencia sem acoplamento com HTTP
- Preparar o comportamento de remocao para evolucao segura em direcao a soft delete

## Criterios de aceite
- O repositorio concreto deve viver em `src/Infrastructure/Persistence`
- O contrato `VeiculoRepositoryInterface` deve ser atendido integralmente
- Nenhuma classe nova de `Domain` ou `Application` pode depender de `PDO`
- Deve existir validacao pratica do repositorio fora do controller legado

## Validacao pratica
```bash
php scripts/test-repository-pdo.php
```
