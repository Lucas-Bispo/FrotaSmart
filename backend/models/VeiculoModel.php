<?php
require_once __DIR__ . '/../config/db.php';  // __DIR__ = path de models/, /.. sobe para backend, /config/db.php
class VeiculoModel {
    public function addVeiculo($placa, $modelo, $status) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO veiculos (placa, modelo, status) VALUES (?, ?, ?)");
        $stmt->execute([$placa, $modelo, $status]);
        return $pdo->lastInsertId(); // Retorna ID para confirmação
    }

    public function getAllVeiculos() {
        global $pdo;
        $stmt = $pdo->query("SELECT * FROM veiculos");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateVeiculo($id, $placa, $modelo, $status) {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE veiculos SET placa = ?, modelo = ?, status = ? WHERE id = ?");
        $stmt->execute([$placa, $modelo, $status, $id]);
        return $stmt->rowCount(); // Quantos afetados
    }

    public function deleteVeiculo($id) {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM veiculos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }
}
?>