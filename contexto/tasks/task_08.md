# Task 08 - RBAC alinhado com regras de negocio

## Navegacao rapida
- Roadmap: [roadmap_tasks.md](./roadmap_tasks.md)
- Progresso geral: [../progresso.md](../progresso.md)
- Regras de negocio: [../regras_negocio.md](../regras_negocio.md)
- Seguranca: [../seguranca.md](../seguranca.md)
- Task anterior: [task_07.md](./task_07.md)

## Status
- Estado atual: planejada
- Dependencia anterior: [task_07.md](./task_07.md)

## Objetivo
Alinhar os perfis e as autorizacoes atuais com os papeis oficiais do projeto, reduzindo divergencias entre o legado e as regras de negocio documentadas.

## Escopo minimo
- Revisar os perfis atuais do sistema
- Mapear permissoes de veiculos aos papeis `admin`, `gerente`, `motorista` e `auditor`
- Remover regras soltas de autorizacao espalhadas sem criterio unico
- Preparar base para reutilizacao do RBAC nos demais modulos

## Criterios de aceite
- As autorizacoes do modulo de veiculos devem refletir os perfis oficiais
- Regras de acesso nao podem ficar duplicadas de forma inconsistente
- O fluxo deve continuar protegido para operacoes mutaveis e leitura sensivel
- Deve existir validacao pratica de permissao por perfil

## Validacao pratica
```bash
php scripts/test-rbac-veiculos.php
```
