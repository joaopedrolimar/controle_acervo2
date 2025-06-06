<!--/controllers/salvar_edicao_anpp.php-->
<?php
session_start();
require_once "../config/conexao.php";
global $pdo;

function sanitizar_valor($valor) {
    $valor = str_replace('.', '', $valor);
    $valor = str_replace(',', '.', $valor);
    return floatval($valor);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $id = intval($_POST['id']);

        // BUSCA ANTIGOS DADOS ANTES DO UPDATE
        $stmt_old = $pdo->prepare("SELECT * FROM anpp WHERE id = :id");
        $stmt_old->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_old->execute();
        $dados_antigos = $stmt_old->fetch(PDO::FETCH_ASSOC);

        // DADOS NOVOS
        $numero_inquerito = trim($_POST['numero_inquerito']);
        $indiciado = trim($_POST['indiciado']);
        $crime_id = intval($_POST['crime']);
        $nome_vitima = !empty($_POST['nome_vitima']) ? trim($_POST['nome_vitima']) : null;
        $data_audiencia = !empty($_POST['data_audiencia']) ? $_POST['data_audiencia'] : null;
        $acordo_realizado = (isset($_POST['acordo']) && $_POST['acordo'] === "realizado") ? "sim" : "nao";

        $valor_reparacao = null;
        $tempo_servico = null;
        $valor_multa = null;
        $restituicao = null;

        if ($acordo_realizado === "sim") {
            $valor_reparacao = (isset($_POST['reparacao']) && $_POST['reparacao'] === "sim" && !empty($_POST['valor_reparacao'])) ? sanitizar_valor($_POST['valor_reparacao']) : null;
            $tempo_servico = (isset($_POST['servico_comunitario']) && $_POST['servico_comunitario'] === "sim" && !empty($_POST['tempo_servico'])) ? intval($_POST['tempo_servico']) : null;
            $valor_multa = (isset($_POST['multa']) && $_POST['multa'] === "sim" && !empty($_POST['valor_multa'])) ? sanitizar_valor($_POST['valor_multa']) : null;
            $restituicao = !empty($_POST['restituicao']) ? trim($_POST['restituicao']) : null;
        }

        // ARRAY DE NOVOS DADOS
        $dados_novos = [
            'numero_inquerito' => $numero_inquerito,
            'indiciado' => $indiciado,
            'crime_id' => $crime_id,
            'nome_vitima' => $nome_vitima,
            'data_audiencia' => $data_audiencia,
            'acordo_realizado' => $acordo_realizado,
            'valor_reparacao' => $valor_reparacao,
            'tempo_servico' => $tempo_servico,
            'valor_multa' => $valor_multa,
            'restituicao' => $restituicao
        ];

        // COMPARA
        $valores_anteriores = [];
        $valores_novos = [];
        foreach ($dados_novos as $campo => $valor_novo) {
            $valor_antigo = $dados_antigos[$campo] ?? null;
            if ((string)$valor_novo !== (string)$valor_antigo) {
                $valores_anteriores[$campo] = $valor_antigo;
                $valores_novos[$campo] = $valor_novo;
            }
        }

        // UPDATE
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

        $stmt->execute([
            ':id' => $id,
            ':numero_inquerito' => $numero_inquerito,
            ':indiciado' => $indiciado,
            ':crime_id' => $crime_id,
            ':nome_vitima' => $nome_vitima,
            ':data_audiencia' => $data_audiencia,
            ':acordo_realizado' => $acordo_realizado,
            ':valor_reparacao' => $valor_reparacao,
            ':tempo_servico' => $tempo_servico,
            ':valor_multa' => $valor_multa,
            ':restituicao' => $restituicao
        ]);

        // LOG DE ATUALIZAÇÃO
        if (!empty($valores_anteriores)) {
            $stmt_log = $pdo->prepare("INSERT INTO logs 
                (usuario_id, acao, tabela_afetada, registro_id, valores_anteriores, valores_novos, data_hora)
                VALUES (:usuario_id, :acao, :tabela_afetada, :registro_id, :valores_anteriores, :valores_novos, NOW())");

            $stmt_log->execute([
                ':usuario_id' => $_SESSION['usuario_id'],
                ':acao' => 'Editou um ANPP',
                ':tabela_afetada' => 'anpp',
                ':registro_id' => $id,
                ':valores_anteriores' => json_encode($valores_anteriores, JSON_UNESCAPED_UNICODE),
                ':valores_novos' => json_encode($valores_novos, JSON_UNESCAPED_UNICODE)
            ]);
        }

        $_SESSION['mensagem'] = "ANPP atualizado com sucesso!";
        header("Location: ../views/listar_anpp.php");
        exit();

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
