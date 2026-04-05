# Recuperacao do localhost com WSL + MySQL

## Data
2026-04-05

## Objetivo
Restaurar o ambiente local do FrotaSmart no Windows usando Ubuntu WSL, removendo o conflito com MariaDB e colocando a aplicacao para responder em `http://localhost:8000/login.php`.

## Problema encontrado
- O `mysql.service` no WSL estava falhando.
- O MySQL entrou em modo de protecao com mensagem de freeze.
- O datadir continha arquivos de MariaDB misturados com MySQL, como:
  - `aria_log.00000001`
  - `aria_log_control`
  - `debian-10.11.flag`
- O `.env` estava apontando para `DB_HOST=172.25.240.1`, mas o cenário correto para o banco rodando dentro do WSL era `127.0.0.1`.

## Diagnostico validado
- WSL ativo: `Ubuntu-24.04` em WSL2
- PHP no WSL: `8.3.6`
- Composer no WSL: ok
- MySQL antes do reset: quebrado por mistura com MariaDB

## Acoes executadas
Foi feito reset completo do banco local no Ubuntu WSL:

```bash
service mysql stop || true
service mariadb stop || true
apt purge -y mysql-server mysql-client mysql-common mariadb-server mariadb-client mariadb-common
apt autoremove -y
rm -rf /var/lib/mysql /var/lib/mysql-8.0 /etc/mysql /var/log/mysql
apt update
apt install -y mysql-server mysql-client
service mysql start
```

Depois foi recriado o banco do projeto:

```sql
CREATE DATABASE IF NOT EXISTS frota_smart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'frota_user'@'localhost' IDENTIFIED BY 'frota123';
GRANT ALL PRIVILEGES ON frota_smart.* TO 'frota_user'@'localhost';
FLUSH PRIVILEGES;
```

## Ajuste no projeto
No arquivo `.env`, o host do banco foi corrigido para:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=frota_smart
DB_USER=frota_user
DB_PASS=frota123
ADMIN_DEFAULT_USER=admin_frota
ADMIN_DEFAULT_PASS=SenhaAdmin123
```

## Validacoes executadas
Na raiz do projeto dentro do Ubuntu WSL:

```bash
cd /mnt/c/Users/lukao/Documents/FrotaSmart
php scripts/bootstrap-db.php
php scripts/test-repository-pdo.php
```

Resultados observados:
- `Bootstrap concluido. Usuario administrador criado.`
- `Repositorio PDO validado com sucesso.`

## Problema adicional encontrado no login
Depois de o banco voltar, a tela de login carregava, mas o `POST /auth.php` respondia `403 Forbidden` mesmo com as credenciais corretas.

### Causa raiz
O bloqueio estava na validacao de same-origin em `backend/config/security.php`.

O codigo comparava:
- `HTTP_HOST`, que vinha como `127.0.0.1:8000`
- host extraido de `Origin` ou `Referer`, que vinha como `127.0.0.1`

Como a comparacao era literal, a aplicacao rejeitava o POST legitimo do formulario de login.

### Correcao aplicada
Foi ajustada a funcao `is_same_origin_request()` para:
- extrair host e porta corretamente do request atual
- comparar apenas os hostnames entre si
- comparar a porta somente quando ela vier informada nos dois lados

### Resultado
Depois da correcao:
- a resposta do login deixou de ser `403`
- o `POST /auth.php` passou a redirecionar para `/dashboard.php`
- as credenciais `admin_frota / SenhaAdmin123` ficaram funcionais

## Como subir a aplicacao novamente
No Ubuntu WSL:

```bash
cd /mnt/c/Users/lukao/Documents/FrotaSmart
php -S 0.0.0.0:8000 -t public
```

Abrir no Windows:

```text
http://localhost:8000/login.php
```

## Credenciais atuais
Login da aplicacao:
- usuario: `admin_frota`
- senha: `SenhaAdmin123`

Banco MySQL:
- banco: `frota_smart`
- usuario: `frota_user`
- senha: `frota123`

## Observacoes
- A decisao correta para este ambiente foi eliminar o MariaDB e manter somente MySQL.
- Se o MySQL voltar a congelar e reaparecerem arquivos `aria_log*`, assumir novamente mistura indevida com MariaDB.
- Se precisar validar rapidamente o ambiente no futuro, checar:

```bash
wsl -l -v
wsl -d Ubuntu-24.04 -- bash -lc "php -v"
wsl -d Ubuntu-24.04 -u root -- bash -lc "service mysql status"
wsl -d Ubuntu-24.04 -- bash -lc "cd /mnt/c/Users/lukao/Documents/FrotaSmart && php scripts/test-repository-pdo.php"
```
