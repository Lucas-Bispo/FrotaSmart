# Task 14 - Fornecedores, oficinas e parceiros operacionais

## Navegacao rapida
- Roadmap do ciclo: [roadmap_ciclo_02.md](./roadmap_ciclo_02.md)
- Task anterior: [task_13_viagens_rotas.md](./task_13_viagens_rotas.md)
- Documento de melhorias: [../../engenharia/melhorias_novas_funcionalidades.md](../../engenharia/melhorias_novas_funcionalidades.md)

## Status
- Estado atual: planejada
- Dependencia anterior: [task_13_viagens_rotas.md](./task_13_viagens_rotas.md)

## Objetivo
Cadastrar fornecedores e parceiros operacionais da frota para melhorar rastreabilidade e controle de gasto publico.

## Escopo minimo
- cadastrar oficinas
- cadastrar postos de combustivel
- cadastrar fornecedores de pecas
- permitir vinculo com manutencoes e abastecimentos
- permitir consulta por tipo de parceiro

## Campos minimos sugeridos
- nome fantasia
- razao social
- CNPJ
- tipo de parceiro
- telefone
- endereco
- contato responsavel
- status: ativo ou inativo
- observacoes

## Criterios de aceite
- fornecedores devem poder ser vinculados a operacoes reais
- o cadastro deve apoiar manutencao e abastecimento
- a base deve permitir relatorio futuro por fornecedor
- a modelagem deve preparar o caminho para analise de custo por parceiro

## Observacoes de negocio
- esse modulo fortalece transparencia e rastreabilidade
- esse modulo reduz cadastro solto em texto livre dentro de manutencao e abastecimento
