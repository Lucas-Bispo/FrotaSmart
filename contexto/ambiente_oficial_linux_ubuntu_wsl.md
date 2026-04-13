# Ambiente Oficial Linux Ubuntu e WSL

## Objetivo
Padronizar o FrotaSmart para o ambiente oficial de execucao e desenvolvimento:
- servidor em Linux Ubuntu
- desenvolvimento local em Ubuntu WSL

## Diretriz principal
- o projeto deve ser executado com PHP e MySQL em ambiente Linux
- o document root oficial e `public/`
- o fluxo local deve reproduzir o ambiente de servidor o maximo possivel

## Fluxo oficial local
1. entrar no projeto pelo Ubuntu WSL:
```bash
cd /mnt/c/Users/lukao/Documents/FrotaSmart
```

2. validar a stack:
```bash
php scripts/test-wsl-stack.php
```

3. subir o servidor local:
```bash
php -S 127.0.0.1:8000 -t public
```

4. acessar:
```text
http://127.0.0.1:8000/login.php
```

## Fluxo oficial de servidor
- usar Linux Ubuntu com PHP, extensoes necessarias e MySQL ou MariaDB
- publicar a aplicacao apontando o servidor web para `public/`
- manter `.env` com credenciais e configuracoes do ambiente de producao
- validar bootstrap e testes essenciais antes de cada entrega

## Checklist operacional
- usar `php scripts/test-wsl-stack.php` como health check principal
- validar sintaxe e testes no proprio ambiente Linux
- evitar documentar ou depender de binarios locais fora do fluxo Ubuntu
- manter o projeto portavel entre WSL e servidor Linux

## Criterio de alinhamento
Consideramos o projeto alinhado ao ambiente oficial quando:
- toda orientacao operacional principal estiver descrita para Ubuntu e WSL
- a aplicacao subir localmente por `php -S ... -t public`
- o bootstrap e os testes integrados passarem em Linux
- nao houver orientacao ativa para execucao fora do fluxo oficial Linux
