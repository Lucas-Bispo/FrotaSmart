<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../frontend/views/login.php');
    exit;
}
require_once '../models/VeiculoModel.php';

class VeiculoController {
    private $model;

    public function __construct() {
        $this->model = new VeiculoModel();
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $placa = filter_input(INPUT_POST, 'placa', FILTER_SANITIZE_STRING);
            $modelo = filter_input(INPUT_POST, 'modelo', FILTER_SANITIZE_STRING);
            $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
            if ($placa && $modelo && $status) { // Validação básica
                $this->model->addVeiculo($placa, $modelo, $status);
                $_SESSION['success'] = "Veículo adicionado!";
            } else {
                $_SESSION['error'] = "Campos inválidos!";
            }
            header('Location: ../../frontend/views/dashboard.php');
            exit;
        }
    }

    // Futuro: Métodos para update/delete

    public function update() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $placa = filter_input(INPUT_POST, 'placa', FILTER_SANITIZE_STRING);
        $modelo = filter_input(INPUT_POST, 'modelo', FILTER_SANITIZE_STRING);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
        if ($id && $placa && $modelo && $status) {
            $this->model->updateVeiculo($id, $placa, $modelo, $status);
            $_SESSION['success'] = "Veículo atualizado!";
        } else {
            $_SESSION['error'] = "Dados inválidos!";
        }
        header('Location: ../../frontend/views/dashboard.php');
        exit;
    }
}

public function delete() {
    if (isset($_GET['id'])) {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            $this->model->deleteVeiculo($id);
            $_SESSION['success'] = "Veículo deletado!";
        } else {
            $_SESSION['error'] = "ID inválido!";
        }
        header('Location: ../../frontend/views/dashboard.php');
        exit;
    }
}
}

// Chamada automática
$controller = new VeiculoController();
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'add_veiculo') $controller->add();
    if ($_POST['action'] === 'update_veiculo') $controller->update();
} elseif (isset($_GET['action']) && $_GET['action'] === 'delete_veiculo') {
    $controller->delete();
}