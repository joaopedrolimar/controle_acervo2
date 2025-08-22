<!-- /controle_acervo/views/mural.php -->
<?php
session_start();
require_once "../config/conexao.php"; 
global $pdo; 

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Define o perfil do usuário logado
$perfil = $_SESSION['usuario_perfil'] ?? '';

// Buscar sugestões do banco
$stmt = $pdo->query("
    SELECT s.*, u.nome AS usuario_nome 
    FROM sugestoes s
    JOIN usuarios u ON s.usuario_id = u.id
    ORDER BY s.data_criacao DESC
");

$sugestoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Mural de Atualizações</title>

 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
 <link rel="icon" href="../public/img/favicon-32x32.png" type="../img/favicon-32x32.png">
 <style>
 /* Ajuste da logo na navbar */
 .logo-navbar {
  max-width: 300px;
  /* Define um tamanho máximo */
  height: auto;
  /* Mantém a proporção correta */
 }

 /* Ajuste para telas menores */
 @media (max-width: 576px) {
  .logo-navbar {
   max-width: 250px;
   /* Reduz a logo para melhor encaixe */
   display: block;
   /* Evita que fique desalinhada */
   margin: auto;
   /* Centraliza no mobile */
  }
 }

 .btn-action {
  width: 100px;
  /* Mantém o tamanho padrão */
  text-align: center;
  /* Centraliza o conteúdo */
  display: inline-flex;
  /* Mantém alinhamento entre ícone e texto */
  align-items: center;
  /* Centraliza verticalmente */
  justify-content: center;
  /* Centraliza horizontalmente */
  white-space: nowrap;
  /* Impede que o texto quebre */
  margin: 3px;
  /* Adiciona um espaçamento entre os botões */
 }

 .sugestao-texto {
  white-space: pre-wrap;
  word-break: break-word;
  overflow-wrap: break-word;
 }

 .sugestao-card {
  background-color: #f8f9fa;
  border-radius: 10px;
  padding: 15px;
  margin-bottom: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
 }

 .sugestao-card strong {
  font-weight: 600;
 }

 .sugestao-texto {
  white-space: pre-wrap;
  overflow-wrap: break-word;
  word-break: break-word;
  margin-top: 5px;
 }
 </style>
</head>

<body>

 <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #900020;">
  <div class="container">
   <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
    <img src="../public/img/logoWhite.png" alt="Logo" class="logo-navbar">
   </a>

   <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
    <span class="navbar-toggler-icon"></span>
   </button>

   <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav ms-auto">

     <!-- Início: todos -->
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'dashboard.php') ? 'active' : '' ?>" href="dashboard.php">
       <i class="fas fa-home"></i> Início
      </a>
     </li>

     <!-- Listar Processos: 1, 2, 3 -->
     <?php if (in_array($perfil, ['administrador', 'consultor', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'listar_processos.php') ? 'active' : '' ?>" href="listar_processos.php">
       <i class="fas fa-list"></i> <br> Listar Processos
      </a>
     </li>
     <?php endif; ?>

     <!-- Cadastrar Processos: 1, 3, 4 -->
     <?php if (in_array($perfil, ['administrador', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'cadastro_processo.php') ? 'active' : '' ?>"
       href="cadastro_processo.php">
       <i class="fas fa-plus"></i> Cadastrar Processos
      </a>
     </li>
     <?php endif; ?>

     <!-- Listagem de ANPP: 1, 2, 3 -->
     <?php if (in_array($perfil, ['administrador', 'consultor', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'listar_anpp.php') ? 'active' : '' ?>" href="listar_anpp.php">
       <i class="fas fa-scale-balanced"></i> Listagem de ANPPs
      </a>
     </li>
     <?php endif; ?>

     <!-- Cadastrar ANPP: 1, 3, 4 -->
     <?php if (in_array($perfil, ['administrador', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'anpp.php') ? 'active' : '' ?>" href="anpp.php">
       <i class="fas fa-file-circle-plus"></i> Cadastrar ANPP
      </a>
     </li>
     <?php endif; ?>

     <!-- Gerenciar Usuários: 1, 3, 4 -->
     <?php if (in_array($perfil, ['administrador', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'gerenciar_usuarios.php') ? 'active' : '' ?>"
       href="gerenciar_usuarios.php">
       <i class="fas fa-users-cog"></i> Gerenciar Usuários
      </a>
     </li>
     <?php endif; ?>

     <!-- Atos: todos -->
     <?php if (in_array($perfil, ['administrador', 'consultor', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'atos.php') ? 'active' : '' ?>" href="atos.php">
       <i class="fas fa-file-alt"></i> Atos
      </a>
     </li>
     <?php endif; ?>

     <!-- Mural de Atualizações: todos -->
     <?php if (in_array($perfil, ['administrador', 'consultor', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'mural.php') ? 'active' : '' ?>" href="mural.php">
       <i class="fas fa-bullhorn"></i> <br> Mural de Atualizações
      </a>
     </li>
     <?php endif; ?>

     <!-- Log de Atividades: somente administrador -->
     <?php if ($perfil === 'administrador'): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'log_atividades.php') ? 'active' : '' ?>" href="log_atividades.php">
       <i class="fas fa-history"></i> <br> Log de Atividades
      </a>
     </li>
     <?php endif; ?>

     <!-- Cadastro Básico: 1, 2, 3 -->
     <?php if (in_array($perfil, ['administrador', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'cadastro_basico.php') ? 'active' : '' ?>" href="cadastro_basico.php">
       <i class="fas fa-address-book"></i> Cadastro Básico
      </a>
     </li>
     <?php endif; ?>

     <?php if (in_array($perfil, ['administrador', 'consultor', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'relatorios.php') ? 'active' : '' ?>" href="relatorios.php">
       <i class="fas fa-chart-bar"></i> Relatórios
      </a>
     </li>
     <?php endif; ?>


     <!-- Sair: todos -->
     <li class="nav-item">
      <a class="nav-link text-white" href="../controllers/logout.php">
       <i class="fas fa-sign-out-alt"></i> Sair
      </a>
     </li>

    </ul>
   </div>
  </div>
 </nav>

 <div class="container my-4 d-flex justify-content-center">
  <div style="max-width: 800px; width: 100%;">
   <h2 class="text-center mb-4"><i class="fas fa-bullhorn"></i> Mural de Atualizações</h2>

   <!-- Formulário de sugestão -->
   <form action="../controllers/salvar_sugestao.php" method="POST" class="mb-4">
    <div class="mb-3">
     <textarea name="texto" class="form-control" placeholder="Digite sua sugestão..." rows="3" required></textarea>
    </div>
    <div class="text-end">
     <button type="submit" class="btn btn-primary">Enviar Sugestão</button>
    </div>
   </form>


   <!-- Exibição das sugestões -->
   <?php foreach ($sugestoes as $s): ?>
   <div class="sugestao-card">

    <strong><?= htmlspecialchars($s['usuario_nome']) ?></strong> -
    <small><?= date('d/m/Y H:i', strtotime($s['data_criacao'])) ?></small><br>
    <div style="white-space: pre-wrap; overflow-wrap: break-word; word-break: break-word;">
     <?= nl2br(htmlspecialchars($s['texto'])) ?>
    </div>

    <?php if ($_SESSION['usuario_id'] == $s['usuario_id']): ?>
    <a href="editar_sugestao.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
    <a href="../controllers/excluir_sugestao.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-danger">Excluir</a>

    <?php
  $classeBotao = $s['finalizada'] ? 'btn-danger' : 'btn-success';
  $textoBotao = $s['finalizada'] ? 'Finalizado' : 'Finalizar';
?>
    <a href="../controllers/finalizar_sugestao.php?id=<?= $s['id'] ?>" class="btn btn-sm <?= $classeBotao ?>">
     <?= $textoBotao ?>
    </a>

    <?php endif; ?>

   </div>
   <?php endforeach; ?>


   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>