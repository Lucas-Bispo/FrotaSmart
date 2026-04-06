# Roadmap do Ciclo 04 - Estabilidade e Governanca Operacional

## Objetivo
Fortalecer o FrotaSmart para uso continuo em ambiente real, com foco em estabilidade no WSL, regras automatizadas e leitura executiva mais forte.

## Origem deste ciclo
- Estado consolidado em [../estado_projeto.md](../estado_projeto.md)
- Progresso acumulado em [../progresso.md](../progresso.md)
- Encerramento funcional do ciclo 03 em [../ciclo_03_consolidacao_nucleo/roadmap_ciclo_03.md](../ciclo_03_consolidacao_nucleo/roadmap_ciclo_03.md)

## Tasks propostas
- `Task 20 - Estabilizacao definitiva do ambiente WSL e validacao integrada` - `concluida`
- `Task 21 - Regras operacionais automaticas de bloqueio e alerta` - `concluida`
- `Task 22 - Painel executivo por secretaria e por veiculo` - `proposta`
- `Task 23 - Auditoria expandida e trilha de exportacao` - `proposta`
- `Task 24 - Refino tecnico da persistencia e reducao de acoplamento legado` - `proposta`

## Diretrizes
- manter o WSL Ubuntu como ambiente principal de desenvolvimento
- preservar `public/` como document root
- evitar dependencias pesadas e seguir com PHP puro
- consolidar testes integrados que batam no banco do proprio Linux
