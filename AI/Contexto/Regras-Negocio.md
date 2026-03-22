# Regras de Negócio — FrotaSmart (Gestão de Frotas Públicas para Prefeituras)

## Visão Geral
O FrotaSmart é um sistema completo de gestão de frota municipal (veículos leves, ônibus, caminhões, máquinas) focado em transparência, eficiência, redução de custos e conformidade legal.  

As regras de negócio são **obrigatórias** para todo código gerado ou revisado pelo Códex. Elas seguem as melhores práticas brasileiras de administração pública (TCU, CGU, LGPD, IN SLTI/MPOG, auditorias reais de prefeituras) e sistemas modernos de frota (Cobli, Aspec Frota, MaxiFrota, Geotab).

**Objetivo final**: transformar a frota em um ativo estratégico com custo por km controlado, auditoria total e zero fraudes.

## Princípios Gerais (obrigatórios em todo o sistema)
- Transparência total (Lei de Acesso à Informação + LGPD)
- Auditoria completa (quem alterou, quando, por quê)
- Minimização de dados (LGPD)
- Controle de custos (custo por km, consumo médio, ociosidade)
- Segurança e prevenção (manutenção preventiva > corretiva)
- Conformidade LGPD (dados de motoristas são pessoais/sensíveis)
- RBAC rígido por perfil (admin, gerente, motorista, auditor)

## Módulos e Regras de Negócio Detalhadas

### 1. Veículos
- Cadastro completo obrigatório: placa (UNIQUE + Value Object com validação), RENAVAM, chassi, ano, modelo, tipo (leve/ônibus/caminhão), combustível, secretaria lotada, quilometragem inicial, data aquisição, documentos (IPVA, licenciamento, seguro, inspeção).
- Status obrigatório: Disponível, Em Manutenção, Em Viagem, Baixado, Reservado.
- Vencimentos automáticos alertados (30 dias antes).
- Soft delete + histórico de alterações (auditoria).

### 2. Motoristas
- Vínculo com usuário do sistema + CNH (número, categoria, validade, exames médicos).
- Dados pessoais tratados com LGPD: consentimento explícito, política de privacidade, direito de acesso/correção/exclusão.
- Status: Ativo, Inativo, Em Férias, Bloqueado (CNH vencida).
- Treinamento obrigatório registrado (condução econômica, segurança).

### 3. Viagens / Deslocamentos
- Autorização prévia obrigatória (requisição com origem, destino, finalidade, passageiros, km previsto).
- Registro de saída/retorno com quilometragem + diário de bordo digital (obrigatório por TCU/IN 3/2008).
- Finalidade deve ser pública (serviço, fiscalização, transporte escolar, etc.).
- Motorista responsável + aprovação da secretaria.

### 4. Abastecimento
- Apenas postos credenciados ou cartão frota.
- Limites por veículo/motorista (valor, litros, frequência).
- Cálculo automático de consumo médio (litros/km) por veículo.
- Detecção automática de fraudes (abastecimento fora de rota, volume incompatível, horário suspeito).
- Integração com manutenção (abastecimento só permitido se veículo em dia).

### 5. Manutenção
- Preventiva: agenda automática por km/horas ou tempo (óleo, filtros, pneus).
- Corretiva: ordem de serviço + aprovação + nota fiscal + garantia.
- Estoque de peças integrado + controle de custo.
- Veículo bloqueado automaticamente se manutenção pendente.

### 6. Secretarias / Departamentos
- Vínculo obrigatório de veículos e motoristas.
- Relatórios por secretaria (custo total, consumo, ociosidade).

### 7. Relatórios e Auditoria
- KPIs obrigatórios:
  - Custo por km rodado
  - Consumo médio (litros/km)
  - Taxa de disponibilidade da frota
  - % de manutenções preventivas
  - Ociosidade (veículos parados > 30 dias)
  - Custo por secretaria
- Auditoria total: log de todas as ações (created_by, updated_at, ip, role).
- Relatórios exportáveis em PDF/CSV para TCU, CGU e transparência pública.

### 8. Segurança e LGPD (crítico para prefeitura)
- RBAC: 
  - Admin → tudo
  - Gerente → cadastrar/editar veículos/viagens/manutenção
  - Motorista → ver apenas suas viagens + registrar km
  - Auditor → só relatórios
- Todos os dados pessoais (CNH, telefone, geolocalização) com:
  - Política de privacidade clara
  - Consentimento registrado
  - RIPD (Registro de Incidentes)
  - Encarregado (DPO) configurável
  - Criptografia e controle de acesso

## Regras que o Códex deve aplicar sempre
- Toda ação mutável (cadastro, alteração, exclusão) deve ter auditoria.
- Placa sempre UNIQUE + validação no Domain.
- Consumo médio calculado automaticamente em todo abastecimento.
- Veículo bloqueado se CNH vencida ou manutenção atrasada.
- Nenhum dado sensível exposto sem autenticação + RBAC.
- Relatórios sempre com filtro por data, secretaria e veículo.

## Fontes e Referências (pesquisa completa 2026)
- LGPD (Lei 13.709/2018) + Guias ANPD/TCU para gestores públicos
- IN SLTI/MPOG nº 3/2008 (cadastro, controle e diário de bordo)
- Portaria TCU e auditorias de frota pública (UFSM, prefeituras)
- Boas práticas Cobli, Aspec Frota, MaxiFrota, Geotab
- Guia de Abastecimento Veic.com.br (limites, fraudes, consumo médio)
- TCU – Auditoria LGPD e controle de frota oficial
- Normas estaduais (ex: Paraná, Rio Grande do Sul) sobre uso de veículos públicos

**Última atualização**: 22 de março de 2026 — por Códex  
**Versão**: 1.0 — Padrão oficial do FrotaSmart
