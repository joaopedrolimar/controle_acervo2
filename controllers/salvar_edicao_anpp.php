<!--/controllers/salvar_edicao_anpp.php-->
<?php
session_start();
require_once "../config/conexao.php";
global $pdo;

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Capturar os dados do formulário
        $id = intval($_POST['id']);
        $numero_inquerito = trim($_POST['numero_inquerito']);
        $indiciado = trim($_POST['indiciado']);
        $crime_id = intval($_POST['crime']);
        $nome_vitima = !empty($_POST['nome_vitima']) ? trim($_POST['nome_vitima']) : null;
        $data_audiencia = !empty($_POST['data_audiencia']) ? $_POST['data_audiencia'] : null;
        $acordo_realizado = isset($_POST['acordo']) && in_array($_POST['acordo'], ['sim', 'nao']) ? $_POST['acordo'] : 'nao';

        // Definir valores padrão para os campos opcionais
        $valor_reparacao = ($_POST['reparacao'] === "sim") ? floatval($_POST['valor_reparacao']) : null;
        $tempo_servico = ($_POST['servico_comunitario'] === "sim") ? intval($_POST['tempo_servico']) : null;
        $valor_multa = ($_POST['multa'] === "sim") ? floatval($_POST['valor_multa']) : null;
        $restituicao = !empty($_POST['restituicao']) ? trim($_POST['restituicao']) : null;

        // Atualizar os dados no banco
        $stmt = $pdo->prepare("UPDATE anpp SET 
            numero_inquerito = :numero_inquerito, 
            indiciado = :indiciado, 
            crime_id = :crime_id, 
            nome_vitima = :nome_vitima, 
            data_audiencia = :data_audiencia, 
            acordo_realizado = :acordo_realizado, 
            valor_reparacao = :valor_reparacao, 
            tempo_servico = :tempo_servico, 
            valor_multa = :valor_multa, 
            restituicao = :restituicao 
            WHERE id = :id");

        // Bind dos valores
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':numero_inquerito', $numero_inquerito, PDO::PARAM_STR);
        $stmt->bindParam(':indiciado', $indiciado, PDO::PARAM_STR);
        $stmt->bindParam(':crime_id', $crime_id, PDO::PARAM_INT);
        $stmt->bindParam(':nome_vitima', $nome_vitima, PDO::PARAM_STR);
        $stmt->bindParam(':data_audiencia', $data_audiencia, PDO::PARAM_STR);
        $stmt->bindParam(':acordo_realizado', $acordo_realizado, PDO::PARAM_STR);
        $stmt->bindParam(':valor_reparacao', $valor_reparacao, PDO::PARAM_STR);
        $stmt->bindParam(':tempo_servico', $tempo_servico, PDO::PARAM_INT);
        $stmt->bindParam(':valor_multa', $valor_multa, PDO::PARAM_STR);
        $stmt->bindParam(':restituicao', $restituicao, PDO::PARAM_STR);

        // Executar a atualização
        if ($stmt->execute()) {
            $_SESSION['mensagem'] = "ANPP atualizado com sucesso!";
            header("Location: ../views/listar_anpp.php");
            exit();
        } else {
            $_SESSION['mensagem'] = "Erro ao atualizar ANPP.";
            header("Location: ../views/editar_anpp.php?id=$id");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = "Erro: " . $e->getMessage();
        header("Location: ../views/editar_anpp.php?id=$id");
        exit();
    }
} else {
    $_SESSION['mensagem'] = "Acesso inválido!";
    header("Location: ../views/listar_anpp.php");
    exit();
}