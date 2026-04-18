<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../models/OperacaoFrotaGuard.php';
require_once __DIR__ . '/../models/ViagemModel.php';

final class ViagemController
{
    private const ALLOWED_STATUS = ['em_curso', 'concluida', 'cancelada'];

    private ViagemModel $model;
    private OperacaoFrotaGuard $guard;

    public function __construct(
        ?ViagemModel $model = null,
        ?OperacaoFrotaGuard $guard = null
    )
    {
        secure_session_start();
        $this->model = $model ?? new ViagemModel();
        $this->guard = $guard ?? new OperacaoFrotaGuard();
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
                'add_viagem' => $this->create(),
                'update_viagem' => $this->update(),
                default => $this->flashAndRedirect('error', 'Acao de viagem nao suportada.'),
            };
        } catch (PDOException $exception) {
            error_log('Erro ao salvar viagem: ' . $exception->getMessage());
            $this->flashAndRedirect('error', 'Erro interno ao salvar viagem.');
        }
    }

    private function create(): void
    {
        $payload = $this->validatedPayload();
        $warnings = $this->assertOperationalRules($payload, 'viagem.created_blocked');
        $id = $this->model->create($payload);

        audit_log('viagem.created', [
            'viagem_id' => $id,
            'veiculo_id' => $payload['veiculo_id'],
            'motorista_id' => $payload['motorista_id'],
            'secretaria' => $payload['secretaria'],
            'status' => $payload['status'],
        ]);

        $this->flashAndRedirect('success', $this->buildSuccessMessage('Viagem registrada com sucesso.', $warnings));
    }

    private function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0 || $this->model->findById($id) === null) {
            $this->flashAndRedirect('error', 'Viagem nao encontrada para atualizacao.');
        }

        $payload = $this->validatedPayload();
        $warnings = $this->assertOperationalRules($payload, 'viagem.updated_blocked');
        $this->model->update($id, $payload);

        audit_log('viagem.updated', [
            'viagem_id' => $id,
            'veiculo_id' => $payload['veiculo_id'],
            'motorista_id' => $payload['motorista_id'],
            'secretaria' => $payload['secretaria'],
            'status' => $payload['status'],
        ]);

        $this->flashAndRedirect('success', $this->buildSuccessMessage('Viagem atualizada com sucesso.', $warnings));
    }

    private function validatedPayload(): array
    {
        $payload = $this->collectPayload();
        $this->assertRequiredSelections($payload);
        $this->assertRequiredTextFields($payload);
        $this->assertDateFields($payload);
        $this->assertKilometers($payload);
        $this->assertStatus($payload);

        return [
            'veiculo_id' => $payload['veiculo_id'],
            'motorista_id' => $payload['motorista_id'],
            'secretaria' => $payload['secretaria'],
            'solicitante' => $payload['solicitante'],
            'origem' => $payload['origem'],
            'destino' => $payload['destino'],
            'finalidade' => $payload['finalidade'],
            'data_saida' => $payload['data_saida'],
            'data_retorno' => $this->nullableText($payload['data_retorno']),
            'km_saida' => $payload['km_saida'],
            'km_chegada' => $payload['km_chegada'],
            'status' => $payload['status'],
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
            'motorista_id' => (int) ($_POST['motorista_id'] ?? 0),
            'secretaria' => trim((string) ($_POST['secretaria'] ?? '')),
            'solicitante' => trim((string) ($_POST['solicitante'] ?? '')),
            'origem' => trim((string) ($_POST['origem'] ?? '')),
            'destino' => trim((string) ($_POST['destino'] ?? '')),
            'finalidade' => trim((string) ($_POST['finalidade'] ?? '')),
            'data_saida' => trim((string) ($_POST['data_saida'] ?? '')),
            'data_retorno' => trim((string) ($_POST['data_retorno'] ?? '')),
            'km_saida' => $this->normalizeInteger((string) ($_POST['km_saida'] ?? '0')),
            'km_chegada' => $this->normalizeOptionalInteger((string) ($_POST['km_chegada'] ?? '')),
            'status' => (string) ($_POST['status'] ?? ''),
            'observacoes' => trim((string) ($_POST['observacoes'] ?? '')),
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

        if ((int) $payload['motorista_id'] <= 0) {
            $this->flashAndRedirect('error', 'Selecione um motorista valido.');
        }
    }

    /**
     * @param array<string, int|string|null> $payload
     */
    private function assertRequiredTextFields(array $payload): void
    {
        $requiredFields = [
            'secretaria' => 'Informe a secretaria solicitante.',
            'solicitante' => 'Informe o solicitante ou responsavel.',
            'origem' => 'Informe a origem da viagem.',
            'destino' => 'Informe o destino da viagem.',
            'finalidade' => 'Informe a finalidade da viagem.',
        ];

        foreach ($requiredFields as $field => $message) {
            if (trim((string) ($payload[$field] ?? '')) === '') {
                $this->flashAndRedirect('error', $message);
            }
        }
    }

    /**
     * @param array<string, int|string|null> $payload
     */
    private function assertDateFields(array $payload): void
    {
        $dataSaida = (string) ($payload['data_saida'] ?? '');
        $dataRetorno = (string) ($payload['data_retorno'] ?? '');

        if (! $this->isValidDateTime($dataSaida)) {
            $this->flashAndRedirect('error', 'Informe uma data e hora de saida valida.');
        }

        if ($dataRetorno !== '' && ! $this->isValidDateTime($dataRetorno)) {
            $this->flashAndRedirect('error', 'Informe uma data e hora de retorno valida.');
        }
    }

    /**
     * @param array<string, int|string|null> $payload
     */
    private function assertKilometers(array $payload): void
    {
        $kmSaida = (int) ($payload['km_saida'] ?? 0);
        $kmChegada = $payload['km_chegada'];

        if ($kmSaida <= 0) {
            $this->flashAndRedirect('error', 'Informe um km inicial valido.');
        }

        if (is_int($kmChegada) && $kmChegada < $kmSaida) {
            $this->flashAndRedirect('error', 'O km final nao pode ser menor que o km inicial.');
        }
    }

    /**
     * @param array<string, int|string|null> $payload
     */
    private function assertStatus(array $payload): void
    {
        $status = (string) ($payload['status'] ?? '');
        $dataRetorno = (string) ($payload['data_retorno'] ?? '');
        $kmChegada = $payload['km_chegada'];

        if (! in_array($status, self::ALLOWED_STATUS, true)) {
            $this->flashAndRedirect('error', 'Informe um status de viagem valido.');
        }

        if ($status === 'concluida' && ($dataRetorno === '' || ! is_int($kmChegada))) {
            $this->flashAndRedirect('error', 'Viagens concluidas exigem data de retorno e km final.');
        }
    }

    private function nullableText(string $value): ?string
    {
        return $value !== '' ? $value : null;
    }

    /**
     * @param array<string, mixed> $payload
     * @return list<string>
     */
    private function assertOperationalRules(array $payload, string $auditEvent): array
    {
        $analysis = $this->guard->analyzeTrip(
            (int) $payload['veiculo_id'],
            (int) $payload['motorista_id'],
            (string) $payload['data_saida'],
            (int) $payload['km_saida']
        );

        if ($analysis['blocked'] !== []) {
            audit_log($auditEvent, [
                'veiculo_id' => $payload['veiculo_id'],
                'motorista_id' => $payload['motorista_id'],
                'blocked_reasons' => $analysis['blocked'],
            ]);

            $this->flashAndRedirect('error', implode(' ', $analysis['blocked']));
        }

        return $analysis['warnings'];
    }

    /**
     * @param list<string> $warnings
     */
    private function buildSuccessMessage(string $base, array $warnings): string
    {
        if ($warnings === []) {
            return $base;
        }

        return $base . ' Alertas: ' . implode(' ', array_slice($warnings, 0, 3));
    }

    private function isValidDateTime(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $value);

        return $date instanceof \DateTimeImmutable && $date->format('Y-m-d\TH:i') === $value;
    }

    private function normalizeInteger(string $value): int
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        return $digits === '' ? 0 : (int) $digits;
    }

    private function normalizeOptionalInteger(string $value): ?int
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        return $digits === '' ? null : (int) $digits;
    }

    private function assertCanManage(): void
    {
        if (! isset($_SESSION['user']) || ! user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_MANAGE)) {
            set_flash('error', 'Acesso negado ao modulo de viagens.');
            header('Location: /dashboard.php');
            exit;
        }
    }

    private function flashAndRedirect(string $level, string $message): void
    {
        set_flash($level, $message);
        header('Location: /viagens.php');
        exit;
    }
}
