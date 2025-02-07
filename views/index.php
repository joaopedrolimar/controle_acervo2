<?php
session_start();

// Se o usuário estiver logado, redireciona para o dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Se não estiver logado, redireciona para login.php
header("Location: login.php");
exit();
?>