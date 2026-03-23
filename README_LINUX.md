# FrotaSmart no Linux / WSL

Este projeto deve ser executado com PHP 8.2+ e MySQL ou MariaDB em ambiente Linux ou WSL, sem depender de XAMPP.

## Requisitos

- PHP 8.2 ou superior
- Extensoes PHP: `pdo_mysql`, `openssl`, `mbstring`
- Composer instalado no sistema
- MySQL ou MariaDB em execucao

## 1. Preparar variaveis de ambiente

Crie o arquivo `.env` a partir do exemplo:

```bash
cp .env.example .env
```

Ajuste as credenciais do banco no `.env`.

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

A aplicacao ficara disponivel em:

```text
http://localhost:8000/login.php
```

## 5. Validacoes uteis

```bash
php scripts/test-autoload.php
php scripts/test-domain.php
php scripts/test-repository-contract.php
```

## Observacoes de deploy Ubuntu

- Configure o document root do servidor web para a pasta `public/`
- Nao exponha a raiz do repositorio
- Mantenha `.env` fora do versionamento
- Garanta que o usuario do servidor tenha permissao de leitura no projeto
