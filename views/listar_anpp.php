<!--/controle_acervo/views/listar_anpp.php-->
<?php
session_start();
require_once "../config/conexao.php";
global $pdo;

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$perfil = $_SESSION['usuario_perfil'] ?? '';

// 🔒 Verifica permissão
$perfil = $_SESSION['usuario_perfil'] ?? '';
if (!in_array($perfil, ['administrador', 'cadastrador', 'consultor', 'cadastrador_consulta'])) {
    $_SESSION['mensagem'] = "Você não tem permissão para acessar esta página.";
    header("Location: listar_anpp.php");
    exit();
}


$pagina_atual = basename(__FILE__);

// Paginação
$porPagina = 10;
$paginaAtual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$offset = ($paginaAtual - 1) * $porPagina;

// Filtros
$busca = $_GET['busca'] ?? '';
$numero_filter = $_GET['numero_filter'] ?? '';
$indiciado_filter = $_GET['indiciado_filter'] ?? '';
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';

// Captura os filtros do formulário
$search = $_GET['search'] ?? '';
$advanced_search = isset($_GET['advanced_search']);

$data_fato_inicio = $_GET['data_fato_inicio'] ?? '';
$data_fato_fim = $_GET['data_fato_fim'] ?? '';

$id_filter = $_GET['id_filter'] ?? '';
$date_filter = $_GET['date_filter'] ?? '';
$municipio_filter = $_GET['municipio_filter'] ?? '';
$bairro_filter = $_GET['bairro_filter'] ?? '';
$vitima_filter = $_GET['vitima_filter'] ?? '';
$denunciado_filter = $_GET['denunciado_filter'] ?? '';
$sentenca_filter = $_GET['sentenca_filter'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';

$sql = "SELECT anpp.*, crimes_anpp.nome AS crime_nome
        FROM anpp
        JOIN crimes_anpp ON anpp.crime_id = crimes_anpp.id
        WHERE 1=1";

$params = [];

// Pesquisa simples
if (!empty($busca)) {
    $sql .= " AND (
        anpp.numero_inquerito LIKE :busca OR
        anpp.indiciado LIKE :busca OR
        crimes_anpp.nome LIKE :busca
    )";
    $params[':busca'] = "%$busca%";
}

// Pesquisa avançada
if (!empty($numero_filter)) {
    $sql .= " AND anpp.numero_inquerito LIKE :numero";
    $params[':numero'] = "%$numero_filter%";
}
if (!empty($indiciado_filter)) {
    $sql .= " AND anpp.indiciado LIKE :indiciado";
    $params[':indiciado'] = "%$indiciado_filter%";
}
if (!empty($data_inicio)) {
    $sql .= " AND anpp.data_audiencia >= :data_inicio";
    $params[':data_inicio'] = $data_inicio;
}
if (!empty($data_fim)) {
    $sql .= " AND anpp.data_audiencia <= :data_fim";
    $params[':data_fim'] = $data_fim;
}

// Conta total para paginação
$sqlCount = "SELECT COUNT(*) 
             FROM anpp 
             JOIN crimes_anpp ON anpp.crime_id = crimes_anpp.id 
             WHERE 1=1";

// Reaplicando os mesmos filtros individualmente
if (!empty($busca)) {
    $sqlCount .= " AND (
        anpp.numero_inquerito LIKE :busca OR
        anpp.indiciado LIKE :busca OR
        crimes_anpp.nome LIKE :busca
    )";
}

if (!empty($numero_filter)) {
    $sqlCount .= " AND anpp.numero_inquerito LIKE :numero";
}
if (!empty($indiciado_filter)) {
    $sqlCount .= " AND anpp.indiciado LIKE :indiciado";
}
if (!empty($data_inicio)) {
    $sqlCount .= " AND anpp.data_audiencia >= :data_inicio";
}
if (!empty($data_fim)) {
    $sqlCount .= " AND anpp.data_audiencia <= :data_fim";
}

$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$total = $stmtCount->fetchColumn();

// Aplica limite para paginação
$sql .= " ORDER BY anpp.data_audiencia DESC LIMIT $porPagina OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$anpps = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalPaginas = ceil($total / $porPagina);
?>

<!DOCTYPE html>
<html lang="pt">

<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Listagem de ANPPs</title>
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
  text-align: center;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  white-space: nowrap;
  margin: 2px 0;
 }
 </style>


</head>

<body class="bg-light">

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





 <div class="container mt-5">
  <h2 class="text-center"><i class="fas fa-scale-balanced"></i> Listagem de ANPPs
  </h2>

  <!-- Pesquisa Simples -->
  <form method="GET" class="mb-3">
   <div class="input-group">
    <input type="text" name="busca" class="form-control" placeholder="🔍 Pesquisa Simples"
     value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
   </div>
  </form>

  <!-- Botão para abrir pesquisa avançada -->
  <button class="btn btn-secondary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#pesquisaAvancada">
   🔍 Pesquisa Avançada
  </button>

  <!-- Pesquisa Avançada -->
  <div class="collapse" id="pesquisaAvancada">
   <form method="GET" class="row g-3 mb-4">
    <div class="col-md-4">
     <input type="text" name="numero_filter" class="form-control" placeholder="Número do Inquérito"
      value="<?= htmlspecialchars($_GET['numero_filter'] ?? '') ?>">
    </div>
    <div class="col-md-4">
     <input type="text" name="indiciado_filter" class="form-control" placeholder="Indiciado"
      value="<?= htmlspecialchars($_GET['indiciado_filter'] ?? '') ?>">
    </div>
    <div class="col-md-4">
     <input type="date" name="data_inicio" class="form-control"
      value="<?= htmlspecialchars($_GET['data_inicio'] ?? '') ?>">
    </div>
    <div class="col-md-4">
     <input type="date" name="data_fim" class="form-control" value="<?= htmlspecialchars($_GET['data_fim'] ?? '') ?>">
    </div>
    <div class="col-md-4">
     <button type="submit" class="btn btn-success w-100"><i class="fas fa-search"></i> Filtrar</button>
    </div>
   </form>
  </div>




  <?php if (!empty($_SESSION['mensagem'])): ?>
  <div class="alert alert-info"><?= $_SESSION['mensagem']; unset($_SESSION['mensagem']); ?></div>
  <?php endif; ?>

  <div class="table-responsive">
   <table class="table table-bordered mt-3">

    <thead class="table-dark">
     <tr>
      <th>Número do Inquérito</th>
      <th>Indiciado</th>
      <th>Crime</th>
      <th>Nome da Vítima</th>
      <th>Data da Audiência</th>
      <th>Acordo</th>
      <th>Reparação</th>
      <th>Prestação Serviço</th>
      <th>Multa</th>
      <th>Restituição</th>
      <th>Ações</th>
     </tr>
    </thead>
    <tbody>
     <?php foreach ($anpps as $anpp): ?>
     <tr>
      <td><?= htmlspecialchars($anpp['numero_inquerito']) ?></td>
      <td><?= htmlspecialchars($anpp['indiciado']) ?></td>
      <td><?= htmlspecialchars($anpp['crime_nome']) ?></td>
      <td><?= htmlspecialchars($anpp['nome_vitima']) ?></td>
      <td><?= !empty($anpp['data_audiencia']) ? date("d/m/Y", strtotime($anpp['data_audiencia'])) : '-' ?>
      </td>

      <td>
       <?= (!empty($anpp['acordo_realizado']) && $anpp['acordo_realizado'] === "sim") ? "Sim" : "Não" ?>
      </td>





      <!-- Exibir Reparação da Vítima se houver -->
      <td>
       <?php 
                        if (!empty($anpp['valor_reparacao'])) {
                            echo "Sim - R$ " . number_format($anpp['valor_reparacao'], 2, ',', '.');
                        } else {
                            echo "Não";
                        }
                        ?>
      </td>

      <!-- Exibir Prestação de Serviço se houver -->
      <td>
       <?php 
                        if (!empty($anpp['tempo_servico'])) {
                            echo "Sim - " . htmlspecialchars($anpp['tempo_servico']) . " horas";
                        } else {
                            echo "Não";
                        }
                        ?>
      </td>

      <!-- Exibir Multa se houver -->
      <td>
       <?php 
                        if (!empty($anpp['valor_multa'])) {
                            echo "Sim - R$ " . number_format($anpp['valor_multa'], 2, ',', '.');
                        } else {
                            echo "Não";
                        }
                        ?>
      </td>

      <!-- Exibir Restituição se houver -->
      <td>
       <?= isset($anpp['restituicao']) ? htmlspecialchars($anpp['restituicao']) : '-' ?>
      </td>

      <td>
       <div class="d-flex flex-column align-items-center">
        <!-- Exibir (todos os perfis podem ver) -->
        <button type="button" class="btn btn-info btn-sm btn-action mb-1" data-bs-toggle="modal"
         data-bs-target="#modalInspecionar<?= $anpp['id'] ?>">
         <i class="fas fa-eye"></i> Exibir
        </button>

        <?php if ($perfil === 'administrador' || $perfil === 'cadastrador' || $perfil === 'cadastrador_consulta'): ?>
        <!-- Editar -->
        <a href="editar_anpp.php?id=<?= $anpp['id'] ?>" class="btn btn-warning btn-sm btn-action mb-1">
         <i class="fas fa-edit"></i> Editar
        </a>
        <?php endif; ?>

        <?php if ($perfil === 'administrador' || $perfil === 'cadastrador'): ?>
        <!-- Excluir -->
        <form action="../controllers/deletar_anpp.php" method="POST" style="display:inline;"
         onsubmit="return confirm('Tem certeza que deseja excluir este ANPP?');">
         <input type="hidden" name="id" value="<?= $anpp['id'] ?>">
         <button type="submit" class="btn btn-danger btn-sm btn-action mb-1">
          <i class="fas fa-trash"></i> Excluir
         </button>
        </form>
        <?php endif; ?>

        <!-- PDF (todos podem ver) -->
        <a href="../controllers/gerar_pdf_anpp.php?id=<?= $anpp['id'] ?>" target="_blank"
         class="btn btn-success btn-sm btn-action">
         <i class="fas fa-file-pdf"></i> PDF
        </a>
       </div>
      </td>



     </tr>
     <!-- Modal -->
     <div class="modal fade" id="modalInspecionar<?= $anpp['id'] ?>" tabindex="-1"
      aria-labelledby="modalLabel<?= $anpp['id'] ?>" aria-hidden="true">
      <div class="modal-dialog modal-lg">
       <div class="modal-content">
        <div class="modal-header bg-info text-white">
         <h5 class="modal-title" id="modalLabel<?= $anpp['id'] ?>">Detalhes do ANPP</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
         <p><strong>Número do Inquérito:</strong>
          <?= htmlspecialchars($anpp['numero_inquerito']) ?></p>
         <p><strong>Indiciado:</strong> <?= htmlspecialchars($anpp['indiciado']) ?></p>
         <p><strong>Crime:</strong> <?= htmlspecialchars($anpp['crime_nome']) ?></p>
         <p><strong>Data da Audiência:</strong>
          <?= !empty($anpp['data_audiencia']) ? date("d/m/Y", strtotime($anpp['data_audiencia'])) : '-' ?>
         </p>
         <p><strong>Acordo Realizado:</strong>
          <?= ($anpp['acordo_realizado'] === 'sim') ? 'Sim' : 'Não' ?></p>
         <p><strong>Reparação:</strong>
          <?= !empty($anpp['valor_reparacao']) ? 'R$ ' . number_format($anpp['valor_reparacao'], 2, ',', '.') : 'Não' ?>
         </p>
         <p><strong>Prestação de Serviço:</strong>
          <?= !empty($anpp['tempo_servico']) ? $anpp['tempo_servico'] . ' horas' : 'Não' ?>
         </p>
         <p><strong>Multa:</strong>
          <?= !empty($anpp['valor_multa']) ? 'R$ ' . number_format($anpp['valor_multa'], 2, ',', '.') : 'Não' ?>
         </p>
         <p><strong>Restituição:</strong>
          <?= !empty($anpp['restituicao']) ? htmlspecialchars($anpp['restituicao']) : 'Não' ?>
         </p>
        </div>
       </div>
      </div>
     </div>
     <?php endforeach; ?>
    </tbody>
   </table>
  </div>

  <!-- Paginação -->
  <nav>
   <ul class="pagination justify-content-center mt-4">
    <?php
  $max_links = 2; // Quantas páginas antes e depois da atual
  $start = max(1, $paginaAtual - $max_links);
  $end = min($totalPaginas, $paginaAtual + $max_links);


  $base_url = "?pagina=";
  $filtros = "";
  $filtros .= $search ? "&search=$search" : '';
  $filtros .= $advanced_search ? '&advanced_search=1' : '';
  $filtros .= $id_filter ? "&id_filter=$id_filter" : '';
  $filtros .= $date_filter ? "&date_filter=$date_filter" : '';
  $filtros .= $municipio_filter ? "&municipio_filter=$municipio_filter" : '';
  $filtros .= $bairro_filter ? "&bairro_filter=$bairro_filter" : '';

  // Primeira página
  if ($start > 1) {
    echo '<li class="page-item"><a class="page-link" href="' . $base_url . '1' . $filtros . '">1</a></li>';
    if ($start > 2) {
      echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
  }

  // Páginas do intervalo
  for ($i = $start; $i <= $end; $i++) {
    $active = ($i == $paginaAtual) ? 'active' : '';
    echo '<li class="page-item ' . $active . '"><a class="page-link" href="' . $base_url . $i . $filtros . '">' . $i . '</a></li>';
  }

  // Última página
if ($end < $totalPaginas) {
    if ($end < $totalPaginas - 1) {
        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
    echo '<li class="page-item"><a class="page-link" href="' . $base_url . $totalPaginass . $filtros . '">' . $totalPaginas . '</a></li>';
}

  ?>
   </ul>
  </nav>




  <div class="text-center mt-3 mb-5 text-muted">
   Página <?= $paginaAtual ?> de <?= $totalPaginas ?>,
   <?= count($anpps) ?> registro<?= count($anpps) != 1 ? 's' : '' ?> nesta página de um total de <?= $total ?>
   registros.
  </div>



 </div>

 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>