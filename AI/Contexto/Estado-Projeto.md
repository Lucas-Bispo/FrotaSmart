# Estado atual do projeto

## Data de referencia
2026-03-22

## Leitura rapida
- O FrotaSmart hoje esta organizado em `backend/` e `frontend/`
- A base atual segue um MVC simples
- Ja existe autenticacao, dashboard e CRUD basico de veiculos
- A arquitetura alvo oficial e Clean Architecture adaptada em `src/`
- O dominio novo de veiculos ja possui `Veiculo` e `Placa` em `src/Domain`

## Achados tecnicos
- `backend/models/VeiculoModel.php` usa `global $pdo`
- `backend/controllers/VeiculoController.php` mistura validacao, autorizacao e fluxo HTTP
- `backend/config/db.php` centraliza conexao e leitura do `.env`
- Ja existem `src/` e `composer.json`
- Ainda nao existe `public/` como front controller unico
- O ambiente possui PHP em `C:\xampp\php\php.exe`
- O Composer foi baixado localmente como `composer.phar`

## Conclusao de viabilidade
A `task01` e viavel e deve ser feita.

Mas ela deve ser entendida como:
- fundacao arquitetural
- nao como refactor total do projeto

## Estrategia recomendada
- Criar a nova base em paralelo ao legado
- Migrar modulo por modulo
- Comecar por veiculos, porque ja existe fluxo funcional e ele e o centro do dominio

## Riscos atuais
- O arquivo de arquitetura fala em `public/`, mas o projeto ainda nao foi movido para front controller unico
- O legado ainda depende de `global $pdo` e `require_once`
- O CRUD legado de veiculos ainda faz `DELETE` fisico, enquanto a regra de negocio pede soft delete

## Decisao atual
- Concluir `task01` com validacao real de autoload usando PHP local + Composer local
- Manter `composer.phar` apenas como ferramenta local, fora do versionamento
- Evoluir o modulo de veiculos por migracao incremental a partir do dominio novo
- Avancar para contratos de repositorio antes de adaptar controllers legados
- Remover scripts operacionais de `backend/config` e concentrar operacoes CLI em `scripts/`
