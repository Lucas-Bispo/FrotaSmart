# Apache Hardening para o FrotaSmart

## Navegacao rapida
- Guia Linux: [README_LINUX.md](../README_LINUX.md)
- Revisao de seguranca: [SECURITY_REVIEW.md](./SECURITY_REVIEW.md)
- Arquitetura: [Arquitetura-Projeto.md](../AI/Contexto/Arquitetura-Projeto.md)

## Objetivo
Padronizar a publicacao do FrotaSmart em Linux com Apache 2.4+, reduzindo risco de exposicao da aplicacao PHP, dos diretorios internos e dos arquivos sensiveis do repositorio.

## Checklist minimo
1. Configure o `DocumentRoot` para `public/`
2. Mantenha `backend/`, `src/`, `scripts/`, `AI/` e `.env` fora da area publica
3. Habilite apenas os modulos necessarios (`headers`, `rewrite`, `ssl`)
4. Desabilite listagem de diretorios (`Options -Indexes`)
5. Force HTTPS e HSTS quando houver certificado valido
6. Restrinja metodos HTTP para `GET`, `POST` e `HEAD`
7. Garanta permissoes minimas de leitura para o usuario do Apache
8. Direcione logs do Apache e do PHP para rotacao centralizada
