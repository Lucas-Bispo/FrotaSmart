# Padrao Clean Code do FrotaSmart

## Objetivo
Definir um padrao pratico de Clean Code para o FrotaSmart, alinhado ao contexto real do projeto:
- PHP puro
- MVC legado com migracao incremental para `src/`
- desenvolvimento local em Ubuntu WSL
- deploy alvo em Linux Ubuntu

Este documento nao e uma lista generica de boas intencoes. Ele serve como criterio de implementacao, revisao e refatoracao continua.

## Referencias base
- Martin Fowler sobre unit tests: https://martinfowler.com/bliki/UnitTest.html
- Martin Fowler sobre refatoracao: https://martinfowler.com/books/refactoring.html
- Microsoft Learn sobre complexidade ciclomática: https://learn.microsoft.com/visualstudio/code-quality/code-metrics-cyclomatic-complexity
- Microsoft Learn sobre excecoes: https://learn.microsoft.com/dotnet/standard/exceptions/
- Microsoft Learn sobre funcoes puras e efeitos colaterais: https://learn.microsoft.com/archive/msdn-magazine/2010/april/fsharp-basics-an-introduction-to-functional-programming-for-net-developers

## Principios obrigatorios

### A regra do escoteiro
Deixe o codigo um pouco melhor do que estava antes.

No FrotaSmart isso significa:
- ao tocar um arquivo, corrigir ao menos um problema local de legibilidade, duplicacao ou acoplamento
- preferir pequenas melhorias seguras a grandes reescritas arriscadas
- atualizar documentacao e testes quando a mudanca alterar comportamento ou fluxo

### Mantenha classes, metodos e arquivos pequenos
Arquivos, classes e funcoes devem ter responsabilidade clara e tamanho controlado.

Regra pratica no projeto:
- controllers devem orquestrar HTTP, permissao, CSRF, flash e redirecionamento
- validacoes complexas devem ser quebradas em metodos pequenos e nomeados
- consultas SQL extensas devem migrar para camadas de leitura dedicadas
- views grandes devem ser reduzidas por extracao de helpers e blocos reutilizaveis

Sinais de alerta:
- metodos com muitas validacoes em cadeia
- arquivos com mais de uma responsabilidade funcional
- controllers decidindo regra de negocio que deveria estar em service, guard ou dominio

### Comente apenas o necessario
Comentario bom explica intencao ou contexto que o codigo sozinho nao deixa claro.

No FrotaSmart:
- evitar comentar o obvio
- comentar apenas decisoes arquiteturais, restricoes do legado, compatibilidades e regras menos intuitivas
- preferir nomes melhores e extracao de metodos antes de adicionar comentario

### Use nomes significativos
Nomes devem reduzir a necessidade de interpretacao.

Boas praticas:
- preferir `validatedPayload()` a `processData()`
- preferir `assertOperationalRules()` a `checkStuff()`
- preferir `preventivaStatusSeverity()` a `calc()`

No projeto:
- nomes devem refletir linguagem do dominio: `veiculo`, `motorista`, `manutencao`, `abastecimento`, `viagem`, `secretaria`
- nomes booleanos devem responder como pergunta: `estaArquivado`, `estaDisponivel`, `isValidDate`

### Formatacao e estilo de codigo
Codigo deve ser previsivel de ler.

Padrao do projeto:
- `declare(strict_types=1);` sempre que aplicavel
- espacos consistentes em condicionais e chamadas
- arrays e SQL longos em blocos legiveis
- um nivel de abstracao por bloco de codigo
- evitar misturar montagem de payload, validacao e persistencia no mesmo trecho

### Evite abstracoes precipitadas e refatore sempre
Nao criar camada so porque "um dia pode precisar".

No FrotaSmart:
- abstrair apenas quando houver duplicacao real, acoplamento evidente ou responsabilidade mal posicionada
- refatorar em passos pequenos, mantendo compatibilidade com o legado
- privilegiar extracoes incrementais como `QueryService`, `Service`, `Guard`, `Value Object`

### Minimize a complexidade ciclomática
Quanto mais ramificacoes, mais dificil testar, manter e prever comportamento.

No projeto:
- preferir guard clauses
- extrair validacoes para metodos pequenos
- reduzir `if` encadeado quando houver grupos logicos claros
- separar montagem de payload, validacao e operacao principal

Objetivo pratico:
- controllers e models nao devem concentrar todas as decisoes em um unico metodo

### Prefira excecoes a retorno de codigos de erro
Falhas devem ser tratadas de forma expressiva e consistente.

No FrotaSmart:
- usar excecoes para falhas tecnicas e de regra de negocio quando a chamada nao deve continuar
- evitar retornos como `false`, `0`, `'erro'` ou arrays ambiguos para sinalizar falha
- controllers devem capturar excecoes quando fizer sentido converter em flash amigavel

### Seja consistente
Consistencia reduz custo mental.

Aplicacao pratica:
- se um modulo usa `Service + Repository`, novos fluxos do mesmo modulo devem seguir o mesmo caminho
- se um controller usa `flashAndRedirect`, manter o padrao
- se um model usa `PDO` explicito, evitar voltar para dependencia oculta

### O que sao Testes de unidade
Teste de unidade verifica uma unidade pequena de comportamento com foco, isolamento e feedback rapido.

No FrotaSmart:
- dominio, services, guards e normalizadores sao os melhores candidatos
- unit tests devem validar regra, nao infraestrutura externa
- banco real e fluxo HTTP completo ficam melhor em testes integrados

Exemplos bons no projeto:
- entidades e value objects
- services de veiculo
- guardas operacionais
- helpers puros de sumarizacao e normalizacao

### Evite passar booleanos e nulos
Booleanos e `null` em assinaturas geralmente escondem mais de um comportamento na mesma funcao.

No FrotaSmart:
- evitar novos parametros como `bool $includeArchived = false` quando houver alternativa mais expressiva
- preferir metodos distintos quando o comportamento for diferente
- usar objetos de filtro ou arrays nomeados quando houver varias opcoes relacionadas

Exemplos de evolucao desejada:
- `buscarPorPlaca()` e `buscarPorPlacaIncluindoArquivados()`
- `listarAtivos()` e `listarArquivados()` em vez de um unico metodo sobrecarregado por flag

### O que sao Funcoes puras
Funcoes puras dependem apenas dos argumentos de entrada e nao causam efeitos colaterais observaveis.

Beneficios:
- mais simples de testar
- mais simples de reutilizar
- mais previsiveis para refatorar

No FrotaSmart, candidatos naturais:
- normalizadores
- formatadores
- calculos de resumo
- ordenadores e comparadores
- funcoes de mapeamento entre banco, view e dominio

## Analise do projeto nesta data

### Pontos fortes
- `src/` ja comeca a concentrar services, repositorios, auditoria e read models
- existe trilha clara de migracao incremental sem reescrita total
- regras centrais de veiculos ja estao melhor posicionadas do que no inicio do projeto

### Principais hotspots atuais
- [dashboard.php](../frontend/views/dashboard.php) ainda e grande demais para uma unica view
- [relatorios.php](../frontend/views/relatorios.php) concentra muita variacao de tela na mesma view
- [bootstrap-db.php](../scripts/bootstrap-db.php) tem alta ramificacao e mistura bootstrap com migracao incremental
- controllers operacionais ainda concentram validacoes extensas
- alguns metodos e contratos ainda usam booleanos e `null` como chaves de comportamento

### Direcao recomendada
1. reduzir complexidade em controllers
2. extrair consultas SQL e read models
3. mover regras e calculos puros para funcoes e services pequenos
4. reduzir views grandes por blocos reutilizaveis
5. revisar contratos que ainda dependem de flags booleanas

## Regras praticas para novas mudancas
- toda mudanca deve melhorar legibilidade ou estrutura local
- todo metodo novo deve ter uma responsabilidade clara
- toda validacao longa deve ser quebrada em blocos nomeados
- toda regra de negocio duplicada deve ser candidata a extracao
- toda consulta SQL grande deve ser avaliada para `src/Infrastructure/ReadModels`
- todo comportamento complexo deve ganhar teste automatizado quando viavel

## O que ja comecou a ser executado
- extracao de leitura de frota para `VeiculoDashboardService`
- extracao de leitura de relatorios para `RelatorioOperacionalQueryService`
- reducao gradual de dependencia em `global $pdo`

## Proxima trilha de adocao
1. controllers de `viagens`, `manutencoes` e `abastecimentos`
2. simplificacao de `RelatorioOperacionalModel`
3. fracionamento das views grandes
4. revisao de contratos com booleanos e nulos

## Criterio de sucesso
Este padrao estara bem implementado quando:
- o codigo novo entrar menor, mais nomeado e mais testavel
- os hotspots legados forem reduzidos a cada task
- o projeto mantiver compatibilidade com o legado sem sacrificar clareza
- a equipe conseguir ler, alterar e validar o sistema com menos custo mental
