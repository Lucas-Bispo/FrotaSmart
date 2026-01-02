<?php
require_once __DIR__ . '/../config/db.php';

class ManutencaoModel {
    public function addManutencao($veiculoId, $data, $tipo, $custo, $descricao) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO manutencoes (veiculo_id, data, tipo, custo, descricao) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$veiculoId, $data, $tipo, $custo, $descricao]);
        error_log("Manutenção adicionada para veiculo $veiculoId");  // Audit log
    }

    public function getByVeiculo($veiculoId) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM manutencoes WHERE veiculo_id = ?");
        $stmt->execute([$veiculoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Add update/delete similar
}
?>