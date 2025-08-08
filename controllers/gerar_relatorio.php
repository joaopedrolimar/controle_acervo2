<?php
require_once "../config/conexao.php";
require_once "../vendor/autoload.php";

use Dompdf\Options;
use Dompdf\Dompdf;

$tipo = $_POST['tipo'] ?? null;
$data_inicial = $_POST['data_inicial'] ?? null;
$data_final = $_POST['data_final'] ?? null;

if (!$tipo) die("Tipo de relatório não especificado.");

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

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
<h4 style="text-align: center;">93a. Promotoria de Justiça de Manaus</h4>
<h1>Sistema de Acervo</h1>
';

if (!empty($data_inicial) || !empty($data_final)) {
    $inicioFmt = !empty($data_inicial) ? date('d/m/Y', strtotime($data_inicial)) : '---';
    $finalFmt = !empty($data_final) ? date('d/m/Y', strtotime($data_final)) : '---';
    $html .= "<p style='text-align:center; margin-top:10px;'><strong>Período do relatório:</strong> {$inicioFmt} a {$finalFmt}</p>";
}

/**
 * Relatório Vítima Pessoa Idosa
 */
if ($tipo === "vitima_idosa") {
    $sql = "
        SELECT 
            p.numero,
            c.nome AS crime, 
            YEAR(p.data_denuncia) AS ano_denuncia,
            COALESCE(b.nome, 'Não informado') AS bairro,
            p.vitima
        FROM processos p
        LEFT JOIN crimes c ON p.crime_id = c.id
        LEFT JOIN bairros b ON p.local_bairro = b.id
        WHERE p.vitima LIKE :termo
    ";

    $params = [':termo' => '%idosa%'];

    if (!empty($data_inicial)) {
        $sql .= " AND p.data_denuncia >= :data_inicial";
        $params[':data_inicial'] = $data_inicial;
    }

    if (!empty($data_final)) {
        $sql .= " AND p.data_denuncia <= :data_final";
        $params[':data_final'] = $data_final;
    }

    $sql .= " ORDER BY ano_denuncia DESC, p.numero";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $intervalo = "";
    if (!empty($data_inicial) && !empty($data_final)) {
        $intervalo = " de " . date('d/m/Y', strtotime($data_inicial)) . " até " . date('d/m/Y', strtotime($data_final));
    } elseif (!empty($data_inicial)) {
        $intervalo = " a partir de " . date('d/m/Y', strtotime($data_inicial));
    } elseif (!empty($data_final)) {
        $intervalo = " até " . date('d/m/Y', strtotime($data_final));
    }

    $html .= "<h2>Vítima: Pessoa Idosa{$intervalo}</h2>";
    $html .= "<table>
                <tr>
                    <th>Número</th>
                    <th>Crime</th>
                    <th>Ano da Denúncia</th>
                    <th>Bairro</th>
                    <th>Nome da Vítima</th>
                </tr>";

                $total_vitimas_idosas = 0;

    foreach ($stmt as $row) {
        $html .= "<tr>
                    <td>{$row['numero']}</td>
                    <td>{$row['crime']}</td>
                    <td>{$row['ano_denuncia']}</td>
                    <td>{$row['bairro']}</td>
                    <td>{$row['vitima']}</td>
                  </tr>";

                  $total_vitimas_idosas++;
    }

    $html .= "</table>";
    // Mostra o total de vítimas idosas
$html .= "<p><strong>Total de vítimas idosas registradas:</strong> {$total_vitimas_idosas}</p>";
}


/**
 * Relatório Tipo do Crime, Ano da Denúncia, Bairro, Número do Processo e Nome da Vítima
 */
elseif ($tipo === "tipo_crime_ano_bairro") {
    $sql = "
        SELECT 
            p.numero,
            c.nome AS crime, 
            YEAR(p.data_denuncia) AS ano_denuncia,
            COALESCE(b.nome, 'Não informado') AS bairro,
            p.vitima
        FROM processos p
        LEFT JOIN crimes c ON p.crime_id = c.id
        LEFT JOIN bairros b ON p.local_bairro = b.id
        WHERE 1=1
    ";

    $params = [];

    if (!empty($data_inicial)) {
        $sql .= " AND p.data_denuncia >= :data_inicial";
        $params[':data_inicial'] = $data_inicial;
    }

    if (!empty($data_final)) {
        $sql .= " AND p.data_denuncia <= :data_final";
        $params[':data_final'] = $data_final;
    }

    // Ordenar por ano primeiro
    $sql .= " ORDER BY ano_denuncia DESC, crime, bairro";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Intervalo exibido no título
    $intervalo = "";
    if (!empty($data_inicial) && !empty($data_final)) {
        $intervalo = " de " . date('d/m/Y', strtotime($data_inicial)) . " até " . date('d/m/Y', strtotime($data_final));
    } elseif (!empty($data_inicial)) {
        $intervalo = " a partir de " . date('d/m/Y', strtotime($data_inicial));
    } elseif (!empty($data_final)) {
        $intervalo = " até " . date('d/m/Y', strtotime($data_final));
    }

    $html .= "<h2>Tipo do Crime, Ano da Denúncia, Bairro e Vítima{$intervalo}</h2>";
    $html .= "<table>
                <tr>
                    <th>Número do Processo</th>
                    <th>Tipo do Crime</th>
                    <th>Ano da Denúncia</th>
                    <th>Bairro</th>
                    <th>Nome da Vítima</th>
                </tr>";

    foreach ($stmt as $row) {
        $html .= "<tr>
                    <td>{$row['numero']}</td>
                    <td>{$row['crime']}</td>
                    <td>{$row['ano_denuncia']}</td>
                    <td>{$row['bairro']}</td>
                    <td>{$row['vitima']}</td>
                  </tr>";
    }

    $html .= "</table>";
}

/**
 * Relatório Oferecimento de Denúncia por Ano
 */
elseif ($tipo === "oferecimento_denuncia_ano") {
    $sql = "
        SELECT YEAR(data_denuncia) AS ano, COUNT(*) AS total
        FROM processos
        WHERE data_denuncia IS NOT NULL AND data_denuncia != '0000-00-00'
    ";
    $params = [];
    if (!empty($data_inicial)) {
        $sql .= " AND data_denuncia >= :data_inicial";
        $params[':data_inicial'] = $data_inicial;
    }
    if (!empty($data_final)) {
        $sql .= " AND data_denuncia <= :data_final";
        $params[':data_final'] = $data_final;
    }
    $sql .= " GROUP BY ano ORDER BY ano DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $html .= "<h2>Oferecimento de Denúncia por Ano</h2>";
    $html .= "<table><tr><th>Ano</th><th>Total de Denúncias</th></tr>";
    foreach ($stmt as $row) {
        $html .= "<tr><td>{$row['ano']}</td><td>{$row['total']}</td></tr>";
    }
    $html .= "</table>";
}


// Relatório: Tempo entre Denúncia e Recebimento
if ($tipo === "tempo_denuncia_recebimento") {
$stmt = $pdo->query("
    SELECT id, numero, natureza, data_denuncia, data_recebimento_denuncia, status
    FROM processos
    WHERE data_denuncia IS NOT NULL AND data_recebimento_denuncia IS NOT NULL
");


    $html .= "<h2>Tempo médio entre o oferecimento da Denúncia e Recebimento da Denúncia</h2>";
$html .= "<table><tr>
            <th>ID</th>
            <th>Número do Processo</th>
            <th>Natureza</th>
            <th>Status</th>
            <th>Data da Denúncia</th>
            <th>Data de Recebimento</th>
            <th>Tempo (dias)</th>
          </tr>";


    $linhas = [];
    $totalDias = 0;
    $quantidade = 0;
    $ignorados = 0; // contador de registros ignorados

    foreach ($stmt as $row) {
        if ($row['data_denuncia'] < '2000-01-01' || $row['data_recebimento_denuncia'] < '2000-01-01') {
            $ignorados++;
            continue; // pula registros ruins
        }

        $data1 = new DateTime($row['data_denuncia']);
        $data2 = new DateTime($row['data_recebimento_denuncia']);
        $dias = $data1->diff($data2)->days;

        $totalDias += $dias;
        $quantidade++;

$linhas[] = [
    'id' => $row['id'],
    'numero' => $row['numero'],
    'natureza' => $row['natureza'],
    'status' => $row['status'],
    'data_denuncia' => date('d/m/Y', strtotime($row['data_denuncia'])),
    'data_recebimento' => date('d/m/Y', strtotime($row['data_recebimento_denuncia'])),
    'dias' => $dias
];

    }

    // Ordena do maior para o menor tempo
    usort($linhas, function($a, $b) {
        return $b['dias'] <=> $a['dias'];
    });

    // Preenche a tabela com os dados ordenados
foreach ($linhas as $linha) {
    $html .= "<tr>
                <td>{$linha['id']}</td>
                <td>{$linha['numero']}</td>
                <td>{$linha['natureza']}</td>
                <td>{$linha['status']}</td>
                <td>{$linha['data_denuncia']}</td>
                <td>{$linha['data_recebimento']}</td>
                <td>{$linha['dias']} dias</td>
              </tr>";
}


    $media = ($quantidade > 0) ? round($totalDias / $quantidade) : 0;

    $html .= "</table>";
    $html .= "<p><strong>Média de tempo entre denúncia e recebimento:</strong> {$media} dias</p>";

    // Mostra o total de registros ignorados por data inválida
    if ($ignorados > 0) {
        $html .= "<p style='font-style: italic; font-size: 13px; color: #333;'>
                    {$ignorados} registros foram ignorados por conterem datas inválidas.
                  </p>";
    }
}

/**
 * Relatório Crimes por Bairro e Ano
 */
elseif ($tipo === "crimes_bairro_ano") {
    // Monta base da query
    $sql = "
        SELECT 
            CASE 
                WHEN b.nome IS NULL OR b.nome = '' 
                     OR LOWER(b.nome) LIKE '%não informado%' 
                     OR LOWER(b.nome) LIKE '%indefinido%' 
                THEN 'Desconhecido'
                ELSE b.nome
            END AS bairro,
            YEAR(p.data_denuncia) AS ano, 
            COUNT(*) AS total
        FROM processos p
        LEFT JOIN bairros b ON p.local_bairro = b.id
        WHERE 1=1
    ";

    // Parâmetros e filtros
    $params = [];
    if (!empty($data_inicial)) {
        $sql .= " AND p.data_denuncia >= :data_inicial";
        $params[':data_inicial'] = $data_inicial;
    }
    if (!empty($data_final)) {
        $sql .= " AND p.data_denuncia <= :data_final";
        $params[':data_final'] = $data_final;
    }

    // Agrupa e ordena por ano DESC, total DESC, e coloca 'Desconhecido' por último
    $sql .= "
        GROUP BY bairro, ano
        ORDER BY 
            ano DESC,
            total DESC,
            CASE WHEN LOWER(bairro) = 'desconhecido' THEN 1 ELSE 0 END
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Define o título com intervalo (se houver filtro)
    $intervalo = "";
    if (!empty($data_inicial) && !empty($data_final)) {
        $intervalo = " de " . date('d/m/Y', strtotime($data_inicial)) . " até " . date('d/m/Y', strtotime($data_final));
    } elseif (!empty($data_inicial)) {
        $intervalo = " a partir de " . date('d/m/Y', strtotime($data_inicial));
    } elseif (!empty($data_final)) {
        $intervalo = " até " . date('d/m/Y', strtotime($data_final));
    }

    // Gera o HTML
    $html .= "<h2>Crimes por Bairro e Ano{$intervalo}</h2>";
    $html .= "<table><tr><th>Ano</th><th>Bairro</th><th>Total de Crimes</th></tr>";

    $totalGeral = 0;
    foreach ($stmt as $row) {
        $html .= "<tr><td>{$row['ano']}</td><td>{$row['bairro']}</td><td>{$row['total']}</td></tr>";
        $totalGeral += $row['total'];
    }

    $html .= "<tr style='font-weight: bold; background-color: #f0f0f0;'>
                <td colspan='2'>Total Geral</td>
                <td>{$totalGeral}</td>
              </tr>";

    $html .= "</table>";
}



$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("relatorio.pdf", ["Attachment" => false]);
exit;
?>