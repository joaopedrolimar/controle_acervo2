<!--controle_acervo/views/listar_processos.php-->
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



// Definições de paginação
$registros_por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_atual - 1) * $registros_por_pagina;

// Captura os filtros do formulário
$search = $_GET['search'] ?? '';
$advanced_search = isset($_GET['advanced_search']);
$id_filter = $_GET['id_filter'] ?? '';
$date_filter = $_GET['date_filter'] ?? '';
$municipio_filter = $_GET['municipio_filter'] ?? '';
$bairro_filter = $_GET['bairro_filter'] ?? '';
$vitima_filter = $_GET['vitima_filter'] ?? '';
$denunciado_filter = $_GET['denunciado_filter'] ?? '';
$sentenca_filter = $_GET['sentenca_filter'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';

// Construção da Query SQL dinâmica com PDO
$sql = "SELECT * FROM processos WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (numero LIKE :search OR crime LIKE :search OR denunciado LIKE :search OR vitima LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($advanced_search) {
    if (!empty($id_filter)) {
        $sql .= " AND id = :id_filter";
        $params[':id_filter'] = $id_filter;
    }
    if (!empty($date_filter)) {
        $sql .= " AND data_denuncia = :date_filter";
        $params[':date_filter'] = $date_filter;
    }
    if (!empty($municipio_filter)) {
        $sql .= " AND local_municipio LIKE :municipio_filter";
        $params[':municipio_filter'] = "%$municipio_filter%";
    }
    if (!empty($bairro_filter)) {
        $sql .= " AND local_bairro LIKE :bairro_filter";
        $params[':bairro_filter'] = "%$bairro_filter%";
    }
    if (!empty($vitima_filter)) {
        $sql .= " AND vitima LIKE :vitima_filter";
        $params[':vitima_filter'] = "%$vitima_filter%";
    }
    if (!empty($denunciado_filter)) {
        $sql .= " AND denunciado LIKE :denunciado_filter";
        $params[':denunciado_filter'] = "%$denunciado_filter%";
    }
    if (!empty($sentenca_filter)) {
        $sql .= " AND sentenca LIKE :sentenca_filter";
        $params[':sentenca_filter'] = "%$sentenca_filter%";
    }
}

// Contagem total de registros para a paginação
$sql_count = str_replace("SELECT *", "SELECT COUNT(*) AS total", $sql);
$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params);
$total_registros = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Adiciona o LIMIT para paginação
$sql .= " LIMIT $offset, $registros_por_pagina";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$processos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Processos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #900020;">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
            <img src="../public/img/logoPGJ.png" alt="Logo" width="180" height="80" class="me-2">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-home"></i> Início</a></li>
                <li class="nav-item"><a class="nav-link active" href="listar_processos.php"><i class="fas fa-list"></i> Listar Processos</a></li>
                <li class="nav-item"><a class="nav-link" href="../controllers/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Conteúdo -->
<div class="container mt-4">
    <h2 class="text-center"><i class="fas fa-folder-open"></i> Acervo Processual</h2>

    <!-- Pesquisa Simples -->
    <form method="GET">
        <div class="input-group mb-3">
            <input type="text" name="search" class="form-control" placeholder="🔍 Pesquisa Simples" value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
        </div>
    </form>

    <!-- Botão para Pesquisa Avançada -->
    <button class="btn btn-secondary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#advancedSearch">🔍 Pesquisa Avançada</button>

    <!-- Pesquisa Avançada -->
    <div class="collapse" id="advancedSearch">
        <form method="GET" class="row g-3">
            <input type="hidden" name="advanced_search" value="1">
            <div class="col-md-3"><input type="text" name="id_filter" class="form-control" placeholder="ID"></div>
            <div class="col-md-3"><input type="date" name="date_filter" class="form-control"></div>
            <div class="col-md-3"><input type="text" name="municipio_filter" class="form-control" placeholder="Município"></div>
            <div class="col-md-3"><input type="text" name="bairro_filter" class="form-control" placeholder="Bairro"></div>
            <div class="col-md-3"><input type="text" name="vitima_filter" class="form-control" placeholder="Nome da Vítima"></div>
            <div class="col-md-3"><input type="text" name="denunciado_filter" class="form-control" placeholder="Denunciado"></div>
            <div class="col-md-3"><input type="text" name="sentenca_filter" class="form-control" placeholder="Sentença"></div>
            <div class="col-md-3"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Buscar</button></div>
        </form>
    </div>

    <!-- Paginação -->
    <nav>
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="page-item <?= ($i == $pagina_atual) ? 'active' : '' ?>">
                    <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>