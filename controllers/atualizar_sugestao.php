<?php
session_start();
require_once "../config/conexao.php";

$id = $_POST['id'] ?? null;
$texto = trim($_POST['texto'] ?? '');

if (!$id || !$texto) {
    die("Dados inválidos.");
}

// Garante que o usuário só edita o que é dele
$stmt = $pdo->prepare("UPDATE sugestoes SET texto = ? WHERE id = ? AND usuario_id = ?");
$stmt->execute([$texto, $id, $_SESSION['usuario_id']]);

header("Location: ../views/mural.php");
exit;