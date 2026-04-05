<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/backend/controllers/ParceiroOperacionalController.php';

$controller = new ParceiroOperacionalController();
$controller->handle();

require_once dirname(__DIR__) . '/frontend/views/parceiros.php';
