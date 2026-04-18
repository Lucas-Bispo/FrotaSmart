<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../models/ManutencaoModel.php';

final class ManutencaoController
{
    private const ALLOWED_TYPES = ['preventiva', 'corretiva'];
    private const ALLOWED_STATUS = ['aberta', 'em_andamento', 'concluida', 'cancelada'];

    private ManutencaoModel $model;

    public function __construct(?ManutencaoModel $model = null)
    {
        secure_session_start();
        $this->model = $model ?? new ManutencaoModel();
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
                'add_manutencao' => $this->create(),
                'update_manutencao' => $this->update(),
                default => $this->flashAndRedirect('error', 'Acao de manutencao nao suportada.'),
            };
        } catch (PDOException $exception) {
            error_log('Erro ao salvar manutencao: ' . $exception->getMessage());
            $this->flashAndRedirect('error', 'Erro interno ao salvar manutencao.');
        }
    }

    private function create(): void
    {
        $payload = $this->validatedPayload();
        $id = $this->model->create($payload);

        audit_log('manutencao.created', [
            'manutencao_id' => $id,
            'veiculo_id' => $payload['veiculo_id'],
            'status' => $payload['status'],
            'tipo' => $payload['tipo'],
        ]);

        $this->flashAndRedirect('success', 'Manutencao registrada com sucesso.');
    }

    private function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0 || $this->model->findById($id) === null) {
            $this->flashAndRedirect('error', 'Manutencao nao encontrada para atualizacao.');
        }

        $payload = $this->validatedPayload();
        $this->model->update($id, $payload);

        audit_log('manutencao.updated', [
            'manutencao_id' => $id,
            'veiculo_id' => $payload['veiculo_id'],
            'status' => $payload['status'],
            'tipo' => $payload['tipo'],
        ]);

        $this->flashAndRedirect('success', 'Manutencao atualizada com sucesso.');
    }

    private function validatedPayload(): array
    {
        $payload = $this->collectPayload();
        $this->assertRequiredSelections($payload);
        $this->assertTypeAndStatus($payload);
        $this->assertDateFields($payload);
        $this->assertDescription($payload);
        $this->assertPreventivePlan($payload);

        return [
            'veiculo_id' => $payload['veiculo_id'],
            'tipo' => $payload['tipo'],
            'status' => $payload['status'],
            'data_abertura' => $payload['data_abertura'],
            'data_conclusao' => $this->nullableText($payload['data_conclusao']),
            'km_referencia' => $payload['km_referencia'],
            'km_proxima_preventiva' => $payload['km_proxima_preventiva'],
            'data_proxima_preventiva' => $this->nullableText($payload['data_proxima_preventiva']),
            'recorrencia_dias' => $payload['recorrencia_dias'],
            'recorrencia_km' => $payload['recorrencia_km'],
            'parceiro_id' => $this->nullablePositiveInteger($payload['parceiro_id']),
            'fornecedor' => $this->nullableText($payload['fornecedor']),
            'custo_estimado' => $this->normalizeDecimal((string) $payload['custo_estimado']),
            'custo_final' => $this->normalizeDecimal((string) $payload['custo_final']),
            'descricao' => $payload['descricao'],
            'observacoes' => $this->nullableText($payload['observacoes']),
        ];
    }

    /**
     * @return array<string, int|string|null>
     */
    private function collectPayload(): array
    {
        return [
            'veiculo_id' => (int) ($_POST['veiculo_id'] ?? 0),
            'tipo' => (string) ($_POST['tipo'] ?? ''),
            'status' => (string) ($_POST['status'] ?? ''),
            'data_abertura' => (string) ($_POST['data_abertura'] ?? ''),
            'data_conclusao' => trim((string) ($_POST['data_conclusao'] ?? '')),
            'parceiro_id' => (int) ($_POST['parceiro_id'] ?? 0),
            'fornecedor' => trim((string) ($_POST['fornecedor'] ?? '')),
            'descricao' => trim((string) ($_POST['descricao'] ?? '')),
            'observacoes' => trim((string) ($_POST['observacoes'] ?? '')),
            'custo_estimado' => trim((string) ($_POST['custo_estimado'] ?? '0')),
            'custo_final' => trim((string) ($_POST['custo_final'] ?? '0')),
            'km_referencia' => $this->normalizeOptionalInteger(trim((string) ($_POST['km_referencia'] ?? ''))),
            'km_proxima_preventiva' => $this->normalizeOptionalInteger(trim((string) ($_POST['km_proxima_preventiva'] ?? ''))),
            'data_proxima_preventiva' => trim((string) ($_POST['data_proxima_preventiva'] ?? '')),
            'recorrencia_dias' => $this->normalizeOptionalInteger(trim((string) ($_POST['recorrencia_dias'] ?? ''))),
            'recorrencia_km' => $this->normalizeOptionalInteger(trim((string) ($_POST['recorrencia_km'] ?? ''))),
        ];
    }

    /**
     * @param array<string, int|string|null> $payload
     */
    private function assertRequiredSelections(array $payload): void
    {
        if ((int) $payload['veiculo_id'] <= 0) {
            $this->flashAndRedirect('error', 'Selecione um veiculo valido.');
        }
    }

    /**
     * @param array<string, int|string|null> $payload
     */
    private function assertTypeAndStatus(array $payload): void
    {
        if (! in_array((string) $payload['tipo'], self::ALLOWED_TYPES, true)) {
            $this->flashAndRedirect('error', 'Informe um tipo de manutencao valido.');
        }

        if (! in_array((string) $payload['status'], self::ALLOWED_STATUS, true)) {
            $this->flashAndRedirect('error', 'Informe um status de manutencao valido.');
        }
    }

    /**
     * @param array<string, int|string|null> $payload
     */
    private function assertDateFields(array $payload): void
    {
        $dataAbertura = (string) ($payload['data_abertura'] ?? '');
        $dataConclusao = (string) ($payload['data_conclusao'] ?? '');
        $dataProximaPreventiva = (string) ($payload['data_proxima_preventiva'] ?? '');

        if (! $this->isValidDate($dataAbertura)) {
            $this->flashAndRedirect('error', 'Informe uma data de abertura valida.');
        }

        if ($dataConclusao !== '' && ! $this->isValidDate($dataConclusao)) {
            $this->flashAndRedirect('error', 'Informe uma data de conclusao valida.');
        }

        if ($dataProximaPreventiva !== '' && ! $this->isValidDate($dataProximaPreventiva)) {
            $this->flashAndRedirect('error', 'Informe uma data valida para a proxima preventiva.');
        }
    }

    /**
     * @param array<string, int|string|null> $payload
     */
    private function assertDescription(array $payload): void
    {
        if (trim((string) ($payload['descricao'] ?? '')) === '') {
            $this->flashAndRedirect('error', 'Descreva o motivo ou defeito da manutencao.');
        }
    }

    /**
     * @param array<string, int|string|null> $payload
     */
    private function assertPreventivePlan(array $payload): void
    {
        if ((string) ($payload['tipo'] ?? '') !== 'preventiva') {
            return;
        }

        $hasPlan = $payload['km_proxima_preventiva'] !== null
            || trim((string) ($payload['data_proxima_preventiva'] ?? '')) !== ''
            || $payload['recorrencia_dias'] !== null
            || $payload['recorrencia_km'] !== null;

        if (! $hasPlan) {
            $this->flashAndRedirect('error', 'Preventivas exigem ao menos uma regra por km, data ou recorrencia.');
        }
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

        return (float) $normalized;
    }

    private function normalizeOptionalInteger(string $value): ?int
    {
        if ($value === '') {
            return null;
        }

        if (!ctype_digit($value)) {
            $this->flashAndRedirect('error', 'Campos de km e recorrencia devem conter apenas numeros inteiros.');
        }

        return (int) $value;
    }

    private function nullableText(string $value): ?string
    {
        return $value !== '' ? $value : null;
    }

    private function nullablePositiveInteger(int $value): ?int
    {
        return $value > 0 ? $value : null;
    }

    private function assertCanManage(): void
    {
        if (! isset($_SESSION['user']) || ! user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_MANAGE)) {
            set_flash('error', 'Acesso negado ao modulo de manutencao.');
            header('Location: /dashboard.php');
            exit;
        }
    }

    private function flashAndRedirect(string $level, string $message): void
    {
        set_flash($level, $message);
        header('Location: /manutencoes.php');
        exit;
    }
}
