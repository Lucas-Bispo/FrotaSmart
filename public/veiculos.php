<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/backend/controllers/VeiculoController.php';

$controller = VeiculoController::fromGlobals();
$controller->handle($_POST['action'] ?? null);
