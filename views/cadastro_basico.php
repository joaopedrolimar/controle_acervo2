<!--/controle_acervo/views/cadastro_basico.php-->

<?php
session_start();
require_once "../config/conexao.php";
global $pdo;

$perfil = $_SESSION['usuario_perfil'] ?? '';

$pagina_atual = basename($_SERVER['PHP_SELF']);

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Verifica se o usuário é administrador
if ($_SESSION['usuario_perfil'] !== 'administrador') {
    $_SESSION['mensagens'][] = "Acesso negado! Apenas administradores podem acessar esta página.";
    header("Location: dashboard.php");
    exit();
}

// Array para armazenar mensagens de erro/sucesso
$_SESSION['mensagens'] = $_SESSION['mensagens'] ?? [];

// Adicionar novo município
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_municipio'])) {
    $novo_municipio = trim($_POST['novo_municipio']);
    if (!empty($novo_municipio)) {
        // Verifica se o município já existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM municipios WHERE nome = :nome");
        $stmt->bindParam(':nome', $novo_municipio, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['mensagens'][] = "❌ Este município já está cadastrado.";
        } else {
            // Insere novo município
            $stmt = $pdo->prepare("INSERT INTO municipios (nome) VALUES (:nome)");
            $stmt->bindParam(':nome', $novo_municipio, PDO::PARAM_STR);
            $stmt->execute();
            $_SESSION['mensagens'][] = "✅ Município adicionado com sucesso!";
        }
    }
}

// Adicionar novo bairro
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_bairro'])) {
    $novo_bairro = trim($_POST['novo_bairro']);
    $municipio_id = trim($_POST['municipio_id']);
    if (!empty($novo_bairro) && !empty($municipio_id)) {
        // Verifica se o bairro já existe no município
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bairros WHERE nome = :nome AND municipio_id = :municipio_id");
        $stmt->bindParam(':nome', $novo_bairro, PDO::PARAM_STR);
        $stmt->bindParam(':municipio_id', $municipio_id, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['mensagens'][] = "❌ Este bairro já está cadastrado neste município.";
        } else {
            // Insere novo bairro
            $stmt = $pdo->prepare("INSERT INTO bairros (nome, municipio_id) VALUES (:nome, :municipio_id)");
            $stmt->bindParam(':nome', $novo_bairro, PDO::PARAM_STR);
            $stmt->bindParam(':municipio_id', $municipio_id, PDO::PARAM_INT);
            $stmt->execute();
            $_SESSION['mensagens'][] = "✅ Bairro adicionado com sucesso!";
        }
    }
}

// Adicionar novo crime
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_crime'])) {
    $novo_crime = trim($_POST['novo_crime']);
    if (!empty($novo_crime)) {
        // Verifica se o crime já existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM crimes WHERE nome = :nome");
        $stmt->bindParam(':nome', $novo_crime, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['mensagens'][] = "❌ Este crime já está cadastrado.";
        } else {
            // Insere novo crime
            $stmt = $pdo->prepare("INSERT INTO crimes (nome) VALUES (:nome)");
            $stmt->bindParam(':nome', $novo_crime, PDO::PARAM_STR);
            $stmt->execute();
            $_SESSION['mensagens'][] = "✅ Crime adicionado com sucesso!";
        }
    }
}

// Adicionar novo crime ANPP
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_crime_anpp'])) {
    $novo_crime_anpp = trim($_POST['novo_crime_anpp']);
    if (!empty($novo_crime_anpp)) {
        // Verifica se o crime ANPP já existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM crimes_anpp WHERE nome = :nome");
        $stmt->bindParam(':nome', $novo_crime_anpp, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['mensagens'][] = "❌ Este crime ANPP já está cadastrado.";
        } else {
            // Insere novo crime ANPP
            $stmt = $pdo->prepare("INSERT INTO crimes_anpp (nome) VALUES (:nome)");
            $stmt->bindParam(':nome', $novo_crime_anpp, PDO::PARAM_STR);
            $stmt->execute();
            $_SESSION['mensagens'][] = "✅ Crime ANPP adicionado com sucesso!";
        }
    }
}


// Buscar municípios, bairros e crimes para listagem
$municipios = $pdo->query("SELECT * FROM municipios ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
$bairros = $pdo->query("SELECT bairros.id, bairros.nome, municipios.nome AS municipio FROM bairros JOIN municipios ON bairros.municipio_id = municipios.id ORDER BY municipios.nome ASC, bairros.nome ASC")->fetchAll(PDO::FETCH_ASSOC);
$crimes = $pdo->query("SELECT * FROM crimes ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
$crimes_anpp = $pdo->query("SELECT * FROM crimes_anpp ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Básico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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

                    <li class="nav-item">
                        <a class="nav-link <?= ($pagina_atual == 'dashboard.php') ? 'active' : '' ?>"
                            href="dashboard.php">
                            <i class="fas fa-home"></i> Início
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($pagina_atual == 'listar_processos.php') ? 'active' : '' ?>"
                            href="listar_processos.php">
                            <i class="fas fa-list"></i> Listar Processos
                        </a>
                    </li>

                    <?php if ($perfil === 'cadastrador' || $perfil === 'administrador'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($pagina_atual == 'cadastro_processo.php') ? 'active' : '' ?>"
                            href="cadastro_processo.php">
                            <i class="fas fa-plus"></i> Cadastrar Processos
                        </a>
                    </li>
                    <?php endif; ?>

                    <!-- Novos itens de ANPP -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($pagina_atual == 'listar_anpp.php') ? 'active' : '' ?>"
                            href="listar_anpp.php">
                            <i class="fas fa-scale-balanced"></i> Listagem de ANPPs
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($pagina_atual == 'anpp.php') ? 'active' : '' ?>" href="anpp.php">
                            <i class="fas fa-file-circle-plus"></i> Cadastrar ANPP
                        </a>
                    </li>
                    <!-- Fim dos itens de ANPP -->

                    <?php if ($perfil === 'administrador'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($pagina_atual == 'gerenciar_usuarios.php') ? 'active' : '' ?>"
                            href="gerenciar_usuarios.php">
                            <i class="fas fa-users-cog"></i> Gerenciar Usuários
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($pagina_atual == 'atos.php') ? 'active' : '' ?>" href="atos.php">
                            <i class="fas fa-file-alt"></i> Atos
                        </a>
                    </li>


                    <li class="nav-item">
                        <a class="nav-link <?= ($pagina_atual == 'log_atividades.php') ? 'active' : '' ?>"
                            href="log_atividades.php">
                            <i class="fas fa-history"></i> Log de Atividades
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($pagina_atual == 'cadastro_basico.php') ? 'active' : '' ?>"
                            href="cadastro_basico.php">
                            <i class="fas fa-address-book"></i> Cadastro Básico
                        </a>
                    </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link text-white" href="../controllers/logout.php">
                            <i class="fas fa-sign-out-alt"></i> Sair
                        </a>
                    </li>

                </ul>
            </div>
        </div>
    </nav>


    <!-- Conteúdo Principal -->
    <div class="container mt-5">
        <h2 class="text-center"><i class="fas fa-address-book"></i> Cadastro Básico</h2>
        <div class="row">
            <!-- Formulários -->
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h5>Adicionar Cadastro</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($_SESSION['mensagens'])): ?>
                        <div class="alert alert-info">
                            <ul>
                                <?php foreach ($_SESSION['mensagens'] as $mensagem): ?>
                                <li><?= $mensagem; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php unset($_SESSION['mensagens']); ?>
                        <?php endif; ?>

                        <form action="" method="POST">
                            <h6>Município</h6>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" name="novo_municipio"
                                    placeholder="Digite o nome do município" required>
                                <button type="submit" class="btn btn-success" name="add_municipio"><i
                                        class="fas fa-plus"></i></button>
                            </div>
                        </form>

                        <form action="" method="POST">
                            <h6>Bairro</h6>
                            <select class="form-control mb-2" name="municipio_id" required>
                                <option value="">Selecione um Município</option>
                                <?php foreach ($municipios as $municipio): ?>
                                <option value="<?= $municipio['id'] ?>"><?= htmlspecialchars($municipio['nome']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" name="novo_bairro"
                                    placeholder="Digite o nome do bairro" required>
                                <button type="submit" class="btn btn-success" name="add_bairro"><i
                                        class="fas fa-plus"></i></button>
                            </div>
                        </form>

                        <form action="" method="POST">
                            <h6>Crime</h6>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" name="novo_crime"
                                    placeholder="Digite o nome do crime" required>
                                <button type="submit" class="btn btn-success" name="add_crime"><i
                                        class="fas fa-plus"></i></button>
                            </div>
                        </form>

                        <!-- Formulário de Crimes do ANPP -->
                        <form action="" method="POST">
                            <h6>Crime do ANPP</h6>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" name="novo_crime_anpp"
                                    placeholder="Digite o crime do ANPP" required>
                                <button type="submit" class="btn btn-success" name="add_crime_anpp"><i
                                        class="fas fa-plus"></i></button>
                            </div>
                        </form>



                    </div>
                </div>
            </div>

            <!-- Listagem -->
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white text-center">
                        <h5>Dados Cadastrados</h5>
                    </div>
                    <div class="card-body">
                        <h5>Crimes Cadastrados</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Crime</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($crimes as $crime): ?>
                                <tr>
                                    <td><?= htmlspecialchars($crime['nome']) ?></td>
                                    <td>
                                        <form action="../controllers/deletar_item.php" method="POST"
                                            onsubmit="return confirm('Tem certeza que deseja excluir este crime?');">
                                            <input type="hidden" name="tipo" value="crime">
                                            <input type="hidden" name="id" value="<?= $crime['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm"><i
                                                    class="fas fa-trash"></i> Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <h5>Crimes do ANPP</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Crime do ANPP</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $crimes_anpp = $pdo->query("SELECT * FROM crimes_anpp ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($crimes_anpp as $crime_anpp): ?>
                                <tr>
                                    <td><?= htmlspecialchars($crime_anpp['nome']) ?></td>
                                    <td>
                                        <form action="../controllers/deletar_item.php" method="POST"
                                            onsubmit="return confirm('Tem certeza que deseja excluir este crime ANPP?');">
                                            <input type="hidden" name="tipo" value="crime_anpp">
                                            <input type="hidden" name="id" value="<?= $crime_anpp['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm"><i
                                                    class="fas fa-trash"></i> Excluir</button>
                                        </form>

                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <h5>Municípios Cadastrados</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Município</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($municipios as $municipio): ?>
                                <tr>
                                    <td><?= htmlspecialchars($municipio['nome']) ?></td>
                                    <td>
                                        <form action="../controllers/deletar_item.php" method="POST"
                                            onsubmit="return confirm('Tem certeza que deseja excluir este município?');">
                                            <input type="hidden" name="tipo" value="municipio">
                                            <input type="hidden" name="id" value="<?= $municipio['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm"><i
                                                    class="fas fa-trash"></i> Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <h5>Bairros Cadastrados</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Bairro</th>
                                    <th>Município</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bairros as $bairro): ?>
                                <tr>
                                    <td><?= htmlspecialchars($bairro['nome']) ?></td>
                                    <td><?= htmlspecialchars($bairro['municipio']) ?></td>
                                    <td>
                                        <form action="../controllers/deletar_item.php" method="POST"
                                            onsubmit="return confirm('Tem certeza que deseja excluir este bairro?');">
                                            <input type="hidden" name="tipo" value="bairro">
                                            <input type="hidden" name="id" value="<?= $bairro['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm"><i
                                                    class="fas fa-trash"></i> Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>
    function confirmarExclusao(form) {
        let id = form.querySelector('input[name="id"]').value;
        let tipo = form.querySelector('input[name="tipo"]').value;

        if (!id) {
            alert("Erro: ID do crime ANPP não encontrado.");
            return false;
        }

        console.log("Enviando exclusão:", {
            tipo,
            id
        });

        return confirm("Tem certeza que deseja excluir este crime ANPP?");
    }
    </script>

</body>

</html>