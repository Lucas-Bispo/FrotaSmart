# Estado atual do projeto

## Data de referencia
2026-03-22

## Leitura rapida
- O FrotaSmart hoje esta organizado em `backend/` e `frontend/`
- A base atual segue um MVC simples
- Ja existe autenticacao, dashboard e CRUD basico de veiculos
- A arquitetura alvo oficial e Clean Architecture adaptada em `src/`

## Achados tecnicos
- `backend/models/VeiculoModel.php` usa `global $pdo`
- `backend/controllers/VeiculoController.php` mistura validacao, autorizacao e fluxo HTTP
- `backend/config/db.php` centraliza conexao e leitura do `.env`
- Ainda nao existe `src/`, `public/` ou `composer.json`
- O ambiente desta sessao nao encontrou `php` nem `composer` no PATH

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
- Sem Composer no ambiente, nao da para gerar `vendor/` nesta sessao
- Sem PHP no PATH, nao da para rodar o teste de autoload nesta sessao
- O arquivo de arquitetura fala em `public/`, mas o projeto ainda nao foi movido para front controller unico

## Decisao atual
- Executar `task01` parcialmente ate o ponto maximo possivel no ambiente atual
- Registrar pendencias operacionais para fechamento total da task
