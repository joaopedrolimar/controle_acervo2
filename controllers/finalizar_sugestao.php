<?php
session_start();
require_once "../config/conexao.php";

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    die("ID inválido.");
}

// Verifica se a sugestão pertence ao usuário logado
$stmt = $pdo->prepare("SELECT * FROM sugestoes WHERE id = ? AND usuario_id = ?");
$stmt->execute([$id, $_SESSION['usuario_id']]);
$sugestao = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sugestao) {
    die("Sugestão não encontrada ou você não tem permissão.");
}

// Alterna entre 0 e 1
$novo_estado = $sugestao['finalizada'] ? 0 : 1;

$update = $pdo->prepare("UPDATE sugestoes SET finalizada = ? WHERE id = ?");
$update->execute([$novo_estado, $id]);

header("Location: ../views/mural.php");
exit();