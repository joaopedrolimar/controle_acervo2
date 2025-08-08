<?php
require_once '../vendor/autoload.php';
require_once '../config/conexao.php';

use Dompdf\Options;
use Dompdf\Dompdf;

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$id = $_GET['id'] ?? null;
if (!$id) die("ID não fornecido.");

$stmt = $pdo->prepare("SELECT p.*, c.nome AS crime_nome, m.nome AS municipio_nome, b.nome AS bairro_nome 
                       FROM processos p
                       LEFT JOIN crimes c ON p.crime_id = c.id
                       LEFT JOIN municipios m ON p.local_municipio = m.id
                       LEFT JOIN bairros b ON p.local_bairro = b.id
                       WHERE p.id = ?");
$stmt->execute([$id]);
$proc = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$proc) die("Processo não encontrado.");

function exibir($v) {
    return !empty($v) ? htmlspecialchars($v) : 'Não há';
}
function dataFmt($d) {
    return (!empty($d) && $d!='0000-00-00') ? date('d/m/Y', strtotime($d)) : 'Não há';
}

$logoPath = 'http://localhost/controle_acervo2/public/img/logo.png';

// Ajuste do rótulo do denunciado
switch ($proc['natureza']) {
    case 'Inquérito Policial': $labelDenunciado = 'Flagrado/Indiciado'; break;
    case 'PIC':                 $labelDenunciado = 'Investigado'; break;
    case 'NF':                  $labelDenunciado = 'Noticiado'; break;
    case 'Outra':               $labelDenunciado = 'Investigado/Requerido'; break;
    default:                    $labelDenunciado = 'Denunciado';
}

// Monta decisões finais
$decisoes = [];
if ($proc['oferecendo_denuncia']) $decisoes[] = 'Oferecimento Denúncia';
if ($proc['arquivamento']) $decisoes[] = 'Arquivamento';
if ($proc['realizacao_anpp']) $decisoes[] = 'Realização ANPP';
if ($proc['requisicao_inquerito']) $decisoes[] = 'Requisição Inquérito';
if ($proc['conversao_pic']) $decisoes[] = 'Conversão PIC';
if ($proc['outra_medida']) $decisoes[] = $proc['especifique_outra_medida'] ?: 'Outra Medida';

// Ajusta sentença para exibir "Outra (texto)" se necessário
$sentenca = exibir($proc['sentenca']);
if ($proc['sentenca'] === 'Outra' && !empty($proc['outra_sentenca'])) {
    $sentenca .= ' (' . htmlspecialchars($proc['outra_sentenca']) . ')';
}

// Monta HTML
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
<h4 style="text-align: center;">93a. Promotoria de Justiça de Manaus</h4>
<h1>Sistema de Acervo</h1>

<div class="section">
    <h3>🧾 Dados do Processo</h3>
    <table>
        <tr><td><strong>ID:</strong></td><td>' . $proc['id'] . '</td></tr>
        <tr><td><strong>Número:</strong></td><td>' . exibir($proc['numero']) . '</td></tr>
        <tr><td><strong>Natureza:</strong></td><td>' . exibir($proc['natureza']) . '</td></tr>
        ' . (!empty($proc['data_instauracao']) && $proc['data_instauracao'] != '0000-00-00' ? 
        '<tr><td><strong>Data da Instauração:</strong></td><td>' . dataFmt($proc['data_instauracao']) . '</td></tr>' 
        : '') . '
        <tr><td><strong>Data da Denúncia:</strong></td><td>' . dataFmt($proc['data_denuncia']) . '</td></tr>
        <tr><td><strong>Data do Recebimento da Denúncia:</strong></td><td>' . dataFmt($proc['data_recebimento_denuncia']) . '</td></tr>
        <tr><td><strong>Crime:</strong></td><td>' . exibir($proc['crime_nome']) . '</td></tr>
    </table>
</div>


<div class="section">
    <h3>👤 Envolvidos</h3>
    <table>
        <tr><td><strong>Vítima:</strong></td><td>' . exibir($proc['vitima']) . '</td></tr>
        <tr><td><strong>' . $labelDenunciado . ':</strong></td><td>' . exibir($proc['denunciado']) . '</td></tr>
    </table>
</div>

<div class="section">
    <h3>📍 Local do Fato</h3>
    <table>
        <tr><td><strong>Município:</strong></td><td>' . exibir($proc['municipio_nome']) . '</td></tr>
        <tr><td><strong>Bairro:</strong></td><td>' . exibir($proc['bairro_nome']) . '</td></tr>
    </table>
</div>

<div class="section">
    <h3>📄 Informações Finais</h3>
    <table>
        <tr><td><strong>Sentença:</strong></td><td>' . $sentenca . '</td></tr>
        <tr><td><strong>Data Sentença:</strong></td><td>' . dataFmt($proc['data_sentenca']) . '</td></tr>
        <tr><td><strong>Recursos:</strong></td><td>' . exibir($proc['recursos']) . '</td></tr>
        <tr><td><strong>Status:</strong></td><td>' . exibir($proc['status']) . '</td></tr>
    </table>
</div>';

if ($decisoes) {
    $html .= '
    <div class="section">
        <h3>⚖️ Decisões Finais</h3>
        <table>
            <tr><td>' . implode(', ', $decisoes) . '</td></tr>
        </table>
    </div>';
}

$html .= '</body>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("processo_{$id}.pdf", ["Attachment" => false]);
exit;
?>