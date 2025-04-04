<?php
require_once '../vendor/autoload.php';
require_once '../config/conexao.php';

use Dompdf\Options;
use Dompdf\Dompdf;

// Configurações do Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Verifica o ID
$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID do ANPP não fornecido.");
}

// Consulta os dados do ANPP
$stmt = $pdo->prepare("SELECT anpp.*, crimes_anpp.nome AS crime_nome 
                       FROM anpp 
                       LEFT JOIN crimes_anpp ON anpp.crime_id = crimes_anpp.id 
                       WHERE anpp.id = ?");
$stmt->execute([$id]);
$anpp = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$anpp) {
    die("ANPP não encontrado.");
}

function exibir($valor) {
    return !empty($valor) ? htmlspecialchars($valor) : 'Não há';
}

function formatarData($data) {
    return (!empty($data) && $data !== '0000-00-00') ? date("d/m/Y", strtotime($data)) : 'Não há';
}

function formatarReais($valor) {
    return ($valor !== null && $valor !== '') ? 'R$ ' . number_format($valor, 2, ',', '.') : 'Não';
}

$logoPath = "http://localhost/controle_acervo2/public/img/logo.png";

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
<h1>Termo de ANPP</h1>

<div class="section">
    <h3>🔍 Dados do ANPP</h3>
    <table>
        <tr><td><strong>ID:</strong></td><td>' . $anpp['id'] . '</td></tr>
        <tr><td><strong>Nº do Inquérito:</strong></td><td>' . exibir($anpp['numero_inquerito']) . '</td></tr>
        <tr><td><strong>Indiciado:</strong></td><td>' . exibir($anpp['indiciado']) . '</td></tr>
        <tr><td><strong>Crime:</strong></td><td>' . exibir($anpp['crime_nome']) . '</td></tr>
        <tr><td><strong>Nome da Vítima:</strong></td><td>' . exibir($anpp['nome_vitima']) . '</td></tr>
        <tr><td><strong>Data da Audiência:</strong></td><td>' . formatarData($anpp['data_audiencia']) . '</td></tr>
        <tr><td><strong>Acordo Realizado:</strong></td><td>' . exibir($anpp['acordo_realizado']) . '</td></tr>
        <tr><td><strong>Valor de Reparação:</strong></td><td>' . formatarReais($anpp['valor_reparacao']) . '</td></tr>
        <tr><td><strong>Tempo de Serviço:</strong></td><td>' . (!empty($anpp['tempo_servico']) ? $anpp['tempo_servico'] . ' horas' : 'Não') . '</td></tr>
        <tr><td><strong>Multa:</strong></td><td>' . formatarReais($anpp['valor_multa']) . '</td></tr>
        <tr><td><strong>Restituição:</strong></td><td>' . exibir($anpp['restituicao']) . '</td></tr>
    </table>
</div>
';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("anpp_{$id}.pdf", ["Attachment" => false]);
exit;