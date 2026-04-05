<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/backend/controllers/AbastecimentoController.php';

$controller = new AbastecimentoController();
$controller->handle();

require_once dirname(__DIR__) . '/frontend/views/abastecimentos.php';
