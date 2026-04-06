# Arquitetura FrotaSmart

## Navegacao rapida
- Estado atual: [estado_projeto.md](./estado_projeto.md)
- Regras de negocio: [regras_negocio.md](./regras_negocio.md)
- Roadmap atual: [roadmap_ciclo_04.md](./ciclo_04_estabilidade_governanca/roadmap_ciclo_04.md)
- Roadmap ciclo 01: [roadmap_ciclo_01.md](./ciclo_01_fundacao_arquitetura/roadmap_ciclo_01.md)
- Progresso: [progresso.md](./progresso.md)
- Guia Linux: [readme_linux.md](./readme_linux.md)

## Status do documento
- Papel: referencia oficial de arquitetura do projeto
- Ultima revisao: 2026-03-22
- Relacao com o estado atual: arquitetura alvo com migracao incremental a partir do legado

## Visao geral
O FrotaSmart e um sistema de gestao de frota publica para prefeituras, construido em PHP puro com MySQL.

A arquitetura oficial adotada e uma Clean Architecture adaptada:
- `Domain`
- `Application`
- `Infrastructure`
- `Presentation`

O objetivo nao e reescrever o sistema inteiro de uma vez.
A estrategia correta e migrar o projeto atual por etapas, preservando login, dashboard e CRUD basico enquanto o nucleo novo em `src/` amadurece.

## Principios obrigatorios
1. O dominio nao depende de banco, HTTP, sessao ou HTML.
2. A aplicacao orquestra casos de uso e conversa com contratos do dominio.
3. A infraestrutura implementa detalhes tecnicos, como PDO e configuracoes.
4. A apresentacao apenas recebe entrada e entrega saida.
5. Toda migracao deve reduzir acoplamento sem quebrar o legado.

## Fluxo arquitetural correto
`Presentation -> Application -> Domain`

`Infrastructure` entra por injecao de dependencia para satisfazer contratos definidos no dominio ou na aplicacao.

## Estrutura alvo do projeto

```text
FrotaSmart/
|-- src/
|   |-- Domain/
|   |   |-- Entities/
|   |   |-- ValueObjects/
|   |   |-- Repositories/
|   |   `-- Exceptions/
|   |-- Application/
|   |   `-- Services/
|   |-- Infrastructure/
|   |   |-- Persistence/
|   |   `-- Config/
|   `-- Presentation/
|       |-- Controllers/
|       `-- Views/
|-- scripts/
|-- public/
|-- backend/
|-- frontend/
|-- contexto/
|-- composer.json
`-- .env
```

## Estado atual do repositorio
- `src/` ja existe e iniciou o novo dominio
- `backend/` e `frontend/` ainda sustentam o fluxo em producao
- `scripts/` concentra operacoes CLI do projeto
- `public/` ja existe como document root recomendado para Linux/WSL

## Regras de implementacao
- Todo modulo novo deve comecar em `src/Domain`
- Toda regra de negocio deve sair gradualmente de controllers e models legados
- Evitar `global $pdo`
- Evitar `require_once` em codigo de dominio e aplicacao
- Controllers devem ficar finos
- Toda acao mutavel relevante precisa preparar caminho para auditoria

## Estrategia de migracao
1. Criar e validar a base tecnica com Composer e PSR-4
2. Enriquecer o dominio central de veiculos
3. Definir contratos de repositorio
4. Implementar persistencia em `src/Infrastructure`
5. Criar services de aplicacao
6. Adaptar controllers legados para usar a nova espinha dorsal
7. Remover partes legadas obsoletas apenas quando substituidas

## Decisoes atuais
- O projeto esta validado com PHP 8.2 local
- O autoload PSR-4 funciona via Composer local
- O projeto ja possui `public/` como base de execucao Linux-friendly
- O proximo grande passo arquitetural e desacoplar persistencia via repositorios concretos
