# Auditoria tecnica e de seguranca do projeto FrotaSmart

## Navegacao rapida
- Guia Linux: [README_LINUX.md](../README_LINUX.md)
- Hardening Apache: [APACHE_HARDENING.md](./APACHE_HARDENING.md)
- Arquitetura: [Arquitetura-Projeto.md](../AI/Contexto/Arquitetura-Projeto.md)
- Roadmap: [tasks.md](../AI/Tasks/tasks.md)

## Escopo avaliado
- superficie de ataque web
- autenticacao e sessao
- controles de autorizacao
- exposicao indevida de arquivos no Apache
- scripts administrativos
- organizacao arquitetural e backlog de engenharia

## Resumo executivo
O projeto possui uma base promissora, com `public/`, `backend/`, `frontend/` e `src/`, mas ainda depende de endurecimento continuo em seguranca operacional e migracao arquitetural.

## Backlog priorizado
1. Implementar persistencia concreta alinhada ao dominio
2. Adicionar auditoria persistente
3. Remover dependencias front-end via CDN em producao
4. Criar migrations versionadas
5. Criar testes automatizados de seguranca e regressao
