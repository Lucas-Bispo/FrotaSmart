# Onde Paramos - FrotaSmart

## Objetivo
Servir como ponto rapido de retomada do projeto em futuras sessoes, deixando claro o ultimo estado confiavel de entrega, o ambiente padrao e o proximo passo recomendado.

## Data de referencia
2026-04-05

## Estado atual
- ambiente principal do projeto: Ubuntu WSL no Windows
- document root oficial: `public/`
- stack atual: PHP puro, MySQL, MVC legado com migracao incremental para `src/`
- repositorio limpo e com commits organizados por task ate este ponto

## Ultima entrega funcional consolidada
- `Task 21 - Regras operacionais automaticas de bloqueio e alerta`
- o sistema agora bloqueia viagens e sinaliza operacoes com base em:
- CNH vencida
- manutencao preventiva vencida
- veiculo arquivado
- veiculo em manutencao, baixado ou ja em viagem

## Ultima entrega documental consolidada
- reorganizacao da documentacao antiga em `contexto/ciclo_01_fundacao_arquitetura/`
- padronizacao dos nomes das tasks antigas
- atualizacao dos links e roadmaps para refletir os ciclos 01, 02, 03 e 04

## Proximo passo recomendado
- executar a `Task 22 - Painel executivo por secretaria e por veiculo`

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
- task 22: proxima
- ver: [roadmap_ciclo_04.md](./ciclo_04_estabilidade_governanca/roadmap_ciclo_04.md)

## Arquivos principais para retomar rapido
- estado consolidado: [estado_projeto.md](./estado_projeto.md)
- progresso acumulado: [progresso.md](./progresso.md)
- guia WSL: [readme_wsl_ubuntu_windows.md](./readme_wsl_ubuntu_windows.md)
- regras de negocio: [regras_negocio.md](./regras_negocio.md)
- task atual mais recente: [task_21_regras_operacionais_bloqueio_alerta.md](./ciclo_04_estabilidade_governanca/task_21_regras_operacionais_bloqueio_alerta.md)

## Validacao de ambiente recomendada antes de continuar
```bash
cd /mnt/c/Users/lukao/Documents/FrotaSmart
php scripts/test-wsl-stack.php
```

## Ultimos commits importantes
- `cecf42f` `docs(contexto): reorganizar tasks em ciclos e padronizar nomes`
- `5b0d76a` `feat(operacao): aplicar bloqueios automaticos na frota`
- `e5a1973` `chore(wsl): estabilizar runtime e bootstrap do ambiente`
- `944f1c4` `feat(relatorios): publicar consultas operacionais exportaveis`

## Direcao de produto mais forte neste momento
- painel executivo por secretaria
- painel executivo por veiculo
- compliance documental e vencimentos
- transparencia de dados nao pessoais
- checklists operacionais com evidencias como possivel passo seguinte apos o painel
