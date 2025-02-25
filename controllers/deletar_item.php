<!--/controle_acervo/controllers/deletar_item.php-->
<?php
session_start();
require_once "../config/conexao.php";
global $pdo;

// Verifica se o usuário está logado e se é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_perfil'] !== 'administrador') {
    $_SESSION['mensagem'] = "Acesso negado!";
    header("Location: ../views/cadastro_basico.php");
    exit();
}

// Verifica se os dados foram enviados corretamente
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tipo']) && isset($_POST['id'])) {
    $tipo = $_POST['tipo'];
    $id = intval($_POST['id']); // Converte para inteiro para evitar erros

    try {
        if ($tipo === "municipio") {
            // Verifica se existem bairros vinculados a esse município
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bairros WHERE municipio_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $bairros_vinculados = $stmt->fetchColumn();

            if ($bairros_vinculados > 0) {
                $_SESSION['mensagem'] = "Erro: Não é possível excluir o município, pois existem $bairros_vinculados bairros vinculados.";
                header("Location: ../views/cadastro_basico.php");
                exit();
            }

            // Exclui o município
            $stmt = $pdo->prepare("DELETE FROM municipios WHERE id = :id");

        } elseif ($tipo === "bairro") {
            $stmt = $pdo->prepare("DELETE FROM bairros WHERE id = :id");

        } elseif ($tipo === "crime") {
            // Verifica se existem processos vinculados a esse crime
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM processos WHERE crime_id = :id"); // <-- Certifique-se que a coluna na tabela processos é crime_id
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $processos_vinculados = $stmt->fetchColumn();

            if ($processos_vinculados > 0) {
                $_SESSION['mensagem'] = "Erro: Não é possível excluir o crime, pois existem $processos_vinculados processos vinculados.";
                header("Location: ../views/cadastro_basico.php");
                exit();
            }

            // Exclui o crime
            $stmt = $pdo->prepare("DELETE FROM crimes WHERE id = :id");

        } else {
            $_SESSION['mensagem'] = "Erro: Tipo de exclusão inválido!";
            header("Location: ../views/cadastro_basico.php");
            exit();
        }

        // Executa a exclusão
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $_SESSION['mensagem'] = ucfirst($tipo) . " excluído com sucesso!";
        } else {
            $_SESSION['mensagem'] = "Erro ao excluir " . $tipo . ".";
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = "Erro: " . $e->getMessage();
    }
}

// Redireciona de volta para a página de cadastro básico
header("Location: ../views/cadastro_basico.php");
exit();
