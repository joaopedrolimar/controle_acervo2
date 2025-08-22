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

// Gráfico de processos por natureza processual
$por_natureza = $pdo->query("
    SELECT natureza, COUNT(*) as total 
    FROM processos 
    GROUP BY natureza
    ORDER BY total DESC
")->fetchAll(PDO::FETCH_ASSOC);


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


// Gráfico de ANPPs por status do acordo
$anpps_status = $pdo->query("
    SELECT acordo_realizado, COUNT(*) as total
    FROM anpp
    GROUP BY acordo_realizado
")->fetchAll(PDO::FETCH_ASSOC);

// Prepara dados do gráfico de ANPP
$anppLabels = [];
$anppValues = [];

foreach ($anpps_status as $row) {
    $label = strtolower($row['acordo_realizado']) === 'sim' ? 'Realizado' : 'Não Realizado';
    $anppLabels[] = $label;
    $anppValues[] = (int)$row['total'];
}

$query = "
    SELECT COUNT(*) as total_alertas
    FROM processos
    WHERE data_recebimento_denuncia IS NULL
      AND DATEDIFF(CURDATE(), data_denuncia) > 30
";

$stmt = $pdo->query($query);
$alerta = $stmt->fetchColumn();

$alertas_rows = [];
if ((int)$alerta > 0) {
    $sqlAlertas = "
        SELECT id, numero, data_denuncia,
               DATEDIFF(CURDATE(), data_denuncia) AS dias
        FROM processos
        WHERE data_recebimento_denuncia IS NULL
          AND data_denuncia IS NOT NULL
          AND data_denuncia <> '0000-00-00'
          AND DATEDIFF(CURDATE(), data_denuncia) >= 30
        ORDER BY data_denuncia ASC
        LIMIT 200
    ";
    $alertas_rows = $pdo->query($sqlAlertas)->fetchAll(PDO::FETCH_ASSOC);
}



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

 .card {
  border-radius: 20px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
 }

 .card-header {
  font-weight: 600;
 }

 .card-body canvas {
  max-height: 320px;
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
       <i class="fas fa-list"></i> <br>Listar Processos
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

     <!-- Mural de Atualizações: todos -->
     <?php if (in_array($perfil, ['administrador', 'consultor', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'mural.php') ? 'active' : '' ?>" href="mural.php">
       <i class="fas fa-bullhorn"></i> <br> Mural de Atualizações
      </a>
     </li>
     <?php endif; ?>

     <?php if ($perfil === 'administrador'): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'log_atividades.php') ? 'active' : '' ?>" href="log_atividades.php">
       <i class="fas fa-history"></i> <br> Log de Atividades
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

     <?php if (in_array($perfil, ['administrador', 'consultor', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'relatorios.php') ? 'active' : '' ?>" href="relatorios.php">
       <i class="fas fa-chart-bar"></i> Relatórios
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

  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mt-4">

<?php if ($alerta > 0): ?>
  <div class="alert alert-danger text-center" style="font-size:16px; cursor:pointer"
       data-bs-toggle="modal" data-bs-target="#modalAlertas">
    ⚠️ Existem <strong><?= (int)$alerta ?></strong> processos com mais de 30 dias sem recebimento da denúncia!
    <br><u>Clique para ver os IDs</u>
  </div>
<?php endif; ?>


<?php if (!empty($alertas_rows)): ?>
<div class="modal fade" id="modalAlertas" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Processos com 30+ dias sem recebimento</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex gap-2 mb-3">
          <a class="btn btn-outline-danger btn-sm" href="listar_processos.php?alerta_30=1">
            Ver na lista filtrada
          </a>
          <button class="btn btn-outline-secondary btn-sm" id="btnCopiarIds">Copiar IDs</button>
        </div>

        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th style="width:80px">ID</th>
                <th>Número</th>
                <th style="width:140px">Denúncia</th>
                <th style="width:120px">Atraso</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($alertas_rows as $row): ?>
              <tr>
                <td><?= (int)$row['id'] ?></td>
                <td><?= htmlspecialchars($row['numero'] ?? '') ?></td>
                <td><?= $row['data_denuncia'] ? date('d/m/Y', strtotime($row['data_denuncia'])) : '—' ?></td>
                <td><span class="badge bg-danger"><?= (int)$row['dias'] ?>d</span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Campo escondido para copiar IDs -->
        <textarea id="txtIds" class="visually-hidden"><?=
          implode(',', array_map(fn($r)=>$r['id'], $alertas_rows))
        ?></textarea>
      </div>
      <div class="modal-footer">
        <small class="text-muted">Mostrando até 200 registros mais antigos. Para ver todos, use “Ver na lista”.</small>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>


   <div class="col">
    <div class="card">
     <div class="card-header bg-primary text-white">
      <i class="fas fa-chart-pie"></i> Situação dos Processos
     </div>
     <div class="card-body">
      <canvas id="graficoStatus"></canvas>
     </div>
    </div>
   </div>

   <div class="col">
    <div class="card">
     <div class="card-header bg-secondary text-white">
      <i class="fas fa-balance-scale"></i> Processos por Crime
     </div>
     <div class="card-body">
      <canvas id="graficoCrimes"></canvas>
     </div>
    </div>
   </div>

   <div class="col">
    <div class="card">
     <div class="card-header bg-success text-white">
      <i class="fas fa-map-marker-alt"></i> Processos por Município
     </div>
     <div class="card-body">
      <canvas id="graficoMunicipios"></canvas>
     </div>
    </div>
   </div>
   <div class="col">
    <div class="card">
     <div class="card-header bg-info text-white">
      <i class="fas fa-calendar-alt"></i> Processos Ativos por Mês
     </div>
     <div class="card-body">
      <canvas id="graficoMeses"></canvas>
     </div>
    </div>
   </div>
   <div class="col">
    <div class="card">
     <div class="card-header bg-warning text-dark">
      <i class="fas fa-map"></i> Top 5 Bairros com Mais Processos
     </div>
     <div class="card-body">
      <canvas id="graficoBairros"></canvas>
     </div>
    </div>
   </div>

   <div class="col">
    <div class="card">
     <div class="card-header bg-danger text-white">
      <i class="fas fa-handshake"></i> Status dos ANPPs
     </div>
     <div class="card-body">
      <canvas id="graficoAnppStatus"></canvas>
     </div>
    </div>
   </div>

   <div class="col">
    <div class="card">
     <div class="card-header bg-dark text-white">
      <i class="fas fa-folder-tree"></i> Processos por Natureza Processual
     </div>
     <div class="card-body">
      <canvas id="graficoNatureza"></canvas>
     </div>
    </div>
   </div>

  </div>
 </div>






 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
 <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
 <script>
 const ctxStatus = document.getElementById('graficoStatus').getContext('2d');
 new Chart(ctxStatus, {
  type: 'pie',
  data: {
   labels: ['Ativo', 'Finalizado'],
   datasets: [{
    data: [<?= $ativos ?>, <?= $finalizados ?>],
    backgroundColor: ['#0d6efd', '#198754'],
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

 new Chart(document.getElementById('graficoAnppStatus'), {
  type: 'pie',
  data: {
   labels: <?= json_encode($anppLabels) ?>,
   datasets: [{
    label: 'Status dos ANPPs',
    data: <?= json_encode($anppValues) ?>,
    backgroundColor: ['#198754', '#dc3545'], // verde e vermelho
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

 new Chart(document.getElementById('graficoNatureza'), {
  type: 'bar',
  data: {
   labels: <?= json_encode(array_column($por_natureza, 'natureza')) ?>,
   datasets: [{
    label: 'Total de Processos',
    data: <?= json_encode(array_column($por_natureza, 'total')) ?>,
    backgroundColor: '#343a40' // cinza escuro
   }]
  },
  options: {
   responsive: true,
   plugins: {
    legend: {
     display: false
    }
   }
  }
 });
 </script>


<script>
document.addEventListener('DOMContentLoaded', function() {
  const btn = document.getElementById('btnCopiarIds');
  if (btn) {
    btn.addEventListener('click', async () => {
      const txt = document.getElementById('txtIds').value;
      try {
        await navigator.clipboard.writeText(txt);
        btn.textContent = 'Copiado!';
        setTimeout(()=>btn.textContent='Copiar IDs', 1500);
      } catch(e) {
        alert('Não foi possível copiar. Selecione e copie manualmente:\n' + txt);
      }
    });
  }
});
</script>


</body>

</html>