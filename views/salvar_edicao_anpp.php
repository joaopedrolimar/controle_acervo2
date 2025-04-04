<?php
session_start();
require_once "../config/conexao.php";
require_once "logs_controller.php"; // para registrar_log()
global $pdo;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Buscar valores antigos para log
    $stmtOld = $pdo->prepare("SELECT * FROM anpp WHERE id = :id");
    $stmtOld->bindParam(':id', $id);
    $stmtOld->execute();
    $dadosAntigos = $stmtOld->fetch(PDO::FETCH_ASSOC);

    // Capturar dados novos
    $dadosNovos = [
        'numero_inquerito' => $_POST['numero_inquerito'],
        'indiciado' => $_POST['indiciado'],
        'crime_id' => $_POST['crime'],
        'nome_vitima' => $_POST['nome_vitima'] ?? null,
        'data_audiencia' => $_POST['data_audiencia'] ?? null,
        'acordo_realizado' => $_POST['acordo'] ?? 'nao',
        'valor_reparacao' => $_POST['valor_reparacao'] !== '' ? $_POST['valor_reparacao'] : null,
        'tempo_servico' => $_POST['tempo_servico'] !== '' ? $_POST['tempo_servico'] : null,
        'valor_multa' => $_POST['valor_multa'] !== '' ? $_POST['valor_multa'] : null,
        'restituicao' => $_POST['restituicao'] ?? null
    ];

    $sql = "UPDATE anpp SET 
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
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge($dadosNovos, ['id' => $id]));

    // Registrar log da edição
    registrar_log(
        $_SESSION['usuario_id'],
        "Editou um ANPP",
        "anpp",
        $id,
        json_encode($dadosAntigos),
        json_encode($dadosNovos)
    );

    $_SESSION['mensagem'] = "ANPP atualizado com sucesso!";
    header("Location: ../views/listar_anpp.php");
    exit();
} else {
    $_SESSION['mensagem'] = "Requisição inválida!";
    header("Location: ../views/listar_anpp.php");
    exit();
}