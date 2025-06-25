<?php
require_once "../config/conexao.php";
require_once "../vendor/autoload.php";

use Dompdf\Options;
use Dompdf\Dompdf;

$tipo = $_POST['tipo'] ?? null;
if (!$tipo) die("Tipo de relatório não especificado.");

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Caminho da logo institucional
$logoPath = 'http://localhost/controle_acervo2/public/img/logo.png';

$html = '
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    h1, h2, h3 { text-align: center; margin-bottom: 5px; }
    .logo { text-align: center; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    td, th { border: 1px solid #000; padding: 6px; text-align: left; }
</style>

<div class="logo">
    <img src="' . $logoPath . '" height="80">
</div>

<h2>Ministério Público do Estado do Amazonas</h2>
<h3>Procuradoria-Geral de Justiça</h3>
<h1>Sistema de Acervo</h1>
';

if ($tipo === "tempo_denuncia_recebimento") {
    $stmt = $pdo->query("
        SELECT numero, natureza, data_denuncia, data_recebimento_denuncia
        FROM processos
        WHERE data_denuncia IS NOT NULL AND data_recebimento_denuncia IS NOT NULL
    ");

    $html .= "<h2>Tempo entre Data da Denúncia e Recebimento</h2>";
    $html .= "<table><tr>
                <th>Número do Processo</th>
                <th>Natureza</th>
                <th>Data da Denúncia</th>
                <th>Data de Recebimento</th>
                <th>Tempo (dias)</th>
              </tr>";

    $totalDias = 0;
    $quantidade = 0;
    $ignorados = 0;

    foreach ($stmt as $row) {
        $dataDenuncia = $row['data_denuncia'];
        $dataRecebimento = $row['data_recebimento_denuncia'];

        if (
            $dataDenuncia !== '0000-00-00' && $dataRecebimento !== '0000-00-00' &&
            strtotime($dataDenuncia) && strtotime($dataRecebimento)
        ) {
            try {
                $data1 = new DateTime($dataDenuncia);
                $data2 = new DateTime($dataRecebimento);
                $dias = $data1->diff($data2)->days;

                $totalDias += $dias;
                $quantidade++;

                $html .= "<tr>
                            <td>{$row['numero']}</td>
                            <td>{$row['natureza']}</td>
                            <td>" . date('d/m/Y', strtotime($dataDenuncia)) . "</td>
                            <td>" . date('d/m/Y', strtotime($dataRecebimento)) . "</td>
                            <td>$dias dias</td>
                          </tr>";
            } catch (Exception $e) {
                $ignorados++;
                continue;
            }
        } else {
            $ignorados++;
        }
    }

    $media = ($quantidade > 0) ? round($totalDias / $quantidade) : 0;

    $html .= "</table>";
    $html .= "<p><strong>Média de tempo entre denúncia e recebimento:</strong> {$media} dias</p>";

    if ($ignorados > 0) {
        $html .= "<p><em>{$ignorados} registros foram ignorados por conterem datas inválidas.</em></p>";
    }
}

elseif ($tipo === "crimes_bairro") {
    $stmt = $pdo->query("
        SELECT COALESCE(bairros.nome, 'Não informado') AS bairro, COUNT(*) as total
        FROM processos
        LEFT JOIN bairros ON processos.local_bairro = bairros.id
        GROUP BY bairro
        ORDER BY total DESC
    ");

    $html .= "<h2>Crimes por Bairro</h2>";
    $html .= "<table><tr><th>Bairro</th><th>Total de Crimes</th></tr>";

    foreach ($stmt as $row) {
        $html .= "<tr><td>{$row['bairro']}</td><td>{$row['total']}</td></tr>";
    }

    $html .= "</table>";
}

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("relatorio.pdf", ["Attachment" => false]); // Exibe no navegador
exit;