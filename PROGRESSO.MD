Como engenheiro de software, analisei a estrutura atual do **FrotaSmart**. Você já estabeleceu uma base sólida seguindo o padrão **MVC (Model-View-Controller)**, com uma clara separação de responsabilidades e preocupação com segurança (hash de senhas e proteção de variáveis de ambiente).

Aqui está o mapa completo do que foi realizado até agora, estruturado para o seu arquivo `PROGRESSO.md`.

---

# 🗺️ Mapa de Engenharia de Software - FrotaSmart

## 1. Arquitetura e Padrões de Projeto

* **Padrão MVC:** O sistema está dividido em `Models` (lógica de dados), `Views` (interface Tailwind CSS) e `Controllers` (fluxo de aplicação).
* **Conexão Robusta:** Centralização da conexão PDO em `db.php` com tratamento de erros específico para ambientes como WSL (uso de 127.0.0.1) e criação automática do banco de dados caso não exista.
* **Gestão de Configurações:** Implementação de um carregador manual de arquivos `.env` para evitar a exposição de credenciais sensíveis no código.

## 2. Camada de Dados (Backend - Models)

Foram implementadas as entidades fundamentais do sistema:

* **UserModel:** Gerencia autenticação via `password_verify` e registro de usuários com `password_hash`.
* **VeiculoModel:** Operações completas de CRUD (Create, Read, Update, Delete) para a frota.
* **ManutencaoModel:** Estrutura inicial para registro de ordens de serviço e histórico por veículo.

## 3. Lógica de Controle (Backend - Controllers)

* **AuthController:** Gerenciamento de sessões (`session_start`), login e logout.
* **VeiculoController:** Intermediário que valida inputs (sanitização de strings e inteiros) antes de persistir dados no modelo de veículos.
* **UserController:** Implementação inicial de **RBAC (Role-Based Access Control)**, restringindo a criação de novos usuários apenas para quem possui o papel de `admin`.

## 4. Interface do Usuário (Frontend)

* **Design Responsivo:** Utilização do framework **Tailwind CSS** para uma interface moderna e adaptável.
* **Modularização:** Uso de `header.php` e `sidebar.php` via `require_once` para manter o código DRY (*Don't Repeat Yourself*).
* **Dashboard Operacional:** Visualização resumida com contadores de veículos (Total, Em Operação, Manutenção) e listagem dinâmica.

## 5. DevOps e Utilitários

* **Segurança de Repositório:** Arquivo `.gitignore` configurado para não subir logs e o arquivo `.env` real.
* **Scripts de Inicialização:**
* `seed_admin.php`: Criação automática das tabelas (`users`, `veiculos`, `manutencoes`) e do primeiro administrador.
* `reset_password.php`: Utilitário de emergência para recuperação de acesso ao usuário `admin_frota`.



---

### 📊 Status Atual do Banco de Dados

| Tabela | Responsabilidade | Status |
| --- | --- | --- |
| **users** | Credenciais, Níveis de Acesso (Admin, Gerente, Motorista) | ✅ Completo |
| **veiculos** | Dados da frota (Placa, Modelo, Status) | ✅ Completo |
| **manutencoes** | Registro de custos e tipos de conserto | ⚠️ Básico |

### 🚀 Próximas Etapas Recomendadas

1. **Módulo de Secretarias:** Criar tabela para vincular veículos a departamentos específicos da prefeitura.
2. **Módulo de Viagens:** Implementar a tabela de `viagens` (saída/retorno) para controle de quilometragem.
3. **Refatoração de Segurança:** Mudar de `filter_input(..., FILTER_SANITIZE_STRING)` (depreciado no PHP 8.1+) para métodos mais modernos ou prepared statements puros em todos os controllers.

**Deseja que eu ajude a detalhar a estrutura da Tabela de Viagens para o seu próximo passo?**