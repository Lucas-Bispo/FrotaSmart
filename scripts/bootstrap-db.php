<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/config/db.php';

if (! is_cli_request()) {
    http_response_code(403);
    exit("Este script so pode ser executado via CLI.\n");
}

function table_has_column(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = :table
           AND COLUMN_NAME = :column'
    );
    $stmt->execute([
        ':table' => $table,
        ':column' => $column,
    ]);

    return (int) $stmt->fetchColumn() > 0;
}

function table_has_index(PDO $pdo, string $table, string $indexName): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = :table
           AND INDEX_NAME = :index_name'
    );
    $stmt->execute([
        ':table' => $table,
        ':index_name' => $indexName,
    ]);

    return (int) $stmt->fetchColumn() > 0;
}

/**
 * @param list<string> $statements
 */
function execute_statements(PDO $pdo, array $statements): void
{
    foreach ($statements as $statement) {
        $pdo->exec($statement);
    }
}

function ensure_column(PDO $pdo, string $table, string $column, string $definition): void
{
    if (table_has_column($pdo, $table, $column)) {
        return;
    }

    $pdo->exec(sprintf('ALTER TABLE %s ADD COLUMN %s %s', $table, $column, $definition));
}

function ensure_index(PDO $pdo, string $table, string $indexName, string $definition): void
{
    if (table_has_index($pdo, $table, $indexName)) {
        return;
    }

    $pdo->exec(sprintf('ALTER TABLE %s ADD %s', $table, $definition));
}

function bootstrap_veiculos_schema(PDO $pdo): void
{
    ensure_column($pdo, 'veiculos', 'renavam', 'VARCHAR(20) NULL AFTER modelo');
    ensure_column($pdo, 'veiculos', 'chassi', 'VARCHAR(30) NULL AFTER renavam');
    ensure_column($pdo, 'veiculos', 'ano_fabricacao', 'SMALLINT NULL AFTER chassi');
    ensure_column($pdo, 'veiculos', 'tipo', 'VARCHAR(50) NULL AFTER ano_fabricacao');
    ensure_column($pdo, 'veiculos', 'combustivel', 'VARCHAR(30) NULL AFTER tipo');
    ensure_column($pdo, 'veiculos', 'secretaria_lotada', 'VARCHAR(100) NULL AFTER combustivel');
    ensure_column($pdo, 'veiculos', 'quilometragem_inicial', 'INT NOT NULL DEFAULT 0 AFTER secretaria_lotada');
    ensure_column($pdo, 'veiculos', 'data_aquisicao', 'DATE NULL AFTER quilometragem_inicial');
    ensure_column($pdo, 'veiculos', 'documentos_observacoes', 'TEXT NULL AFTER data_aquisicao');
    ensure_column($pdo, 'veiculos', 'deleted_at', 'TIMESTAMP NULL DEFAULT NULL AFTER status');

    execute_statements($pdo, [
        "UPDATE veiculos SET quilometragem_inicial = COALESCE(quilometragem_inicial, 0)",
        "ALTER TABLE veiculos MODIFY quilometragem_inicial INT NOT NULL DEFAULT 0",
        "ALTER TABLE veiculos MODIFY status ENUM('ativo', 'manutencao', 'em_viagem', 'reservado', 'baixado') NOT NULL DEFAULT 'ativo'",
    ]);

    ensure_index($pdo, 'veiculos', 'uk_veiculos_renavam', 'UNIQUE KEY uk_veiculos_renavam (renavam)');
    ensure_index($pdo, 'veiculos', 'uk_veiculos_chassi', 'UNIQUE KEY uk_veiculos_chassi (chassi)');
}

function bootstrap_motoristas_schema(PDO $pdo): void
{
    ensure_column($pdo, 'motoristas', 'nome', 'VARCHAR(120) NULL AFTER id');
    ensure_column($pdo, 'motoristas', 'cpf', 'VARCHAR(14) NULL AFTER nome');
    ensure_column($pdo, 'motoristas', 'telefone', 'VARCHAR(20) NULL AFTER cpf');
    ensure_column($pdo, 'motoristas', 'secretaria', 'VARCHAR(100) NULL AFTER telefone');
    ensure_column($pdo, 'motoristas', 'user_id', 'INT NULL AFTER status');

    execute_statements($pdo, [
        "UPDATE motoristas SET nome = COALESCE(NULLIF(nome, ''), CONCAT('Motorista ', id))",
        "UPDATE motoristas SET cpf = COALESCE(NULLIF(cpf, ''), LPAD(id, 11, '0'))",
        "UPDATE motoristas SET secretaria = COALESCE(NULLIF(secretaria, ''), 'Secretaria nao informada')",
        "ALTER TABLE motoristas MODIFY nome VARCHAR(120) NOT NULL",
        "ALTER TABLE motoristas MODIFY cpf VARCHAR(14) NOT NULL",
        "ALTER TABLE motoristas MODIFY secretaria VARCHAR(100) NOT NULL",
        "ALTER TABLE motoristas MODIFY cnh_numero VARCHAR(20) NOT NULL",
        "ALTER TABLE motoristas MODIFY cnh_categoria VARCHAR(5) NOT NULL",
        "ALTER TABLE motoristas MODIFY cnh_vencimento DATE NOT NULL",
        "ALTER TABLE motoristas MODIFY status ENUM('ativo', 'afastado', 'ferias', 'desligado') DEFAULT 'ativo'",
        "ALTER TABLE motoristas MODIFY user_id INT NULL",
    ]);

    ensure_index($pdo, 'motoristas', 'uk_motoristas_cpf', 'UNIQUE KEY uk_motoristas_cpf (cpf)');
}

function bootstrap_manutencoes_schema(PDO $pdo): void
{
    ensure_column($pdo, 'manutencoes', 'data_abertura', 'DATE NULL AFTER veiculo_id');
    ensure_column($pdo, 'manutencoes', 'data_conclusao', 'DATE NULL AFTER data_abertura');
    ensure_column($pdo, 'manutencoes', 'status', 'VARCHAR(20) NULL AFTER tipo');
    ensure_column($pdo, 'manutencoes', 'fornecedor', 'VARCHAR(120) NULL AFTER status');
    ensure_column($pdo, 'manutencoes', 'parceiro_id', 'INT NULL AFTER fornecedor');
    ensure_column($pdo, 'manutencoes', 'custo_estimado', 'DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER parceiro_id');
    ensure_column($pdo, 'manutencoes', 'custo_final', 'DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER custo_estimado');
    ensure_column($pdo, 'manutencoes', 'observacoes', 'TEXT NULL AFTER descricao');
    ensure_column($pdo, 'manutencoes', 'km_referencia', 'INT NULL AFTER data_conclusao');
    ensure_column($pdo, 'manutencoes', 'km_proxima_preventiva', 'INT NULL AFTER km_referencia');
    ensure_column($pdo, 'manutencoes', 'data_proxima_preventiva', 'DATE NULL AFTER km_proxima_preventiva');
    ensure_column($pdo, 'manutencoes', 'recorrencia_dias', 'INT NULL AFTER data_proxima_preventiva');
    ensure_column($pdo, 'manutencoes', 'recorrencia_km', 'INT NULL AFTER recorrencia_dias');

    if (table_has_column($pdo, 'manutencoes', 'data')) {
        $pdo->exec("UPDATE manutencoes SET data_abertura = COALESCE(data_abertura, data)");
    }

    if (table_has_column($pdo, 'manutencoes', 'custo')) {
        $pdo->exec("UPDATE manutencoes SET custo_estimado = COALESCE(custo_estimado, 0.00), custo_final = CASE WHEN custo_final = 0.00 THEN custo ELSE custo_final END");
    }

    execute_statements($pdo, [
        "UPDATE manutencoes SET data_abertura = COALESCE(data_abertura, CURRENT_DATE())",
        "UPDATE manutencoes SET status = COALESCE(NULLIF(status, ''), 'aberta')",
        "ALTER TABLE manutencoes MODIFY data_abertura DATE NOT NULL",
        "ALTER TABLE manutencoes MODIFY status ENUM('aberta', 'em_andamento', 'concluida', 'cancelada') NOT NULL DEFAULT 'aberta'",
    ]);
}

function bootstrap_abastecimentos_schema(PDO $pdo): void
{
    ensure_column($pdo, 'abastecimentos', 'posto', 'VARCHAR(120) NULL AFTER data_abastecimento');
    ensure_column($pdo, 'abastecimentos', 'parceiro_id', 'INT NULL AFTER motorista_id');
    ensure_column($pdo, 'abastecimentos', 'tipo_combustivel', 'VARCHAR(20) NULL AFTER posto');
    ensure_column($pdo, 'abastecimentos', 'litros', 'DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER tipo_combustivel');
    ensure_column($pdo, 'abastecimentos', 'valor_total', 'DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER litros');
    ensure_column($pdo, 'abastecimentos', 'km_atual', 'INT NOT NULL DEFAULT 0 AFTER valor_total');
    ensure_column($pdo, 'abastecimentos', 'observacoes', 'TEXT NULL AFTER km_atual');

    execute_statements($pdo, [
        "UPDATE abastecimentos SET posto = COALESCE(NULLIF(posto, ''), 'Posto nao informado')",
        "UPDATE abastecimentos SET tipo_combustivel = COALESCE(NULLIF(tipo_combustivel, ''), 'gasolina')",
        "ALTER TABLE abastecimentos MODIFY posto VARCHAR(120) NOT NULL",
        "ALTER TABLE abastecimentos MODIFY tipo_combustivel ENUM('gasolina', 'etanol', 'diesel', 'diesel_s10', 'gnv', 'flex') NOT NULL DEFAULT 'gasolina'",
    ]);
}

function bootstrap_viagens_schema(PDO $pdo): void
{
    ensure_column($pdo, 'viagens', 'secretaria', 'VARCHAR(100) NULL AFTER secretaria_id');
    ensure_column($pdo, 'viagens', 'solicitante', 'VARCHAR(120) NULL AFTER secretaria');
    ensure_column($pdo, 'viagens', 'origem', 'VARCHAR(120) NULL AFTER solicitante');
    ensure_column($pdo, 'viagens', 'finalidade', 'TEXT NULL AFTER destino');
    ensure_column($pdo, 'viagens', 'observacoes', 'TEXT NULL AFTER status');

    execute_statements($pdo, [
        "UPDATE viagens v LEFT JOIN secretarias s ON s.id = v.secretaria_id SET v.secretaria = COALESCE(NULLIF(v.secretaria, ''), s.nome, 'Secretaria nao informada')",
        "UPDATE viagens SET solicitante = COALESCE(NULLIF(solicitante, ''), 'Solicitante nao informado')",
        "UPDATE viagens SET origem = COALESCE(NULLIF(origem, ''), 'Origem nao informada')",
        "UPDATE viagens SET finalidade = COALESCE(NULLIF(finalidade, ''), 'Finalidade nao informada')",
        "ALTER TABLE viagens MODIFY secretaria_id INT NULL",
        "ALTER TABLE viagens MODIFY secretaria VARCHAR(100) NOT NULL",
        "ALTER TABLE viagens MODIFY solicitante VARCHAR(120) NOT NULL",
        "ALTER TABLE viagens MODIFY origem VARCHAR(120) NOT NULL",
        "ALTER TABLE viagens MODIFY destino VARCHAR(160) NOT NULL",
        "ALTER TABLE viagens MODIFY data_saida DATETIME NOT NULL",
        "ALTER TABLE viagens MODIFY status ENUM('em_curso', 'concluida', 'cancelada') NOT NULL DEFAULT 'em_curso'",
    ]);
}

function bootstrap_audit_logs_schema(PDO $pdo): void
{
    ensure_column($pdo, 'audit_logs', 'actor_role', 'VARCHAR(50) NULL AFTER actor');
    ensure_column($pdo, 'audit_logs', 'context_json', 'LONGTEXT NULL AFTER occurred_at');

    ensure_index($pdo, 'audit_logs', 'idx_audit_logs_occurred_at', 'INDEX idx_audit_logs_occurred_at (occurred_at)');
    ensure_index($pdo, 'audit_logs', 'idx_audit_logs_event', 'INDEX idx_audit_logs_event (event)');
    ensure_index($pdo, 'audit_logs', 'idx_audit_logs_action', 'INDEX idx_audit_logs_action (action)');
    ensure_index($pdo, 'audit_logs', 'idx_audit_logs_actor', 'INDEX idx_audit_logs_actor (actor)');
    ensure_index($pdo, 'audit_logs', 'idx_audit_logs_target', 'INDEX idx_audit_logs_target (target_type, target_id)');
}

function ensure_admin_user(PDO $pdo, string $adminUsername, string $adminPassword): void
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username');
    $stmt->execute([':username' => $adminUsername]);

    if ((int) $stmt->fetchColumn() === 0) {
        $hash = password_hash($adminPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
        $stmt->execute([$adminUsername, $hash, 'admin']);
        echo "Bootstrap concluido. Usuario administrador criado.\n";

        return;
    }

    echo "Bootstrap concluido. Usuario administrador ja existe.\n";
}

$adminUsername = $_ENV['ADMIN_DEFAULT_USER'] ?? 'admin_frota';
$adminPassword = $_ENV['ADMIN_DEFAULT_PASS'] ?? '';

if ($adminPassword === '') {
    exit("Defina ADMIN_DEFAULT_PASS no .env antes de executar o bootstrap.\n");
}

echo "Iniciando bootstrap do banco de dados...\n";

$statements = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'gerente', 'motorista', 'auditor') NOT NULL DEFAULT 'gerente',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS veiculos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        placa VARCHAR(20) NOT NULL,
        modelo VARCHAR(100) NOT NULL,
        renavam VARCHAR(20) DEFAULT NULL,
        chassi VARCHAR(30) DEFAULT NULL,
        ano_fabricacao SMALLINT DEFAULT NULL,
        tipo VARCHAR(50) DEFAULT NULL,
        combustivel VARCHAR(30) DEFAULT NULL,
        secretaria_lotada VARCHAR(100) DEFAULT NULL,
        quilometragem_inicial INT NOT NULL DEFAULT 0,
        data_aquisicao DATE DEFAULT NULL,
        documentos_observacoes TEXT DEFAULT NULL,
        status ENUM('ativo', 'manutencao', 'em_viagem', 'reservado', 'baixado') NOT NULL DEFAULT 'ativo',
        deleted_at TIMESTAMP NULL DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uk_veiculos_placa (placa),
        UNIQUE KEY uk_veiculos_renavam (renavam),
        UNIQUE KEY uk_veiculos_chassi (chassi)
    )",
    "CREATE TABLE IF NOT EXISTS manutencoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        veiculo_id INT NOT NULL,
        data_abertura DATE NOT NULL,
        data_conclusao DATE NULL,
        tipo VARCHAR(50) NOT NULL,
        status ENUM('aberta', 'em_andamento', 'concluida', 'cancelada') NOT NULL DEFAULT 'aberta',
        fornecedor VARCHAR(120) DEFAULT NULL,
        custo_estimado DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        custo_final DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        descricao TEXT,
        observacoes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (veiculo_id) REFERENCES veiculos(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS secretarias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL UNIQUE,
        responsavel VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS motoristas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(120) NOT NULL,
        cpf VARCHAR(14) NOT NULL UNIQUE,
        telefone VARCHAR(20) DEFAULT NULL,
        secretaria VARCHAR(100) NOT NULL,
        cnh_numero VARCHAR(20) NOT NULL UNIQUE,
        cnh_categoria VARCHAR(5) NOT NULL,
        cnh_vencimento DATE NOT NULL,
        status ENUM('ativo', 'afastado', 'ferias', 'desligado') DEFAULT 'ativo',
        user_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uk_motoristas_cpf (cpf),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )",
    "CREATE TABLE IF NOT EXISTS abastecimentos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        veiculo_id INT NOT NULL,
        motorista_id INT NOT NULL,
        parceiro_id INT NULL,
        data_abastecimento DATE NOT NULL,
        posto VARCHAR(120) NOT NULL,
        tipo_combustivel ENUM('gasolina', 'etanol', 'diesel', 'diesel_s10', 'gnv', 'flex') NOT NULL DEFAULT 'gasolina',
        litros DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        valor_total DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        km_atual INT NOT NULL DEFAULT 0,
        observacoes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (veiculo_id) REFERENCES veiculos(id) ON DELETE CASCADE,
        FOREIGN KEY (motorista_id) REFERENCES motoristas(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS parceiros_operacionais (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome_fantasia VARCHAR(120) NOT NULL,
        razao_social VARCHAR(160) NOT NULL,
        cnpj VARCHAR(14) NOT NULL UNIQUE,
        tipo ENUM('oficina', 'posto_combustivel', 'fornecedor_pecas', 'prestador_servico') NOT NULL,
        telefone VARCHAR(20) DEFAULT NULL,
        endereco VARCHAR(180) DEFAULT NULL,
        contato_responsavel VARCHAR(120) DEFAULT NULL,
        status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
        observacoes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS viagens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        veiculo_id INT NOT NULL,
        motorista_id INT NOT NULL,
        secretaria_id INT NULL,
        secretaria VARCHAR(100) NOT NULL,
        solicitante VARCHAR(120) NOT NULL,
        origem VARCHAR(120) NOT NULL,
        km_saida INT NOT NULL,
        km_chegada INT,
        destino VARCHAR(160) NOT NULL,
        finalidade TEXT NOT NULL,
        data_saida DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        data_retorno DATETIME,
        status ENUM('em_curso', 'concluida', 'cancelada') DEFAULT 'em_curso',
        observacoes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (veiculo_id) REFERENCES veiculos(id),
        FOREIGN KEY (motorista_id) REFERENCES motoristas(id),
        FOREIGN KEY (secretaria_id) REFERENCES secretarias(id) ON DELETE SET NULL
    )",
    "CREATE TABLE IF NOT EXISTS audit_logs (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        event VARCHAR(120) NOT NULL,
        action VARCHAR(60) NOT NULL,
        target_type VARCHAR(80) NOT NULL,
        target_id VARCHAR(160) NOT NULL,
        actor VARCHAR(120) DEFAULT NULL,
        actor_role VARCHAR(50) DEFAULT NULL,
        ip VARCHAR(45) DEFAULT NULL,
        occurred_at DATETIME NOT NULL,
        context_json LONGTEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_audit_logs_occurred_at (occurred_at),
        INDEX idx_audit_logs_event (event),
        INDEX idx_audit_logs_action (action),
        INDEX idx_audit_logs_actor (actor),
        INDEX idx_audit_logs_target (target_type, target_id)
    )",
];

try {
    execute_statements($pdo, $statements);
    bootstrap_veiculos_schema($pdo);
    bootstrap_motoristas_schema($pdo);
    bootstrap_manutencoes_schema($pdo);
    bootstrap_abastecimentos_schema($pdo);
    bootstrap_viagens_schema($pdo);
    bootstrap_audit_logs_schema($pdo);
    ensure_admin_user($pdo, $adminUsername, $adminPassword);
} catch (PDOException $e) {
    error_log('Erro no bootstrap do banco: ' . $e->getMessage());
    exit("Erro ao executar bootstrap do banco. Consulte os logs.\n");
}
