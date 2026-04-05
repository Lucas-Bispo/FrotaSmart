# Task 05 - Service de aplicacao para veiculos

## Navegacao rapida
- Roadmap: [roadmap_tasks.md](./roadmap_tasks.md)
- Progresso geral: [../progresso.md](../progresso.md)
- Arquitetura: [../arquitetura_projeto.md](../arquitetura_projeto.md)
- Regras de negocio: [../regras_negocio.md](../regras_negocio.md)
- Task anterior: [task_04.md](./task_04.md)
- Task seguinte: [task_06.md](./task_06.md)

## Status
- Estado atual: concluida
- Dependencia anterior: [task_04.md](./task_04.md)

## Objetivo
Criar um service de aplicacao em `src/Application/Services` para orquestrar os casos de uso de veiculos sem concentrar regras de negocio no controller legado.

## Escopo minimo
- Criar operacoes de cadastro, atualizacao, consulta e remocao logica de veiculos
- Centralizar validacoes de fluxo e integracao com repositorio
- Normalizar a comunicacao entre controller, dominio e infraestrutura
- Preparar pontos de extensao para auditoria e regras de autorizacao

## Criterios de aceite
- O controller legado nao deve precisar conhecer `PDO`
- O service deve depender apenas de contratos e objetos do dominio
- O fluxo de veiculos deve preservar validacao de placa e status oficiais
- Deve existir validacao pratica do service fora da camada HTTP

## Validacao pratica
```bash
php scripts/test-veiculo-service.php
```
