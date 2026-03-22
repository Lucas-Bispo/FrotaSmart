<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FrotaSmart - <?php echo htmlspecialchars($pageTitle ?? 'Dashboard', ENT_QUOTES, 'UTF-8'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-800">
    <?php require_once __DIR__ . '/sidebar.php'; ?>
    <main class="min-h-screen lg:ml-64 p-4 lg:p-8">
