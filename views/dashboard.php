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

// Consultas para estatísticas
$total_processos = $pdo->query("SELECT COUNT(*) FROM processos")->fetchColumn();
$total_finalizados = $pdo->query("SELECT COUNT(*) FROM processos WHERE status = 'Finalizado'")->fetchColumn();
$total_mes = $pdo->query("SELECT COUNT(*) FROM processos WHERE MONTH(data_inicio) = MONTH(CURRENT_DATE()) AND YEAR(data_inicio) = YEAR(CURRENT_DATE())")->fetchColumn();

// Crimes mais comuns
$crimes = $pdo->query("SELECT crime, COUNT(*) as total FROM processos GROUP BY crime ORDER BY total DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Processos recebidos por mês
$processos_por_mes = $pdo->query("SELECT MONTH(data_inicio) as mes, COUNT(*) as total FROM processos WHERE YEAR(data_inicio) = YEAR(CURRENT_DATE()) GROUP BY MONTH(data_inicio) ORDER BY mes")->fetchAll(PDO::FETCH_ASSOC);

// Processos finalizados por mês
$finalizados_por_mes = $pdo->query("SELECT MONTH(data_inicio) as mes, COUNT(*) as total FROM processos WHERE status = 'Finalizado' AND YEAR(data_inicio) = YEAR(CURRENT_DATE()) GROUP BY MONTH(data_inicio) ORDER BY mes")->fetchAll(PDO::FETCH_ASSOC);

// Calcula o tempo de duração dos processos finalizados
$tempo_processos = $pdo->query("
    SELECT numero, DATEDIFF(MAX(data_inicio), MIN(data_inicio)) as dias
    FROM processos 
    WHERE status = 'Finalizado'
    GROUP BY numero
")->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <!-- LOGO -->
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                <img src="../public/img/logoPGJ.png" alt="Logo" width="180" height="80" class="me-2">
            </a>

            <!-- Botão para mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Itens do menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Início</a></li>
                    <li class="nav-item"><a class="nav-link" href="listar_processos.php">Listar Processos</a></li>
                    <li class="nav-item"><a class="nav-link" href="cadastro_processo.php">Cadastrar Processos</a></li>



                    <?php if ($_SESSION['usuario_perfil'] === 'administrador'): ?>
                    <li class="nav-item"><a class="nav-link" href="gerenciar_usuarios.php">Gerenciar Usuários</a></li>
                    <li class="nav-item"><a class="nav-link" href="log_atividades.php">Log de Atividades</a></li>
                <?php endif; ?>

                    <li class="nav-item"><a class="nav-link" href="../controllers/logout.php">Sair</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Conteúdo Principal -->
    <div class="container mt-4">
        <h3 class="text-center mb-4">Bem-vindo, <?= htmlspecialchars($nome_usuario) ?>!</h3>

        <!-- Cards de Resumo -->
        <div class="row text-center">
            <div class="col-md-4">
                <div class="card bg-primary text-white mb-3">
                    <div class="card-body">
                        <h5>Total de Processos</h5>
                        <h3><?= $total_processos ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white mb-3">
                    <div class="card-body">
                        <h5>Processos Finalizados</h5>
                        <h3><?= $total_finalizados ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning text-white mb-3">
                    <div class="card-body">
                        <h5>Recebidos no Mês</h5>
                        <h3><?= $total_mes ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row mt-4">
            <!-- Crimes Mais Comuns -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">Crimes Mais Comuns</div>
                    <div class="card-body">
                        <canvas id="chartCrimes"></canvas>
                    </div>
                </div>
            </div>

            <!-- Processos por Mês -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">Processos por Mês</div>
                    <div class="card-body">
                        <canvas id="chartProcessosMes"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Processos Finalizados por Mês -->
            <div class="col-md-6 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">Processos Finalizados por Mês</div>
                    <div class="card-body">
                        <canvas id="chartFinalizadosMes"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tempo Médio de Duração dos Processos -->
<div class="row mt-4">
    <div class="col-md-6 mx-auto">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">Tempo de Duração dos Processos Finalizados</div>
            <div class="card-body">
                <ul class="list-group">
                    <?php if (!empty($tempo_processos)): ?>
                        <?php foreach ($tempo_processos as $p): ?>
                            <li class="list-group-item">
                                Processo <strong><?= htmlspecialchars($p['numero']) ?></strong> demorou 
                                <strong><?= $p['dias'] ?></strong> dias para ser finalizado.
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item">Nenhum processo finalizado ainda.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Scripts dos Gráficos -->
    <script>
        // Crimes Mais Comuns
        new Chart(document.getElementById("chartCrimes"), {
            type: "bar",
            data: {
                labels: [<?php foreach ($crimes as $c) { echo "'" . $c['crime'] . "',"; } ?>],
                datasets: [{
                    label: "Número de Processos",
                    data: [<?php foreach ($crimes as $c) { echo $c['total'] . ","; } ?>],
                    backgroundColor: "blue"
                }]
            }
        });

        // Processos por Mês
        new Chart(document.getElementById("chartProcessosMes"), {
            type: "line",
            data: {
                labels: [<?php foreach ($processos_por_mes as $p) { echo "'Mês " . $p['mes'] . "',"; } ?>],
                datasets: [{
                    label: "Processos Recebidos",
                    data: [<?php foreach ($processos_por_mes as $p) { echo $p['total'] . ","; } ?>],
                    backgroundColor: "green"
                }]
            }
        });

        // Processos Finalizados por Mês
        new Chart(document.getElementById("chartFinalizadosMes"), {
            type: "line",
            data: {
                labels: [<?php foreach ($finalizados_por_mes as $f) { echo "'Mês " . $f['mes'] . "',"; } ?>],
                datasets: [{
                    label: "Processos Finalizados",
                    data: [<?php foreach ($finalizados_por_mes as $f) { echo $f['total'] . ","; } ?>],
                    backgroundColor: "red"
                }]
            }
        });
    </script>
</body>

</html>
