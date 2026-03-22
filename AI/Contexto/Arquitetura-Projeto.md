**Aqui está o texto completo, sólido e profissional** que você pode copiar e colar **direto** no arquivo `AI/Context/Arquitetura-Projeto.md` (substitua todo o conteúdo antigo).

```markdown
# Arquitetura FrotaSmart — Padrão Oficial do Projeto

## Visão Geral
O FrotaSmart é um sistema de gerenciamento de frota (veículos, ônibus, carros, manutenção, abastecimento, motoristas, secretarias e relatórios) desenvolvido **exclusivamente em PHP puro + MySQL** para prefeituras brasileiras.  

Para garantir **manutenibilidade, testabilidade, escalabilidade e conformidade com LGPD**, adotamos a **Clean Architecture** (adaptada para PHP sem framework pesado). Essa é a arquitetura mais moderna e recomendada em 2026 para sistemas públicos administrativos.

O padrão é obrigatório para todo o código gerado ou revisado pelo **Códex**. Qualquer nova funcionalidade (ex: módulo de viagens, relatórios, API futura) deve respeitar rigorosamente essas camadas.

## Princípios Fundamentais (Clean Architecture — Uncle Bob)

1. **Independência de frameworks** — o core do negócio não depende de PDO, Apache, Tailwind ou qualquer ferramenta externa.
2. **Independência de banco de dados** — troca de MySQL por PostgreSQL ou SQLite deve ser possível sem alterar regras de negócio.
3. **Independência da interface** — o sistema pode virar API REST ou app mobile sem mexer no domínio.
4. **Independência de UI** — views e controllers são apenas “entregadores” de informação.
5. **Regra de dependência** — as camadas externas podem depender das internas, mas **nunca o contrário**.

**Fonte principal**: Robert C. Martin (“Uncle Bob”) — livro *Clean Architecture: A Craftsman’s Guide to Software Structure and Design* (2017) e artigo original de 2012.

## Camadas da Arquitetura (concentricas — do centro para fora)

### 1. Domain (Centro — o mais sagrado)
- Entidades ricas (Veiculo, Motorista, Viagem, Manutencao)
- Value Objects (Placa, Cnh, Quilometragem)
- Repositórios (apenas interfaces — VeiculoRepositoryInterface)
- Exceptions de domínio
- Regras de negócio puras (ex: placa deve ser válida, CNH não pode estar vencida)

**Nunca** sabe de banco, HTTP, sessão ou HTML.

### 2. Application (Use Cases + Services)
- Services (VeiculoService, ManutencaoService, RelatorioService)
- Use Cases (CreateVeiculoUseCase, GerarRelatorioCustoUseCase)
- Orquestra regras de negócio + chama repositórios
- Aqui fica toda a lógica de aplicação (validações, cálculos de consumo, permissões por role)

### 3. Infrastructure (Detalhes técnicos)
- Persistence (PdoVeiculoRepository.php — implementação concreta do PDO + MySQL)
- Config (db.php com PDO factory, security.php com CSRF e sessões)
- Auditoria (logs de quem alterou, updated_at, created_by)

### 4. Presentation (Camada externa)
- Controllers (fios finos — recebem request, chamam Service, devolvem view)
- Views (frontend/views + includes)
- public/index.php (único ponto de entrada web)

**Fluxo correto**: Presentation → Application → Domain  
**Infrastructure** é injetada via construtor (Dependency Injection).

## Estrutura de Pastas Oficial (obrigatória)

```
FrotaSmart/
├── src/                          ← Núcleo principal
│   ├── Domain/
│   │   ├── Entities/
│   │   ├── ValueObjects/
│   │   ├── Repositories/     ← apenas interfaces
│   │   └── Exceptions/
│   ├── Application/
│   │   └── Services/
│   ├── Infrastructure/
│   │   ├── Persistence/
│   │   └── Config/
│   └── Presentation/
│       ├── Controllers/
│       └── Views/
├── public/                       ← Ponto de entrada web (Apache aponta aqui)
│   └── index.php
├── frontend/                     ← Assets estáticos + includes (header, sidebar)
├── .env
├── composer.json                 ← PSR-4 autoload (FrotaSmart\ → src/)
└── PROGRESSO.MD
```

## Regras que o Códex deve seguir sempre

- Todo novo módulo começa pelo **Domain** (entidade + interface).
- Nunca use `global $pdo` ou `require_once` direto em controllers.
- Sempre injete dependências via construtor.
- Auditoria obrigatória em toda alteração (created_by, updated_at, ip, role).
- Placa deve ser UNIQUE + Value Object com validação.
- Conformidade LGPD: dados pessoais isolados, consentimento, direito ao esquecimento.
- Testabilidade: Domain e Application devem ser 100% testáveis sem banco ou web.

## Fontes e Referências (pesquisa 2026)

- Robert C. Martin – Clean Architecture (livro e artigo original)
- GitHub gushakov/clean-php – Exemplo real de Clean Architecture em PHP puro sem framework
- GitHub rudirocha/php-clean-architecture – Boilerplate opinado de Clean Arch em PHP
- Artigo Dmitriy Lezhnev – “Clean architecture implemented as a PHP app” (2025)
- Medium UNIL – “Clean Architecture with PHP” (exemplo completo)
- Repositórios brasileiros de frota: marcosggoncalves/gestao-frota-v1 e roldaojr/transporte.php (usados como base procedural que estamos evoluindo para Clean)
- LGPD Framework (SBC/ANPD) – requisitos de auditoria e governança para sistemas públicos

**Última atualização**: 22 de março de 2026 — por Códex  
**Versão**: 1.0 — Padrão oficial do FrotaSmart
