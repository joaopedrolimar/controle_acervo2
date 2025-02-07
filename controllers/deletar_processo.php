<!--/controllers/deletar_processo.php-->
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

// Verifica se um ID válido foi passado na URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Busca os dados do processo antes da exclusão
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

        // Captura os valores antes da exclusão para o log
        $valores_anteriores = json_encode($processo);

        // Exclui o processo do banco
        $sql = "DELETE FROM processos WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Registra a ação no log
            registrar_log($_SESSION['usuario_id'], "Deletou um processo", "processos", $id, $valores_anteriores, null);

            $_SESSION['mensagem'] = "Processo deletado com sucesso!";
        } else {
            $_SESSION['mensagem'] = "Erro ao deletar o processo.";
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = "Erro no banco: " . $e->getMessage();
    }

    header("Location: ../views/listar_processos.php");
    exit();
} else {
    $_SESSION['mensagem'] = "ID inválido.";
    header("Location: ../views/listar_processos.php");
    exit();
}
?>
