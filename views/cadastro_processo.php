<!--controle_acervo/views/cadastro_processo.php-->
<?php
session_start();
require_once "../config/conexao.php"; // Conexão com o banco
global $pdo; // Torna a conexão acessível

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$perfil = $_SESSION['usuario_perfil'] ?? '';

// Lista de bairros organizados por município
$bairrosPorMunicipio = [
    "Manaus" => ["Centro", "Adrianópolis", "Cidade Nova", "Aleixo"],
    "Itacoatiara" => ["Centro", "Jauari", "Mamoud Amed"],
    "Parintins" => ["Centro", "Itaúna", "Francesa"],
];

// Busca os crimes do banco de dados
try {
    $stmt = $pdo->query("SELECT * FROM crimes ORDER BY nome ASC");
    $crimes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar crimes: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Processo</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Função para habilitar/desabilitar campo "Outra Natureza"
        function toggleOutraNatureza() {
            let select = document.getElementById("natureza");
            let outraNatureza = document.getElementById("outraNatureza");
            outraNatureza.style.display = (select.value === "Outra") ? "block" : "none";
        }

        // Função para habilitar/desabilitar campo "Vítima"
        function toggleVitima() {
            let check = document.getElementById("semVitima");
            let vitimaInput = document.getElementById("vitima");
            vitimaInput.disabled = check.checked;
        }

        // Função para exibir/esconder campos de sentença
        function toggleSentenca() {
            let select = document.getElementById("sentenca");
            let dataSentenca = document.getElementById("dataSentenca");
            let outraSentenca = document.getElementById("outraSentenca");

            if (select.value === "Outra") {
                outraSentenca.style.display = "block";
                dataSentenca.style.display = "block"; // Agora a data também aparece
            } else if (select.value !== "Não há") {
                outraSentenca.style.display = "none";
                dataSentenca.style.display = "block";
            } else {
                outraSentenca.style.display = "none";
                dataSentenca.style.display = "none";
            }
        }

        function carregarBairros() {
            let municipio = document.getElementById("municipio").value;
            let bairroSelect = document.getElementById("bairro");

            bairroSelect.innerHTML = "<option value=''>Selecione um Bairro</option>";

            let bairrosPorMunicipio = <?= json_encode($bairrosPorMunicipio) ?>;

            if (municipio in bairrosPorMunicipio) {
                bairrosPorMunicipio[municipio].forEach(function(bairro) {
                    let option = document.createElement("option");
                    option.value = bairro;
                    option.textContent = bairro;
                    bairroSelect.appendChild(option);
                });
            }
        }

  
    </script>
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

<!-- Conteúdo Principal -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white text-center">
                    <h4>Cadastrar Novo Processo</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['mensagem'])): ?>
                    <div class="alert alert-info">
                        <?= $_SESSION['mensagem']; ?>
                    </div>
                    <?php unset($_SESSION['mensagem']); ?>
                    <?php endif; ?>

                    <form action="../controllers/processo_controller.php" method="POST">

                    <!--Numero do processo-->
                        <div class="mb-3">
                            <label for="numero" class="form-label">Número do Processo</label>
                            <input type="text" class="form-control" id="numero" name="numero" required>
                        </div>


                    <!--Data da denuncia-->
                        <div class="mb-3">
                            <label for="data_denuncia" class="form-label">Data da Denúncia</label>
                            <input type="date" class="form-control" id="data_denuncia" name="data_denuncia" required>
                        </div>

                    <!--Natureza Processual-->
                        <div class="mb-3">
                            <label for="natureza" class="form-label">Natureza Processual/Procedimental</label>
                            <select class="form-control" id="natureza" name="natureza" onchange="toggleOutraNatureza()" required>
                                <option value="Ação Penal">Ação Penal</option>
                                <option value="Inquérito Policial">Inquérito Policial</option>
                                <option value="PICNF">PICNF</option>
                                <option value="Outra">Outra Natureza</option>
                            </select>
                            <input type="text" class="form-control mt-2" id="outraNatureza" name="outra_natureza" placeholder="Especifique..." style="display:none;">
                        </div>

                        <!-- Crime -->
                        <div class="mb-3">
                            <label for="crime" class="form-label">Crime</label>
                            <select class="form-control" id="crime" name="crime" required>
                                <?php foreach ($crimes as $crime): ?>
                                    <option value="<?= htmlspecialchars($crime['id']) ?>"><?= htmlspecialchars($crime['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>


                        <!--Local do crime-->
                        <div class="mb-3">
                            <label class="form-label">Local do Crime</label>
                            <select class="form-control mt-2" id="municipio" name="municipio" onchange="carregarBairros()">
                                <option value="">Selecione um Município</option>
                                <option value="Manaus">Manaus</option>
                                <option value="Itacoatiara">Itacoatiara</option>
                                <option value="Parintins">Parintins</option>
                            </select>

                            <select class="form-control mt-2" id="bairro" name="bairro">
                                <option value="">Selecione um Bairro</option>
                            </select>
                        </div>


                        <!--Denunciado-->
                        <div class="mb-3">
                            <label for="denunciado" class="form-label">Denunciado</label>
                            <input type="text" class="form-control" id="denunciado" name="denunciado" required>
                        </div>

                        <!--vitima-->
                        <div class="mb-3">
                            <label for="vitima" class="form-label">Vítima</label>
                            <input type="text" class="form-control" id="vitima" name="vitima">
                            <input type="checkbox" id="semVitima" onclick="toggleVitima()"> Não há vítima
                        </div>

                        <!--Sentença-->
                        <div class="mb-3">
                            <label for="sentenca" class="form-label">Sentença</label>
                            <select class="form-control" id="sentenca" name="sentenca" onchange="toggleSentenca()">
                                <option value="Condenatória">Condenatória</option>
                                <option value="Absolutória">Absolutória</option>
                                <option value="Prescrição">Prescrição</option>
                                <option value="Outra">Outra</option>
                                <option value="Não há">Não há</option>
                            </select>
                            <input type="text" class="form-control mt-2" id="outraSentenca" name="outra_sentenca" placeholder="Especifique outra sentença..." style="display:none;">
                            <input type="date" class="form-control mt-2" id="dataSentenca" name="data_sentenca" style="display:none;">
                        </div>
                        <!-- Recursos -->
                        <div class="mb-3">
                            <label for="recursos" class="form-label">Recursos</label>
                            <select class="form-control" id="recursos" name="recursos" required>
                                <option value="Acusação">Acusação</option>
                                <option value="Defesa">Defesa</option>
                                <option value="Não há">Não há</option>
                            </select>
                        </div>

                        

                        <button type="submit" class="btn btn-success w-100" name="cadastrar">Cadastrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
