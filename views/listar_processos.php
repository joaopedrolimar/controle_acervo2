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

// Define o perfil do usuário logado
$perfil = $_SESSION['usuario_perfil'] ?? '';

// Captura os filtros do formulário
$search = isset($_GET['search']) ? $_GET['search'] : '';
$crime_filter = isset($_GET['crime_filter']) ? $_GET['crime_filter'] : '';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

// Construção da Query SQL dinâmica com PDO
$sql = "SELECT * FROM processos WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (numero LIKE :search OR crime LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($crime_filter)) {
    $sql .= " AND crime LIKE :crime_filter";
    $params[':crime_filter'] = "%$crime_filter%";
}

if (!empty($date_filter)) {
    $sql .= " AND data_inicio = :date_filter";
    $params[':date_filter'] = $date_filter;
}

if (!empty($filter)) {
    $sql .= " AND status = :filter";
    $params[':filter'] = $filter;
}

// Executa a consulta com PDO
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
    <style>
    /* Para rolagem horizontal na tabela em telas pequenas */
    .table-responsive {
        overflow-x: auto;
    }
    </style>
</head>

<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                <img src="../public/img/logoPGJ.png" alt="Logo" width="180" height="80" class="me-2">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Início</a></li>
                    <li class="nav-item"><a class="nav-link" href="listar_processos.php">Listar Processos</a></li>
                    <li class="nav-item"><a class="nav-link" href="cadastro_processo.php">Cadastrar Processos</a></li>

                    <?php if ($perfil === 'administrador'): ?>
                        <li class="nav-item"><a class="nav-link" href="gerenciar_usuarios.php">Gerenciar Usuários</a></li>
                        <li class="nav-item"><a class="nav-link" href="log_atividades.php">Log de Atividades</a></li>
                    <?php endif; ?>

                    <li class="nav-item"><a class="nav-link" href="../controllers/logout.php">Sair</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Conteúdo -->
    <div class="container mt-4">
        <h2 class="text-center">Lista de Processos</h2>

        <!-- Filtros e Pesquisa -->
        <form class="row g-3 mb-3" method="GET">
            <div class="col-md-4 col-12">
                <input type="text" name="search" class="form-control" placeholder="Pesquisar por Número ou Crime"
                    value="<?= htmlspecialchars($search) ?>">
            </div>

            <div class="col-md-3 col-12">
                <input type="text" name="crime_filter" class="form-control" placeholder="Filtrar por Tipo de Crime"
                    value="<?= htmlspecialchars($crime_filter) ?>">
            </div>

            <div class="col-md-2 col-6">
                <input type="date" name="date_filter" class="form-control"
                    value="<?= htmlspecialchars($date_filter) ?>">
            </div>

            <div class="col-md-2 col-6">
                <select name="filter" class="form-control">
                    <option value="">Filtrar por Status</option>
                    <option value="Cadastrado" <?= $filter == "Cadastrado" ? "selected" : "" ?>>Cadastrado</option>
                    <option value="Finalizado" <?= $filter == "Finalizado" ? "selected" : "" ?>>Finalizado</option>
                </select>
            </div>

            <div class="col-md-1 col-12">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </form>

        <!-- Tabela Responsiva -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Número</th>
                        <th>Crime</th>
                        <th>Data Início</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($processos as $processo): ?>
                    <tr>
                        <td><?= $processo['id'] ?></td>
                        <td><?= htmlspecialchars($processo['numero']) ?></td>
                        <td><?= htmlspecialchars($processo['crime']) ?></td>
                        <td><?= date('d/m/Y', strtotime($processo['data_inicio'])) ?></td>
                        <td><?= htmlspecialchars($processo['status']) ?></td>
                        <td>
                            <?php if ($perfil !== 'consultor'): ?>
                                <a href="editar_processo.php?id=<?= $processo['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="../controllers/deletar_processo.php?id=<?= $processo['id'] ?>" class="btn btn-danger btn-sm"
                                   onclick="return confirm('Tem certeza que deseja excluir?');">Excluir</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
