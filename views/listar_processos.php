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
$sql = "SELECT processos.*, 
               crimes.nome AS nome_crime, 
               municipios.nome AS nome_municipio, 
               bairros.nome AS nome_bairro 
        FROM processos 
        LEFT JOIN crimes ON processos.crime_id = crimes.id
        LEFT JOIN municipios ON processos.local_municipio = municipios.id
        LEFT JOIN bairros ON processos.local_bairro = bairros.id
        WHERE 1=1";


$params = [];

if (!empty($search)) {
    $search_normalizado = iconv('UTF-8', 'ASCII//TRANSLIT', mb_strtolower(trim($search)));

    $is_nao_ha = trim($search_normalizado) === 'nao ha';

    $sql .= " AND (";

    // Condições de LIKE
    if (!$is_nao_ha) {
        $sql .= "
            CAST(processos.id AS CHAR) LIKE :search
            OR processos.numero LIKE :search 
            OR crimes.nome LIKE :search
            OR processos.natureza LIKE :search
            OR processos.denunciado LIKE :search 
            OR COALESCE(processos.vitima, '') LIKE :search
            OR COALESCE(processos.local_municipio, '') LIKE :search 
            OR COALESCE(processos.local_bairro, '') LIKE :search 
            OR COALESCE(processos.sentenca, '') LIKE :search 
            OR COALESCE(processos.recursos, '') LIKE :search
            OR COALESCE(processos.status, '') LIKE :search
        ";
    }

    // Se buscar "não há", adiciona verificação de campos nulos ou vazios
    if ($is_nao_ha) {
        $sql .= "
            processos.vitima IS NULL OR TRIM(processos.vitima) = ''
            OR processos.recursos IS NULL OR TRIM(processos.recursos) = ''
            OR processos.status IS NULL OR TRIM(processos.status) = ''
        ";
    }

    $sql .= ")";

    if (!$is_nao_ha) {
        $params[':search'] = "%$search%";
    }
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
    $search_normalizado = iconv('UTF-8', 'ASCII//TRANSLIT', mb_strtolower(trim($search)));
    $is_nao_ha = trim($search_normalizado) === 'nao ha';

    $sql_count .= " AND (";

    if (!$is_nao_ha) {
        $sql_count .= "
            CAST(processos.id AS CHAR) LIKE :search
            OR processos.numero LIKE :search 
            OR crimes.nome LIKE :search
            OR processos.natureza LIKE :search
            OR processos.denunciado LIKE :search 
            OR COALESCE(processos.vitima, '') LIKE :search 
            OR COALESCE(processos.local_municipio, '') LIKE :search 
            OR COALESCE(processos.local_bairro, '') LIKE :search 
            OR COALESCE(processos.sentenca, '') LIKE :search 
            OR COALESCE(processos.recursos, '') LIKE :search
            OR COALESCE(processos.status, '') LIKE :search
        ";
    }

    if ($is_nao_ha) {
        $sql_count .= "
            processos.vitima IS NULL OR TRIM(processos.vitima) = ''
            OR processos.recursos IS NULL OR TRIM(processos.recursos) = ''
            OR processos.status IS NULL OR TRIM(processos.status) = ''
        ";
    }

    $sql_count .= ")";

    if (!$is_nao_ha) {
        $params[':search'] = "%$search%";
    }
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
        /* Mantém o tamanho padrão */
        text-align: center;
        /* Centraliza o conteúdo */
        display: inline-flex;
        /* Mantém alinhamento entre ícone e texto */
        align-items: center;
        /* Centraliza verticalmente */
        justify-content: center;
        /* Centraliza horizontalmente */
        white-space: nowrap;
        /* Impede que o texto quebre */
        margin: 3px;
        /* Adiciona um espaçamento entre os botões */
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
                    <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-home"></i> Início</a>
                    </li>
                    <li class="nav-item"><a class="nav-link active" href="listar_processos.php"><i
                                class="fas fa-list"></i> Listar Processos</a></li>

                    <?php if ($perfil === 'cadastrador' || $perfil === 'administrador'): ?>
                    <li class="nav-item"><a class="nav-link" href="cadastro_processo.php"><i class="fas fa-plus"></i>
                            Cadastrar Processos</a></li>
                    <?php endif; ?>

                    <!-- Novos itens para ANPP -->
                    <li class="nav-item">
                        <a class="nav-link" href="listar_anpp.php"><i class="fas fa-scale-balanced"></i> Listagem de
                            ANPPs</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="anpp.php"><i class="fas fa-file-circle-plus"></i> Cadastrar ANPP</a>
                    </li>
                    <!-- Fim dos novos itens -->

                    <?php if ($perfil === 'administrador'): ?>
                    <li class="nav-item"><a class="nav-link" href="gerenciar_usuarios.php"><i
                                class="fas fa-users-cog"></i> Gerenciar Usuários</a></li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($pagina_atual == 'atos.php') ? 'active' : '' ?>" href="atos.php">
                            <i class="fas fa-file-alt"></i> Atos
                        </a>
                    </li>


                    <li class="nav-item">
                        <a class="nav-link" href="log_atividades.php">
                            <i class="fas fa-history">
                            </i> Log de Atividades</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="cadastro_basico.php">
                            <i class="fas fa-address-book">
                            </i> Cadastro Básico</a>
                    </li>
                    <?php endif; ?>

                    <li class="nav-item"><a class="nav-link text-white" href="../controllers/logout.php"><i
                                class="fas fa-sign-out-alt"></i> Sair</a></li>
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
                <input type="text" name="search" class="form-control" placeholder="🔍 Pesquisa Simples"
                    value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
            </div>
        </form>

        <!-- Botão para Pesquisa Avançada -->
        <button class="btn btn-secondary mb-3" type="button" data-bs-toggle="collapse"
            data-bs-target="#advancedSearch">🔍 Pesquisa Avançada</button>

        <!-- Pesquisa Avançada -->
        <div class="collapse" id="advancedSearch">
            <form method="GET" class="row g-3">
                <input type="hidden" name="advanced_search" value="1">
                <div class="col-md-3"><input type="text" name="id_filter" class="form-control" placeholder="ID"></div>
                <div class="col-md-3"><input type="date" name="date_filter" class="form-control"></div>

                <div class="col-md-3 form-floating">
                    <input type="date" name="data_fato_inicio" class="form-control" id="data_fato_inicio"
                        value="<?= htmlspecialchars($_GET['data_fato_inicio'] ?? '') ?>">
                    <label for="data_fato_inicio">📅 Data Fato - Início</label>
                </div>

                <div class="col-md-3 form-floating">
                    <input type="date" name="data_fato_fim" class="form-control" id="data_fato_fim"
                        value="<?= htmlspecialchars($_GET['data_fato_fim'] ?? '') ?>">
                    <label for="data_fato_fim">📅 Data Fato - Fim</label>
                </div>

                <div class="col-md-3"><input type="text" name="municipio_filter" class="form-control"
                        placeholder="Município"></div>
                <div class="col-md-3"><input type="text" name="bairro_filter" class="form-control" placeholder="Bairro">
                </div>
                <div class="col-md-3"><input type="text" name="vitima_filter" class="form-control"
                        placeholder="Nome da Vítima"></div>
                <div class="col-md-3"><input type="text" name="denunciado_filter" class="form-control"
                        placeholder="Denunciado"></div>
                <div class="col-md-3"><input type="text" name="sentenca_filter" class="form-control"
                        placeholder="Sentença"></div>
                <div class="col-md-3"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i>
                        Buscar</button></div>
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
                        <th>Local do Fato</th>
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
                        <td><?= !empty($processo['data_denuncia']) ? date('d/m/Y', strtotime($processo['data_denuncia'])) : 'Não informado' ?>
                        </td>
                        <td><?= htmlspecialchars($processo['nome_crime'] ?? 'Não informado') ?></td>

                        <td><?= htmlspecialchars($processo['denunciado'] ?? 'Não informado') ?></td>
                        <td><?= htmlspecialchars($processo['vitima'] ?? 'Não há') ?></td>
                        <td><?= htmlspecialchars(($processo['nome_municipio'] ?? 'Não informado') . ' - ' . ($processo['nome_bairro'] ?? 'Não informado')) ?>
                        </td>

                        <td><?= htmlspecialchars($processo['sentenca'] ?? 'Não informado') ?></td>
                        <td><?= htmlspecialchars($processo['recursos'] ?? 'Não informado') ?></td>
                        <td><?= htmlspecialchars($processo['status'] ?? 'Não informado') ?></td>

                        <td>
                            <button class="btn btn-info btn-sm btn-action" data-bs-toggle="modal"
                                data-bs-target="#modal<?= $processo['id'] ?>">
                                <i class="fas fa-eye"></i> Exibir
                            </button>

                            <?php if ($perfil !== 'consultor'): ?>
                            <a href="../controllers/editar_processo.php?id=<?= $processo['id'] ?>"
                                class="btn btn-warning btn-sm btn-action">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="../controllers/deletar_processo.php?id=<?= $processo['id'] ?>"
                                class="btn btn-danger btn-sm btn-action"
                                onclick="return confirm('Tem certeza que deseja excluir?');">
                                <i class="fas fa-trash"></i> Excluir
                            </a>
                            <?php endif; ?>

                            <a target="_blank" href="../controllers/gerar_pdf.php?id=<?= $processo['id'] ?>"
                                class="btn btn-success btn-sm btn-action">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>

                        </td>
                    </tr>

                    <!-- Modal para Exibir Detalhes do Processo -->
                    <div class="modal fade" id="modal<?= $processo['id'] ?>" tabindex="-1"
                        aria-labelledby="modalLabel<?= $processo['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="modalLabel<?= $processo['id'] ?>">Detalhes do Processo
                                        #<?= htmlspecialchars($processo['numero']) ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Número do Processo:</strong> <?= htmlspecialchars($processo['numero']) ?>
                                    </p>
                                    <p><strong>Natureza:</strong> <?= htmlspecialchars($processo['natureza']) ?></p>
                                    <p><strong>Data da Denúncia:</strong>
                                        <?= !empty($processo['data_denuncia']) ? date('d/m/Y', strtotime($processo['data_denuncia'])) : 'Não informado' ?>
                                    </p>
                                    <p><strong>Crime:</strong>
                                        <?= htmlspecialchars($processo['nome_crime'] ?? 'Não informado') ?></p>

                                    <p><strong>Denunciado:</strong>
                                        <?= htmlspecialchars($processo['denunciado'] ?? 'Não informado') ?></p>
                                    <p><strong>Vítima:</strong> <?= htmlspecialchars($processo['vitima'] ?? 'Não há') ?>
                                    </p>
                                    <p><strong>Local do Fato:</strong>
                                        <?= htmlspecialchars(($processo['nome_municipio'] ?? 'Não informado') . ' - ' . ($processo['nome_bairro'] ?? 'Não informado')) ?>
                                    </p>


                                    <p><strong>Sentença:</strong>
                                        <?= htmlspecialchars($processo['sentenca'] ?? 'Não informado') ?></p>
                                    <p><strong>Recursos:</strong>
                                        <?= htmlspecialchars($processo['recursos'] ?? 'Não informado') ?></p>
                                    <p><strong>Status:</strong>
                                        <?= htmlspecialchars($processo['status'] ?? 'Não informado') ?></p>

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

<div class="text-center mt-3 mb-5 text-muted">
    Página <?= $pagina_atual ?> de <?= $total_paginas ?>, 
    <?= count($processos) ?> registros nesta página de um total de <?= $total_registros ?> registros.
</div>

    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>