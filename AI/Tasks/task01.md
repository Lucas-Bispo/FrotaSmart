# Task 01 - Fundacao da arquitetura com Composer e PSR-4

## Status
- Estado atual: concluida
- Viabilidade: concluida com ajustes operacionais locais
- Ambiente validado com `C:\xampp\php\php.exe` + `composer.phar` local

## Objetivo real desta task
Criar a base tecnica para a migracao do projeto atual para a arquitetura oficial do FrotaSmart, sem quebrar o sistema existente.

Isso significa:
- criar `src/` com as camadas definidas na arquitetura
- criar `composer.json` com autoload PSR-4
- adicionar um exemplo minimo de classe namespaced
- preparar o projeto para o Composer
- registrar no cerebro do projeto o que foi feito, o que ficou pendente e o proximo passo

## Diagnostico do projeto antes da execucao
- O projeto atual usa `backend/` e `frontend/`
- O backend ainda depende de `require_once` e `global $pdo`
- Ainda nao existe `src/`
- Ainda nao existe `composer.json`
- Ainda nao existe `vendor/`
- A `task01` original estava escrita como guia didatico, nao como atividade executavel

## Decisao de engenharia
Vamos executar a `task01` como uma fundacao incremental.

Nao vamos refatorar todo o sistema legado nesta etapa.
Vamos:
1. criar a nova estrutura
2. configurar o padrao moderno
3. manter a aplicacao atual funcionando como esta
4. usar as proximas tasks para migrar modulo por modulo

## Checklist executavel
- [x] Validar arquitetura oficial em `AI/Contexto`
- [x] Inspecionar o estado real do projeto
- [x] Confirmar viabilidade tecnica da task
- [x] Criar `composer.json`
- [x] Criar estrutura `src/` inicial
- [x] Criar classe de exemplo com namespace `FrotaSmart\\...`
- [x] Registrar andamento em `AI/Tasks/tasks.md`
- [x] Atualizar `PROGRESSO.MD`
- [x] Rodar `composer dump-autoload`
- [x] Validar autoload com execucao real via PHP

## Entregaveis esperados desta task
- `composer.json`
- estrutura base em `src/`
- classe inicial `FrotaSmart\Domain\Entities\Veiculo`
- script simples para validar autoload quando Composer estiver disponivel
- roadmap atualizado

## Como validar quando o ambiente estiver pronto
Comandos esperados:

```powershell
& 'C:\xampp\php\php.exe' .\composer.phar --version
& 'C:\xampp\php\php.exe' .\composer.phar dump-autoload
& 'C:\xampp\php\php.exe' .\scripts\test-autoload.php
```

Resultado esperado:
- o Composer gera a pasta `vendor/`
- o script imprime a descricao do veiculo sem `require_once` manual da classe

## Criterio de conclusao
Esta task sera considerada 100% concluida quando:
- o autoload for gerado
- o teste de autoload rodar com sucesso
- a configuracao ficar alinhada com o PHP real da maquina

## Resultado da validacao
- `composer dump-autoload` executado com sucesso
- `scripts/test-autoload.php` validado com sucesso
- requisito de PHP ajustado de `^8.3` para `^8.2` para refletir o ambiente real disponivel

## Proxima task sugerida
Task 02 - transformar `Veiculo` em entidade rica de dominio e criar o Value Object `Placa`

## Continuidade do trabalho
- Task seguinte executada em: [task02.md](./task02.md)
