# FrotaSmart no Linux / WSL

## Links uteis
- Progresso do projeto: [PROGRESSO.MD](./PROGRESSO.MD)
- Roadmap de tasks: [tasks.md](./AI/Tasks/tasks.md)
- Arquitetura: [Arquitetura-Projeto.md](./AI/Contexto/Arquitetura-Projeto.md)
- Estado atual: [Estado-Projeto.md](./AI/Contexto/Estado-Projeto.md)
- Contexto de transicao: [ContextodeTransicao.md](./AI/Contexto/ContextodeTransicao.md)
- Hardening Apache: [APACHE_HARDENING.md](./docs/APACHE_HARDENING.md)
- Revisao de seguranca: [SECURITY_REVIEW.md](./docs/SECURITY_REVIEW.md)

Este projeto deve ser executado com PHP 8.2+ e MySQL ou MariaDB em ambiente Linux ou WSL, sem depender de XAMPP.

## Requisitos
- PHP 8.2 ou superior
- Extensoes PHP: `pdo_mysql`, `openssl`, `mbstring`
- Composer instalado no sistema
- MySQL ou MariaDB em execucao
- Apache 2.4+ quando houver publicacao em servidor

## 1. Preparar variaveis de ambiente
Crie o arquivo `.env` a partir do exemplo:

```bash
cp .env.example .env
```

Ajuste as credenciais do banco no `.env` e defina uma senha forte para `ADMIN_DEFAULT_PASS`.

## 2. Instalar dependencias e gerar autoload

```bash
composer install
```

## 3. Inicializar o banco

```bash
php scripts/bootstrap-db.php
```

## 4. Subir a aplicacao localmente
Use o servidor embutido do PHP apontando o document root para `public/`:

```bash
php -S 0.0.0.0:8000 -t public
```

A aplicacao ficara disponivel em `http://localhost:8000/login.php`.

## 5. Validacoes uteis

```bash
php scripts/test-autoload.php
php scripts/test-domain.php
php scripts/test-repository-contract.php
```

## 6. Operacoes administrativas seguras
Para redefinir senha de um usuario:

```bash
php scripts/reset-password.php <nova_senha> [usuario]
```

## Observacoes de deploy Ubuntu/Apache
- Configure o document root do servidor web para a pasta `public/`
- Nao exponha a raiz do repositorio
- Mantenha `.env` fora do versionamento
- Garanta que o usuario do servidor tenha permissao de leitura no projeto
- Habilite `mod_headers` e `mod_rewrite` no Apache para aplicar as protecoes de `.htaccess`
- Use HTTPS em producao para que os cookies de sessao sejam enviados com a flag `Secure`

Consulte tambem [APACHE_HARDENING.md](./docs/APACHE_HARDENING.md) e [SECURITY_REVIEW.md](./docs/SECURITY_REVIEW.md).
