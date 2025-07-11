<?php
session_start();
require_once "../config/conexao.php";
global $pdo;

$perfil = $_SESSION['usuario_perfil'] ?? '';

// Verifica se o usuário está logado e se é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_perfil'] !== 'administrador') {
    $_SESSION['mensagem'] = "Acesso negado!";
    header("Location: dashboard.php");
    exit();
}

// Carrega nomes dos municípios, bairros e crimes_anpp
$municipiosMap = [];
$stmt = $pdo->query("SELECT id, nome FROM municipios");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $municipiosMap[$row['id']] = $row['nome'];
}

$bairrosMap = [];
$stmt = $pdo->query("SELECT id, nome FROM bairros");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $bairrosMap[$row['id']] = $row['nome'];
}

$crimesAnppMap = [];
$stmt = $pdo->query("SELECT id, nome FROM crimes_anpp");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $crimesAnppMap[$row['id']] = $row['nome'];
}

$pagina_atual = basename($_SERVER['PHP_SELF']);
$registrosPorPagina = 10;
$paginaAtual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($paginaAtual - 1) * $registrosPorPagina;

$busca = $_GET['busca'] ?? '';
$buscaSql = "";
$params = [];

if (!empty($busca)) {
    $buscaSql = " AND (
        usuarios.nome LIKE :busca OR
        logs.acao LIKE :busca OR
        logs.tabela_afetada LIKE :busca OR
        logs.registro_id LIKE :busca
    )";
    $params[':busca'] = "%$busca%";
}

// Consulta principal com paginação
$sql = "SELECT logs.*, usuarios.nome AS usuario_nome 
        FROM logs 
        JOIN usuarios ON logs.usuario_id = usuarios.id
        WHERE 1=1 $buscaSql
        ORDER BY logs.data_hora DESC
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $registrosPorPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total para paginação
$sqlTotal = "SELECT COUNT(*) FROM logs 
             JOIN usuarios ON logs.usuario_id = usuarios.id
             WHERE 1=1 $buscaSql";
$stmtTotal = $pdo->prepare($sqlTotal);
foreach ($params as $key => $value) {
    $stmtTotal->bindValue($key, $value);
}
$stmtTotal->execute();
$totalRegistros = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);
?>

<!DOCTYPE html>
<html lang="pt">

<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Log de Atividades</title>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
 <link rel="icon" href="../public/img/favicon-32x32.png" type="../img/favicon-32x32.png">
 <style>
 .logo-navbar {
  max-width: 300px;
  height: auto;
 }

 @media (max-width: 576px) {
  .logo-navbar {
   max-width: 250px;
   display: block;
   margin: auto;
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
     <li class="nav-item"><a class="nav-link <?= ($pagina_atual == 'dashboard.php') ? 'active' : '' ?>"
       href="dashboard.php"><i class="fas fa-home"></i> Início</a></li>
     <li class="nav-item"><a class="nav-link <?= ($pagina_atual == 'listar_processos.php') ? 'active' : '' ?>"
       href="listar_processos.php"><i class="fas fa-list"></i> Listar Processos</a></li>
     <li class="nav-item"><a class="nav-link <?= ($pagina_atual == 'cadastro_processo.php') ? 'active' : '' ?>"
       href="cadastro_processo.php"><i class="fas fa-plus"></i> Cadastrar Processos</a></li>
     <li class="nav-item"><a class="nav-link <?= ($pagina_atual == 'listar_anpp.php') ? 'active' : '' ?>"
       href="listar_anpp.php"><i class="fas fa-scale-balanced"></i> Listagem de ANPPs</a></li>
     <li class="nav-item"><a class="nav-link <?= ($pagina_atual == 'anpp.php') ? 'active' : '' ?>" href="anpp.php"><i
        class="fas fa-file-circle-plus"></i> Cadastrar ANPP</a></li>
     <li class="nav-item"><a class="nav-link <?= ($pagina_atual == 'gerenciar_usuarios.php') ? 'active' : '' ?>"
       href="gerenciar_usuarios.php"><i class="fas fa-users-cog"></i> Gerenciar Usuários</a></li>
     <li class="nav-item"><a class="nav-link <?= ($pagina_atual == 'atos.php') ? 'active' : '' ?>" href="atos.php"><i
        class="fas fa-file-alt"></i> Atos</a></li>
     <li class="nav-item"><a class="nav-link <?= ($pagina_atual == 'log_atividades.php') ? 'active' : '' ?>"
       href="log_atividades.php"><i class="fas fa-history"></i> Log de Atividades</a></li>
     <li class="nav-item"><a class="nav-link <?= ($pagina_atual == 'cadastro_basico.php') ? 'active' : '' ?>"
       href="cadastro_basico.php"><i class="fas fa-address-book"></i> Cadastro Básico</a></li>

     <?php if (in_array($perfil, ['administrador', 'consultor', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'relatorios.php') ? 'active' : '' ?>" href="relatorios.php">
       <i class="fas fa-chart-bar"></i> Relatórios
      </a>
     </li>
     <?php endif; ?>


     <li class="nav-item"><a class="nav-link text-white" href="../controllers/logout.php"><i
        class="fas fa-sign-out-alt"></i> Sair</a></li>
    </ul>
   </div>
  </div>
 </nav>

 <div class="container mt-4">
  <h2 class="text-center"><i class="fas fa-history"></i> Log de Atividades</h2>

  <form method="GET" class="mb-3">
   <div class="input-group">
    <input type="text" name="busca" class="form-control" placeholder="🔍 Buscar por ação, usuário ou tabela"
     value="<?= htmlspecialchars($busca) ?>">
    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
   </div>
  </form>

  <div class="table-responsive">
   <table class="table table-striped">
    <thead>
     <tr>
      <th>Usuário</th>
      <th>Ação</th>
      <th>Tabela Afetada</th>
      <th>ID do Registro</th>
      <th>Valores Anteriores</th>
      <th>Valores Novos</th>
      <th>Data/Hora</th>
     </tr>
    </thead>
    <tbody>
     <?php foreach ($logs as $log): ?>
     <?php
     $valores_anteriores = !empty($log['valores_anteriores']) ? json_decode($log['valores_anteriores'], true) : null;
     $valores_novos = !empty($log['valores_novos']) ? json_decode($log['valores_novos'], true) : null;
     ?>
     <tr>
      <td><?= htmlspecialchars($log['usuario_nome']) ?></td>
      <td><?= htmlspecialchars($log['acao']) ?></td>
      <td><?= htmlspecialchars($log['tabela_afetada']) ?></td>
      <td><?= htmlspecialchars($log['registro_id']) ?></td>
      <td>
       <?php if ($valores_anteriores): ?>
       <ul class="mb-0">
        <?php foreach ($valores_anteriores as $chave => $valor): ?>
        <?php
           if ($chave === 'local_municipio') $valor = $municipiosMap[$valor] ?? $valor;
           if ($chave === 'local_bairro') $valor = $bairrosMap[$valor] ?? $valor;
           if ($log['tabela_afetada'] === 'anpp' && $chave === 'crime_id') $valor = $crimesAnppMap[$valor] ?? $valor;
          ?>
        <?php
    // Formatar valores numéricos de dinheiro
    $formatarMoeda = in_array($chave, ['valor_reparacao', 'valor_multa']);
    $valorFormatado = $valor;

    if (is_null($valor)) {
        $valorFormatado = "Não há";
    } elseif ($formatarMoeda && is_numeric($valor)) {
        $valorFormatado = 'R$ ' . number_format($valor, 2, ',', '.');
    }

    echo "<li><strong>" . htmlspecialchars($chave) . ":</strong> " . htmlspecialchars($valorFormatado) . "</li>";
?>


        <?php endforeach; ?>
       </ul>
       <?php else: ?>N/A<?php endif; ?>
      </td>
      <td>
       <?php if ($valores_novos): ?>
       <ul class="mb-0">
        <?php foreach ($valores_novos as $chave => $valor): ?>
        <?php
           if ($chave === 'local_municipio') $valor = $municipiosMap[$valor] ?? $valor;
           if ($chave === 'local_bairro') $valor = $bairrosMap[$valor] ?? $valor;
           if ($log['tabela_afetada'] === 'anpp' && $chave === 'crime_id') $valor = $crimesAnppMap[$valor] ?? $valor;
          ?>
        <?php
    // Formatar valores numéricos de dinheiro
    $formatarMoeda = in_array($chave, ['valor_reparacao', 'valor_multa']);
    $valorFormatado = $valor;

    if (is_null($valor)) {
        $valorFormatado = "Não há";
    } elseif ($formatarMoeda && is_numeric($valor)) {
        $valorFormatado = 'R$ ' . number_format($valor, 2, ',', '.');
    }

    echo "<li><strong>" . htmlspecialchars($chave) . ":</strong> " . htmlspecialchars($valorFormatado) . "</li>";
?>


        <?php endforeach; ?>
       </ul>
       <?php else: ?>N/A<?php endif; ?>
      </td>
      <td><?= date("d/m/Y H:i:s", strtotime($log['data_hora'])) ?></td>
     </tr>
     <?php endforeach; ?>
    </tbody>
   </table>
  </div>

  <nav>
   <ul class="pagination justify-content-center">
    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
    <li class="page-item <?= ($i == $paginaAtual) ? 'active' : '' ?>">
     <a class="page-link" href="?pagina=<?= $i ?>&busca=<?= urlencode($busca) ?>"><?= $i ?></a>
    </li>
    <?php endfor; ?>
   </ul>
  </nav>
 </div>

 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>