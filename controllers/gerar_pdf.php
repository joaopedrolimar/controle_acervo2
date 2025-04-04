<?php
require_once '../vendor/autoload.php';
require_once '../config/conexao.php';

use Dompdf\Options;
use Dompdf\Dompdf;

// Ativa imagens remotas
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// ID do processo
$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID do processo não fornecido.");
}

// Consulta
$stmt = $pdo->prepare("SELECT p.*, c.nome AS crime_nome, m.nome AS municipio_nome, b.nome AS bairro_nome 
                       FROM processos p
                       LEFT JOIN crimes c ON p.crime_id = c.id
                       LEFT JOIN municipios m ON p.local_municipio = m.id
                       LEFT JOIN bairros b ON p.local_bairro = b.id
                       WHERE p.id = ?");
$stmt->execute([$id]);
$processo = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$processo) {
    die("Processo não encontrado.");
}

// Funções auxiliares
function exibir($valor) {
    return !empty($valor) ? htmlspecialchars($valor) : 'Não há';
}
function dataFormatada($data) {
    return (!empty($data) && $data !== '0000-00-00') ? date('d/m/Y', strtotime($data)) : 'Não há';
}

// Caminho via HTTP (acessível ao navegador e DomPDF)
$logoPath = 'http://localhost/controle_acervo2/public/img/logo.png';

// HTML do PDF
$html = '
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    h1, h2, h3 { text-align: center; margin-bottom: 5px; }
    .logo { text-align: center; margin-bottom: 10px; }
    .section { margin-top: 20px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    td, th { border: 1px solid #000; padding: 6px; text-align: left; }
</style>

<div class="logo">
    <img src="' . $logoPath . '" height="80">
</div>

<h2>Ministério Público do Estado do Amazonas</h2>
<h3>Procuradoria-Geral de Justiça</h3>
<h1>Sistema de Acervo</h1>

<div class="section">
    <h3>🧾 Dados do Processo</h3>
    <table>
        <tr><td><strong>ID:</strong></td><td>' . $processo['id'] . '</td></tr>
        <tr><td><strong>Número:</strong></td><td>' . exibir($processo['numero']) . '</td></tr>
        <tr><td><strong>Natureza:</strong></td><td>' . exibir($processo['natureza']) . '</td></tr>
        <tr><td><strong>Data da Denúncia:</strong></td><td>' . dataFormatada($processo['data_denuncia']) . '</td></tr>
        <tr><td><strong>Crime:</strong></td><td>' . exibir($processo['crime_nome']) . '</td></tr>
    </table>
</div>

<div class="section">
    <h3>👤 Envolvidos</h3>
    <table>
        <tr><td><strong>Vítima:</strong></td><td>' . exibir($processo['vitima']) . '</td></tr>
        <tr><td><strong>Denunciado:</strong></td><td>' . exibir($processo['denunciado']) . '</td></tr>
    </table>
</div>

<div class="section">
    <h3>📍 Local do Fato</h3>
    <table>
        <tr><td><strong>Município:</strong></td><td>' . exibir($processo['municipio_nome']) . '</td></tr>
        <tr><td><strong>Bairro:</strong></td><td>' . exibir($processo['bairro_nome']) . '</td></tr>
    </table>
</div>

<div class="section">
    <h3>📄 Informações Finais</h3>
    <table>
        <tr><td><strong>Sentença:</strong></td><td>' . exibir($processo['sentenca']) . '</td></tr>
        <tr><td><strong>Recursos:</strong></td><td>' . exibir($processo['recursos']) . '</td></tr>
        <tr><td><strong>Status:</strong></td><td>' . exibir($processo['status']) . '</td></tr>
    </table>
</div>
';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("processo_{$id}.pdf", ["Attachment" => false]);
exit;