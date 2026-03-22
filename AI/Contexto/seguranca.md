1. Contexto de Segurança para a IA (System Prompt)
Copie e cole este bloco nas configurações de "instruções personalizadas" ou "system role" da sua IA:

Diretriz de Segurança e Privacidade (SysOps/DevOps Focus):

Proibição de Hardcoding: Você está terminantemente proibido de sugerir ou escrever caminhos de arquivos absolutos (ex: /mnt/c/Users/..., C:\Users\...) ou nomes de usuários locais em variáveis de sistema.

Abstração de Ambiente: Sempre utilize variáveis de ambiente (process.env, os.getenv, $_ENV) para caminhos de diretórios e credenciais.

Sanitização de Commits: Antes de sugerir qualquer comando de git commit ou bloco de código, verifique se existem informações pessoais (PII), nomes de usuários de sistema, tokens ou caminhos locais. Se encontrar, substitua por placeholders como <YOUR_PROJECT_PATH> ou <USER_NAME>.

Prevenção de Vazamento: Sempre que eu solicitar a criação de um novo arquivo, verifique se ele deve constar no .gitignore (ex: arquivos .env, .pem, .json de credenciais).

Modo Auditoria: Se eu te enviar um código, sua primeira tarefa é escanear por segredos (secrets) e caminhos locais antes de processar a lógica funcional.

2. Ferramentas de Limpeza e Busca (Automáticas)
Não dependa apenas da IA; implemente travas no próprio Git para impedir que o commit saia da sua máquina se houver lixo.

A. Uso do .gitignore Estratégico
Crie ou edite seu arquivo .gitignore na raiz do projeto:

Bash
# Ignorar arquivos de ambiente e chaves
.env
*.pem
*.key

# Ignorar configurações locais de IDE
.vscode/
.idea/

# Ignorar logs e temporários
*.log
tmp/
B. Implementação de "Pre-commit Hook"
Você pode criar um script que roda antes de cada commit para barrar caminhos do Windows/WSL.

No seu terminal Linux (WSL), dentro da pasta do projeto, rode:
nano .git/hooks/pre-commit

Cole este script (ele busca o padrão /mnt/c/Users nos arquivos que você está tentando commitar):

Bash
#!/bin/bash
# Busca por caminhos locais do Windows/WSL antes de permitir o commit
if git diff --cached | grep -E "/mnt/c/Users|C:\\\\Users" > /dev/null; then
    echo "ERRO: Caminho local detectado no código (Ex: /mnt/c/Users). Commit abortado."
    exit 1
fi
Dê permissão de execução:
chmod +x .git/hooks/pre-commit

3. Como limpar o que já foi commitado?
Se você já enviou informações pessoais para o histórico, use o TruffleHog ou o Gitleaks. Eles são o padrão ouro em DevOps para buscar "segredos" em repositórios.

Para buscar no histórico agora (via Docker):

Bash
docker run --it -v "$PWD:/pwd" trufflesecurity/trufflehog:latest github --repo https://github.com/SEU_USUARIO/SEU_REPO
Resumo da Estratégia