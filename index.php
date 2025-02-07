<?php
session_start();

// Se o usuário estiver logado, pode acessar o dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: views/dashboard.php");
    exit();
}

// Se não estiver logado, manda para o login
header("Location: views/login.php");
exit();
?>