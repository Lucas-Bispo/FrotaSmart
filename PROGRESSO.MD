# Progresso - FrotaSmart

## 2026-03-22

### Task 01 - Fundacao da arquitetura
- Criado `composer.json` na raiz do projeto
- Definido autoload PSR-4 `FrotaSmart\\` -> `src/`
- Criada a estrutura base de camadas em `src/`
- Criada a classe inicial `FrotaSmart\Domain\Entities\Veiculo`
- Criado `scripts/test-autoload.php` para validacao futura
- Atualizado o backlog em `AI/Tasks/tasks.md`
- Registrado o diagnostico arquitetural em `AI/Contexto/Estado-Projeto.md`

### Estado atual
- A base para migracao para Clean Architecture foi iniciada
- O sistema legado em `backend/` e `frontend/` foi preservado
- Ainda nao foi feita a migracao dos controllers e models antigos

### Bloqueios
- `php` e `composer` nao estavam no PATH no inicio da sessao
- O projeto foi validado usando PHP local e `composer.phar` local

### Proximo passo recomendado
- Concluir a `Task 02`: enriquecer `Veiculo` como entidade de dominio e criar `Placa` como Value Object

## 2026-03-22 - Task 02

### Dominio inicial de veiculos
- Criado o Value Object [Placa](./src/Domain/ValueObjects/Placa.php)
- Enriquecida a entidade [Veiculo](./src/Domain/Entities/Veiculo.php)
- Criadas exceptions de dominio para falhas de placa e status
- Registrada a task em [task02.md](./AI/Tasks/task02.md)
- Atualizado o roadmap em [tasks.md](./AI/Tasks/tasks.md)
- Criado o teste de dominio em [test-domain.php](./scripts/test-domain.php)

### Regras agora no dominio
- validacao de placa no proprio dominio
- normalizacao de status legados para status oficiais
- transicoes basicas de estado do veiculo

### Proximo passo recomendado
- Iniciar a `Task 03`: criar o contrato de repositorio para o modulo de veiculos

## 2026-03-22 - Task 03

### Contrato de repositorio de veiculos
- Criado o contrato [VeiculoRepositoryInterface.php](./src/Domain/Repositories/VeiculoRepositoryInterface.php)
- Definidas operacoes de persistencia, consulta, existencia, listagem e remocao sem acoplamento com `PDO`
- Adotada [Placa](./src/Domain/ValueObjects/Placa.php) como identidade de consulta do dominio nesta etapa
- Registrada a task em [task03.md](./AI/Tasks/task03.md)
- Criado o teste de validacao do contrato em [test-repository-contract.php](./scripts/test-repository-contract.php)

### Proximo passo recomendado
- Iniciar a `Task 04`: implementar o repositorio concreto em `src/Infrastructure/Persistence`

## 2026-03-22 - Revisao estrutural do projeto

### Validacao operacional da arquitetura
- Composer local baixado como ferramenta de trabalho e ignorado no Git
- `composer dump-autoload` executado com sucesso
- Teste real de autoload validado com PHP local
- `composer.json` alinhado ao PHP real da maquina com `php:^8.2`

### Limpeza arquitetural
- Removidos scripts CLI de `backend/config`
- Criados scripts operacionais em [bootstrap-db.php](./scripts/bootstrap-db.php) e [reset-password.php](./scripts/reset-password.php)
- Normalizado o nome da regra geral do Codex para [Codex-Regras-Gerais.md](./AI/Contexto/Codex-Regras-Gerais.md)

### Seguranca e higiene do repositorio
- Removidos caminhos absolutos com dados locais da documentacao principal
- `.gitignore` reforcado com chaves, arquivos temporarios e configuracoes locais de IDE

### Alertas encontrados na revisao
- o legado ainda usa `global $pdo`
- o CRUD de veiculos ainda faz exclusao fisica
- ainda nao existe `public/index.php` como front controller unico

### Proximo passo recomendado
- Criar a `Task 03` e iniciar o contrato de repositorio para reduzir o acoplamento do legado
