<!--/controllers/editar_processo.php-->
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../config/conexao.php";
require_once "logs_controller.php"; // Importa função de logs
global $pdo;

$perfil = $_SESSION['usuario_perfil'] ?? '';



// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../views/login.php");
    exit();
}

// Verifica se o ID do processo foi passado na URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Buscar todos os crimes disponíveis
        $stmt_crimes = $pdo->prepare("SELECT id, nome FROM crimes");
        $stmt_crimes->execute();
        $crimes = $stmt_crimes->fetchAll(PDO::FETCH_ASSOC);

        // Buscar todos os municípios disponíveis
        $stmt_municipios = $pdo->prepare("SELECT id, nome FROM municipios");
        $stmt_municipios->execute();
        $municipios = $stmt_municipios->fetchAll(PDO::FETCH_ASSOC);

        // Buscar todos os bairros disponíveis
        $stmt_bairros = $pdo->prepare("SELECT id, nome, municipio_id FROM bairros");
        $stmt_bairros->execute();
        $bairros = $stmt_bairros->fetchAll(PDO::FETCH_ASSOC);

        // Criar um array associativo de bairros agrupados por município
        $bairrosPorMunicipio = [];
        foreach ($bairros as $bairro) {
            $bairrosPorMunicipio[$bairro['municipio_id']][] = [
                'id' => $bairro['id'],
                'nome' => $bairro['nome']
            ];
        }

// Buscar os dados do processo com JOIN para pegar corretamente o crime associado
$stmt = $pdo->prepare("
    SELECT processos.*, crimes.id AS crime_id, crimes.nome AS nome_crime
    FROM processos 
    LEFT JOIN crimes ON processos.crime_id = crimes.id
    WHERE processos.id = :id
");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$processo = $stmt->fetch(PDO::FETCH_ASSOC);


        if (!$processo) {
            $_SESSION['mensagem'] = "Processo não encontrado.";
            header("Location: ../views/listar_processos.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = "Erro no banco: " . $e->getMessage();
        header("Location: ../views/listar_processos.php");
        exit();
    }
} else {
    $_SESSION['mensagem'] = "ID inválido.";
    header("Location: ../views/listar_processos.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['atualizar'])) {
    $numero = trim($_POST['numero']);
    $natureza = trim($_POST['natureza']);
    $outra_natureza = !empty($_POST['outra_natureza']) ? $_POST['outra_natureza'] : null;
    $data_denuncia = $_POST['data_denuncia'];
    
    // Pegando o crime_id corretamente
    $crime_id = $_POST['crime'] !== "Outro" ? intval($_POST['crime']) : null;
    $outro_crime = !empty($_POST['outro_crime']) ? $_POST['outro_crime'] : null;

    $denunciado = trim($_POST['denunciado']);
    $vitima = !empty($_POST['vitima']) ? $_POST['vitima'] : null;

    // Pegando o municipio_id e bairro_id corretamente
    $municipio_id = isset($_POST['local_municipio']) && is_numeric($_POST['local_municipio']) ? intval($_POST['local_municipio']) : null;
    $bairro_id = isset($_POST['local_bairro']) && is_numeric($_POST['local_bairro']) ? intval($_POST['local_bairro']) : null;

    // Sentença
    $sentenca = trim($_POST['sentenca']);
    $outra_sentenca = !empty($_POST['outra_sentenca']) ? $_POST['outra_sentenca'] : null;
    $data_sentenca = !empty($_POST['data_sentenca']) ? $_POST['data_sentenca'] : null;

    $recursos = trim($_POST['recursos']);
    $status = $_POST['status'];

    try {
        // Atualiza o processo no banco
        $sql = "UPDATE processos SET 
                numero = :numero, 
                natureza = :natureza,
                outra_natureza = :outra_natureza,
                data_denuncia = :data_denuncia, 
                crime_id = :crime_id, 
                outro_crime = :outro_crime,
                denunciado = :denunciado, 
                vitima = :vitima,
                municipio_id = :municipio_id,
                bairro_id = :bairro_id,
                sentenca = :sentenca,
                outra_sentenca = :outra_sentenca,
                data_sentenca = :data_sentenca,
                recursos = :recursos,
                status = :status
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':numero', $numero);
        $stmt->bindParam(':natureza', $natureza);
        $stmt->bindParam(':outra_natureza', $outra_natureza);
        $stmt->bindParam(':data_denuncia', $data_denuncia);
        $stmt->bindParam(':crime_id', $crime_id, PDO::PARAM_INT);
        $stmt->bindParam(':outro_crime', $outro_crime);
        $stmt->bindParam(':denunciado', $denunciado);
        $stmt->bindParam(':vitima', $vitima);
        $stmt->bindParam(':municipio_id', $municipio_id, PDO::PARAM_INT);
        $stmt->bindParam(':bairro_id', $bairro_id, PDO::PARAM_INT);
        $stmt->bindParam(':sentenca', $sentenca);
        $stmt->bindParam(':outra_sentenca', $outra_sentenca);
        $stmt->bindParam(':data_sentenca', $data_sentenca);
        $stmt->bindParam(':recursos', $recursos);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $_SESSION['mensagem'] = "Processo atualizado com sucesso!";
        } else {
            $_SESSION['mensagem'] = "Erro ao atualizar o processo.";
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = "Erro no banco: " . $e->getMessage();
    }

    header("Location: ../views/listar_processos.php");
    exit();
}


?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Processo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Função para mostrar/esconder o campo "Outra Natureza"
        function toggleOutraNatureza() {
            let select = document.getElementById("natureza");
            let outraNatureza = document.getElementById("outraNatureza");
            outraNatureza.style.display = (select.value === "Outra") ? "block" : "none";
        }

        // Função para mostrar/esconder o campo "Outro Crime"
        function toggleOutroCrime() {
            let crimeSelect = document.getElementById("crime");
            let outroCrimeInput = document.getElementById("outroCrime");
            outroCrimeInput.style.display = (crimeSelect.value === "Outro") ? "block" : "none";
        }

        // Ativar ao carregar a página para mostrar corretamente se o campo "Outro Crime" deve estar visível
        document.addEventListener("DOMContentLoaded", function () {
            toggleOutroCrime();
        });


        // Função para mostrar/esconder o campo de sentença e data da sentença
        function toggleSentenca() {
            let select = document.getElementById("sentenca");
            let outraSentenca = document.getElementById("outraSentenca");
            let dataSentenca = document.getElementById("dataSentenca");

            if (select.value === "Outra") {
                outraSentenca.style.display = "block";
                dataSentenca.style.display = "block";
            } else if (select.value !== "Não há") {
                outraSentenca.style.display = "none";
                dataSentenca.style.display = "block";
            } else {
                outraSentenca.style.display = "none";
                dataSentenca.style.display = "none";
            }
        }

        let bairrosPorMunicipio = <?= json_encode($bairrosPorMunicipio) ?>;

function carregarBairros() {
    let municipioSelect = document.getElementById("municipio");
    let bairroSelect = document.getElementById("bairro");
    let municipioSelecionado = municipioSelect.value;

    bairroSelect.innerHTML = '<option value="">Selecione um bairro</option>';

    if (bairrosPorMunicipio[municipioSelecionado]) {
        bairrosPorMunicipio[municipioSelecionado].forEach(bairro => {
            let option = document.createElement("option");
            option.value = bairro.id;
            option.textContent = bairro.nome;

            // Se for o bairro salvo no processo, ele já vem selecionado
            if (bairro.id == <?= json_encode($processo['bairro_id']) ?>) {
                option.selected = true;
            }

            bairroSelect.appendChild(option);
        });
    }
}

document.addEventListener("DOMContentLoaded", function () {
    carregarBairros();
});

    </script>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-white text-center">
                        <h4>Editar Processo</h4>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            
                            <!-- Número do Processo -->
                            <div class="mb-3">
                                <label for="numero" class="form-label">Número do Processo</label>
                                <input type="text" class="form-control" id="numero" name="numero" required value="<?= htmlspecialchars($processo['numero']) ?>">
                            </div>

                            <div class="mb-3">
                                <label for="data_denuncia" class="form-label">Data da Denúncia</label>
                                <input type="date" class="form-control" id="data_denuncia" name="data_denuncia" required value="<?= $processo['data_denuncia'] ?>">
                            </div>

                            <!-- Natureza -->
                            <div class="mb-3">
                                <label for="natureza" class="form-label">Natureza Processual/Procedimental</label>
                                <select class="form-control" id="natureza" name="natureza" onchange="toggleOutraNatureza()" required>
                                    <option value="Ação Penal" <?= $processo['natureza'] == "Ação Penal" ? "selected" : "" ?>>Ação Penal</option>
                                    <option value="Inquérito Policial" <?= $processo['natureza'] == "Inquérito Policial" ? "selected" : "" ?>>Inquérito Policial</option>
                                    <option value="PICNF" <?= $processo['natureza'] == "PICNF" ? "selected" : "" ?>>PICNF</option>
                                    <option value="Outra" <?= !empty($processo['outra_natureza']) ? "selected" : "" ?>>Outra</option>
                                </select>
                                <input type="text" class="form-control mt-2" id="outraNatureza" name="outra_natureza" placeholder="Especifique..."
                                    value="<?= htmlspecialchars($processo['outra_natureza'] ?? '') ?>" 
                                    style="<?= empty($processo['outra_natureza']) ? 'display:none;' : '' ?>">
                            </div>

                            <!--Crime -->
                            <div class="mb-3">
    <label for="crime" class="form-label">Crime</label>
    <select class="form-control" id="crime" name="crime" onchange="toggleOutroCrime()" required>
        <?php foreach ($crimes as $crime): ?>
            <option value="<?= $crime['id'] ?>" <?= ($crime['id'] == $processo['crime_id']) ? "selected" : "" ?>>
                <?= htmlspecialchars($crime['nome']) ?>
            </option>
        <?php endforeach; ?>
        <option value="Outro">Outro Crime</option>
    </select>

    <!-- Campo para outro crime, aparece apenas se 'Outro' for selecionado -->
    <input type="text" class="form-control mt-2" id="outroCrime" name="outro_crime" 
           placeholder="Especifique o crime..." 
           value="<?= htmlspecialchars($processo['outro_crime'] ?? '') ?>" 
           style="<?= empty($processo['outro_crime']) ? 'display:none;' : '' ?>">
</div>



                            <!-- Local do Crime -->
                            <div class="mb-3">
                                <label for="municipio" class="form-label">Município</label>
                                <select class="form-control" id="municipio" name="local_municipio" onchange="carregarBairros()" required>
                                    <option value="">Selecione um município</option>
                                    <?php foreach ($municipios as $municipio): ?>
                                        <option value="<?= $municipio['id'] ?>" <?= ($municipio['id'] == $processo['municipio_id']) ? "selected" : "" ?>>
                                            <?= htmlspecialchars($municipio['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="bairro" class="form-label">Bairro</label>
                                <select class="form-control" id="bairro" name="local_bairro" required>
                                    <option value="">Selecione um bairro</option>
                                </select>
                            </div>

                                <!-- Vítima -->
                             <div class="mb-3">
                                <label for="denunciado" class="form-label">Denunciado</label>
                                <input type="text" class="form-control" id="denunciado" name="denunciado" value="<?= htmlspecialchars($processo['denunciado'] ?? '') ?>">

                            </div>

                            <!-- Vítima -->
                            <div class="mb-3">
                                <label for="vitima" class="form-label">Vítima</label>
                                <input type="text" class="form-control" id="vitima" name="vitima" value="<?= htmlspecialchars($processo['vitima'] ?? '') ?>">
                                <input type="checkbox" id="semVitima" onclick="toggleVitima()"> Não há vítima
                            </div>

                            <!-- Sentença -->
                            <div class="mb-3">
                                <label for="sentenca" class="form-label">Sentença</label>
                                <select class="form-control" id="sentenca" name="sentenca" onchange="toggleSentenca()" required>
                                    <option value="Condenatória" <?= $processo['sentenca'] == "Condenatória" ? "selected" : "" ?>>Condenatória</option>
                                    <option value="Absolutória" <?= $processo['sentenca'] == "Absolutória" ? "selected" : "" ?>>Absolutória</option>
                                    <option value="Prescrição" <?= $processo['sentenca'] == "Prescrição" ? "selected" : "" ?>>Prescrição</option>
                                    <option value="Outra" <?= !empty($processo['outra_sentenca']) ? "selected" : "" ?>>Outra</option>
                                    <option value="Não há" <?= $processo['sentenca'] == "Não há" ? "selected" : "" ?>>Não há</option>
                                </select>
                                <input type="text" class="form-control mt-2" id="outraSentenca" name="outra_sentenca" placeholder="Especifique outra sentença..."
                                    value="<?= htmlspecialchars($processo['outra_sentenca'] ?? '') ?>" 
                                    style="<?= empty($processo['outra_sentenca']) ? 'display:none;' : '' ?>">
                                <input type="date" class="form-control mt-2" id="dataSentenca" name="data_sentenca" value="<?= $processo['data_sentenca'] ?>">
                            </div>

                                                        <!-- Recursos -->
                                                        <div class="mb-3">
                                <label for="recursos" class="form-label">Recursos</label>
                                <select class="form-control" id="recursos" name="recursos" required>
                                    <option value="Acusação" <?= $processo['recursos'] == "Acusação" ? "selected" : "" ?>>Acusação</option>
                                    <option value="Defesa" <?= $processo['recursos'] == "Defesa" ? "selected" : "" ?>>Defesa</option>
                                    <option value="Não há" <?= $processo['recursos'] == "Não há" ? "selected" : "" ?>>Não há</option>
                                </select>
                            </div>

                            

                            <!-- Botões -->
                            <button type="submit" class="btn btn-success w-100" name="atualizar">Atualizar Processo</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
