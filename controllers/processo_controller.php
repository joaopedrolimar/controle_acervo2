<!--/controllers/processo_controller.php"-->
<?php
session_start();
require_once "../config/conexao.php";
require_once "logs_controller.php";
global $pdo;

if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['cadastrar']) || isset($_POST['continuar_editando']))) {

    $numero = trim($_POST['numero']);
    $data_recebimento = $_POST['data_recebimento'] ?? null;


    $natureza = trim($_POST['natureza']);
    $data_instauracao = $_POST['data_instauracao'] ?? null;

    $outra_natureza = $_POST['outra_natureza'] ?? null;
    $data_denuncia = $_POST['data_denuncia'] ?? null;

    $crime_id = intval($_POST['crime']);
    $denunciado = trim($_POST['denunciado']);

    if (isset($_POST['semVitima'])) {
        $vitima = 'Não há';
    } else {
        $vitima = isset($_POST['vitima']) ? trim($_POST['vitima']) : null;
    }

    $local_municipio = trim($_POST['municipio']);
    $local_bairro = trim($_POST['bairro']);
    $sentenca = trim($_POST['sentenca']);
    $outra_sentenca = isset($_POST['outra_sentenca']) ? trim($_POST['outra_sentenca']) : null;
    $data_sentenca = isset($_POST['data_sentenca']) ? $_POST['data_sentenca'] : null;
    $recursos = trim($_POST['recursos']);

    // Status tratado
    if (isset($_POST['continuar_editando'])) {
        $status = 'Incompleto';
    } else {
        $status_input = $_POST['status'] ?? 'Ativo';
        $status = in_array($status_input, ['Ativo', 'Finalizado']) ? $status_input : 'Ativo';
    }

    // Novos campos booleanos
    $oferecendo_denuncia = 0;
    $arquivamento = 0;
    $realizacao_anpp = 0;
    $requisicao_inquerito = 0;
    $conversao_pic = 0;
    $outra_medida = 0;
    $especifique_outra_medida = isset($_POST['especifique_outra_medida']) ? trim($_POST['especifique_outra_medida']) : null;

    if (isset($_POST['opcoes_finalizado']) && is_array($_POST['opcoes_finalizado'])) {
        foreach ($_POST['opcoes_finalizado'] as $opcao) {
            switch ($opcao) {
                case 'Oferecendo de Denúncia':
                    $oferecendo_denuncia = 1;
                    break;
                case 'Arquivamento':
                    $arquivamento = 1;
                    break;
                case 'Realização de ANPP':
                    $realizacao_anpp = 1;
                    break;
                case 'Requisição de Inquérito':
                    $requisicao_inquerito = 1;
                    break;
                case 'Conversão em PIC':
                    $conversao_pic = 1;
                    break;
                case 'Outra Medida':
                    $outra_medida = 1;
                    break;
            }
        }
    }

    $usuario_id = $_SESSION['usuario_id'];

    try {
        // Número do processo único, exceto em rascunho
        if (isset($_POST['continuar_editando']) && $numero === '') {
            $numero = null;
        }

        if (isset($_POST['cadastrar'])) {
            if (empty($numero)) {
                $_SESSION['mensagem'] = "Erro: O número do processo é obrigatório para cadastro!";
                header("Location: ../views/cadastro_processo.php");
                exit();
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM processos WHERE numero = :numero");
            $stmt->bindParam(':numero', $numero, PDO::PARAM_STR);
            $stmt->execute();
            $existe = $stmt->fetchColumn();

            if ($existe > 0) {
                $_SESSION['mensagem'] = "Erro: O número de processo '$numero' já está cadastrado!";
                header("Location: ../views/cadastro_processo.php");
                exit();
            }
        }

        // INSERE no banco
$sql = "INSERT INTO processos 
    (numero, data_recebimento_denuncia, natureza, data_instauracao, outra_natureza, data_denuncia, crime_id, 
     denunciado, vitima, local_municipio, local_bairro, sentenca, outra_sentenca, 
     data_sentenca, recursos, status, usuario_id, 
     oferecendo_denuncia, arquivamento, realizacao_anpp, requisicao_inquerito, conversao_pic, outra_medida, especifique_outra_medida, data_status_ativo)

 
    VALUES 
    (:numero, :data_recebimento, :natureza, :data_instauracao, :outra_natureza, :data_denuncia, :crime_id, 
     :denunciado, :vitima, :local_municipio, :local_bairro, :sentenca, :outra_sentenca, 
     :data_sentenca, :recursos, :status, :usuario_id,
     :oferecendo_denuncia, :arquivamento, :realizacao_anpp, :requisicao_inquerito, :conversao_pic, :outra_medida, :especifique_outra_medida, :data_status_ativo)
";


        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':numero', $numero, PDO::PARAM_STR);
        $stmt->bindParam(':data_recebimento', $data_recebimento, PDO::PARAM_STR);
        $stmt->bindParam(':natureza', $natureza, PDO::PARAM_STR);
        $stmt->bindParam(':data_instauracao', $data_instauracao, PDO::PARAM_STR);

        $stmt->bindParam(':outra_natureza', $outra_natureza, PDO::PARAM_STR);
        $stmt->bindParam(':data_denuncia', $data_denuncia, PDO::PARAM_STR);
        $stmt->bindParam(':crime_id', $crime_id, PDO::PARAM_INT);
        $stmt->bindParam(':denunciado', $denunciado, PDO::PARAM_STR);
        $stmt->bindParam(':vitima', $vitima, PDO::PARAM_STR);
        $stmt->bindParam(':local_municipio', $local_municipio, PDO::PARAM_STR);
        $stmt->bindParam(':local_bairro', $local_bairro, PDO::PARAM_STR);
        $stmt->bindParam(':sentenca', $sentenca, PDO::PARAM_STR);
        $stmt->bindParam(':outra_sentenca', $outra_sentenca, PDO::PARAM_STR);
        $stmt->bindParam(':data_sentenca', $data_sentenca, PDO::PARAM_STR);
        $stmt->bindParam(':recursos', $recursos, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->bindParam(':oferecendo_denuncia', $oferecendo_denuncia, PDO::PARAM_INT);
        $stmt->bindParam(':arquivamento', $arquivamento, PDO::PARAM_INT);
        $stmt->bindParam(':realizacao_anpp', $realizacao_anpp, PDO::PARAM_INT);
        $stmt->bindParam(':requisicao_inquerito', $requisicao_inquerito, PDO::PARAM_INT);
        $stmt->bindParam(':conversao_pic', $conversao_pic, PDO::PARAM_INT);
        $stmt->bindParam(':outra_medida', $outra_medida, PDO::PARAM_INT);
        $stmt->bindParam(':especifique_outra_medida', $especifique_outra_medida, PDO::PARAM_STR);
        $data_status_ativo = ($status === 'Ativo') ? date('Y-m-d') : null;
$stmt->bindParam(':data_status_ativo', $data_status_ativo, PDO::PARAM_STR);


        if ($stmt->execute()) {
            $registro_id = $pdo->lastInsertId();

            // LOG
            registrar_log($usuario_id, "Cadastrou um novo processo", "processos", $registro_id, null, json_encode([
                'numero' => $numero, 
                'natureza' => $natureza,
                'data_instauracao' => $data_instauracao,

                'outra_natureza' => $outra_natureza,
                'data_denuncia' => $data_denuncia, 
                'crime_id' => $crime_id, 
                'denunciado' => $denunciado, 
                'vitima' => $vitima,
                'local_municipio' => $local_municipio,
                'local_bairro' => $local_bairro,
                'sentenca' => $sentenca,
                'outra_sentenca' => $outra_sentenca,
                'data_sentenca' => $data_sentenca,
                'recursos' => $recursos,
                'status' => $status,
                'oferecendo_denuncia' => $oferecendo_denuncia,
                'arquivamento' => $arquivamento,
                'realizacao_anpp' => $realizacao_anpp,
                'requisicao_inquerito' => $requisicao_inquerito,
                'conversao_pic' => $conversao_pic,
                'outra_medida' => $outra_medida,
                'especifique_outra_medida' => $especifique_outra_medida
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