# Contexto de Transicao - Operacao Clean Linux

## Navegacao rapida
- Guia Linux: [README_LINUX.md](../../README_LINUX.md)
- Roadmap: [tasks.md](../Tasks/tasks.md)
- Task executada: [task03-1.md](../Tasks/task03-1.md)
- Arquitetura: [Arquitetura-Projeto.md](./Arquitetura-Projeto.md)

## Objetivo
Eliminar o legado de acoplamento com Windows/XAMPP e padronizar o FrotaSmart para um ambiente de desenvolvimento e deploy profissional em Linux/WSL.

## 1. Diagnostico de debito tecnico
O projeto possuia raizes no XAMPP que impediam a portabilidade e a automacao.

- Hardcoding: referencias explicitas a `C:\xampp\...` e `.exe`
- Arquitetura de entrada: ausencia inicial de `public/`
- Gestao de dependencias: `composer.lock` ausente e uso incompleto do autoload PSR-4
- Instanciacao do banco: uso de `global $pdo` em controllers e models

## 2. Diretrizes de correcao da Task 03.1
Antes da Task 04, a correcao deveria seguir estas regras:

- substituir caminhos absolutos e referencias a drivers de letra por caminhos relativos e configuracao de ambiente
- garantir compatibilidade case-sensitive para Linux
- iniciar a transicao de `global $pdo` para padroes mais isolados
- padronizar o uso de `php -S 0.0.0.0:8000 -t public`

## 3. Checklist de definicao de pronto
O projeto seria considerado Linux Ready quando:

- `grep -r "xampp" .` nao retornasse resultado nos scripts operacionais
- `.env` fosse a fonte de verdade para banco
- existisse um guia Linux com `composer install`, `php scripts/bootstrap-db.php` e `php -S`
- `composer.json` gerisse o carregamento de classes via PSR-4

## Resultado observado
- O guia [README_LINUX.md](../../README_LINUX.md) foi criado
- `public/` passou a ser o document root recomendado
- os scripts operacionais foram limpos de referencias explicitas a XAMPP
- a migracao arquitetural ainda segue pendente na camada de persistencia e aplicacao
