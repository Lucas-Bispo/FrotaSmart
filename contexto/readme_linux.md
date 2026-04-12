# FrotaSmart no Linux / WSL

## Links uteis
- Progresso do projeto: [progresso.md](./progresso.md)
- Roadmap atual: [roadmap_ciclo_04.md](./ciclo_04_estabilidade_governanca/roadmap_ciclo_04.md)
- Roadmap ciclo 01: [roadmap_ciclo_01.md](./ciclo_01_fundacao_arquitetura/roadmap_ciclo_01.md)
- Arquitetura: [arquitetura_projeto.md](./arquitetura_projeto.md)
- Estado atual: [estado_projeto.md](./estado_projeto.md)
- Contexto de transicao: [contexto_transicao.md](./contexto_transicao.md)
- Guia WSL Ubuntu no Windows: [readme_wsl_ubuntu_windows.md](./readme_wsl_ubuntu_windows.md)
- Hardening Apache: [apache_hardening.md](./docs/apache_hardening.md)
- Revisao de seguranca: [security_review.md](./docs/security_review.md)

Este projeto deve ser executado com PHP 8.2+ e MySQL ou MariaDB em ambiente Linux ou Ubuntu WSL, seguindo o mesmo caminho operacional previsto para deploy em nuvem.

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

Consulte tambem [apache_hardening.md](./docs/apache_hardening.md) e [security_review.md](./docs/security_review.md).
