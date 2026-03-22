<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/config/db.php';

if (! is_cli_request()) {
    http_response_code(403);
    exit("Este script so pode ser executado via CLI.\n");
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
        status ENUM('ativo', 'manutencao') NOT NULL DEFAULT 'ativo',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uk_veiculos_placa (placa)
    )",
    "CREATE TABLE IF NOT EXISTS manutencoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        veiculo_id INT NOT NULL,
        data DATE NOT NULL,
        tipo VARCHAR(50) NOT NULL,
        custo DECIMAL(10, 2) NOT NULL,
        descricao TEXT,
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
        user_id INT NOT NULL,
        cnh_numero VARCHAR(20) NOT NULL UNIQUE,
        cnh_categoria VARCHAR(5) NOT NULL,
        cnh_vencimento DATE NOT NULL,
        status ENUM('ativo', 'afastado', 'ferias') DEFAULT 'ativo',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS viagens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        veiculo_id INT NOT NULL,
        motorista_id INT NOT NULL,
        secretaria_id INT NOT NULL,
        km_saida INT NOT NULL,
        km_chegada INT,
        destino TEXT NOT NULL,
        data_saida DATETIME DEFAULT CURRENT_TIMESTAMP,
        data_retorno DATETIME,
        status ENUM('em_curso', 'concluida', 'cancelada') DEFAULT 'em_curso',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (veiculo_id) REFERENCES veiculos(id),
        FOREIGN KEY (motorista_id) REFERENCES motoristas(id),
        FOREIGN KEY (secretaria_id) REFERENCES secretarias(id)
    )",
];

try {
    foreach ($statements as $statement) {
        $pdo->exec($statement);
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username');
    $stmt->execute([':username' => $adminUsername]);

    if ((int) $stmt->fetchColumn() === 0) {
        $hash = password_hash($adminPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
        $stmt->execute([$adminUsername, $hash, 'admin']);
        echo "Bootstrap concluido. Usuario administrador criado.\n";
    } else {
        echo "Bootstrap concluido. Usuario administrador ja existe.\n";
    }
} catch (PDOException $e) {
    error_log('Erro no bootstrap do banco: ' . $e->getMessage());
    exit("Erro ao executar bootstrap do banco. Consulte os logs.\n");
}
