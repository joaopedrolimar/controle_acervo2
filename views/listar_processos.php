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

// Definições de paginação
$registros_por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_atual - 1) * $registros_por_pagina;

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

// Construção da Query SQL dinâmica com PDO
$sql = "SELECT processos.*, crimes.nome AS nome_crime 
        FROM processos 
        LEFT JOIN crimes ON processos.crime_id = crimes.id
        WHERE 1=1";

$params = [];

if (!empty($search)) {
    $sql .= " AND (
        processos.numero LIKE :search 
        OR crimes.nome LIKE :search
        OR processos.natureza LIKE :search
        OR processos.denunciado LIKE :search 
        OR processos.vitima LIKE :search 
        OR processos.local_municipio LIKE :search 
        OR processos.local_bairro LIKE :search 
        OR processos.sentenca LIKE :search 
        OR processos.status LIKE :search
    )";
    $params[':search'] = "%$search%";
}


if ($advanced_search) {
    if (!empty($id_filter)) {
        $sql .= " AND processos.id = :id_filter";
        $params[':id_filter'] = $id_filter;
    }
    if (!empty($date_filter)) {
        $sql .= " AND processos.data_denuncia = :date_filter";
        $params[':date_filter'] = $date_filter;
    }

    if (!empty($data_fato_inicio)) {
        $sql .= " AND processos.data_denuncia >= :data_fato_inicio";
        $params[':data_fato_inicio'] = $data_fato_inicio;
    }
    
    if (!empty($data_fato_fim)) {
        $sql .= " AND processos.data_denuncia <= :data_fato_fim";
        $params[':data_fato_fim'] = $data_fato_fim;
    }
    

    if (!empty($municipio_filter)) {
        $sql .= " AND processos.local_municipio LIKE :municipio_filter";
        $params[':municipio_filter'] = "%$municipio_filter%";
    }
    if (!empty($bairro_filter)) {
        $sql .= " AND processos.local_bairro LIKE :bairro_filter";
        $params[':bairro_filter'] = "%$bairro_filter%";
    }
    if (!empty($vitima_filter)) {
        $sql .= " AND processos.vitima LIKE :vitima_filter";
        $params[':vitima_filter'] = "%$vitima_filter%";
    }
    if (!empty($denunciado_filter)) {
        $sql .= " AND processos.denunciado LIKE :denunciado_filter";
        $params[':denunciado_filter'] = "%$denunciado_filter%";
    }
    if (!empty($sentenca_filter)) {
        $sql .= " AND processos.sentenca LIKE :sentenca_filter";
        $params[':sentenca_filter'] = "%$sentenca_filter%";
    }
    if (!empty($status_filter)) {
        $sql .= " AND processos.status LIKE :status_filter";
        $params[':status_filter'] = "%$status_filter%";
    }
    if (!empty($_GET['crime_filter'])) { // Verifica se há um filtro de crime
        $sql .= " AND crimes.nome LIKE :crime_filter";
        $params[':crime_filter'] = "%{$_GET['crime_filter']}%";
    }
}

// Contagem total de registros para a paginação (mantendo os mesmos filtros)
$sql_count = "SELECT COUNT(*) AS total 
              FROM processos 
              LEFT JOIN crimes ON processos.crime_id = crimes.id 
              WHERE 1=1";

if (!empty($search)) {
    $sql_count .= " AND (
        processos.numero LIKE :search 
        OR crimes.nome LIKE :search
        OR processos.natureza LIKE :search
        OR processos.denunciado LIKE :search 
        OR processos.vitima LIKE :search 
        OR processos.local_municipio LIKE :search 
        OR processos.local_bairro LIKE :search 
        OR processos.sentenca LIKE :search 
        OR processos.status LIKE :search
    )";
}

if ($advanced_search) {
    if (!empty($id_filter)) {
        $sql_count .= " AND processos.id = :id_filter";
    }
    if (!empty($date_filter)) {
        $sql_count .= " AND processos.data_denuncia = :date_filter";
    }

    if (!empty($data_fato_inicio)) {
        $sql_count .= " AND processos.data_denuncia >= :data_fato_inicio";
    }
    
    if (!empty($data_fato_fim)) {
        $sql_count .= " AND processos.data_denuncia <= :data_fato_fim";
    }
    

    if (!empty($municipio_filter)) {
        $sql_count .= " AND processos.local_municipio LIKE :municipio_filter";
    }
    if (!empty($bairro_filter)) {
        $sql_count .= " AND processos.local_bairro LIKE :bairro_filter";
    }
    if (!empty($vitima_filter)) {
        $sql_count .= " AND processos.vitima LIKE :vitima_filter";
    }
    if (!empty($denunciado_filter)) {
        $sql_count .= " AND processos.denunciado LIKE :denunciado_filter";
    }
    if (!empty($sentenca_filter)) {
        $sql_count .= " AND processos.sentenca LIKE :sentenca_filter";
    }
    if (!empty($status_filter)) {
        $sql_count .= " AND processos.status LIKE :status_filter";
    }
    if (!empty($_GET['crime_filter'])) {
        $sql_count .= " AND crimes.nome LIKE :crime_filter";
    }
}

// Prepara e executa a contagem
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

            <div class="col-md-3">
                <label for="data_fato_inicio" class="form-label">📅 Data Fato - Início</label>
                <input type="date" name="data_fato_inicio" class="form-control" value="<?= htmlspecialchars($_GET['data_fato_inicio'] ?? '') ?>">
            </div>

            <div class="col-md-3">
                <label for="data_fato_fim" class="form-label">📅 Data Fato - Fim</label>
                <input type="date" name="data_fato_fim" class="form-control" value="<?= htmlspecialchars($_GET['data_fato_fim'] ?? '') ?>">
            </div>

            <div class="col-md-3"><input type="text" name="municipio_filter" class="form-control" placeholder="Município"></div>
            <div class="col-md-3"><input type="text" name="bairro_filter" class="form-control" placeholder="Bairro"></div>
            <div class="col-md-3"><input type="text" name="vitima_filter" class="form-control" placeholder="Nome da Vítima"></div>
            <div class="col-md-3"><input type="text" name="denunciado_filter" class="form-control" placeholder="Denunciado"></div>
            <div class="col-md-3"><input type="text" name="sentenca_filter" class="form-control" placeholder="Sentença"></div>
            <div class="col-md-3"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Buscar</button></div>
        </form>
    </div>

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
        <td><?= htmlspecialchars($processo['nome_crime'] ?? 'Não informado') ?></td>

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