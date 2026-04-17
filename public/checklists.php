<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/backend/controllers/ChecklistOperacionalController.php';

$controller = new ChecklistOperacionalController();
$controller->handle();

require_once dirname(__DIR__) . '/frontend/views/checklists.php';
