# FrotaSmart no Ubuntu WSL

## Links uteis
- Guia Linux geral: [readme_linux.md](./readme_linux.md)
- Estado atual: [estado_projeto.md](./estado_projeto.md)
- Progresso: [progresso.md](./progresso.md)
- Arquitetura: [arquitetura_projeto.md](./arquitetura_projeto.md)
- Roadmap atual: [ciclo_04_estabilidade_governanca/roadmap_ciclo_04.md](./ciclo_04_estabilidade_governanca/roadmap_ciclo_04.md)
- Roadmap ciclo 01: [ciclo_01_fundacao_arquitetura/roadmap_ciclo_01.md](./ciclo_01_fundacao_arquitetura/roadmap_ciclo_01.md)

Este guia mostra como executar o FrotaSmart localmente no Ubuntu WSL, com o mesmo desenho operacional pensado para deploy futuro em Linux Ubuntu na nuvem.

## Cenario esperado
- Windows 10 ou 11
- WSL 2 instalado
- Ubuntu instalado no WSL
- Projeto localizado em `C:\Users\lukao\Documents\FrotaSmart`

No Ubuntu via WSL, esse caminho vira:

```bash
/mnt/c/Users/lukao/Documents/FrotaSmart
```

## 1. Instalar o WSL e o Ubuntu
No PowerShell do Windows, como administrador:

```powershell
wsl --install
```

Se o WSL ja estiver instalado, confira:

```powershell
wsl --status
wsl -l -v
```

Abra o Ubuntu e atualize os pacotes:

```bash
sudo apt update && sudo apt upgrade -y
```

## 2. Instalar PHP, extensoes e Composer no Ubuntu

```bash
sudo apt install -y php php-cli php-mysql php-mbstring php-xml php-curl php-zip unzip curl git composer mariadb-client
```

Valide:

```bash
php -v
composer --version
php -m | grep -E "pdo_mysql|mbstring|openssl"
```

## 3. Entrar na pasta do projeto

```bash
cd /mnt/c/Users/lukao/Documents/FrotaSmart
```

Se quiser abrir no VS Code com a extensao Remote WSL:

```bash
code .
```

## 4. Criar e ajustar o `.env`
Se ainda nao existir:

```bash
cp .env.example .env
```

Edite o arquivo:

```bash
nano .env
```

Exemplo recomendado para banco local acessivel pelo proprio Ubuntu WSL:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=frota_smart
DB_USER=root
DB_PASS=sua_senha
ADMIN_DEFAULT_USER=admin_frota
ADMIN_DEFAULT_PASS=uma_senha_forte
```

## 5. Garantir acesso ao MySQL ou MariaDB
Voce pode usar:
- MySQL rodando dentro do proprio Ubuntu no WSL
- MySQL acessivel por TCP no host local, desde que o `.env` permaneça apontando para um endpoint Linux/WSL compativel

Para testar conexao com banco local:

```bash
mysql -h 127.0.0.1 -P 3306 -u root -p
```

Se for usar banco dentro do WSL, instale:

```bash
sudo apt install -y mariadb-server
sudo service mariadb start
```

Depois ajuste o `.env` com o usuario e senha corretos.

## 6. Instalar dependencias do projeto

```bash
composer install
```

Se ja existir `vendor/`, voce ainda pode garantir o autoload:

```bash
composer dump-autoload
```

## 7. Inicializar o banco

```bash
php scripts/bootstrap-db.php
```

Esse script cria a estrutura inicial e tenta criar o usuario administrador padrao definido no `.env`.

## 8. Rodar os testes basicos

```bash
php scripts/test-autoload.php
php scripts/test-domain.php
php scripts/test-repository-contract.php
php scripts/test-veiculo-service.php
php scripts/test-veiculo-controller-flow.php
php scripts/test-audit-flow.php
php scripts/test-rbac-veiculos.php
```

Se o banco estiver corretamente configurado, voce tambem pode rodar:

```bash
php scripts/test-repository-pdo.php
php scripts/test-operacao-frota-guard.php
php scripts/test-wsl-stack.php
```

Ou, se preferir pelo Composer:

```bash
composer run test:wsl-stack
```

## 9. Subir a aplicacao localmente
A partir da raiz do projeto:

```bash
php -S 0.0.0.0:8000 -t public
```

Depois abra no navegador:

```text
http://localhost:8000/login.php
```

## 10. Rotas publicas principais
- `http://localhost:8000/login.php`
- `http://localhost:8000/dashboard.php`
- `http://localhost:8000/user_management.php`

## 11. Parar o servidor
No terminal onde o servidor estiver rodando:

```bash
Ctrl+C
```

## Troubleshooting

### `php: command not found`
Instale o PHP no Ubuntu:

```bash
sudo apt install -y php php-cli
```

### `composer: command not found`

```bash
sudo apt install -y composer
```

### Erro de conexao com banco
- confira `DB_HOST`, `DB_PORT`, `DB_USER` e `DB_PASS` no `.env`
- teste com `mysql -h 127.0.0.1 -P 3306 -u usuario -p`
- confirme que o banco esta aceitando conexoes TCP em `127.0.0.1:3306`

### `Access denied for user`
As credenciais do `.env` nao batem com o MySQL configurado. Ajuste usuario e senha antes de rodar `bootstrap-db.php` ou `test-repository-pdo.php`.

### Porta `8000` em uso
Suba em outra porta:

```bash
php -S 0.0.0.0:8080 -t public
```

Depois acesse:

```text
http://localhost:8080/login.php
```

### Lentidao ao trabalhar em `/mnt/c/...`
No WSL isso pode acontecer. Para desenvolvimento local simples funciona bem, mas, para ficar mais proximo do ambiente final em Ubuntu, o ideal depois e mover o projeto para dentro do filesystem Linux e sincronizar via Git.

## Fluxo resumido

```bash
cd /mnt/c/Users/lukao/Documents/FrotaSmart
cp .env.example .env
composer install
php scripts/bootstrap-db.php
php scripts/test-domain.php
php scripts/test-wsl-stack.php
php -S 0.0.0.0:8000 -t public
```
