<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../models/ParceiroOperacionalModel.php';

final class ParceiroOperacionalController
{
    private ParceiroOperacionalModel $model;

    public function __construct()
    {
        secure_session_start();
        $this->model = new ParceiroOperacionalModel();
    }

    public function handle(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return;
        }

        $this->assertCanManage();

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            $this->flashAndRedirect('error', 'Requisicao invalida. Atualize a pagina e tente novamente.');
        }

        $action = (string) ($_POST['action'] ?? '');

        try {
            match ($action) {
                'add_parceiro' => $this->create(),
                'update_parceiro' => $this->update(),
                default => $this->flashAndRedirect('error', 'Acao de parceiro nao suportada.'),
            };
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $this->flashAndRedirect('error', 'CNPJ ja cadastrado para outro parceiro.');
            }

            error_log('Erro ao salvar parceiro operacional: ' . $exception->getMessage());
            $this->flashAndRedirect('error', 'Erro interno ao salvar parceiro operacional.');
        }
    }

    private function create(): void
    {
        $payload = $this->validatedPayload();
        $id = $this->model->create($payload);

        audit_log('parceiro.created', [
            'parceiro_id' => $id,
            'tipo' => $payload['tipo'],
            'nome_fantasia' => $payload['nome_fantasia'],
            'status' => $payload['status'],
        ]);

        $this->flashAndRedirect('success', 'Parceiro operacional cadastrado com sucesso.');
    }

    private function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0 || $this->model->findById($id) === null) {
            $this->flashAndRedirect('error', 'Parceiro operacional nao encontrado para atualizacao.');
        }

        $payload = $this->validatedPayload();
        $this->model->update($id, $payload);

        audit_log('parceiro.updated', [
            'parceiro_id' => $id,
            'tipo' => $payload['tipo'],
            'nome_fantasia' => $payload['nome_fantasia'],
            'status' => $payload['status'],
        ]);

        $this->flashAndRedirect('success', 'Parceiro operacional atualizado com sucesso.');
    }

    private function validatedPayload(): array
    {
        $nomeFantasia = trim((string) ($_POST['nome_fantasia'] ?? ''));
        $razaoSocial = trim((string) ($_POST['razao_social'] ?? ''));
        $cnpj = preg_replace('/\D+/', '', (string) ($_POST['cnpj'] ?? '')) ?? '';
        $tipo = (string) ($_POST['tipo'] ?? '');
        $telefone = trim((string) ($_POST['telefone'] ?? ''));
        $endereco = trim((string) ($_POST['endereco'] ?? ''));
        $contatoResponsavel = trim((string) ($_POST['contato_responsavel'] ?? ''));
        $status = (string) ($_POST['status'] ?? '');
        $observacoes = trim((string) ($_POST['observacoes'] ?? ''));

        if ($nomeFantasia === '') {
            $this->flashAndRedirect('error', 'Informe o nome fantasia do parceiro.');
        }
        if ($razaoSocial === '') {
            $this->flashAndRedirect('error', 'Informe a razao social do parceiro.');
        }
        if (!preg_match('/^\d{14}$/', $cnpj)) {
            $this->flashAndRedirect('error', 'Informe um CNPJ valido com 14 digitos.');
        }
        if (!in_array($tipo, ['oficina', 'posto_combustivel', 'fornecedor_pecas', 'prestador_servico'], true)) {
            $this->flashAndRedirect('error', 'Informe um tipo de parceiro valido.');
        }
        if (!in_array($status, ['ativo', 'inativo'], true)) {
            $this->flashAndRedirect('error', 'Informe um status valido para o parceiro.');
        }

        return [
            'nome_fantasia' => $nomeFantasia,
            'razao_social' => $razaoSocial,
            'cnpj' => $cnpj,
            'tipo' => $tipo,
            'telefone' => $telefone !== '' ? $telefone : null,
            'endereco' => $endereco !== '' ? $endereco : null,
            'contato_responsavel' => $contatoResponsavel !== '' ? $contatoResponsavel : null,
            'status' => $status,
            'observacoes' => $observacoes !== '' ? $observacoes : null,
        ];
    }

    private function assertCanManage(): void
    {
        if (!isset($_SESSION['user']) || !user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_MANAGE)) {
            set_flash('error', 'Acesso negado ao modulo de parceiros operacionais.');
            header('Location: /dashboard.php');
            exit;
        }
    }

    private function flashAndRedirect(string $level, string $message): void
    {
        set_flash($level, $message);
        header('Location: /parceiros.php');
        exit;
    }
}
