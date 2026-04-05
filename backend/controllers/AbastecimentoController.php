<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../models/AbastecimentoModel.php';

final class AbastecimentoController
{
    private AbastecimentoModel $model;

    public function __construct()
    {
        secure_session_start();
        $this->model = new AbastecimentoModel();
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
                'add_abastecimento' => $this->create(),
                'update_abastecimento' => $this->update(),
                default => $this->flashAndRedirect('error', 'Acao de abastecimento nao suportada.'),
            };
        } catch (PDOException $exception) {
            error_log('Erro ao salvar abastecimento: ' . $exception->getMessage());
            $this->flashAndRedirect('error', 'Erro interno ao salvar abastecimento.');
        }
    }

    private function create(): void
    {
        $payload = $this->validatedPayload();
        $id = $this->model->create($payload);

        audit_log('abastecimento.created', [
            'abastecimento_id' => $id,
            'veiculo_id' => $payload['veiculo_id'],
            'motorista_id' => $payload['motorista_id'],
            'tipo_combustivel' => $payload['tipo_combustivel'],
            'valor_total' => $payload['valor_total'],
        ]);

        $this->flashAndRedirect('success', 'Abastecimento registrado com sucesso.');
    }

    private function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0 || $this->model->findById($id) === null) {
            $this->flashAndRedirect('error', 'Abastecimento nao encontrado para atualizacao.');
        }

        $payload = $this->validatedPayload();
        $this->model->update($id, $payload);

        audit_log('abastecimento.updated', [
            'abastecimento_id' => $id,
            'veiculo_id' => $payload['veiculo_id'],
            'motorista_id' => $payload['motorista_id'],
            'tipo_combustivel' => $payload['tipo_combustivel'],
            'valor_total' => $payload['valor_total'],
        ]);

        $this->flashAndRedirect('success', 'Abastecimento atualizado com sucesso.');
    }

    private function validatedPayload(): array
    {
        $veiculoId = (int) ($_POST['veiculo_id'] ?? 0);
        $motoristaId = (int) ($_POST['motorista_id'] ?? 0);
        $parceiroId = (int) ($_POST['parceiro_id'] ?? 0);
        $dataAbastecimento = (string) ($_POST['data_abastecimento'] ?? '');
        $posto = trim((string) ($_POST['posto'] ?? ''));
        $tipoCombustivel = (string) ($_POST['tipo_combustivel'] ?? '');
        $litros = trim((string) ($_POST['litros'] ?? '0'));
        $valorTotal = trim((string) ($_POST['valor_total'] ?? '0'));
        $kmAtual = trim((string) ($_POST['km_atual'] ?? '0'));
        $observacoes = trim((string) ($_POST['observacoes'] ?? ''));

        if ($veiculoId <= 0) {
            $this->flashAndRedirect('error', 'Selecione um veiculo valido.');
        }

        if ($motoristaId <= 0) {
            $this->flashAndRedirect('error', 'Selecione um motorista valido.');
        }

        if (!$this->isValidDate($dataAbastecimento)) {
            $this->flashAndRedirect('error', 'Informe uma data valida para o abastecimento.');
        }

        if ($posto === '') {
            $this->flashAndRedirect('error', 'Informe o posto ou fornecedor.');
        }

        if (!in_array($tipoCombustivel, ['gasolina', 'etanol', 'diesel', 'diesel_s10', 'gnv', 'flex'], true)) {
            $this->flashAndRedirect('error', 'Informe um tipo de combustivel valido.');
        }

        $litrosNormalizados = $this->normalizeDecimal($litros);
        if ($litrosNormalizados <= 0) {
            $this->flashAndRedirect('error', 'Informe uma quantidade valida de litros.');
        }

        $valorTotalNormalizado = $this->normalizeDecimal($valorTotal);
        if ($valorTotalNormalizado <= 0) {
            $this->flashAndRedirect('error', 'Informe um valor total valido.');
        }

        $kmAtualNormalizado = $this->normalizeInteger($kmAtual);
        if ($kmAtualNormalizado <= 0) {
            $this->flashAndRedirect('error', 'Informe um km atual valido para o veiculo.');
        }

        return [
            'veiculo_id' => $veiculoId,
            'motorista_id' => $motoristaId,
            'parceiro_id' => $parceiroId > 0 ? $parceiroId : null,
            'data_abastecimento' => $dataAbastecimento,
            'posto' => $posto,
            'tipo_combustivel' => $tipoCombustivel,
            'litros' => $litrosNormalizados,
            'valor_total' => $valorTotalNormalizado,
            'km_atual' => $kmAtualNormalizado,
            'observacoes' => $observacoes !== '' ? $observacoes : null,
        ];
    }

    private function isValidDate(string $date): bool
    {
        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d', $date);

        return $parsed instanceof \DateTimeImmutable && $parsed->format('Y-m-d') === $date;
    }

    private function normalizeDecimal(string $value): float
    {
        $normalized = str_replace(',', '.', $value);
        if ($normalized === '' || !is_numeric($normalized)) {
            return 0.0;
        }

        return round((float) $normalized, 2);
    }

    private function normalizeInteger(string $value): int
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        return $digits === '' ? 0 : (int) $digits;
    }

    private function assertCanManage(): void
    {
        if (!isset($_SESSION['user']) || !user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_MANAGE)) {
            set_flash('error', 'Acesso negado ao modulo de abastecimento.');
            header('Location: /dashboard.php');
            exit;
        }
    }

    private function flashAndRedirect(string $level, string $message): void
    {
        set_flash($level, $message);
        header('Location: /abastecimentos.php');
        exit;
    }
}
