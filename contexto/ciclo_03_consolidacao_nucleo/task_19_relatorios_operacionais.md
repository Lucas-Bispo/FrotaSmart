# Task 19 - Relatorios operacionais com exportacao

## Objetivo
Concluir o ciclo com leitura gerencial estruturada da frota municipal.

## Escopo sugerido
- filtros por periodo, secretaria, veiculo e status
- relatorios de abastecimento, manutencao, viagens e disponibilidade
- exportacao inicial em CSV
- preparar terreno para PDF em etapa posterior

## Valor de negocio
Entrega a camada de transparencia e apoio a decisao que fecha o nucleo da aplicacao.

## Entrega realizada em 2026-04-05
- criada a pagina `relatorios.php` com filtros por periodo, secretaria, veiculo e status
- implementado `RelatorioOperacionalModel` agregando relatorios de abastecimentos, manutencoes, viagens e disponibilidade
- adicionada exportacao inicial em CSV por relatorio e mantendo os filtros ativos
- integrado o modulo de relatorios ao menu lateral do sistema
- consolidado resumo gerencial com gasto de abastecimento, custo de manutencao, viagens e disponibilidade

## Resultado observado
- o ciclo 03 passou a fechar com leitura gerencial estruturada da frota
- a exportacao CSV deixa o sistema pronto para uso administrativo e auditoria externa
- o terreno ficou preparado para uma etapa futura de PDF sem precisar refazer a modelagem
