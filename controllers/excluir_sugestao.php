<?php
session_start();
require_once "../config/conexao.php";

$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID inválido.");
}

// Verifica se a sugestão pertence ao usuário
$stmt = $pdo->prepare("DELETE FROM sugestoes WHERE id = ? AND usuario_id = ?");
$stmt->execute([$id, $_SESSION['usuario_id']]);

header("Location: ../views/mural.php");
exit;