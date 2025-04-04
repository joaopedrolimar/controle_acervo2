<?php
require_once 'vendor/autoload.php';

use Dompdf\Options;
use Dompdf\Dompdf;

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Caminho acessível via HTTP local
$logoPath = 'http://localhost/controle_acervo2/public/img/logo.png';

$html = '
<style>
    body { text-align: center; font-family: sans-serif; }
</style>
<h1>Teste da imagem</h1>
<img src="' . $logoPath . '" height="100">
';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4');
$dompdf->render();
$dompdf->stream('teste_img.pdf', ['Attachment' => false]);
exit;