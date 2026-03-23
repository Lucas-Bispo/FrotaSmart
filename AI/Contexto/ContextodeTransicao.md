Contexto de Transição: "Operação Clean Linux" (Pré-Task 04)
Objetivo: Eliminar o legado de acoplamento com Windows/XAMPP e padronizar o FrotaSmart para um ambiente de desenvolvimento e deploy profissional em Linux/WSL.

1. Diagnóstico de Débito Técnico
O projeto possui raízes no XAMPP que impedem a portabilidade e a automação (SysOps/DevOps).

Hardcoding: Referências explícitas a C:\xampp\... e .exe.

Arquitetura de Entrada: Ausência de um diretório public/ (Entrypoint), expondo arquivos sensíveis na raiz.

Gestão de Dependências: composer.lock ausente e falta de uso pleno do Autoload PSR-4 em detrimento de require_once manuais.

Instanciação do Banco: O uso de global $pdo em Controllers/Models dificulta testes e isolamento de ambiente.

2. Diretrizes de Correção (Task 03.1)
Antes de avançar para a Task 04, o Codex deve seguir estas regras de implementação:

Portabilidade de Caminhos: Substituir qualquer caminho absoluto ou referência a drivers de letra (C:, D:) por caminhos relativos baseados em __DIR__ ou constantes de ambiente.

Normalização Case-Sensitive: Garantir que todos os require, include e namespaces correspondam exatamente ao nome dos arquivos no disco (essencial para Linux).

Refatoração de Conexão: Iniciar a transição do global $pdo para Injeção de Dependência ou um Singleton Pattern dentro das Classes de Model.

Ambiente de Execução: Padronizar o uso do servidor embutido do PHP (php -S 0.0.0.0:8000) em vez de depender de uma interface gráfica de controle (Control Panel do XAMPP).

3. Checklist de Definição de Pronto (DoP) para a Task 03.1
O projeto será considerado "Linux Ready" quando:

[ ] grep -r "xampp" . não retornar nenhum resultado em arquivos de script.

[ ] O arquivo .env for a única fonte de verdade para DB_HOST, DB_PORT, etc.

[ ] Existir um arquivo README_LINUX.md ou similar com o passo a passo: composer install -> php bootstrap-db.php -> php -S.

[ ] O composer.json gerir todos os carregamentos de classe via PSR-4.