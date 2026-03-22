<?php
require_once __DIR__ . '/../config/security.php';
secure_session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ../../frontend/views/login.php');
    exit;
}

require_once __DIR__ . '/../models/VeiculoModel.php';

class VeiculoController {
    private $model;

    public function __construct() {
        $this->model = new VeiculoModel();
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->ensureManagementAccess();

            if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
                set_flash('error', 'Requisição inválida. Atualize a página e tente novamente.');
                header('Location: ../../frontend/views/dashboard.php');
                exit;
            }

            $placa = $this->normalizePlaca((string) ($_POST['placa'] ?? ''));
            $modelo = trim((string) ($_POST['modelo'] ?? ''));
            $status = (string) ($_POST['status'] ?? '');

            if ($placa && $modelo !== '' && $this->isValidStatus($status)) {
                try {
                    $this->model->addVeiculo($placa, $modelo, $status);
                    set_flash('success', 'Veículo adicionado com sucesso.');
                } catch (PDOException $e) {
                    if ($e->getCode() === '23000') {
                        set_flash('error', 'A placa informada já está cadastrada.');
                    } else {
                        error_log('Erro ao adicionar veículo: ' . $e->getMessage());
                        set_flash('error', 'Erro interno ao adicionar o veículo.');
                    }
                }
            } else {
                set_flash('error', 'Campos inválidos. Revise placa, modelo e status.');
            }

            header('Location: ../../frontend/views/dashboard.php');
            exit;
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->ensureManagementAccess();

            if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
                set_flash('error', 'Requisição inválida. Atualize a página e tente novamente.');
                header('Location: ../../frontend/views/dashboard.php');
                exit;
            }

            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $placa = $this->normalizePlaca((string) ($_POST['placa'] ?? ''));
            $modelo = trim((string) ($_POST['modelo'] ?? ''));
            $status = (string) ($_POST['status'] ?? '');

            if ($id && $placa && $modelo !== '' && $this->isValidStatus($status)) {
                try {
                    $this->model->updateVeiculo($id, $placa, $modelo, $status);
                    set_flash('success', 'Veículo atualizado com sucesso.');
                } catch (PDOException $e) {
                    if ($e->getCode() === '23000') {
                        set_flash('error', 'A placa informada já está cadastrada em outro veículo.');
                    } else {
                        error_log('Erro ao atualizar veículo: ' . $e->getMessage());
                        set_flash('error', 'Erro interno ao atualizar o veículo.');
                    }
                }
            } else {
                set_flash('error', 'Dados inválidos.');
            }

            header('Location: ../../frontend/views/dashboard.php');
            exit;
        }
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->ensureManagementAccess();

            if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
                set_flash('error', 'Requisição inválida. Atualize a página e tente novamente.');
                header('Location: ../../frontend/views/dashboard.php');
                exit;
            }

            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

            if ($id) {
                $this->model->deleteVeiculo($id);
                set_flash('success', 'Veículo excluído com sucesso.');
            } else {
                set_flash('error', 'ID inválido.');
            }

            header('Location: ../../frontend/views/dashboard.php');
            exit;
        }
    }

    private function ensureManagementAccess(): void {
        $allowedRoles = ['admin', 'gerente'];

        if (!in_array($_SESSION['role'] ?? '', $allowedRoles, true)) {
            set_flash('error', 'Você não tem permissão para alterar a frota.');
            header('Location: ../../frontend/views/dashboard.php');
            exit;
        }
    }

    private function isValidStatus(string $status): bool {
        return in_array($status, ['ativo', 'manutencao'], true);
    }

    private function normalizePlaca(string $placa): ?string {
        $placa = strtoupper(trim($placa));
        return preg_match('/^[A-Z0-9-]{7,8}$/', $placa) === 1 ? $placa : null;
    }
}

$controller = new VeiculoController();
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'add_veiculo') {
        $controller->add();
    }

    if ($_POST['action'] === 'update_veiculo') {
        $controller->update();
    }

    if ($_POST['action'] === 'delete_veiculo') {
        $controller->delete();
    }
}
?>
