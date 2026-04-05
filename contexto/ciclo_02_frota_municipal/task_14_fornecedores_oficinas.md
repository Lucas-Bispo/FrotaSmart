# Task 14 - Fornecedores, oficinas e parceiros operacionais

## Navegacao rapida
- Roadmap do ciclo: [roadmap_ciclo_02.md](./roadmap_ciclo_02.md)
- Task anterior: [task_13_viagens_rotas.md](./task_13_viagens_rotas.md)
- Documento de melhorias: [../../engenharia/melhorias_novas_funcionalidades.md](../../engenharia/melhorias_novas_funcionalidades.md)

## Status
- Estado atual: concluida
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

## Entrega realizada
- modulo central de parceiros operacionais implementado com cadastro, listagem, filtros e edicao
- oficinas, postos, fornecedores de pecas e prestadores agora compartilham uma base unica
- manutencoes e abastecimentos passaram a aceitar vinculo com parceiro cadastrado
- a base ficou pronta para relatorio futuro por fornecedor e analise de custo por parceiro

## Escopo entregue nesta fase
- tela publica em `public/parceiros.php`
- controller dedicado para validacao e persistencia
- model legado para consulta por tipo e status
- integracao com manutencoes e abastecimentos por `parceiro_id`
- filtros por tipo e status no cadastro central
- teste automatizado do model e regressao basica dos modulos integrados

## Validacao realizada
- `php -l backend/models/ParceiroOperacionalModel.php`
- `php -l backend/controllers/ParceiroOperacionalController.php`
- `php -l frontend/views/parceiros.php`
- `php -l public/parceiros.php`
- `php -l scripts/test-parceiro-operacional-model.php`
- `php scripts/bootstrap-db.php`
- `php scripts/test-parceiro-operacional-model.php`
- `php scripts/test-manutencao-model.php`
- `php scripts/test-abastecimento-model.php`
- acesso autenticado em `http://127.0.0.1:8000/parceiros.php` com `200 OK`
