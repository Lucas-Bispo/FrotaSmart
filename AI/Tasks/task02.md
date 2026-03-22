# Task 02 - Dominio inicial de veiculos

## Status
- Estado atual: concluida
- Dependencia anterior: [Task 01](./task01.md)
- Roadmap principal: [tasks.md](./tasks.md)

## Objetivo
Evoluir o dominio de veiculos em `src/` para sair de uma estrutura anemica e passar a concentrar regras basicas de negocio no proprio dominio.

## Arquivos envolvidos
- Entidade: [Veiculo.php](../../src/Domain/Entities/Veiculo.php)
- Value Object: [Placa.php](../../src/Domain/ValueObjects/Placa.php)
- Exceptions:
  - [DomainException.php](../../src/Domain/Exceptions/DomainException.php)
  - [InvalidPlacaException.php](../../src/Domain/Exceptions/InvalidPlacaException.php)
  - [InvalidVeiculoStatusException.php](../../src/Domain/Exceptions/InvalidVeiculoStatusException.php)
- Teste de dominio: [test-domain.php](../../scripts/test-domain.php)
- Regras de referencia: [Regras-Negocio.md](../Contexto/Regras-Negocio.md)

## Decisoes de engenharia
- `Placa` virou Value Object dedicado com normalizacao e validacao de formatos brasileiro antigo e Mercosul
- `Veiculo` agora recebe `Placa|string`, valida modelo, normaliza status e aplica transicoes de estado
- O dominio aceita aliases legados como `ativo` e `manutencao` para facilitar a migracao sem quebrar o sistema atual
- As regras continuam isoladas de banco, HTTP e sessao

## Regras implementadas
- Placa obrigatoria e validada no dominio
- Modelo obrigatorio
- Status suportados pelo dominio:
  - `disponivel`
  - `em_manutencao`
  - `em_viagem`
  - `baixado`
  - `reservado`
- Alias legado aceito:
  - `ativo` -> `disponivel`
  - `manutencao` -> `em_manutencao`
- Transicoes basicas disponiveis:
  - reservar
  - iniciar viagem
  - enviar para manutencao
  - liberar para uso
  - baixar

## Cenarios de teste preparados
- Criar `Veiculo` com placa `ABC1234` deve funcionar
- Criar `Veiculo` com placa `ABC1D23` deve funcionar
- Criar `Veiculo` com placa invalida deve lancar `InvalidPlacaException`
- Criar `Veiculo` com modelo vazio deve falhar
- Criar `Veiculo` com status legado `ativo` deve normalizar para `disponivel`
- Reservar veiculo disponivel deve mudar status para `reservado`
- Enviar veiculo baixado para manutencao deve falhar

## Validacao pratica
Comando para executar:

```powershell
php .\scripts\test-domain.php
```

Resultado esperado:
- `Testes de dominio executados com sucesso.`

## Observacoes para a proxima task
- A `Task 03` deve criar a interface de repositorio em torno desse dominio
- A `Task 04` deve adaptar a persistencia PDO para salvar os status normalizados do dominio
- A adaptacao do legado deve considerar que o ambiente atual esta validado em PHP 8.2
