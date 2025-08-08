<!--/controllers/salvar_sugestao.php-->
<?php
session_start();
require_once "../config/conexao.php";

$usuario_id = $_SESSION['usuario_id'];
$texto = trim($_POST['texto']);

$stmt = $pdo->prepare("INSERT INTO sugestoes (usuario_id, texto) VALUES (?, ?)");
$stmt->execute([$usuario_id, $texto]);

header("Location: ../views/mural.php");
exit;