<?php
require_once __DIR__ . '/config/db.php';

if (!is_cli_request()) {
    http_response_code(404);
    exit('Recurso indisponível.');
}

echo 'Conexão DB OK!' . PHP_EOL;
echo 'Versão PHP: ' . phpversion() . PHP_EOL;
?>
