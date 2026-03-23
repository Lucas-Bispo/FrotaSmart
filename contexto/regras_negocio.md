# Regras de negocio - FrotaSmart

## Navegacao rapida
- Arquitetura: [arquitetura_projeto.md](./arquitetura_projeto.md)
- Estado atual: [estado_projeto.md](./estado_projeto.md)
- Roadmap: [roadmap_tasks.md](./tasks/roadmap_tasks.md)
- Progresso: [progresso.md](./progresso.md)

## Visao geral
O FrotaSmart e um sistema completo de gestao de frota municipal focado em transparencia, eficiencia, reducao de custos e conformidade legal.

As regras de negocio sao obrigatorias para todo codigo gerado ou revisado no projeto.

## Principios gerais
- Transparencia total
- Auditoria completa
- Minimizacao de dados
- Controle de custos
- Seguranca e prevencao
- Conformidade LGPD
- RBAC rigido por perfil

## Modulos e regras de negocio detalhadas

### 1. Veiculos
- Cadastro completo obrigatorio: placa, RENAVAM, chassi, ano, modelo, tipo, combustivel, secretaria lotada, quilometragem inicial, data de aquisicao e documentos
- Status obrigatorio: `disponivel`, `em_manutencao`, `em_viagem`, `baixado`, `reservado`
- Vencimentos automaticos alertados com antecedencia
- Soft delete e historico de alteracoes

### 2. Motoristas
- Vinculo com usuario do sistema e CNH
- Dados pessoais tratados com LGPD
- Status: ativo, inativo, em ferias e bloqueado
- Treinamento obrigatorio registrado

### 3. Viagens e deslocamentos
- Autorizacao previa obrigatoria
- Registro de saida e retorno com quilometragem
- Finalidade sempre publica
- Motorista responsavel e aprovacao da secretaria

### 4. Abastecimento
- Apenas postos credenciados ou cartao frota
- Limites por veiculo e motorista
- Calculo automatico de consumo medio
- Deteccao automatica de fraudes
- Integracao com manutencao

### 5. Manutencao
- Preventiva por km, horas ou tempo
- Corretiva com ordem de servico, aprovacao e nota fiscal
- Estoque de pecas integrado
- Veiculo bloqueado se manutencao estiver pendente

### 6. Secretarias e departamentos
- Vinculo obrigatorio de veiculos e motoristas
- Relatorios por secretaria

### 7. Relatorios e auditoria
- KPIs obrigatorios de custo, consumo, disponibilidade, manutencao preventiva, ociosidade e custo por secretaria
- Auditoria total de acoes
- Exportacao em PDF e CSV

### 8. Seguranca e LGPD
- Perfis: admin, gerente, motorista e auditor
- Controle de acesso, criptografia e politica de privacidade

## Regras que devem ser aplicadas sempre
- Toda acao mutavel deve ter auditoria
- Placa sempre UNIQUE com validacao no dominio
- Consumo medio calculado automaticamente
- Veiculo bloqueado se houver restricao critica
- Nenhum dado sensivel exposto sem autenticacao e RBAC
- Relatorios com filtros por data, secretaria e veiculo

