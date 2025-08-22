<?php
// controle_acervo/views/atos.php
session_start();
require_once "../config/conexao.php";
global $pdo;

// Inicializa mensagens de sessão antes de qualquer verificação
$_SESSION['mensagens'] = $_SESSION['mensagens'] ?? [];

$perfil = $_SESSION['usuario_perfil'] ?? '';
$pagina_atual = basename($_SERVER['PHP_SELF']);

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Verifica se o usuário tem permissão para acessar o Cadastro Básico
$perfis_autorizados = ['administrador', 'consultor', 'cadastrador_consulta'];
if (!in_array($perfil, $perfis_autorizados)) {
    $_SESSION['mensagens'][] = "Acesso negado! Você não tem permissão para acessar esta página.";
    header("Location: dashboard.php");
    exit();
}


$categorias = ['CGMPAM', 'CSMPAM', 'PGJMPAM', 'CPJMPAM', 'CNMP', 'OUVIDORIA/MPAM', 'Corregedoria Nacional do Ministério Público'];

// Configuração de paginação
$registros_por_pagina = 5;

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Atos - Biblioteca</title>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
 <link rel="icon" href="../public/img/favicon-32x32.png" type="../img/favicon-32x32.png">

 <style>
 .card {
  border-radius: 1rem;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
 }

 .card-header {
  font-weight: bold;
  font-size: 1.25rem;
  text-align: center;
 }

 .search-input {
  border-radius: 1rem;
 }

 .doc-list {
  max-height: 200px;
  overflow-y: auto;
 }

 .pagination {
  justify-content: center;
 }

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
 </style>
</head>

<body class="bg-light">


 <!-- Navbar -->
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

     <!-- Listar Processos -->
     <?php if (in_array($perfil, ['administrador', 'consultor', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'listar_processos.php') ? 'active' : '' ?>" href="listar_processos.php">
       <i class="fas fa-list"></i> <br> Listar Processos
      </a>
     </li>
     <?php endif; ?>

     <!-- Cadastrar Processos -->
     <?php if (in_array($perfil, ['administrador', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'cadastro_processo.php') ? 'active' : '' ?>"
       href="cadastro_processo.php">
       <i class="fas fa-plus"></i> Cadastrar Processos
      </a>
     </li>
     <?php endif; ?>

     <!-- Listagem ANPP -->
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'listar_anpp.php') ? 'active' : '' ?>" href="listar_anpp.php">
       <i class="fas fa-scale-balanced"></i> Listagem de ANPPs
      </a>
     </li>

     <!-- Cadastrar ANPP -->
     <?php if (in_array($perfil, ['administrador', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'anpp.php') ? 'active' : '' ?>" href="anpp.php">
       <i class="fas fa-file-circle-plus"></i> Cadastrar ANPP
      </a>
     </li>
     <?php endif; ?>

     <!-- Gerenciar Usuários -->
     <?php if (in_array($perfil, ['administrador', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'gerenciar_usuarios.php') ? 'active' : '' ?>"
       href="gerenciar_usuarios.php">
       <i class="fas fa-users-cog"></i> Gerenciar Usuários
      </a>
     </li>
     <?php endif; ?>

     <!-- Atos: todos -->
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'atos.php') ? 'active' : '' ?>" href="atos.php">
       <i class="fas fa-file-alt"></i> Atos
      </a>
     </li>

     <!-- Mural de Atualizações: todos -->
     <?php if (in_array($perfil, ['administrador', 'consultor', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'mural.php') ? 'active' : '' ?>" href="mural.php">
       <i class="fas fa-bullhorn"></i> <br> Mural de Atualizações
      </a>
     </li>
     <?php endif; ?>

     <!-- Log de Atividades -->
     <?php if ($perfil === 'administrador'): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'log_atividades.php') ? 'active' : '' ?>" href="log_atividades.php">
       <i class="fas fa-history"></i> <br> Log de Atividades
      </a>
     </li>
     <?php endif; ?>

     <!-- Cadastro Básico -->
     <?php if (in_array($perfil, ['administrador', 'cadasrador', 'cadastrador_consulta'])): ?>
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


 <div class="container py-4">
  <h2 class="text-center mb-4"><i class="fas fa-file-alt"></i> Atos (Resoluções, Recomendações, Portarias, etc.)
  </h2>
  <div class="row row-cols-1 row-cols-md-3 g-4">

   <?php foreach ($categorias as $categoria): 
        $pagina = isset($_GET['pagina_' . md5($categoria)]) ? (int)$_GET['pagina_' . md5($categoria)] : 1;
        $busca = isset($_GET['busca_' . md5($categoria)]) ? trim($_GET['busca_' . md5($categoria)]) : '';
        $offset = ($pagina - 1) * $registros_por_pagina;

        $sql = "SELECT * FROM atos WHERE categoria = :categoria";
        $params = [':categoria' => $categoria];
if (!empty($busca)) {
  $sql .= " AND (nome_arquivo LIKE :busca OR titulo LIKE :busca)";
  $params[':busca'] = "%$busca%";
}


        $sql_total = $sql;
        $sql .= " ORDER BY data_upload DESC LIMIT $offset, $registros_por_pagina";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Paginação total
        $stmt_total = $pdo->prepare($sql_total);
        $stmt_total->execute($params);
        $total_registros = $stmt_total->rowCount();
        $total_paginas = ceil($total_registros / $registros_por_pagina);
      ?>

   <div class="col">
    <div class="card h-100">
     <div class="card-header bg-light text-dark">
      <?= htmlspecialchars($categoria) ?>
     </div>
     <div class="card-body">
      <form method="GET" class="mb-3">
       <input type="text" class="form-control search-input" name="busca_<?= md5($categoria) ?>"
        placeholder="Buscar documentos..." value="<?= htmlspecialchars($busca) ?>">
      </form>

      <ul class="list-group doc-list">
       <?php foreach ($documentos as $doc): ?>
       <li class="list-group-item">
        <div class="d-flex justify-content-between align-items-center">
         <a href="<?= $doc['caminho'] ?>" target="_blank" class="text-truncate" style="max-width: 85%;">
          <?= htmlspecialchars($doc['titulo'] ?? $doc['nome_arquivo']) ?>
         </a>
         <form action="../controllers/deletar_ato.php" method="POST" class="ms-2"
          onsubmit="return confirm('Deseja mesmo excluir este documento?');">
          <input type="hidden" name="id" value="<?= $doc['id'] ?>">
          <button class="btn btn-sm btn-outline-danger p-1" style="width: 30px; height: 30px;">
           <i class="fas fa-trash-alt"></i>
          </button>
         </form>
        </div>
       </li>


       <?php endforeach; ?>
      </ul>

      <!-- Paginação -->
      <nav class="mt-2">
       <ul class="pagination pagination-sm">
        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
        <li class="page-item <?= ($i === $pagina) ? 'active' : '' ?>">
         <a class="page-link"
          href="?pagina_<?= md5($categoria) ?>=<?= $i ?>&busca_<?= md5($categoria) ?>=<?= urlencode($busca) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
       </ul>
      </nav>

      <form action="../controllers/upload_ato.php" method="POST" enctype="multipart/form-data" class="mt-3">
       <input type="hidden" name="categoria" value="<?= $categoria ?>">

       <div class="mb-2">
        <input type="text" name="titulo" class="form-control" placeholder="Título do documento" required>
       </div>

       <div class="mb-2">
        <input type="file" name="arquivo" class="form-control">
       </div>

       <div class="mb-2">
        <input type="text" name="link" class="form-control" placeholder="ou cole um link">
       </div>

       <button type="submit" name="enviar_ato" class="btn btn-primary w-100">Enviar</button>
      </form>

     </div>
    </div>
   </div>
   <?php endforeach; ?>

  </div>
 </div>

 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


</html>