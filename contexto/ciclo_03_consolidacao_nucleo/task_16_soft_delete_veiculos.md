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
