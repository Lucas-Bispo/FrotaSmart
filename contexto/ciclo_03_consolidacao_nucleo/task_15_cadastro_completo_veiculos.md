# Task 15 - Consolidacao completa do cadastro de veiculos

## Problema
O modulo de veiculos ainda estava enxuto demais para a realidade da frota municipal. Faltavam campos centrais das regras de negocio como `RENAVAM`, `chassi`, `ano`, `tipo`, `combustivel`, `secretaria lotada`, `quilometragem inicial`, `data de aquisicao` e observacoes de documentos.

## Objetivo
Ampliar o cadastro de veiculos sem trocar stack e sem framework, fortalecendo dominio, persistencia, bootstrap, validacao e leitura no dashboard.

## Escopo desta task
- expandir schema `veiculos`
- enriquecer `Veiculo` no dominio
- adaptar `VeiculoService` e `PdoVeiculoRepository`
- atualizar `VeiculoController`
- melhorar o formulario do dashboard
- mostrar lotacao e dados operacionais na listagem
- validar com testes e bootstrap

## Resultado esperado
- base pronta para `soft delete` forte no proximo passo
- cadastro alinhado com a operacao municipal
- persistencia compativel com MySQL no WSL

## Entrega realizada em 2026-04-05
- schema `veiculos` expandido com `renavam`, `chassi`, `ano_fabricacao`, `tipo`, `combustivel`, `secretaria_lotada`, `quilometragem_inicial`, `data_aquisicao`, `documentos_observacoes` e `deleted_at`
- entidade de dominio `Veiculo` enriquecida com validacoes e getters desses campos
- `VeiculoService`, `PdoVeiculoRepository` e `VeiculoController` adaptados para o novo payload
- dashboard atualizado com formulario consolidado e listagem com lotacao e dados operacionais
- testes de dominio, service, controller e repositorio PDO validados com sucesso

## Referencia externa observada
Em referencias externas de maturidade funcional apareceu um padrao de frota mais detalhado, com previsao de manutencao, custo e estrutura mais forte de veiculo. O FrotaSmart absorve aqui apenas a profundidade funcional, nao a stack.
