<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/backend/controllers/MotoristaController.php';

$controller = new MotoristaController();
$controller->handle();

require_once dirname(__DIR__) . '/frontend/views/motoristas.php';
