<!--/controle_acervo/controllers/uploud_ato.php-->
<?php
require_once "../config/conexao.php";
global $pdo;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $categoria = $_POST['categoria'];
    $titulo = trim($_POST['titulo'] ?? '');
    $link = trim($_POST['link'] ?? '');
    $arquivo = $_FILES['arquivo'] ?? null;

    try {
        if (!empty($link)) {
            $sql = "INSERT INTO atos (nome_arquivo, caminho, categoria, tipo, titulo) 
                    VALUES (:nome, :caminho, :categoria, 'link', :titulo)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $link,
                ':caminho' => $link,
                ':categoria' => $categoria,
                ':titulo' => !empty($titulo) ? $titulo : $link
            ]);
        } elseif ($arquivo && $arquivo['error'] == 0) {
            $uploadDir = '../uploads/atos/';
            $fileName = basename($arquivo['name']);
            $filePath = $uploadDir . time() . '_' . $fileName;

            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            if (move_uploaded_file($arquivo['tmp_name'], $filePath)) {
                $sql = "INSERT INTO atos (nome_arquivo, caminho, categoria, tipo, titulo) 
                        VALUES (:nome, :caminho, :categoria, 'arquivo', :titulo)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nome' => $fileName,
                    ':caminho' => $filePath,
                    ':categoria' => $categoria,
                    ':titulo' => !empty($titulo) ? $titulo : $fileName
                ]);
            }
        }
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}

header("Location: ../views/atos.php");
exit();