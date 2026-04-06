# Task 05 - Service de aplicacao para veiculos

## Navegacao rapida
- Roadmap: [roadmap_ciclo_01.md](./roadmap_ciclo_01.md)
- Progresso geral: [../progresso.md](../progresso.md)
- Arquitetura: [../arquitetura_projeto.md](../arquitetura_projeto.md)
- Regras de negocio: [../regras_negocio.md](../regras_negocio.md)
- Task anterior: [task_04_persistencia_pdo_nova_arquitetura.md](./task_04_persistencia_pdo_nova_arquitetura.md)
- Task seguinte: [task_06_adaptacao_controller_legado_veiculos.md](./task_06_adaptacao_controller_legado_veiculos.md)

## Status
- Estado atual: concluida
- Dependencia anterior: [task_04_persistencia_pdo_nova_arquitetura.md](./task_04_persistencia_pdo_nova_arquitetura.md)

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
