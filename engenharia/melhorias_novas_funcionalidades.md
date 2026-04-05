# Melhorias e Novas Funcionalidades do FrotaSmart

## Objetivo deste documento
Registrar uma analise comparativa entre o estado atual do FrotaSmart e o repositorio de referencia `marcosroriz/sete`, transformando essa comparacao em um backlog de melhorias realistas.

Importante:
- este documento nao recomenda migracao para framework
- a direcao continua sendo PHP puro, MySQL e evolucao incremental
- o foco aqui e produto, funcionalidades, organizacao e robustez operacional

## Base da analise

### FrotaSmart hoje
Fontes locais consideradas:
- `contexto/arquitetura_projeto.md`
- `contexto/estado_projeto.md`
- `contexto/progresso.md`
- `frontend/views/dashboard.php`
- `frontend/views/user_management.php`
- `backend/controllers/VeiculoController.php`
- `backend/controllers/UserController.php`

### Repositorio de referencia
Repositorio analisado:
- `https://github.com/marcosroriz/sete`

Leitura local de referencia em:
- `exemplo/sete_ref/README.md`
- `exemplo/sete_ref/src/renderer/dashboard-main.html`
- `exemplo/sete_ref/src/renderer/modules/rota/rota-sugestao-view.html`
- `exemplo/sete_ref/src/renderer/modules/custo/custo-parametros-view.html`
- estrutura de modulos em `exemplo/sete_ref/src/renderer/modules/`

## O que o FrotaSmart ja faz bem

Mesmo sendo menor que o SETE, o FrotaSmart ja tem uma base boa e promissora:

- login com sessao segura, CSRF, flash messages e trilha de seguranca inicial
- RBAC centralizado com perfis oficiais
- CRUD basico de veiculos funcionando
- cadastro de usuarios com validacao minima e perfis
- dashboard inicial com indicadores operacionais da frota
- inicio concreto de Clean Architecture em `src/`
- repositorio PDO novo para veiculos
- service de aplicacao para veiculos
- auditoria minima de mutacoes do modulo de veiculos
- compatibilidade local com WSL e Linux

## O que o SETE faz de forte como produto

O SETE nao serve como modelo tecnico para ser copiado na stack, mas serve muito bem como referencia de cobertura funcional e visao de produto publico.

Pontos fortes observados:

- dominio amplo, com modulos separados para `aluno`, `escola`, `rota`, `motorista`, `monitor`, `fornecedor`, `frota`, `relatorio`, `custo`, `config` e `usuario`
- dashboard com atalhos e indicadores operacionais mais ricos
- foco forte em mapa e geografia operacional
- gestao de rotas como modulo central, nao como detalhe
- sugestao de rotas com parametros de negocio
- relatorios especializados por entidade
- configuracao de parametros de custo
- importacao de dados por planilha
- organizacao modular de telas por contexto de negocio
- preocupacao com operacao publica real, inclusive cenarios com conectividade limitada

## Diferenca principal entre os dois projetos

Hoje o FrotaSmart esta mais proximo de um sistema base de administracao de frota.

O SETE, por outro lado, se comporta como uma plataforma operacional completa de transporte, com:
- planejamento
- execucao
- analise
- parametrizacao
- relatorios
- apoio a decisao

Traduzindo para o FrotaSmart:
o maior espaco de evolucao nao esta em trocar tecnologia, e sim em aprofundar o dominio.

## Direcao recomendada para o FrotaSmart

Em vez de ampliar tudo de uma vez, a evolucao mais inteligente e:

1. consolidar o nucleo de frota
2. enriquecer operacao e rastreabilidade
3. criar modulos de apoio operacional
4. depois adicionar inteligencia de planejamento e relatorios

## Backlog de melhorias e novas funcionalidades

## Prioridade alta

### 1. Cadastro completo de motoristas
Hoje o sistema fala em perfis e menciona motoristas, mas ainda nao aparece um modulo operacional dedicado.

Sugestao:
- cadastro de motorista
- CNH, categoria e validade
- telefone e contato
- situacao do motorista: ativo, ferias, afastado, desligado
- vinculo atual com veiculo

Valor:
- aproxima o sistema da rotina real da frota
- prepara o terreno para viagens, escalas e compliance documental

### 2. Historico de manutencao por veiculo
Hoje existe status de manutencao, mas falta historico operacional.

Sugestao:
- abrir ordem de servico
- registrar defeito informado
- registrar data de entrada e saida
- registrar fornecedor/oficina
- registrar custo da manutencao
- registrar pecas trocadas
- registrar responsavel pelo apontamento

Valor:
- transforma manutencao de status simples em processo auditavel
- permite acompanhar indisponibilidade e custo por veiculo

### 3. Soft delete e arquivamento real de veiculos
Esse ponto ja aparece como risco no proprio contexto do projeto.

Sugestao:
- substituir exclusao fisica por `ativo/inativo/baixado/arquivado`
- manter historico para auditoria
- esconder baixados por padrao da tela principal

Valor:
- evita perda de historico
- alinha o sistema a uma operacao administrativa mais segura

### 4. Dashboard operacional mais forte
O dashboard atual e limpo e funcional, mas ainda simples.

Inspirado no SETE:
- atalhos para funcoes frequentes
- quantidade de veiculos por status oficial
- motoristas ativos e indisponiveis
- manutencoes em aberto
- proximas manutencoes previstas
- alertas de documentos vencendo

Valor:
- transforma dashboard em painel de trabalho, nao apenas vitrine

### 5. Modulo de viagens ou rotas operacionais
O maior salto de valor de negocio para o FrotaSmart seria sair de cadastro puro para operacao.

Sugestao:
- cadastrar rota ou viagem
- origem e destino
- motorista responsavel
- veiculo vinculado
- data e horario
- km inicial e final
- observacoes da operacao

Valor:
- liga veiculo, motorista e operacao real
- cria base para custos, relatorios e produtividade

## Prioridade media

### 6. Previsao de manutencao
O SETE tem uma ideia muito boa aqui: manutencao nao deve ser so reativa.

Sugestao:
- proxima revisao por km
- proxima revisao por data
- alerta de vencimento
- lista de pendencias preventivas

Valor:
- reduz risco operacional
- melhora planejamento da oficina

### 7. Controle de abastecimento
Muito aderente ao dominio de frota e com grande retorno.

Sugestao:
- data do abastecimento
- veiculo
- motorista
- posto fornecedor
- litros
- valor total
- km no momento do abastecimento

Valor:
- permite calcular consumo medio
- ajuda a detectar anomalias e desperdicio

### 8. Cadastro de fornecedores e oficinas
Inspirado no modulo de fornecedor do SETE.

Sugestao:
- oficinas
- postos de combustivel
- autopecas
- prestadores de servico
- historico de atendimentos por fornecedor

Valor:
- melhora rastreabilidade de custo e manutencao

### 9. Relatorios especializados
O SETE mostra bem o valor de relatorios por modulo.

Sugestao inicial:
- relatorio de frota
- relatorio de manutencoes
- relatorio de abastecimento
- relatorio de utilizacao por veiculo
- relatorio de usuarios e perfis

Valor:
- da cara de sistema de gestao, nao apenas cadastro

### 10. Importacao por planilha
O SETE possui importacoes e isso faz muito sentido para administracao publica.

Sugestao:
- importar veiculos por CSV
- importar motoristas por CSV
- validar cabecalho antes da importacao
- relatorio de erros por linha

Valor:
- acelera implantacao em novos municipios ou secretarias

## Prioridade estrategica

### 11. Modulo de custos operacionais
Inspirado no modulo `custo` do SETE.

Sugestao:
- parametros de custo por tipo de veiculo
- custo medio por km
- custo acumulado por veiculo
- custo por manutencao
- custo por rota ou viagem

Valor:
- eleva o FrotaSmart de sistema operacional para sistema gerencial

### 12. Mapa operacional
O mapa no SETE e um diferencial forte.

No FrotaSmart, sem exagerar:
- localizacao de garagens
- localizacao de oficinas parceiras
- pontos de origem e destino de rotas
- visualizacao de trajetos cadastrados

Valor:
- melhora leitura operacional da frota
- prepara terreno para roteirizacao futura

### 13. Sugestao de rotas
No SETE esse e um modulo sofisticado.

No FrotaSmart, a versao inicial pode ser bem mais simples:
- montar roteiro manual
- ordenar paradas
- estimar distancia e tempo
- sugerir agrupamentos futuros como fase posterior

Valor:
- adiciona inteligencia sem exigir salto tecnico imediato

### 14. Indicadores gerenciais
Sugestao:
- disponibilidade da frota
- tempo medio em manutencao
- custo medio mensal por veiculo
- consumo medio por categoria
- quantidade de viagens por periodo

Valor:
- apoio a decisao da gestao publica

## Melhorias de UX e organizacao inspiradas no SETE

Sem copiar layout, existem ideias boas para absorver:

- atalhos rapidos no dashboard para tarefas frequentes
- separar melhor os modulos por contexto de negocio
- paginas de listagem, visualizacao e cadastro por entidade
- filtros e buscas mais ricos
- estados vazios mais informativos
- mais indicadores visuais por status e urgencia
- paginas de parametros do sistema para itens administrativos

## O que nao vale copiar do SETE

Para manter o FrotaSmart saudavel no seu contexto atual, eu nao recomendo trazer agora:

- stack Electron
- dependencias geoespaciais pesadas
- modulo de simulacao sofisticada logo no inicio
- offline desktop como prioridade imediata
- escopo de transporte escolar completo antes de consolidar frota geral

O ganho maior vem de adaptar a ideia de produto, nao de copiar a infraestrutura tecnica.

## Roadmap sugerido sem framework

### Fase 1 - Consolidacao do nucleo
- soft delete de veiculos
- historico de manutencao
- cadastro de motoristas
- dashboard operacional melhorado

### Fase 2 - Operacao real
- viagens ou rotas operacionais
- abastecimento
- fornecedores e oficinas
- relatorios iniciais

### Fase 3 - Gestao e inteligencia
- previsao de manutencao
- custos operacionais
- indicadores gerenciais
- mapa operacional simples

### Fase 4 - Evolucao avancada
- importacao por planilha
- roteirizacao assistida
- analises comparativas por periodo

## Conclusao

O FrotaSmart esta em uma boa direcao tecnica.
O principal proximo salto nao e tecnologia, e profundidade de dominio.

Se o projeto seguir em PHP puro com arquitetura incremental, ele pode ficar robusto sem perder simplicidade.

O melhor aprendizado vindo do SETE e:
- pensar em modulos de negocio completos
- transformar o dashboard em centro operacional
- sair de cadastro basico para gestao de processo
- investir em relatorios, parametros e historico

## Recomendacao pratica imediata

Se fosse escolher as proximas 3 entregas com melhor custo-beneficio, eu faria nesta ordem:

1. modulo de motoristas
2. historico de manutencao
3. abastecimento com relatorio simples

Essas tres frentes ja mudariam bastante a maturidade do FrotaSmart sem exigir framework nem reescrita total.
