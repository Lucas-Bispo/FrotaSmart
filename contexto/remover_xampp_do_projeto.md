# Remover XAMPP do Projeto

## Objetivo
Eliminar o XAMPP como dependencia operacional do FrotaSmart e manter o projeto alinhado ao ambiente oficial definido hoje: Ubuntu WSL com `public/` como document root.

## Estado atual
- o ambiente oficial do projeto ja e o Ubuntu WSL
- o servidor local recomendado ja pode subir com `php -S 127.0.0.1:8000 -t public`
- o banco ja esta sendo validado no Linux via `php scripts/test-wsl-stack.php`
- ainda existem referencias residuais ao XAMPP em documentacao de validacao historica

## O que remover do fluxo do projeto
- nao usar mais `C:\xampp\php\php.exe` como binario padrao de validacao
- nao depender do Apache e do MySQL do XAMPP para desenvolvimento local
- nao documentar novos passos baseados em `htdocs`, painel do XAMPP ou PATH do Windows
- nao registrar novas validacoes usando o PHP do XAMPP como referencia principal

## Fluxo oficial substituto
1. Entrar no projeto pelo WSL:
```bash
cd /mnt/c/Users/lukao/Documents/FrotaSmart
```

2. Validar a stack oficial:
```bash
php scripts/test-wsl-stack.php
```

3. Subir o servidor local:
```bash
php -S 127.0.0.1:8000 -t public
```

4. Acessar no navegador:
```text
http://127.0.0.1:8000/login.php
```

## Checklist de remocao no repositorio
- revisar `.md`, `.php` e `.json` procurando por `xampp`, `C:\xampp` e `php.exe`
- substituir exemplos antigos por comandos em WSL
- manter referencias antigas apenas quando forem historicas e claramente marcadas como legado
- atualizar progresso e contexto sempre que uma validacao deixar de usar o PHP do Windows

## Pontos residuais conhecidos nesta data
- `contexto/progresso.md` ainda guarda validacoes historicas usando `C:\xampp\php\php.exe`

## Comando util para localizar referencias
```powershell
rg -n "xampp|XAMPP|C:\\xampp|php.exe" . -g "*.md" -g "*.php" -g "*.json"
```

## Remocao da instalacao local do Windows
Essa parte nao e obrigatoria para o projeto funcionar. Se voce quiser remover o XAMPP da maquina tambem:

1. confirmar que o projeto sobe e valida no WSL
2. confirmar que nenhum outro projeto da maquina depende do XAMPP
3. parar Apache e MySQL do XAMPP
4. desinstalar o XAMPP pelo Windows
5. remover entradas antigas de PATH, alias ou atalhos se existirem

## Criterio de concluido
Consideramos o XAMPP removido do projeto quando:
- toda validacao operacional principal estiver descrita em WSL
- o localhost do projeto subir via `php -S` no Ubuntu
- nao houver mais orientacao ativa mandando usar `C:\xampp\php\php.exe`
- referencias antigas restantes estiverem apenas como registro historico
