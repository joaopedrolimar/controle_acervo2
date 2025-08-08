<?php
session_start();
require_once "../config/conexao.php";

$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID inválido.");
}

// Verifica se a sugestão pertence ao usuário logado
$stmt = $pdo->prepare("SELECT * FROM sugestoes WHERE id = ? AND usuario_id = ?");
$stmt->execute([$id, $_SESSION['usuario_id']]);
$sugestao = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sugestao) {
    die("Sugestão não encontrada ou você não tem permissão.");
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Editar Sugestão</title>

 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

 <style>
 .container-center {
  max-width: 800px;
  margin-top: 40px;
 }

 .card {
  border-radius: 10px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
 }

 textarea {
  resize: vertical;
 }
 </style>
</head>

<body>

 <div class="container d-flex justify-content-center">
  <div class="container-center w-100">
   <div class="card p-4">
    <h2 class="text-center mb-4"><i class="fas fa-edit"></i> Editar Sugestão</h2>

    <form action="../controllers/atualizar_sugestao.php" method="POST">
     <input type="hidden" name="id" value="<?= $sugestao['id'] ?>">

     <div class="mb-3">
      <label for="texto" class="form-label">Texto da Sugestão</label>
      <textarea name="texto" id="texto" class="form-control" rows="5"
       required><?= htmlspecialchars($sugestao['texto']) ?></textarea>
     </div>

     <div class="text-end">
      <a href="mural.php" class="btn btn-secondary">Cancelar</a>
      <button type="submit" class="btn btn-primary">Salvar Alterações</button>
     </div>
    </form>
   </div>
  </div>
 </div>

</body>

</html>