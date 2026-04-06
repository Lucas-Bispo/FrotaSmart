# Roadmap do Ciclo 03 - Consolidacao do Nucleo

## Objetivo
Fortalecer o nucleo do FrotaSmart para ficar mais aderente a frota municipal real, sem framework e sem desviar do PHP puro.

## Origem deste ciclo
- Regras de negocio em [regras_negocio.md](../regras_negocio.md)
- Estado consolidado em [estado_projeto.md](../estado_projeto.md)
- Referencias observadas em `exemplo/sete_ref`

## Tasks do ciclo
- `Task 15 - Consolidacao completa do cadastro de veiculos` - `concluida`
- `Task 16 - Soft delete, arquivamento e historico forte de veiculos` - `concluida`
- `Task 17 - Manutencao preventiva por km e por data` - `concluida`
- `Task 18 - Consumo medio e alertas de abastecimento` - `concluida`
- `Task 19 - Relatorios operacionais com exportacao` - `concluida`

## Diretrizes
- manter MVC simples com evolucao incremental da arquitetura em `src/`
- preservar compatibilidade com MySQL, WSL e `public/`
- reforcar o dominio de frota municipal por secretaria
- documentar cada entrega em `.md` e em commit limpo

## Nota de produto
O `SETE` foi usado apenas como referencia de maturidade funcional, especialmente em frota, manutencao, previsao e relatorios. Nao sera trazido Electron, stack pesada nem dependencias fora da proposta do FrotaSmart.
