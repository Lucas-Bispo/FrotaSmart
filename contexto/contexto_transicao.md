# Contexto de transicao - operacao clean linux

## Navegacao rapida
- Guia Linux: [readme_linux.md](./readme_linux.md)
- Roadmap atual: [roadmap_ciclo_04.md](./ciclo_04_estabilidade_governanca/roadmap_ciclo_04.md)
- Task executada: [task_03_1_operacao_clean_linux.md](./ciclo_01_fundacao_arquitetura/task_03_1_operacao_clean_linux.md)
- Arquitetura: [arquitetura_projeto.md](./arquitetura_projeto.md)

## Objetivo
Eliminar o legado de acoplamento com Windows/XAMPP e padronizar o FrotaSmart para um ambiente de desenvolvimento e deploy profissional em Linux/WSL.

## 1. Diagnostico de debito tecnico
O projeto possuia raizes no XAMPP que impediam a portabilidade e a automacao.

- Hardcoding: referencias explicitas a `C:\xampp\...` e `.exe`
- Arquitetura de entrada: ausencia inicial de `public/`
- Gestao de dependencias: `composer.lock` ausente e uso incompleto do autoload PSR-4
- Instanciacao do banco: uso de `global $pdo` em controllers e models

## 2. Diretrizes de correcao da task_03_1
- substituir caminhos absolutos e referencias a drivers de letra por caminhos relativos e configuracao de ambiente
- garantir compatibilidade case-sensitive para Linux
- iniciar a transicao de `global $pdo` para padroes mais isolados
- padronizar o uso de `php -S 0.0.0.0:8000 -t public`

## 3. Checklist de definicao de pronto
- `grep -r "xampp" .` nao retornar resultado nos scripts operacionais
- `.env` ser a fonte de verdade para banco
- existir um guia Linux com `composer install`, `php scripts/bootstrap-db.php` e `php -S`
- `composer.json` gerir o carregamento de classes via PSR-4

## Resultado observado
- O guia [readme_linux.md](./readme_linux.md) foi criado
- `public/` passou a ser o document root recomendado
- os scripts operacionais foram limpos de referencias explicitas a XAMPP
- a migracao arquitetural ainda segue pendente na camada de persistencia e aplicacao
