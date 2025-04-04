<?php
session_start();
require_once "../config/conexao.php";
global $pdo;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $numero_inquerito = trim($_POST['numero_inquerito']);
        $indiciado = trim($_POST['indiciado']);
        $crime_id = intval($_POST['crime']);
        $nome_vitima = !empty($_POST['nome_vitima']) ? trim($_POST['nome_vitima']) : null;
        $data_audiencia = !empty($_POST['data_audiencia']) ? $_POST['data_audiencia'] : null;
        $acordo_realizado = isset($_POST['acordo']) && $_POST['acordo'] === "realizado" ? "sim" : "nao";

        // Corrige verificacao incorreta
        $valor_reparacao = null;
        $tempo_servico = null;
        $valor_multa = null;
        $restituicao = null;

        if ($acordo_realizado === "sim") {
            $valor_reparacao = (isset($_POST['reparacao']) && $_POST['reparacao'] === "sim" && $_POST['valor_reparacao'] !== '')
                ? floatval($_POST['valor_reparacao']) : null;

            $tempo_servico = (isset($_POST['servico_comunitario']) && $_POST['servico_comunitario'] === "sim" && $_POST['tempo_servico'] !== '')
                ? intval($_POST['tempo_servico']) : null;

            $valor_multa = (isset($_POST['multa']) && $_POST['multa'] === "sim" && $_POST['valor_multa'] !== '')
                ? floatval($_POST['valor_multa']) : null;

            $restituicao = !empty($_POST['restituicao']) ? trim($_POST['restituicao']) : null;
        }

        $sql = "INSERT INTO anpp 
            (numero_inquerito, indiciado, crime_id, nome_vitima, data_audiencia, acordo_realizado, 
            valor_reparacao, tempo_servico, valor_multa, restituicao) 
            VALUES (:numero_inquerito, :indiciado, :crime_id, :nome_vitima, :data_audiencia, :acordo_realizado, 
            :valor_reparacao, :tempo_servico, :valor_multa, :restituicao)";

        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':numero_inquerito', $numero_inquerito, PDO::PARAM_STR);
        $stmt->bindParam(':indiciado', $indiciado, PDO::PARAM_STR);
        $stmt->bindParam(':crime_id', $crime_id, PDO::PARAM_INT);
        $stmt->bindParam(':nome_vitima', $nome_vitima, PDO::PARAM_STR);
        $stmt->bindParam(':data_audiencia', $data_audiencia, PDO::PARAM_STR);
        $stmt->bindValue(':acordo_realizado', $acordo_realizado, PDO::PARAM_STR);

        $stmt->bindParam(':valor_reparacao', $valor_reparacao);
        $stmt->bindParam(':tempo_servico', $tempo_servico);
        $stmt->bindParam(':valor_multa', $valor_multa);
        $stmt->bindParam(':restituicao', $restituicao);

        if ($stmt->execute()) {
            // Registrar log de cadastro
    require_once "logs_controller.php"; // <- ajuste o caminho se necessário
    registrar_log(
        $_SESSION['usuario_id'],
        "Cadastro",
        "anpp",
        $pdo->lastInsertId(),
        null,
        json_encode($_POST)
    );

            $_SESSION['mensagem'] = "ANPP cadastrado com sucesso!";
            header("Location: ../views/anpp.php");
            exit();
        } else {
            $_SESSION['mensagem'] = "Erro ao cadastrar ANPP.";
            header("Location: ../views/anpp.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = "Erro: " . $e->getMessage();
        header("Location: ../views/anpp.php");
        exit();
    }
} else {
    $_SESSION['mensagem'] = "Acesso inválido!";
    header("Location: ../views/anpp.php");
    exit();
}