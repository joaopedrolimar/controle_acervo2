<!--controllers/gerar_pdf.php-->
<?php
require('../fpdf/fpdf.php'); // Certifique-se de que o caminho está correto
require_once "../config/conexao.php"; // Conexão com o banco

// Verifica se foi passado um ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID inválido.");
}

$id = $_GET['id'];

// Busca os dados do processo no banco
$stmt = $pdo->prepare("
    SELECT processos.*, 
           crimes.nome AS nome_crime, 
           municipios.nome AS nome_municipio, 
           bairros.nome AS nome_bairro
    FROM processos 
    LEFT JOIN crimes ON processos.crime_id = crimes.id
    LEFT JOIN municipios ON processos.local_municipio = municipios.id
    LEFT JOIN bairros ON processos.local_bairro = bairros.id
    WHERE processos.id = :id
");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$processo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$processo) {
    die("Processo não encontrado.");
}

// Criando o PDF com FPDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(190, 10, 'Detalhes do Processo', 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 10, 'Número:', 0, 0);
$pdf->Cell(140, 10, $processo['numero'], 0, 1);

$pdf->Cell(50, 10, 'Natureza:', 0, 0);
$pdf->Cell(140, 10, $processo['natureza'], 0, 1);

$pdf->Cell(50, 10, 'Data da Denúncia:', 0, 0);
$pdf->Cell(140, 10, date('d/m/Y', strtotime($processo['data_denuncia'])), 0, 1);

$pdf->Cell(50, 10, 'Crime:', 0, 0);
$pdf->Cell(140, 10, $processo['nome_crime'], 0, 1);

$pdf->Cell(50, 10, 'Denunciado:', 0, 0);
$pdf->Cell(140, 10, $processo['denunciado'], 0, 1);

$pdf->Cell(50, 10, 'Vítima:', 0, 0);
$pdf->Cell(140, 10, $processo['vitima'] ?? 'Não há', 0, 1);

$pdf->Cell(50, 10, 'Local do Crime:', 0, 0);
$pdf->Cell(140, 10, $processo['nome_municipio'] . ' - ' . $processo['nome_bairro'], 0, 1);

$pdf->Cell(50, 10, 'Sentença:', 0, 0);
$pdf->Cell(140, 10, $processo['sentenca'], 0, 1);

$pdf->Cell(50, 10, 'Recursos:', 0, 0);
$pdf->Cell(140, 10, $processo['recursos'], 0, 1);

$pdf->Cell(50, 10, 'Status:', 0, 0);
$pdf->Cell(140, 10, $processo['status'], 0, 1);

$pdf->Output('D', "Processo_{$processo['numero']}.pdf"); // Força o download do PDF
exit();
?>
