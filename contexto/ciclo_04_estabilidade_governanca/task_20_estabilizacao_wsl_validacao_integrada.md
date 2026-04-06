# Task 20 - Estabilizacao definitiva do ambiente WSL e validacao integrada

## Objetivo
Transformar o Ubuntu WSL no ambiente principal e repetivel de execucao do FrotaSmart, com validacao integrada real e sem dependencia acidental de configuracoes do Windows.

## Problema observado
- havia risco de o projeto herdar configuracoes externas de PHP, especialmente caminho de sessao
- a validacao integrada estava espalhada em varios comandos sem um ponto unico de health check
- o `db.php` ainda usava carregamento legado do `.env`, duplicando responsabilidade

## Entrega realizada em 2026-04-05
- `backend/config/security.php` passou a fixar `session_save_path` dentro de `runtime/sessions`
- `backend/config/db.php` foi simplificado para reutilizar `EnvLoader`
- criado o script `scripts/test-wsl-stack.php` para validar ambiente, banco, bootstrap e testes integrados essenciais
- adicionado o atalho Composer `test:wsl-stack`
- validado no Ubuntu WSL com sucesso usando banco local em `127.0.0.1:3306`

## Resultado observado
- o projeto deixa de depender do `php.ini` externo para sessao
- a validacao do ambiente Linux fica repetivel com um unico comando
- o FrotaSmart passa a ter um caminho operacional mais limpo para os proximos ciclos
