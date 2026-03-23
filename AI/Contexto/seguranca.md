# Contexto de Seguranca

## Navegacao rapida
- Guia Linux: [README_LINUX.md](../../README_LINUX.md)
- Revisao de seguranca: [SECURITY_REVIEW.md](../../docs/SECURITY_REVIEW.md)
- Hardening Apache: [APACHE_HARDENING.md](../../docs/APACHE_HARDENING.md)

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
