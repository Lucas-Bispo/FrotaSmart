# Onde Paramos - FrotaSmart

## Objetivo
Servir como ponto rapido de retomada do projeto em futuras sessoes, deixando claro o ultimo estado confiavel de entrega, o ambiente padrao e o proximo passo recomendado.

## Data de referencia
2026-04-14

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
- o contrato de veiculos agora diferencia leitura ativa e leitura historica com metodos explicitos, sem depender da flag `includeArchived`
- o `dashboard.php` agora comecou a ser fracionado por helpers puros de view e por cards montados em estrutura, reduzindo repeticao visual
- o `relatorios.php` agora tambem comecou a ser fracionado por helpers de view para labels, cards, cabecalhos e linhas por tipo de relatorio
- o `relatorios.php` avancou mais uma etapa e agora tambem delega ao helper a montagem do formulario de filtros, exportacao e abas de navegacao
- os models de `Abastecimento` e `Manutencao` agora aceitam `PDO` explicito, e o `RelatorioOperacionalModel` reutiliza a mesma conexao compartilhada nas leituras do modulo
- as leituras analiticas e preventivas reaproveitadas pelos relatorios agora tambem vivem em `AbastecimentoReadModel` e `ManutencaoReadModel` dentro de `src/Infrastructure/ReadModels`
- a consolidacao executiva do dashboard agora tambem saiu de dentro do `RelatorioOperacionalModel` e passou a ficar em `RelatorioExecutiveSummaryService` dentro de `src/Application/Services`
- o resumo de auditoria e a exportacao CSV do modulo de relatorios agora tambem comecaram a sair do `RelatorioOperacionalModel` e passaram a usar services dedicados em `src/Application/Services`
- o resumo operacional e a selecao de datasets por tipo de relatorio agora tambem usam services pequenos em `src/Application/Services`, reduzindo mais pontos de decisao dentro da fachada legacy
- as transformacoes de linhas de viagem, disponibilidade e auditoria agora tambem usam um service dedicado em `src/Application/Services`, reduzindo mais pos-processamento dentro do `RelatorioOperacionalModel`
- os criterios base do fluxo de abastecimentos agora tambem usam um service dedicado em `src/Application/Services`, reduzindo a normalizacao transacional restante dentro do `RelatorioOperacionalModel`
- o fluxo completo do relatorio de abastecimentos agora tambem usa um service dedicado em `src/Application/Services`, reunindo criterios, leitura analitica e filtros residuais fora da fachada legacy
- a normalizacao compartilhada dos filtros de manutencoes, viagens, disponibilidade e auditoria agora tambem usa um service dedicado em `src/Application/Services`, reduzindo repeticao dentro do `RelatorioOperacionalQueryService`
- a view de relatorios agora tambem usa um service dedicado para capturar filtros da request e resolver a aba ativa, reduzindo preparacao local dentro de `relatorios.php`
- a view de relatorios agora tambem delega a um helper dedicado a montagem do pacote principal de dados da tela, reduzindo mais atribuicoes locais em `relatorios.php`
- a view de relatorios agora tambem delega ao helper o titulo da aba, o link de limpeza e a renderizacao final das linhas da tabela, reduzindo mais composicao local na pagina principal
- o fluxo de auditoria do modulo de relatorios agora tambem usa um service dedicado em `src/Application/Services`, reunindo leitura, transformacao e resumo fora da fachada legacy
- os fluxos de manutencoes, viagens e disponibilidade do modulo de relatorios agora tambem usam um service operacional dedicado em `src/Application/Services`, reunindo leitura e transformacoes fora da fachada legacy
- a exportacao e a montagem das dependencias do modulo de relatorios agora tambem usam componentes dedicados em `src/`, reduzindo mais composicao e `new` espalhado dentro da fachada legacy
- o `dashboard.php` agora tambem centraliza no helper o pacote principal de dados da tela, incluindo cards executivos e tabs de filtro da frota, reduzindo mais atribuicoes e repeticao local na view principal
- o `dashboard.php` agora tambem prepara no helper as linhas das tabelas executivas por secretaria, por veiculo e de abastecimentos recentes, reduzindo mais formatacao inline e composicao local
- o `bootstrap-db.php` agora tambem separa a evolucao de schema por modulo e usa helpers pequenos para colunas, indices e statements, reduzindo mais ramificacao no script operacional
- os modulos de motoristas e parceiros operacionais agora tambem delegam validacao e normalizacao de entrada para services dedicados em `src/Application/Services`, reduzindo validacoes cruas dentro dos controllers
- o controller administrativo de usuarios agora tambem delega a validacao de cadastro para um service dedicado em `src/Application/Services`, reduzindo mais regra inline no nivel HTTP

## Ultima entrega documental consolidada
- reorganizacao da documentacao antiga em `contexto/ciclo_01_fundacao_arquitetura/`
- padronizacao dos nomes das tasks antigas
- atualizacao dos links e roadmaps para refletir os ciclos 01, 02, 03 e 04

## Proximo passo recomendado
- revisar se a `Task 24 - Refino tecnico da persistencia e reducao de acoplamento legado` ja pode ser encerrada formalmente e escolher o proximo hotspot entre contratos ainda dependentes de flags, pequenos residuais administrativos e algum residual final de view

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
