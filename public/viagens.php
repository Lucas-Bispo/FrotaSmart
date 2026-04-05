<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/backend/controllers/ViagemController.php';

$controller = new ViagemController();
$controller->handle();

require_once dirname(__DIR__) . '/frontend/views/viagens.php';
