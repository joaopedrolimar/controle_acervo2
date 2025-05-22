<!--controle_acervo/controllers/processo_controller.php-->
<?php
session_start();
require_once "../config/conexao.php";
require_once "logs_controller.php"; // Importa a função de logs
global $pdo;

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cadastrar'])) {
    $numero = trim($_POST['numero']);
    $natureza = trim($_POST['natureza']);
    $outra_natureza = isset($_POST['outra_natureza']) ? trim($_POST['outra_natureza']) : null;
    $data_denuncia = $_POST['data_denuncia'];
    $crime_id = intval($_POST['crime']); // Agora usamos o ID do crime
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
$status = "Ativo"; // Sempre começa com status "Ativo"

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

        // Insere no banco de dados com crime_id
        $sql = "INSERT INTO processos (numero, natureza, outra_natureza, data_denuncia, crime_id, denunciado, vitima, 
                                       local_municipio, local_bairro, sentenca, outra_sentenca, data_sentenca, recursos, status, usuario_id) 
                VALUES (:numero, :natureza, :outra_natureza, :data_denuncia, :crime_id, :denunciado, :vitima, 
                        :local_municipio, :local_bairro, :sentenca, :outra_sentenca, :data_sentenca, :recursos, :status, :usuario_id)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':numero', $numero, PDO::PARAM_STR);
        $stmt->bindParam(':natureza', $natureza, PDO::PARAM_STR);
        $stmt->bindParam(':outra_natureza', $outra_natureza, PDO::PARAM_STR);
        $stmt->bindParam(':data_denuncia', $data_denuncia, PDO::PARAM_STR);
        $stmt->bindParam(':crime_id', $crime_id, PDO::PARAM_INT); // Agora usamos crime_id
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

        if ($stmt->execute()) {
            $registro_id = $pdo->lastInsertId(); // Pega o ID do processo inserido

            // Registra a ação no log
            registrar_log($usuario_id, "Cadastrou um novo processo", "processos", $registro_id, null, json_encode([
                'numero' => $numero, 
                'natureza' => $natureza,
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
                'status' => $status
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