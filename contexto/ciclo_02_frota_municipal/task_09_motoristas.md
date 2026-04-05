# Task 09 - Cadastro operacional de motoristas

## Navegacao rapida
- Roadmap do ciclo: [roadmap_ciclo_02.md](./roadmap_ciclo_02.md)
- Arquitetura: [../arquitetura_projeto.md](../arquitetura_projeto.md)
- Estado atual: [../estado_projeto.md](../estado_projeto.md)

## Status
- Estado atual: planejada
- Dependencia anterior: conclusao da base atual de veiculos, autenticacao e RBAC

## Objetivo
Criar o modulo inicial de motoristas para que a frota municipal deixe de depender apenas do cadastro de veiculos e passe a representar tambem o condutor responsavel por cada operacao.

## Escopo minimo
- cadastrar motorista
- listar motoristas
- editar dados principais do motorista
- definir situacao operacional do motorista
- permitir vinculo futuro com veiculo e viagem

## Campos minimos sugeridos
- nome completo
- CPF
- numero da CNH
- categoria da CNH
- validade da CNH
- telefone
- secretaria de lotacao
- status: ativo, ferias, afastado, desligado

## Criterios de aceite
- deve ser possivel cadastrar e consultar motoristas
- a validacao minima de documentos deve existir no servidor
- o modulo deve respeitar RBAC
- operacoes mutaveis devem ficar preparadas para auditoria
- a modelagem deve permitir uso futuro em viagens e escalas

## Observacoes de arquitetura
- evitar repetir o acoplamento legado com `global $pdo`
- preferir seguir o caminho incremental ja adotado em `src/`
- manter controller fino e regras de negocio fora da camada HTTP
