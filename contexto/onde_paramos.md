# Onde Paramos - FrotaSmart

## Objetivo
Servir como ponto rapido de retomada do projeto em futuras sessoes, deixando claro o ultimo estado confiavel de entrega, o ambiente padrao e o proximo passo recomendado.

## Data de referencia
2026-04-13

## Estado atual
- ambiente principal do projeto: Ubuntu WSL no Windows
- document root oficial: `public/`
- stack atual: PHP puro, MySQL, MVC legado com migracao incremental para `src/`
- repositorio limpo e com commits organizados por task ate este ponto

## Ultima entrega funcional consolidada
- `Task 23 - Auditoria expandida e trilha de exportacao`
- a aplicacao agora persiste auditoria em `audit_logs`, combinando log tecnico com trilha consultavel
- o modulo de relatorios ganhou uma aba de auditoria com filtros por ator, evento, modulo e acao
- exportacoes CSV agora tambem geram evento auditavel, fortalecendo a governanca operacional

## Avanco atual em andamento
- `Task 24 - Refino tecnico da persistencia e reducao de acoplamento legado`
- a leitura principal da frota no dashboard passou a sair de `VeiculoDashboardService` em `src/`, reaproveitando `PdoVeiculoRepository`
- o `VeiculoModel` legado deixou de ser o ponto central da frota na pagina principal
- a leitura SQL de relatorios comecou a migrar para `RelatorioOperacionalQueryService` em `src/Infrastructure/ReadModels`
- o padrao de Clean Code do projeto agora esta formalizado em `engenharia/padrao_clean_code_frotasmart.md` e ja comecou a ser aplicado no `ViagemController`
- o mesmo padrao agora tambem foi aplicado em `ManutencaoController` e `AbastecimentoController`, reduzindo validacoes extensas em metodos menores

## Ultima entrega documental consolidada
- reorganizacao da documentacao antiga em `contexto/ciclo_01_fundacao_arquitetura/`
- padronizacao dos nomes das tasks antigas
- atualizacao dos links e roadmaps para refletir os ciclos 01, 02, 03 e 04

## Proximo passo recomendado
- continuar a `Task 24 - Refino tecnico da persistencia e reducao de acoplamento legado` e levar o padrao de Clean Code para views grandes e contratos ainda dependentes de booleanos

## O que ja esta concluido por ciclo

### Ciclo 01 - Fundacao da arquitetura
- concluido
- ver: [roadmap_ciclo_01.md](./ciclo_01_fundacao_arquitetura/roadmap_ciclo_01.md)

### Ciclo 02 - Frota municipal
- concluido
- ver: [roadmap_ciclo_02.md](./ciclo_02_frota_municipal/roadmap_ciclo_02.md)

### Ciclo 03 - Consolidacao do nucleo
- concluido
- ver: [roadmap_ciclo_03.md](./ciclo_03_consolidacao_nucleo/roadmap_ciclo_03.md)

### Ciclo 04 - Estabilidade e governanca operacional
- em andamento
- task 20: concluida
- task 21: concluida
- task 22: concluida
- task 23: concluida
- task 24: em andamento
- ver: [roadmap_ciclo_04.md](./ciclo_04_estabilidade_governanca/roadmap_ciclo_04.md)

## Arquivos principais para retomar rapido
- estado consolidado: [estado_projeto.md](./estado_projeto.md)
- progresso acumulado: [progresso.md](./progresso.md)
- guia WSL: [readme_wsl_ubuntu_windows.md](./readme_wsl_ubuntu_windows.md)
- regras de negocio: [regras_negocio.md](./regras_negocio.md)
- task atual mais recente: [task_24_refino_tecnico_persistencia_reducao_acoplamento_legado.md](./ciclo_04_estabilidade_governanca/task_24_refino_tecnico_persistencia_reducao_acoplamento_legado.md)

## Validacao de ambiente recomendada antes de continuar
```bash
cd /mnt/c/Users/lukao/Documents/FrotaSmart
php scripts/test-wsl-stack.php
```

## Ultimos commits importantes
- pendente registrar commit desta retomada
- `em andamento local` `feat(auditoria): persistir trilha auditavel e exportacao`
- `cecf42f` `docs(contexto): reorganizar tasks em ciclos e padronizar nomes`
- `5b0d76a` `feat(operacao): aplicar bloqueios automaticos na frota`
- `e5a1973` `chore(wsl): estabilizar runtime e bootstrap do ambiente`

## Direcao de produto mais forte neste momento
- refino tecnico da persistencia e reducao de acoplamento legado
- compliance documental e vencimentos
- transparencia de dados nao pessoais
- checklists operacionais com evidencias como possivel passo seguinte apos o refino tecnico
