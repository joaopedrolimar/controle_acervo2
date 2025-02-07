<!--controle_acervo/controllers/processo_controller.php-->
<?php
session_start();
require_once "../config/conexao.php";
require_once "logs_controller.php"; // Importa a função de logs
global $pdo;

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cadastrar'])) {
    $numero = trim($_POST['numero']);
    $tipo = trim($_POST['tipo']);
    $data_inicio = $_POST['data_inicio'];
    $crime = trim($_POST['crime']);
    $denunciado = trim($_POST['denunciado']);
    $status = $_POST['status'];
    $usuario_id = $_SESSION['usuario_id']; // ID do usuário logado

    try {
        // Verifica se o processo já existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM processos WHERE numero = :numero");
        $stmt->bindParam(':numero', $numero, PDO::PARAM_STR);
        $stmt->execute();
        $existe = $stmt->fetchColumn();

        if ($existe > 0) {
            $_SESSION['mensagem'] = "Erro: O número de processo '$numero' já está cadastrado!";
            header("Location: ../views/cadastro_processo.php");
            exit();
        }

        // Insere no banco de dados
        $sql = "INSERT INTO processos (numero, tipo, data_inicio, crime, denunciado, status, usuario_id) 
                VALUES (:numero, :tipo, :data_inicio, :crime, :denunciado, :status, :usuario_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':numero', $numero, PDO::PARAM_STR);
        $stmt->bindParam(':tipo', $tipo, PDO::PARAM_STR);
        $stmt->bindParam(':data_inicio', $data_inicio, PDO::PARAM_STR);
        $stmt->bindParam(':crime', $crime, PDO::PARAM_STR);
        $stmt->bindParam(':denunciado', $denunciado, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $registro_id = $pdo->lastInsertId(); // Pega o ID do processo inserido

            // Registra a ação no log
            registrar_log($usuario_id, "Cadastrou um novo processo", "processos", $registro_id, null, json_encode([
                'numero' => $numero, 'tipo' => $tipo, 'data_inicio' => $data_inicio, 'crime' => $crime, 'denunciado' => $denunciado, 'status' => $status
            ]));

            $_SESSION['mensagem'] = "Processo cadastrado com sucesso!";
        } else {
            $_SESSION['mensagem'] = "Erro ao cadastrar processo.";
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = "Erro: " . $e->getMessage();
    }

    header("Location: ../views/cadastro_processo.php");
    exit();
}
?>
