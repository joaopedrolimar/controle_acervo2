<!--editar_anpp.php-->
<?php
session_start();
require_once "../config/conexao.php";
global $pdo;

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Verifica se foi passado um ID pela URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensagem'] = "ID do ANPP inválido!";
    header("Location: listar_anpp.php");
    exit();
}

$id = $_GET['id'];

// Busca os dados do ANPP pelo ID
$stmt = $pdo->prepare("SELECT * FROM anpp WHERE id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$anpp = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$anpp) {
    $_SESSION['mensagem'] = "ANPP não encontrado!";
    header("Location: listar_anpp.php");
    exit();
}

$crimes_anpp = $pdo->query("SELECT * FROM crimes_anpp ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar ANPP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center">Editar ANPP</h2>
        <form action="../controllers/salvar_edicao_anpp.php" method="POST">
            <input type="hidden" name="id" value="<?= $anpp['id'] ?>">

            <div class="card p-4 shadow">
                <div class="mb-3">
                    <label for="numero_inquerito" class="form-label">Número do Inquérito</label>
                    <input type="text" class="form-control" name="numero_inquerito" value="<?= htmlspecialchars($anpp['numero_inquerito']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="indiciado" class="form-label">Indiciado</label>
                    <input type="text" class="form-control" name="indiciado" value="<?= htmlspecialchars($anpp['indiciado']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="crime" class="form-label">Crime</label>
                    <select class="form-control" name="crime" required>
                        <option value="">Selecione um Crime</option>
                        <?php foreach ($crimes_anpp as $crime): ?>
                        <option value="<?= $crime['id'] ?>" <?= ($crime['id'] == $anpp['crime_id']) ? 'selected' : '' ?>><?= htmlspecialchars($crime['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="nome_vitima" class="form-label">Nome da Vítima</label>
                    <input type="text" class="form-control" name="nome_vitima" value="<?= htmlspecialchars($anpp['nome_vitima']) ?>">
                </div>
                <div class="mb-3">
                    <label for="data_audiencia" class="form-label">Data da Audiência</label>
                    <input type="date" class="form-control" name="data_audiencia" value="<?= $anpp['data_audiencia'] ?>">
                </div>

                <!-- Acordo -->
                <div class="mb-3">
                    <label class="form-label">Acordo</label>
                    <div>
                        <input type="radio" name="acordo" value="realizado" onclick="mostrarCampos(true)" <?= ($anpp['acordo_realizado'] === 'sim') ? 'checked' : '' ?>> Realizado
                        <input type="radio" name="acordo" value="nao_realizado" onclick="mostrarCampos(false)" <?= ($anpp['acordo_realizado'] === 'nao') ? 'checked' : '' ?>> Não Realizado
                    </div>
                </div>

                <div id="camposAcordo" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Reparação da Vítima</label>
                        <select class="form-control" name="reparacao" id="reparacao" onchange="toggleInput(this, 'valor_reparacao')">
                            <option value="nao">Não</option>
                            <option value="sim" <?= $anpp['valor_reparacao'] ? 'selected' : '' ?>>Sim</option>
                        </select>
                        <input type="text" class="form-control mt-2" name="valor_reparacao" id="valor_reparacao" value="<?= $anpp['valor_reparacao'] ?>" style="display: none;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prestação de Serviço Comunitário</label>
                        <select class="form-control" name="servico_comunitario" id="servico_comunitario" onchange="toggleInput(this, 'tempo_servico')">
                            <option value="nao">Não</option>
                            <option value="sim" <?= $anpp['tempo_servico'] ? 'selected' : '' ?>>Sim</option>
                        </select>
                        <input type="text" class="form-control mt-2" name="tempo_servico" id="tempo_servico" value="<?= $anpp['tempo_servico'] ?>" style="display: none;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Multa</label>
                        <select class="form-control" name="multa" id="multa" onchange="toggleInput(this, 'valor_multa')">
                            <option value="nao">Não</option>
                            <option value="sim" <?= $anpp['valor_multa'] ? 'selected' : '' ?>>Sim</option>
                        </select>
                        <input type="text" class="form-control mt-2" name="valor_multa" id="valor_multa" value="<?= $anpp['valor_multa'] ?>" style="display: none;">
                    </div>
                    <div class="mb-3">
                        <label for="restituicao" class="form-label">Restituição da Coisa à Vítima</label>
                       <input type="text" class="form-control" name="restituicao" value="<?= htmlspecialchars($anpp['restituicao'] ?? '') ?>">

                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                <a href="listar_anpp.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <script>
        function mostrarCampos(ativo) {
            document.getElementById('camposAcordo').style.display = ativo ? 'block' : 'none';
        }

        function toggleInput(select, inputId) {
            const input = document.getElementById(inputId);
            if (select.value === 'sim') {
                input.style.display = 'block';
                input.removeAttribute('disabled');
            } else {
                input.style.display = 'none';
                input.setAttribute('disabled', 'true');
                input.value = '';
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            const acordoSim = document.querySelector('input[name="acordo"][value="realizado"]');
            if (acordoSim && acordoSim.checked) {
                mostrarCampos(true);
            }
            toggleInput(document.getElementById('reparacao'), 'valor_reparacao');
            toggleInput(document.getElementById('servico_comunitario'), 'tempo_servico');
            toggleInput(document.getElementById('multa'), 'valor_multa');
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
