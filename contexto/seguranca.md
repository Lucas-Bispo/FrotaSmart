# Contexto de seguranca

## Navegacao rapida
- Guia Linux: [readme_linux.md](./readme_linux.md)
- Revisao de seguranca: [security_review.md](./docs/security_review.md)
- Hardening Apache: [apache_hardening.md](./docs/apache_hardening.md)

## Diretrizes principais
- proibido hardcoding de caminhos absolutos ou nomes locais de usuario
- use variaveis de ambiente para credenciais e caminhos sensiveis
- revise commits em busca de PII, segredos e caminhos locais
- verifique se novos arquivos sensiveis devem entrar no `.gitignore`
- priorize auditoria de seguranca antes de processar a logica funcional

## Ferramentas e reforcos recomendados
- `.gitignore` estrategico para `.env`, chaves e temporarios
- `pre-commit hook` para bloquear caminhos locais de Windows/WSL
- varredura de historico com ferramentas como TruffleHog ou Gitleaks

