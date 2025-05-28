<!--/controllers/salvar_edicao_anpp.php-->
<?php
session_start();
require_once "../config/conexao.php";
global $pdo;

// Função para tratar valores monetários do formulário (ex: "1.000,00" -> 1000.00)
function sanitizar_valor($valor) {
    $valor = str_replace('.', '', $valor);       // remove separadores de milhar
    $valor = str_replace(',', '.', $valor);      // troca vírgula por ponto decimal
    return floatval($valor);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $id = intval($_POST['id']);
        $numero_inquerito = trim($_POST['numero_inquerito']);
        $indiciado = trim($_POST['indiciado']);
        $crime_id = intval($_POST['crime']);
        $nome_vitima = !empty($_POST['nome_vitima']) ? trim($_POST['nome_vitima']) : null;
        $data_audiencia = !empty($_POST['data_audiencia']) ? $_POST['data_audiencia'] : null;

        // Ajuste para aceitar valor "realizado"
        $acordo_realizado = (isset($_POST['acordo']) && $_POST['acordo'] === "realizado") ? "sim" : "nao";

        // Inicializa como null por padrão
        $valor_reparacao = null;
        $tempo_servico = null;
        $valor_multa = null;
        $restituicao = null;

        // Preenche os campos adicionais apenas se houver acordo realizado
        if ($acordo_realizado === "sim") {
            $valor_reparacao = (isset($_POST['reparacao']) && $_POST['reparacao'] === "sim" && !empty($_POST['valor_reparacao']))
                ? sanitizar_valor($_POST['valor_reparacao']) : null;

            $tempo_servico = (isset($_POST['servico_comunitario']) && $_POST['servico_comunitario'] === "sim" && !empty($_POST['tempo_servico']))
                ? intval($_POST['tempo_servico']) : null;

            $valor_multa = (isset($_POST['multa']) && $_POST['multa'] === "sim" && !empty($_POST['valor_multa']))
                ? sanitizar_valor($_POST['valor_multa']) : null;

            $restituicao = !empty($_POST['restituicao']) ? trim($_POST['restituicao']) : null;
        }

        // Atualização no banco de dados
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

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':numero_inquerito', $numero_inquerito, PDO::PARAM_STR);
        $stmt->bindParam(':indiciado', $indiciado, PDO::PARAM_STR);
        $stmt->bindParam(':crime_id', $crime_id, PDO::PARAM_INT);
        $stmt->bindParam(':nome_vitima', $nome_vitima, PDO::PARAM_STR);
        $stmt->bindParam(':data_audiencia', $data_audiencia, PDO::PARAM_STR);
        $stmt->bindParam(':acordo_realizado', $acordo_realizado, PDO::PARAM_STR);
        $stmt->bindParam(':valor_reparacao', $valor_reparacao);
        $stmt->bindParam(':tempo_servico', $tempo_servico);
        $stmt->bindParam(':valor_multa', $valor_multa);
        $stmt->bindParam(':restituicao', $restituicao, PDO::PARAM_STR);

        // Executa a atualização
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