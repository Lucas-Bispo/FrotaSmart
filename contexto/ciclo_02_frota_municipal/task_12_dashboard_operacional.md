# Task 12 - Dashboard operacional da frota

## Navegacao rapida
- Roadmap do ciclo: [roadmap_ciclo_02.md](./roadmap_ciclo_02.md)
- Task anterior: [task_11_abastecimento.md](./task_11_abastecimento.md)
- Estado atual: [../estado_projeto.md](../estado_projeto.md)

## Status
- Estado atual: concluida
- Dependencia anterior: [task_11_abastecimento.md](./task_11_abastecimento.md)

## Objetivo
Evoluir o dashboard atual para um painel realmente operacional e gerencial para secretarias municipais.

## Escopo minimo
- mostrar indicadores mais ricos da frota
- criar atalhos para tarefas frequentes
- destacar alertas operacionais
- preparar segmentacao por secretaria

## Indicadores sugeridos
- total da frota
- veiculos em operacao
- veiculos em manutencao
- manutencoes em aberto
- abastecimentos recentes
- motoristas ativos
- CNHs proximas do vencimento
- custo operacional do periodo

## Criterios de aceite
- o dashboard deve continuar simples de usar
- os indicadores devem vir de dados reais do sistema
- deve existir separacao clara entre informacao operacional e acao rapida
- o painel nao pode depender de regras duplicadas fora do dominio ou da aplicacao

## Observacoes de UX
- manter o estilo objetivo do projeto
- usar blocos de destaque, alertas e atalhos
- evitar poluicao visual desnecessaria

## Entrega realizada
- dashboard evoluido para leitura operacional e gerencial da frota
- indicadores reais integrados com veiculos, motoristas, manutencoes e abastecimentos
- painel com alertas, acoes rapidas e blocos de segmentacao por secretaria
- historicos recentes de manutencao e abastecimento visiveis na pagina inicial

## Escopo entregue nesta fase
- novos cards de indicadores operacionais
- custo do periodo calculado com dados reais de abastecimento
- contagem de manutencoes abertas, motoristas ativos e CNHs vencendo
- atalhos para modulos operacionais mais frequentes
- consolidacao inicial por secretaria para apoiar leituras futuras
- correcoes de consistencia visual no cadastro rapido de veiculos

## Validacao realizada
- `php -l backend/models/AbastecimentoModel.php`
- `php -l backend/models/ManutencaoModel.php`
- `php -l frontend/views/dashboard.php`
- `php -l public/dashboard.php`
- acesso autenticado em `http://127.0.0.1:8000/dashboard.php` com `200 OK`
