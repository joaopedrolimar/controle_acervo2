<!--/controle_acervo/views/dashboard.php-->
<?php
session_start();
require_once "../config/conexao.php";
global $pdo;

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Obtém o nome do usuário logado
$nome_usuario = $_SESSION['usuario_nome'];
// Obtém o perfil do usuário logado
$perfil = $_SESSION['usuario_perfil'] ?? '';

$pagina_atual = basename($_SERVER['PHP_SELF']);

// Contagem de processos por status
$sql_ativos = "SELECT COUNT(*) FROM processos WHERE status = 'Ativo'";
$sql_finalizados = "SELECT COUNT(*) FROM processos WHERE status = 'Finalizado'";
$ativos = $pdo->query($sql_ativos)->fetchColumn();
$finalizados = $pdo->query($sql_finalizados)->fetchColumn();

// Gráfico por crime
$crimes = $pdo->query("SELECT crimes.nome, COUNT(*) as total FROM processos LEFT JOIN crimes ON processos.crime_id = crimes.id GROUP BY crimes.nome ORDER BY total DESC")->fetchAll(PDO::FETCH_ASSOC);

// Gráfico por município
$municipios = $pdo->query("SELECT municipios.nome, COUNT(*) as total FROM processos LEFT JOIN municipios ON processos.local_municipio = municipios.id GROUP BY municipios.nome ORDER BY total DESC")->fetchAll(PDO::FETCH_ASSOC);

// Gráfico de processos ativos por mês (últimos 6 meses)
$meses = $pdo->query("SELECT DATE_FORMAT(data_denuncia, '%m/%Y') as mes, COUNT(*) as total FROM processos WHERE status = 'Ativo' AND data_denuncia >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY mes ORDER BY data_denuncia")->fetchAll(PDO::FETCH_ASSOC);

// Top 5 bairros com mais processos
$bairros = $pdo->query("SELECT bairros.nome, COUNT(*) as total FROM processos LEFT JOIN bairros ON processos.local_bairro = bairros.id GROUP BY bairros.nome ORDER BY total DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="pt">

<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Dashboard</title>
 <!-- Bootstrap CSS -->
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
 <link rel="icon" href="../public/img/favicon-32x32.png" type="../img/favicon-32x32.png">
 <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

 canvas {
  background-color: #fff;
  border-radius: 15px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  padding: 10px;
  margin-bottom: 40px;
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
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'dashboard.php') ? 'active' : '' ?>" href="dashboard.php">
       <i class="fas fa-home"></i> Início
      </a>
     </li>

     <?php if (in_array($perfil, ['administrador', 'consultor', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'listar_processos.php') ? 'active' : '' ?>" href="listar_processos.php">
       <i class="fas fa-list"></i> Listar Processos
      </a>
     </li>
     <?php endif; ?>

     <?php if (in_array($perfil, ['administrador', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'cadastro_processo.php') ? 'active' : '' ?>"
       href="cadastro_processo.php">
       <i class="fas fa-plus"></i> Cadastrar Processos
      </a>
     </li>
     <?php endif; ?>

     <?php if (in_array($perfil, ['administrador', 'consultor', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'listar_anpp.php') ? 'active' : '' ?>" href="listar_anpp.php">
       <i class="fas fa-scale-balanced"></i> Listagem de ANPPs
      </a>
     </li>
     <?php endif; ?>

     <?php if (in_array($perfil, ['administrador', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'anpp.php') ? 'active' : '' ?>" href="anpp.php">
       <i class="fas fa-file-circle-plus"></i> Cadastrar ANPP
      </a>
     </li>
     <?php endif; ?>

     <?php if (in_array($perfil, ['administrador', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'gerenciar_usuarios.php') ? 'active' : '' ?>"
       href="gerenciar_usuarios.php">
       <i class="fas fa-users-cog"></i> Gerenciar Usuários
      </a>
     </li>
     <?php endif; ?>

     <?php if (in_array($perfil, ['administrador', 'consultor', 'cadastrador_consulta', 'cadastrador'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'atos.php') ? 'active' : '' ?>" href="atos.php">
       <i class="fas fa-file-alt"></i> Atos
      </a>
     </li>
     <?php endif; ?>

     <?php if ($perfil === 'administrador'): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'log_atividades.php') ? 'active' : '' ?>" href="log_atividades.php">
       <i class="fas fa-history"></i> Log de Atividades
      </a>
     </li>
     <?php endif; ?>

     <?php if (in_array($perfil, ['administrador', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'cadastro_basico.php') ? 'active' : '' ?>" href="cadastro_basico.php">
       <i class="fas fa-address-book"></i> Cadastro Básico
      </a>
     </li>
     <?php endif; ?>

     <li class="nav-item">
      <a class="nav-link text-white" href="../controllers/logout.php">
       <i class="fas fa-sign-out-alt"></i> Sair
      </a>
     </li>
    </ul>
   </div>
  </div>
 </nav>




 <div class="container mt-4">
  <h2 class="text-center"><i class="fas fa-chart-line"></i> Dashboard</h2>

  <div class="container d-flex flex-column align-items-center mt-5">
   <h2 class="text-center mb-4"><i class="fas fa-chart-pie"></i> Situação dos Processos</h2>
   <div style="max-width: 400px; width: 100%;">
    <canvas id="graficoStatus"></canvas>
   </div>
   <div class="col-md-6">
    <canvas id="graficoCrimes"></canvas>
   </div>
   <div class="col-md-6">
    <canvas id="graficoMunicipios"></canvas>
   </div>
   <div class="col-md-6">
    <canvas id="graficoMeses"></canvas>
   </div>
   <div class="col-md-6">
    <canvas id="graficoBairros"></canvas>
   </div>
  </div>



 </div>

 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
 <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
 <script>
 const ctxStatus = document.getElementById('graficoStatus').getContext('2d');
 const graficoStatus = new Chart(ctxStatus, {
  type: 'pie',
  data: {
   labels: ['Ativo', 'Finalizado'],
   datasets: [{
    data: [<?= $ativos ?>, <?= $finalizados ?>],
    backgroundColor: ['#0d6efd', '#198754'], // amarelo e verde
    borderWidth: 1
   }]
  },
  options: {
   responsive: true,
   plugins: {
    legend: {
     position: 'bottom'
    }
   }
  }
 });


 new Chart(document.getElementById('graficoCrimes'), {
  type: 'bar',
  data: {
   labels: <?= json_encode(array_column($crimes, 'nome')) ?>,
   datasets: [{
    label: 'Processos por Crime',
    data: <?= json_encode(array_column($crimes, 'total')) ?>,
    backgroundColor: '#0d6efd'
   }]
  }
 });

 new Chart(document.getElementById('graficoMunicipios'), {
  type: 'bar',
  data: {
   labels: <?= json_encode(array_column($municipios, 'nome')) ?>,
   datasets: [{
    label: 'Processos por Município',
    data: <?= json_encode(array_column($municipios, 'total')) ?>,
    backgroundColor: '#6610f2'
   }]
  }
 });

 new Chart(document.getElementById('graficoMeses'), {
  type: 'line',
  data: {
   labels: <?= json_encode(array_column($meses, 'mes')) ?>,
   datasets: [{
    label: 'Processos Ativos por Mês',
    data: <?= json_encode(array_column($meses, 'total')) ?>,
    backgroundColor: 'rgba(25,135,84,0.2)',
    borderColor: '#198754',
    borderWidth: 2,
    fill: true
   }]
  }
 });

 new Chart(document.getElementById('graficoBairros'), {
  type: 'bar',
  data: {
   labels: <?= json_encode(array_column($bairros, 'nome')) ?>,
   datasets: [{
    label: 'Top 5 Bairros com Mais Processos',
    data: <?= json_encode(array_column($bairros, 'total')) ?>,
    backgroundColor: '#fd7e14'
   }]
  }
 });
 </script>


</body>

</html>