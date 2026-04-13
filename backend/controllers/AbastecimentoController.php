<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../models/AbastecimentoModel.php';
require_once __DIR__ . '/../models/OperacaoFrotaGuard.php';

final class AbastecimentoController
{
    private const ALLOWED_FUEL_TYPES = ['gasolina', 'etanol', 'diesel', 'diesel_s10', 'gnv', 'flex'];

    private AbastecimentoModel $model;
    private OperacaoFrotaGuard $guard;

    public function __construct()
    {
        secure_session_start();
        $this->model = new AbastecimentoModel();
        $this->guard = new OperacaoFrotaGuard();
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
        $warnings = $this->assertOperationalRules($payload, 'abastecimento.created_blocked');
        $id = $this->model->create($payload);

        audit_log('abastecimento.created', [
            'abastecimento_id' => $id,
            'veiculo_id' => $payload['veiculo_id'],
            'motorista_id' => $payload['motorista_id'],
            'tipo_combustivel' => $payload['tipo_combustivel'],
            'valor_total' => $payload['valor_total'],
        ]);

        $this->flashAndRedirect('success', $this->buildSuccessMessage('Abastecimento registrado com sucesso.', $warnings));
    }

    private function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0 || $this->model->findById($id) === null) {
            $this->flashAndRedirect('error', 'Abastecimento nao encontrado para atualizacao.');
        }

        $payload = $this->validatedPayload();
        $warnings = $this->assertOperationalRules($payload, 'abastecimento.updated_blocked');
        $this->model->update($id, $payload);

        audit_log('abastecimento.updated', [
            'abastecimento_id' => $id,
            'veiculo_id' => $payload['veiculo_id'],
            'motorista_id' => $payload['motorista_id'],
            'tipo_combustivel' => $payload['tipo_combustivel'],
            'valor_total' => $payload['valor_total'],
        ]);

        $this->flashAndRedirect('success', $this->buildSuccessMessage('Abastecimento atualizado com sucesso.', $warnings));
    }

    private function validatedPayload(): array
    {
        $payload = $this->collectPayload();
        $this->assertRequiredSelections($payload);
        $this->assertDateAndSupplier($payload);
        $this->assertFuelType($payload);
        $this->assertNumericValues($payload);

        return [
            'veiculo_id' => $payload['veiculo_id'],
            'motorista_id' => $payload['motorista_id'],
            'parceiro_id' => $this->nullablePositiveInteger((int) $payload['parceiro_id']),
            'data_abastecimento' => $payload['data_abastecimento'],
            'posto' => $payload['posto'],
            'tipo_combustivel' => $payload['tipo_combustivel'],
            'litros' => $payload['litros'],
            'valor_total' => $payload['valor_total'],
            'km_atual' => $payload['km_atual'],
            'observacoes' => $this->nullableText((string) $payload['observacoes']),
        ];
    }

    /**
     * @return array<string, int|string|float>
     */
    private function collectPayload(): array
    {
        return [
            'veiculo_id' => (int) ($_POST['veiculo_id'] ?? 0),
            'motorista_id' => (int) ($_POST['motorista_id'] ?? 0),
            'parceiro_id' => (int) ($_POST['parceiro_id'] ?? 0),
            'data_abastecimento' => (string) ($_POST['data_abastecimento'] ?? ''),
            'posto' => trim((string) ($_POST['posto'] ?? '')),
            'tipo_combustivel' => (string) ($_POST['tipo_combustivel'] ?? ''),
            'litros' => $this->normalizeDecimal(trim((string) ($_POST['litros'] ?? '0'))),
            'valor_total' => $this->normalizeDecimal(trim((string) ($_POST['valor_total'] ?? '0'))),
            'km_atual' => $this->normalizeInteger(trim((string) ($_POST['km_atual'] ?? '0'))),
            'observacoes' => trim((string) ($_POST['observacoes'] ?? '')),
        ];
    }

    /**
     * @param array<string, int|string|float> $payload
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
     * @param array<string, int|string|float> $payload
     */
    private function assertDateAndSupplier(array $payload): void
    {
        if (! $this->isValidDate((string) $payload['data_abastecimento'])) {
            $this->flashAndRedirect('error', 'Informe uma data valida para o abastecimento.');
        }

        if (trim((string) $payload['posto']) === '') {
            $this->flashAndRedirect('error', 'Informe o posto ou fornecedor.');
        }
    }

    /**
     * @param array<string, int|string|float> $payload
     */
    private function assertFuelType(array $payload): void
    {
        if (! in_array((string) $payload['tipo_combustivel'], self::ALLOWED_FUEL_TYPES, true)) {
            $this->flashAndRedirect('error', 'Informe um tipo de combustivel valido.');
        }
    }

    /**
     * @param array<string, int|string|float> $payload
     */
    private function assertNumericValues(array $payload): void
    {
        if ((float) $payload['litros'] <= 0) {
            $this->flashAndRedirect('error', 'Informe uma quantidade valida de litros.');
        }

        if ((float) $payload['valor_total'] <= 0) {
            $this->flashAndRedirect('error', 'Informe um valor total valido.');
        }

        if ((int) $payload['km_atual'] <= 0) {
            $this->flashAndRedirect('error', 'Informe um km atual valido para o veiculo.');
        }
    }

    /**
     * @param array<string, mixed> $payload
     * @return list<string>
     */
    private function assertOperationalRules(array $payload, string $auditEvent): array
    {
        $analysis = $this->guard->analyzeFuel(
            (int) $payload['veiculo_id'],
            (int) $payload['motorista_id'],
            (string) $payload['data_abastecimento'],
            (int) $payload['km_atual']
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
