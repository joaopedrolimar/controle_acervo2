<!--/controllers/editar_processo.php-->
<?php
session_start();
require_once "../config/conexao.php";
require_once "logs_controller.php"; // Importa a função para registrar logs
global $pdo;

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../views/login.php");
    exit();
}

// Verifica se o ID do processo foi passado na URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Busca os dados do processo antes da edição
        $sql = "SELECT * FROM processos WHERE id = :id";
        $stmt = $pdo->prepare($sql);
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
    $tipo = trim($_POST['tipo']);
    $data_inicio = $_POST['data_inicio'];
    $crime = trim($_POST['crime']);
    $denunciado = trim($_POST['denunciado']);
    $status = $_POST['status'];

    try {
        // Captura os valores antigos antes da atualização
        $valores_anteriores = json_encode($processo);

        // Atualiza o processo no banco
        $sql = "UPDATE processos SET numero = :numero, tipo = :tipo, data_inicio = :data_inicio, 
                crime = :crime, denunciado = :denunciado, status = :status WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':numero', $numero);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':data_inicio', $data_inicio);
        $stmt->bindParam(':crime', $crime);
        $stmt->bindParam(':denunciado', $denunciado);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Captura os valores novos após a edição
            $valores_novos = json_encode([
                'numero' => $numero, 
                'tipo' => $tipo, 
                'data_inicio' => $data_inicio, 
                'crime' => $crime, 
                'denunciado' => $denunciado, 
                'status' => $status
            ]);

            // Registra a ação no log
            registrar_log($_SESSION['usuario_id'], "Editou um processo", "processos", $id, $valores_anteriores, $valores_novos);

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
                            <div class="mb-3">
                                <label for="numero" class="form-label">Número do Processo</label>
                                <input type="text" class="form-control" id="numero" name="numero" required
                                    value="<?= htmlspecialchars($processo['numero']) ?>">
                            </div>
                            <div class="mb-3">
                                <label for="tipo" class="form-label">Tipo</label>
                                <input type="text" class="form-control" id="tipo" name="tipo" required
                                    value="<?= htmlspecialchars($processo['tipo']) ?>">
                            </div>
                            <div class="mb-3">
                                <label for="data_inicio" class="form-label">Data de Início</label>
                                <input type="date" class="form-control" id="data_inicio" name="data_inicio" required
                                    value="<?= $processo['data_inicio'] ?>">
                            </div>
                            <div class="mb-3">
                                <label for="crime" class="form-label">Crime</label>
                                <input type="text" class="form-control" id="crime" name="crime" required
                                    value="<?= htmlspecialchars($processo['crime']) ?>">
                            </div>
                            <div class="mb-3">
                                <label for="denunciado" class="form-label">Denunciado</label>
                                <input type="text" class="form-control" id="denunciado" name="denunciado" required
                                    value="<?= htmlspecialchars($processo['denunciado']) ?>">
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="Cadastrado"
                                        <?= $processo['status'] == "Cadastrado" ? "selected" : "" ?>>Cadastrado</option>
                                    <option value="Finalizado"
                                        <?= $processo['status'] == "Finalizado" ? "selected" : "" ?>>Finalizado</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success w-100" name="atualizar">Atualizar
                                Processo</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
