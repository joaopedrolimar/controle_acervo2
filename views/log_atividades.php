<?php
session_start();
require_once "../config/conexao.php";
global $pdo;

// Verifica se o usuário está logado e se é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_perfil'] !== 'administrador') {
    $_SESSION['mensagem'] = "Acesso negado!";
    header("Location: dashboard.php");
    exit();
}

// Busca os logs no banco de dados
$sql = "SELECT logs.*, usuarios.nome AS usuario_nome 
        FROM logs 
        JOIN usuarios ON logs.usuario_id = usuarios.id
        ORDER BY logs.data_hora DESC";
$stmt = $pdo->query($sql);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log de Atividades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
                    <li class="nav-item"><a class="nav-link" href="gerenciar_usuarios.php">Gerenciar Usuários</a></li>
                    <li class="nav-item"><a class="nav-link active" href="log_atividades.php">Log de Atividades</a></li>
                    <li class="nav-item"><a class="nav-link" href="../controllers/logout.php">Sair</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Conteúdo -->
    <div class="container mt-4">
        <h2 class="text-center">Log de Atividades</h2>

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
        // Converte JSON armazenado no banco para array associativo
        $valores_anteriores = !empty($log['valores_anteriores']) ? json_decode($log['valores_anteriores'], true) : null;
        $valores_novos = !empty($log['valores_novos']) ? json_decode($log['valores_novos'], true) : null;

        // Pega o número do processo (se houver)
        $numero_processo = $valores_novos['numero'] ?? $valores_anteriores['numero'] ?? 'N/A';
    ?>
    <tr>
        <td><?= htmlspecialchars($log['usuario_nome']) ?></td>
        <td><?= htmlspecialchars($log['acao']) ?> (Processo: <?= htmlspecialchars($numero_processo) ?>)</td>
        <td><?= htmlspecialchars($log['tabela_afetada']) ?></td>
        <td><?= htmlspecialchars($log['registro_id']) ?></td>
        <td><?= nl2br(htmlspecialchars($log['valores_anteriores'] ?? 'N/A')) ?></td>
        <td><?= nl2br(htmlspecialchars($log['valores_novos'] ?? 'N/A')) ?></td>
        <td><?= date("d/m/Y H:i:s", strtotime($log['data_hora'])) ?></td>
    </tr>
    <?php endforeach; ?>
</tbody>


            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
