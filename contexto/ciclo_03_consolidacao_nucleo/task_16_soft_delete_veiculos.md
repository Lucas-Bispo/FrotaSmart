# Task 16 - Soft delete, arquivamento e historico forte de veiculos

## Objetivo
Transformar a remocao de veiculos em arquivamento seguro, preservando rastreabilidade e evitando perda de historico.

## Escopo sugerido
- consolidar `deleted_at`
- impedir exclusao fisica pelo legado
- diferenciar veiculos ativos e arquivados
- preparar filtros de consulta
- reforcar auditoria de arquivamento e restauracao

## Valor de negocio
Atende regra obrigatoria de historico e reduz risco operacional e juridico.

## Entrega realizada em 2026-04-05
- fluxo de remocao de veiculos convertido em arquivamento logico com base em `deleted_at`
- restauracao explicita adicionada no dominio, service, repositorio PDO e controller
- consulta de placa fortalecida para distinguir registros ativos e arquivados sem reaproveitar historico silenciosamente
- dashboard atualizado com filtro `ativos | arquivados | todos`, coluna de historico e acao de restauracao
- auditoria separada para `veiculo.archived` e `veiculo.restored`
- testes de contrato, service, controller e repositorio ajustados para o novo fluxo

## Resultado observado
- o sistema preserva o historico do veiculo arquivado sem sumir com o registro
- a frota ativa continua limpa para operacao diaria, mas a consulta gerencial agora enxerga arquivados
- a base fica pronta para a task 17 sem perder rastreabilidade do cadastro
