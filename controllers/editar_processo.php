<!--/controllers/editar_processo.php-->
<?php
session_start();
require_once "../config/conexao.php";
require_once "logs_controller.php"; // Importa função de logs
global $pdo;

$perfil = $_SESSION['usuario_perfil'] ?? '';

// Lista de bairros organizados por município
$bairrosPorMunicipio = [
    "Manaus" => ["Centro", "Adrianópolis", "Cidade Nova", "Aleixo"],
    "Itacoatiara" => ["Centro", "Jauari", "Mamoud Amed"],
    "Parintins" => ["Centro", "Itaúna", "Francesa"],
];

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../views/login.php");
    exit();
}

// Verifica se o ID do processo foi passado na URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Busca os dados do processo
        $stmt = $pdo->prepare("SELECT * FROM processos WHERE id = :id");
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

// Se o formulário for enviado para salvar as alterações
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['atualizar'])) {
    $numero = trim($_POST['numero']);
    $natureza = trim($_POST['natureza']);
    $outra_natureza = $_POST['outra_natureza'] ?? null;
    $data_denuncia = $_POST['data_denuncia'];
    $crime = trim($_POST['crime']);
    $outro_crime = $_POST['outro_crime'] ?? null;
    $denunciado = trim($_POST['denunciado']);
    $vitima = $_POST['vitima'] ?? null;
    $local_municipio = $_POST['local_municipio'] ?? null;
    $local_bairro = $_POST['local_bairro'] ?? null;
    $sentenca = trim($_POST['sentenca']);
    $outra_sentenca = $_POST['outra_sentenca'] ?? null;
    $data_sentenca = $_POST['data_sentenca'] ?? null;
    $recursos = trim($_POST['recursos']);
    $status = $_POST['status'];

    try {
        // Atualiza o processo no banco
        $sql = "UPDATE processos SET 
                numero = :numero, 
                natureza = :natureza,
                outra_natureza = :outra_natureza,
                data_denuncia = :data_denuncia, 
                crime = :crime, 
                outro_crime = :outro_crime,
                denunciado = :denunciado, 
                vitima = :vitima,
                local_municipio = :local_municipio,
                local_bairro = :local_bairro,
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
        $stmt->bindParam(':crime', $crime);
        $stmt->bindParam(':outro_crime', $outro_crime);
        $stmt->bindParam(':denunciado', $denunciado);
        $stmt->bindParam(':vitima', $vitima);
        $stmt->bindParam(':local_municipio', $local_municipio);
        $stmt->bindParam(':local_bairro', $local_bairro);
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

        function carregarBairros() {
            let municipio = document.getElementById("municipio").value;
            let bairroSelect = document.getElementById("bairro");
            bairroSelect.innerHTML = ""; // Limpa opções antes de carregar novas

            let bairrosPorMunicipio = <?= json_encode($bairrosPorMunicipio) ?>;

            if (municipio in bairrosPorMunicipio) {
                bairrosPorMunicipio[municipio].forEach(function(bairro) {
                    let option = document.createElement("option");
                    option.value = bairro;
                    option.textContent = bairro;
                    if (bairro === "<?= htmlspecialchars($processo['local_bairro']) ?>") {
                        option.selected = true; // Mantém o bairro selecionado
                    }
                    bairroSelect.appendChild(option);
                });
            }
        }

        // Carregar bairros automaticamente ao abrir a página
        document.addEventListener("DOMContentLoaded", function() {
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
        <option value="Furto" <?= $processo['crime'] == "Furto" ? "selected" : "" ?>>Furto</option>
        <option value="Roubo" <?= $processo['crime'] == "Roubo" ? "selected" : "" ?>>Roubo</option>
        <option value="Latrocínio" <?= $processo['crime'] == "Latrocínio" ? "selected" : "" ?>>Latrocínio</option>
        <option value="Receptação" <?= $processo['crime'] == "Receptação" ? "selected" : "" ?>>Receptação</option>
        <option value="Homicídio" <?= $processo['crime'] == "Homicídio" ? "selected" : "" ?>>Homicídio</option>
        <option value="Outro" <?= !empty($processo['outro_crime']) ? "selected" : "" ?>>Outro Crime</option>
    </select>
    <input type="text" class="form-control mt-2" id="outroCrime" name="outro_crime" placeholder="Especifique o crime..."
        value="<?= htmlspecialchars($processo['outro_crime'] ?? '') ?>" 
        style="<?= empty($processo['outro_crime']) ? 'display:none;' : '' ?>">
</div> 




                            <!-- Local do Crime -->
                            <div class="mb-3">
                                <label class="form-label">Local do Crime</label>
                                <select class="form-control mt-2" id="municipio" name="local_municipio" onchange="carregarBairros()" required>
                                    <?php foreach ($bairrosPorMunicipio as $municipio => $bairros): ?>
                                        <option value="<?= $municipio ?>" <?= ($processo['local_municipio'] == $municipio) ? "selected" : "" ?>><?= $municipio ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select class="form-control mt-2" id="bairro" name="local_bairro" required>
                                    <option value="<?= htmlspecialchars($processo['local_bairro']) ?>" selected><?= htmlspecialchars($processo['local_bairro']) ?></option>
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
