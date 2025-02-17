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
    $sql .= " AND (numero LIKE :search OR crime LIKE :search OR denunciado LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($crime_filter)) {
    $sql .= " AND crime LIKE :crime_filter";
    $params[':crime_filter'] = "%$crime_filter%";
}

if (!empty($date_filter)) {
    $sql .= " AND data_denuncia = :date_filter";
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
    .table-responsive {
        overflow-x: auto;
    }
    </style>
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

                <?php if ($perfil === 'cadastrador' || $perfil === 'administrador'): ?>
                    <li class="nav-item"><a class="nav-link" href="cadastro_processo.php"><i class="fas fa-plus"></i> Cadastrar Processos</a></li>
                <?php endif; ?>

                <?php if ($perfil === 'administrador'): ?>
                    <li class="nav-item"><a class="nav-link" href="gerenciar_usuarios.php"><i class="fas fa-users-cog"></i> Gerenciar Usuários</a></li>
                    <li class="nav-item"><a class="nav-link" href="log_atividades.php"><i class="fas fa-history"></i> Log de Atividades</a></li>
                <?php endif; ?>

                <li class="nav-item"><a class="nav-link text-white" href="../controllers/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Conteúdo -->
<div class="container mt-4">
    <h2 class="text-center"><i class="fas fa-folder-open"></i> Acervo Processual</h2>

    <!-- Filtros e Pesquisa -->
    <form class="row g-3 mb-3" method="GET">
        <div class="col-md-4 col-12">
            <input type="text" name="search" class="form-control" placeholder="🔍 Pesquisar Número, Crime ou Denunciado"
                value="<?= htmlspecialchars($search) ?>">
        </div>

        <div class="col-md-3 col-12">
            <input type="text" name="crime_filter" class="form-control" placeholder="⚖️ Filtrar por Crime"
                value="<?= htmlspecialchars($crime_filter) ?>">
        </div>

        <div class="col-md-2 col-6">
            <input type="date" name="date_filter" class="form-control"
                value="<?= htmlspecialchars($date_filter) ?>">
        </div>

        <div class="col-md-2 col-6">
            <select name="filter" class="form-control">
                <option value="">📌 Filtrar por Status</option>
                <option value="Cadastrado" <?= $filter == "Cadastrado" ? "selected" : "" ?>>Cadastrado</option>
                <option value="Finalizado" <?= $filter == "Finalizado" ? "selected" : "" ?>>Finalizado</option>
            </select>
        </div>

        <div class="col-md-1 col-12">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i></button>
        </div>
    </form>

    <!-- Tabela Responsiva -->
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Número</th>
                    <th>Natureza</th>
                    <th>Data da Denúncia</th>
                    <th>Crime</th>
                    <th>Denunciado</th>
                    <th>Vítima</th>
                    <th>Local do Crime</th>
                    <th>Sentença</th>
                    <th>Recursos</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
    <?php foreach ($processos as $processo): ?>
    <tr>
        <td><?= $processo['id'] ?></td>
        <td><?= htmlspecialchars($processo['numero'] ?? 'Não informado') ?></td>
        <td><?= htmlspecialchars($processo['natureza'] ?? 'Não informado') ?></td>
        <td><?= !empty($processo['data_denuncia']) ? date('d/m/Y', strtotime($processo['data_denuncia'])) : 'Não informado' ?></td>
        <td><?= htmlspecialchars($processo['crime'] ?? 'Não informado') ?></td>
        <td><?= htmlspecialchars($processo['denunciado'] ?? 'Não informado') ?></td>
        <td><?= htmlspecialchars($processo['vitima'] ?? 'Não há') ?></td>
        <td><?= htmlspecialchars(($processo['local_municipio'] ?? 'Não informado') . ' - ' . ($processo['local_bairro'] ?? 'Não informado')) ?></td>
        <td><?= htmlspecialchars($processo['sentenca'] ?? 'Não informado') ?></td>
        <td><?= htmlspecialchars($processo['recursos'] ?? 'Não informado') ?></td>
        <td><?= htmlspecialchars($processo['status'] ?? 'Não informado') ?></td>
        <td>
            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modal<?= $processo['id'] ?>">
                <i class="fas fa-eye"></i> Exibir
            </button>

            <?php if ($perfil !== 'consultor'): ?>
                <a href="../controllers/editar_processo.php?id=<?= $processo['id'] ?>" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="../controllers/deletar_processo.php?id=<?= $processo['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Tem certeza que deseja excluir?');">
                    <i class="fas fa-trash"></i> Excluir
                </a>
            <?php endif; ?>
        </td>
    </tr>

    <!-- Modal para Exibir Detalhes do Processo -->
    <div class="modal fade" id="modal<?= $processo['id'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $processo['id'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalLabel<?= $processo['id'] ?>">Detalhes do Processo #<?= htmlspecialchars($processo['numero']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Número do Processo:</strong> <?= htmlspecialchars($processo['numero']) ?></p>
                    <p><strong>Natureza:</strong> <?= htmlspecialchars($processo['natureza']) ?></p>
                    <p><strong>Data da Denúncia:</strong> <?= !empty($processo['data_denuncia']) ? date('d/m/Y', strtotime($processo['data_denuncia'])) : 'Não informado' ?></p>
                    <p><strong>Crime:</strong> <?= htmlspecialchars($processo['crime'] ?? 'Não informado') ?></p>
                    <p><strong>Denunciado:</strong> <?= htmlspecialchars($processo['denunciado'] ?? 'Não informado') ?></p>
                    <p><strong>Vítima:</strong> <?= htmlspecialchars($processo['vitima'] ?? 'Não há') ?></p>
                    <p><strong>Local do Crime:</strong> <?= htmlspecialchars(($processo['local_municipio'] ?? 'Não informado') . ' - ' . ($processo['local_bairro'] ?? 'Não informado')) ?></p>
                    <p><strong>Sentença:</strong> <?= htmlspecialchars($processo['sentenca'] ?? 'Não informado') ?></p>
                    <p><strong>Recursos:</strong> <?= htmlspecialchars($processo['recursos'] ?? 'Não informado') ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($processo['status'] ?? 'Não informado') ?></p>
                </div>
            </div>
        </div>
    </div>

    <?php endforeach; ?>
</tbody>

        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

