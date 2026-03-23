# Auditoria tecnica e de seguranca do projeto FrotaSmart

## Escopo avaliado

Analisei a estrutura atual do sistema PHP puro com foco em:

- superficie de ataque web;
- autenticacao e sessao;
- controles de autorizacao;
- exposicao indevida de arquivos no Apache;
- scripts administrativos;
- organizacao arquitetural e backlog de engenharia.

## Resumo executivo

O projeto possui uma base promissora e ja traz alguns controles positivos, como prepared statements, hash de senha, CSRF e separacao inicial entre `public/`, `backend/`, `frontend/` e `src/`. Ainda assim, havia lacunas importantes para um contexto governamental:

1. nao existiam cabecalhos HTTP de endurecimento;
2. a sessao nao expirava por inatividade nem validava contexto do cliente;
3. scripts administrativos expunham senha em linha de comando;
4. faltava blindagem adicional para publicacao via Apache;
5. havia arquivos soltos sem utilidade no repositorio;
6. faltava uma consolidacao documentada do backlog tecnico e de seguranca.

## Correcoes implementadas nesta revisao

### 1. Camada de sessao e resposta HTTP

- inclusao de cabecalhos de seguranca HTTP no bootstrap de seguranca;
- expiracao de sessao por inatividade;
- rotacao periodica do identificador de sessao;
- fingerprint simples de sessao com IP + User-Agent para detectar desvio de contexto;
- validacao complementar de mesma origem para requisicoes `POST`.

### 2. Autenticacao, autorizacao e trilha de auditoria

- reforco do fluxo de login/logout com eventos de auditoria;
- registro de eventos relevantes de autenticacao e manipulacao de usuarios/veiculos via `error_log` estruturado;
- fortalecimento da politica minima de senha para novos usuarios administrativos.

### 3. Operacao segura de administracao

- substituicao do reset de senha via argumento de linha de comando por prompt interativo oculto;
- endurecimento do exemplo `.env` para evitar senha padrao versionada.

### 4. Publicacao em Apache

- adicao de guias e arquivos `.htaccess` para bloquear acesso a arquivos sensiveis e desabilitar listagem de diretorios;
- criacao de documentacao operacional de hardening para Apache.

### 5. Higiene do repositorio

- remocao de arquivos temporarios/soltos sem valor para o produto (`Sem titulo*.base`).

## Revisao por area do projeto

### `public/`

- **Papel:** ponto de entrada web.
- **Status:** adequado como document root, mas depende de Apache corretamente configurado.
- **Risco principal:** se a raiz do repositorio for exposta por engano, `backend/`, `scripts/` e `.env` podem ficar acessiveis.
- **Acao tomada:** endurecimento por `.htaccess` e documentacao de deploy.

### `backend/config/`

- **Papel:** seguranca e conexao com banco.
- **Pontos positivos:** uso de PDO com prepared statements e `ATTR_EMULATE_PREPARES=false`.
- **Riscos encontrados:** faltavam headers de seguranca, timeout de sessao e rotacao de sessao.
- **Acao tomada:** endurecimento centralizado em `security.php`.

### `backend/controllers/`

- **Papel:** autenticar, cadastrar usuarios e manipular frota.
- **Pontos positivos:** validacao de CSRF ja existente e checagem de perfis.
- **Riscos encontrados:** ausencia de validacao de mesma origem, pouca rastreabilidade de eventos e politicas de senha fracas para area administrativa.
- **Acao tomada:** adicao de `require_same_origin_post()`, trilha de auditoria e validacoes mais fortes.

### `backend/models/`

- **Papel:** acesso a dados.
- **Pontos positivos:** prepared statements minimizam SQL injection.
- **Riscos remanescentes:** uso de `global $pdo`, ausencia de servicos/repositories concretos e de transacoes de negocio mais complexas.
- **Backlog recomendado:** migrar gradualmente para classes com injecao de dependencia e repositories alinhados ao dominio.

### `frontend/`

- **Papel:** views e layout.
- **Pontos positivos:** escaping HTML consistente na maior parte das saidas.
- **Riscos remanescentes:** dependencia de Tailwind CDN em producao e uso de script inline, o que impede CSP mais restritiva.
- **Backlog recomendado:** empacotar assets localmente para remover `'unsafe-inline'` e dependencias externas no front.

### `src/Domain/`

- **Papel:** inicio de uma modelagem de dominio mais robusta.
- **Pontos positivos:** `Value Object` para placa e regras de transicao de estado.
- **Riscos remanescentes:** parte do dominio nao esta integrada ao fluxo principal CRUD do `backend/`.
- **Backlog recomendado:** convergir controladores e models legados para a camada de dominio.

### `scripts/`

- **Papel:** bootstrap, testes simples e operacoes administrativas.
- **Riscos encontrados:** reset de senha por CLI exibia senha em lista de processos.
- **Acao tomada:** troca para prompt oculto.
- **Backlog recomendado:** adicionar scripts de verificacao automatica de seguranca, lint e testes integrados.

### `AI/`

- **Papel:** documentacao operacional/contextual do projeto.
- **Observacao:** deve permanecer fora do document root em ambiente Apache.

## Backlog priorizado apos esta PR

### Prioridade alta

1. Implementar controle de tentativas de login persistente por usuario/IP em banco ou cache compartilhado.
2. Adicionar log de auditoria persistente e consultavel no proprio sistema.
3. Eliminar dependencias front-end via CDN em producao.
4. Criar migrations versionadas em vez de bootstrap manual crescente.
5. Criar testes automatizados de autenticacao, autorizacao e regressao de seguranca.

### Prioridade media

1. Adicionar gerenciamento de perfis mais granular (RBAC) para entidades publicas.
2. Introduzir servicos de aplicacao e repositories concretos aderentes ao `src/Domain`.
3. Registrar trilha de alteracoes de cadastros sensiveis (veiculos, contratos, motoristas, CNH).
4. Padronizar validadores reutilizaveis para CPF/CNPJ, CNH, RENAVAM, chassi e datas.

### Prioridade estrutural

1. Separar melhor configuracao, dominio, aplicacao e infraestrutura.
2. Definir pipeline CI com testes, SAST e secret scanning.
3. Preparar observabilidade minima: healthcheck, logs estruturados e metricas.

## Conclusao

A base atual ja permite evolucao segura, mas para um produto de gestao de frota voltado a orgaos publicos e essencial continuar o endurecimento da plataforma, especialmente em governanca de acesso, auditoria, publicacao Apache e maturidade arquitetural.
