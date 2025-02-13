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
    <style>
    /* Para rolagem horizontal na tabela em telas pequenas */
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
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Início</a></li>
                <li class="nav-item"><a class="nav-link" href="listar_processos.php">Listar Processos</a></li>

                <?php if ($perfil === 'cadastrador' || $perfil === 'administrador'): ?>
                    <li class="nav-item"><a class="nav-link" href="cadastro_processo.php">Cadastrar Processos</a></li>
                <?php endif; ?>

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
    <h2 class="text-center">Acervo Processual</h2>

    <!-- Filtros e Pesquisa -->
    <form class="row g-3 mb-3" method="GET">
        <div class="col-md-4 col-12">
            <input type="text" name="search" class="form-control" placeholder="Pesquisar por Número, Crime ou Denunciado"
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
                    <td><?= htmlspecialchars($processo['numero']) ?></td>
                    <td><?= htmlspecialchars($processo['natureza']) ?></td>
                    <td><?= date('d/m/Y', strtotime($processo['data_denuncia'])) ?></td>
                    <td><?= htmlspecialchars($processo['crime']) ?></td>
                    <td><?= htmlspecialchars($processo['denunciado']) ?></td>
                    <td><?= htmlspecialchars($processo['vitima'] ?? 'Não há') ?></td>
                    <td><?= htmlspecialchars($processo['local_municipio']) ?> - <?= htmlspecialchars($processo['local_bairro']) ?></td>
                    <td>
                        <?= htmlspecialchars($processo['sentenca']) ?>
                        <?php if (!empty($processo['data_sentenca'])): ?>
                            (<?= date('d/m/Y', strtotime($processo['data_sentenca'])) ?>)
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($processo['recursos']) ?></td>
                    <td><?= htmlspecialchars($processo['status']) ?></td>
                    <td>
                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modal<?= $processo['id'] ?>">Exibir</button>
                        <?php if ($perfil !== 'consultor'): ?>
                            <a href="../controllers/editar_processo.php?id=<?= $processo['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="../controllers/deletar_processo.php?id=<?= $processo['id'] ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Tem certeza que deseja excluir?');">Excluir</a>
                        <?php endif; ?>
                    </td>
                </tr>

<!-- Modal para Exibir Detalhes -->
<div class="modal fade" id="modal<?= $processo['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detalhes do Processo #<?= $processo['numero'] ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Número do Processo:</strong> <?= htmlspecialchars($processo['numero']) ?></p>
                <p><strong>Natureza:</strong> <?= htmlspecialchars($processo['natureza']) ?></p>
                <p><strong>Data da Denúncia:</strong> <?= date('d/m/Y', strtotime($processo['data_denuncia'])) ?></p>
                <p><strong>Crime:</strong> <?= htmlspecialchars($processo['crime']) ?></p>
                <?php if (!empty($processo['outro_crime'])): ?>
                    <p><strong>Outro Crime:</strong> <?= htmlspecialchars($processo['outro_crime']) ?></p>
                <?php endif; ?>
                <p><strong>Denunciado:</strong> <?= htmlspecialchars($processo['denunciado']) ?></p>
                <p><strong>Vítima:</strong> <?= htmlspecialchars($processo['vitima'] ?? 'Não há') ?></p>
                <p><strong>Local do Crime:</strong> <?= htmlspecialchars($processo['local_municipio']) ?> - <?= htmlspecialchars($processo['local_bairro']) ?></p>
                <p><strong>Sentença:</strong> <?= htmlspecialchars($processo['sentenca']) ?></p>
                <?php if (!empty($processo['outra_sentenca'])): ?>
                    <p><strong>Outra Sentença:</strong> <?= htmlspecialchars($processo['outra_sentenca']) ?></p>
                <?php endif; ?>
                <?php if (!empty($processo['data_sentenca'])): ?>
                    <p><strong>Data da Sentença:</strong> <?= date('d/m/Y', strtotime($processo['data_sentenca'])) ?></p>
                <?php endif; ?>
                <p><strong>Recursos:</strong> <?= htmlspecialchars($processo['recursos']) ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars($processo['status']) ?></p>
            </div>
        </div>
    </div>
</div>


                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Bootstrap Bundle com Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
