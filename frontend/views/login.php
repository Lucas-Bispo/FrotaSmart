<?php
session_start();
// Redireciona se já estiver logado
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

// Prepara mensagens flash
$error_message = null;
if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FrotaSmart - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-blue-600">FrotaSmart</h1>
            <p class="text-gray-600">Gerenciador de Frotas Públicas</p>
        </div>

        <form method="POST" action="../../backend/controllers/AuthController.php" class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
            <h2 class="text-2xl font-semibold mb-6 text-gray-700">Acesso ao Sistema</h2>
            
            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">Erro: <?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-medium mb-2">Usuário</label>
                <input type="text" id="username" name="username" required 
                       class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Seu nome de usuário">
            </div>

            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-medium mb-2">Senha</label>
                <input type="password" id="password" name="password" required 
                       class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Sua senha">
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 w-full transition duration-150 ease-in-out">
                Entrar
            </button>
        </form>
    </div>
</body>
</html>