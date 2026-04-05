<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/backend/controllers/ManutencaoController.php';

$controller = new ManutencaoController();
$controller->handle();

require_once dirname(__DIR__) . '/frontend/views/manutencoes.php';
