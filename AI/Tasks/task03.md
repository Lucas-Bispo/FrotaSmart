# Task 03 - Contrato de repositorio

## Status
- Estado atual: concluida
- Dependencia anterior: [Task 02](./task02.md)
- Roadmap principal: [tasks.md](./tasks.md)

## Objetivo
Criar o contrato de repositorio de veiculos em `src/Domain/Repositories`, desacoplando o dominio de `PDO` e preparando a aplicacao para casos de uso futuros.

## Arquivos envolvidos
- Contrato: [VeiculoRepositoryInterface.php](../../src/Domain/Repositories/VeiculoRepositoryInterface.php)
- Validacao: [test-repository-contract.php](../../scripts/test-repository-contract.php)
- Regras de referencia: [Arquitetura-Projeto.md](../Contexto/Arquitetura-Projeto.md)
- Estado de referencia: [Estado-Projeto.md](../Contexto/Estado-Projeto.md)

## Decisoes de engenharia
- O contrato ficou no dominio, sem depender de `PDO`, SQL, `global $pdo` ou arrays de infraestrutura
- A identidade de consulta e remocao foi modelada por `Placa`, que ja e `Value Object` oficial e `UNIQUE` nas regras do projeto
- A operacao `save` cobre persistencia de novos registros e atualizacoes, sem acoplar o dominio ao mecanismo tecnico de armazenamento
- O contrato inclui operacoes de consulta, existencia e listagem para acomodar os proximos services de aplicacao

## Assinatura definida
- `save(Veiculo $veiculo): void`
- `findByPlaca(Placa $placa): ?Veiculo`
- `existsByPlaca(Placa $placa): bool`
- `findAll(): array`
- `removeByPlaca(Placa $placa): void`

## Criterio de aceite atendido
- contrato desacoplado de PDO
- assinatura compativel com casos de uso futuros de cadastro, consulta, listagem e remocao

## Validacao pratica
Comando para executar:

```powershell
C:\xampp\php\php.exe .\scripts\test-repository-contract.php
```

Resultado esperado:
- `Contrato de repositorio validado com sucesso.`

## Observacoes para a proxima task
- A `Task 04` deve implementar esse contrato em `src/Infrastructure/Persistence`
- A implementacao concreta precisara traduzir entre o legado baseado em `id` e o novo contrato baseado em `Placa`
- A regra de soft delete continua pendente no legado e deve ser considerada na persistencia nova
