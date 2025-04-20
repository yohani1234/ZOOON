<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if ($_POST['password'] === 'admin123') { 
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = "Invalid password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-yellow-200 to-green-400 min-h-screen flex items-center justify-center">
    <div class="bg-gradient-to-br from-green-500 to-yellow-500 p-8 rounded-lg shadow-md w-96">
        <h1 class="text-2xl font-bold text-center text-white mb-6">Admin Login</h1>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-4">
                <label class="block text-white font-black mb-2">Password</label>
                <input type="password" name="password" class="w-full px-3 py-2 border bg-white/80 border-gray-300 rounded-lg" required>
            </div>
            <button type="submit" class="w-full bg-black/70 text-white py-2 px-4 rounded-lg hover:bg-gray-600 hover:text-white">
                Login
            </button>
        </form>
    </div>
</body>
</html>