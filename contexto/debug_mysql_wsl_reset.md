# Debug e reset do MySQL no WSL

## Objetivo
Registrar o passo a passo mais recente para recuperar um ambiente MySQL limpo no Ubuntu WSL, depois do conflito entre MariaDB e MySQL 8.

## Diagnostico observado
- O `mysql.service` chegou a subir inicialmente
- Depois o MySQL entrou em modo de protecao
- O log mostrou:

```text
MySQL has been frozen to prevent damage to your system. Please see /etc/mysql/FROZEN for help.
```

- O diretório `/var/lib/mysql` ficou contaminado por arquivos do MariaDB, como:
  - `aria_log.00000001`
  - `aria_log_control`
  - `debian-10.11.flag`

Isso indica mistura entre datadir de MariaDB e MySQL 8, e o MySQL congelou o start para evitar corrupcao.

## Estrategia decidida
Parar de tentar reaproveitar o estado atual e recriar um ambiente MySQL limpo no WSL.

## Passo a passo de limpeza total

```bash
sudo service mysql stop || true
sudo service mariadb stop || true
sudo apt purge -y mysql-server mysql-client mysql-common mariadb-server mariadb-client mariadb-common
sudo apt autoremove -y
sudo rm -rf /var/lib/mysql /var/lib/mysql-8.0 /etc/mysql /var/log/mysql
sudo apt update
sudo apt install -y mysql-server mysql-client
```

## Subir o MySQL limpo

```bash
sudo service mysql start
sudo service mysql status
```

## Criar banco e usuario do projeto

```bash
sudo mysql
```

Dentro do prompt SQL:

```sql
CREATE DATABASE frota_smart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'frota_user'@'localhost' IDENTIFIED BY 'frota123';
GRANT ALL PRIVILEGES ON frota_smart.* TO 'frota_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## Testar conexao

```bash
mysql -h 127.0.0.1 -P 3306 -u frota_user -pfrota123
```

Se conectar, o banco local do WSL ficou pronto.

## `.env` esperado

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=frota_smart
DB_USER=frota_user
DB_PASS=frota123
ADMIN_DEFAULT_USER=admin_frota
ADMIN_DEFAULT_PASS=SenhaAdmin123
```

## Comandos para subir o projeto depois do banco ok

```bash
cd /mnt/c/Users/lukao/Documents/FrotaSmart
composer install
php scripts/bootstrap-db.php
php scripts/test-repository-pdo.php
php -S 0.0.0.0:8000 -t public
```

## URL local

```text
http://localhost:8000/login.php
```

## Observacao importante
Nao tentar misturar novamente MariaDB e MySQL no mesmo datadir do WSL. A partir daqui, manter apenas um dos dois no ambiente.
