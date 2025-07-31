<!--/controllers/editar_processo.php-->
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../config/conexao.php";
require_once "logs_controller.php";
global $pdo;

// Verifica login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../views/login.php");
    exit();
}

// Verifica ID do processo
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $crimes = $pdo->query("SELECT * FROM crimes ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        $municipios = $pdo->query("SELECT * FROM municipios ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        $bairros = $pdo->query("SELECT * FROM bairros ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        $bairrosPorMunicipio = [];
        foreach ($bairros as $bairro) {
            $bairrosPorMunicipio[$bairro['municipio_id']][] = [
                'id' => $bairro['id'],
                'nome' => $bairro['nome']
            ];
        }
        $stmt = $pdo->prepare("
            SELECT processos.*, 
                   crimes.id AS crime_id, crimes.nome AS nome_crime,
                   municipios.id AS municipio_id, municipios.nome AS nome_municipio,
                   bairros.id AS bairro_id, bairros.nome AS nome_bairro
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
            $_SESSION['mensagem'] = "Processo não encontrado.";
            header("Location: ../views/listar_processos.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = "Erro no banco: " . $e->getMessage();
        header("Location: ../views/listar_processos.php");
        exit();
    }
} else {
    $_SESSION['mensagem'] = "ID inválido.";
    header("Location: ../views/listar_processos.php");
    exit();
}

// Ao enviar formulário
if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['atualizar']) || isset($_POST['continuar_editando']))) {

    $natureza = trim($_POST['natureza']);

    // Regras para limpar datas dependendo da natureza
    if (in_array($natureza, ['PIC', 'Inquérito Policial', 'Outra'])) {
        $data_recebimento = null;
        $data_denuncia = null;
    } else {
        $data_recebimento = $_POST['data_recebimento'] ?? null;
        $data_denuncia = $_POST['data_denuncia'] ?? null;
    }

    // captura opcoes_finalizado
    $opcoes = $_POST['opcoes_finalizado'] ?? [];
    $oferecendo_denuncia = in_array('Oferecendo de Denúncia', $opcoes) ? 1 : 0;
    $arquivamento = in_array('Arquivamento', $opcoes) ? 1 : 0;
    $realizacao_anpp = in_array('Realização de ANPP', $opcoes) ? 1 : 0;
    $requisicao_inquerito = in_array('Requisição de Inquérito', $opcoes) ? 1 : 0;
    $conversao_pic = in_array('Conversão em PIC', $opcoes) ? 1 : 0;
    $outra_medida = in_array('Outra Medida', $opcoes) ? 1 : 0;
    $especifique_outra_medida = $_POST['especifique_outra_medida'] ?? null;

    $novo = [
        'numero' => trim($_POST['numero']),
        'data_recebimento_denuncia' => $data_recebimento,
        'natureza' => $natureza,
        'outra_natureza' => $_POST['outra_natureza'] ?? null,
        'data_denuncia' => $data_denuncia,
        'crime_id' => ($_POST['crime'] !== "Outro") ? intval($_POST['crime']) : null,
        'outro_crime' => $_POST['outro_crime'] ?? null,
        'denunciado' => trim($_POST['denunciado']),
        'vitima' => isset($_POST['sem_vitima']) ? 'Não há' : ($_POST['vitima'] ?? null),
        'local_municipio' => $_POST['municipio'],
        'local_bairro' => $_POST['bairro'],
        'sentenca' => $_POST['sentenca'],
        'outra_sentenca' => $_POST['outra_sentenca'] ?? null,
        'data_sentenca' => $_POST['data_sentenca'] ?? null,
        'recursos' => $_POST['recursos'],
        'status' => isset($_POST['continuar_editando']) ? 'Incompleto' : $_POST['status']
    ];

    // LOG
    $valores_anteriores = [];
    $valores_novos = [];
    foreach ($novo as $campo => $valor_novo) {
        $valor_antigo = $processo[$campo] ?? null;
        if ($valor_novo != $valor_antigo) {
            $valores_anteriores[$campo] = $valor_antigo;
            $valores_novos[$campo] = $valor_novo;
        }
    }

    try {
        $sql = "UPDATE processos SET 
            numero = :numero, data_recebimento_denuncia = :data_recebimento, natureza = :natureza, outra_natureza = :outra_natureza,
            data_denuncia = :data_denuncia, crime_id = :crime_id, outro_crime = :outro_crime, denunciado = :denunciado, vitima = :vitima,
            local_municipio = :municipio_id, local_bairro = :bairro_id, sentenca = :sentenca,
            outra_sentenca = :outra_sentenca, data_sentenca = :data_sentenca, recursos = :recursos, status = :status,
            oferecendo_denuncia = :oferecendo_denuncia, arquivamento = :arquivamento, realizacao_anpp = :realizacao_anpp,
            requisicao_inquerito = :requisicao_inquerito, conversao_pic = :conversao_pic, outra_medida = :outra_medida,
            especifique_outra_medida = :especifique_outra_medida
            WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':numero' => $novo['numero'],
            ':data_recebimento' => $novo['data_recebimento_denuncia'],
            ':natureza' => $novo['natureza'],
            ':outra_natureza' => $novo['outra_natureza'],
            ':data_denuncia' => $novo['data_denuncia'],
            ':crime_id' => $novo['crime_id'],
            ':outro_crime' => $novo['outro_crime'],
            ':denunciado' => $novo['denunciado'],
            ':vitima' => $novo['vitima'],
            ':municipio_id' => $novo['local_municipio'],
            ':bairro_id' => $novo['local_bairro'],
            ':sentenca' => $novo['sentenca'],
            ':outra_sentenca' => $novo['outra_sentenca'],
            ':data_sentenca' => $novo['data_sentenca'],
            ':recursos' => $novo['recursos'],
            ':status' => $novo['status'],
            ':oferecendo_denuncia' => $oferecendo_denuncia,
            ':arquivamento' => $arquivamento,
            ':realizacao_anpp' => $realizacao_anpp,
            ':requisicao_inquerito' => $requisicao_inquerito,
            ':conversao_pic' => $conversao_pic,
            ':outra_medida' => $outra_medida,
            ':especifique_outra_medida' => $especifique_outra_medida,
            ':id' => $id
        ]);

        if (!empty($valores_anteriores)) {
            registrar_log($_SESSION['usuario_id'], "Editou um processo", "processos", $id,
                json_encode($valores_anteriores, JSON_UNESCAPED_UNICODE),
                json_encode($valores_novos, JSON_UNESCAPED_UNICODE));
        }
        $_SESSION['mensagem'] = "Processo atualizado com sucesso!";
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = "Erro ao atualizar: " . $e->getMessage();
    }

    header("Location: ../views/listar_processos.php");
    exit();
}
?>



<!DOCTYPE html>
<html lang="pt">

<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Editar Processo</title>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
 <script>
 let bairrosPorMunicipio = <?= json_encode($bairrosPorMunicipio) ?>;

 let oferecendo_denuncia = <?= json_encode((bool)$processo['oferecendo_denuncia']) ?>;
 let arquivamento = <?= json_encode((bool)$processo['arquivamento']) ?>;
 let realizacao_anpp = <?= json_encode((bool)$processo['realizacao_anpp']) ?>;
 let requisicao_inquerito = <?= json_encode((bool)$processo['requisicao_inquerito']) ?>;
 let conversao_pic = <?= json_encode((bool)$processo['conversao_pic']) ?>;
 let outra_medida = <?= json_encode((bool)$processo['outra_medida']) ?>;
 let especifique_outra_medida = <?= json_encode($processo['especifique_outra_medida']) ?>;

 function toggleOutraNatureza() {
  let select = document.getElementById("natureza");
  let outra = document.getElementById("outraNatureza");
  outra.style.display = select.value === "Outra" ? "block" : "none";
 }

 function toggleOutroCrime() {
  let crime = document.getElementById("crime");
  let outro = document.getElementById("outroCrime");
  outro.style.display = crime.value === "Outro" ? "block" : "none";
 }

 function toggleVitima() {
  let check = document.getElementById("semVitima");
  let input = document.getElementById("vitima");
  if (check.checked) {
   input.value = "Não há";
   input.readOnly = true;
  } else {
   input.readOnly = false;
   if (input.value === "Não há") input.value = "";
  }
 }

 function toggleSentenca() {
  let select = document.getElementById("sentenca");
  let outra = document.getElementById("outraSentenca");
  let data = document.getElementById("dataSentenca");
  if (select.value === "Outra") {
   outra.style.display = "block";
   data.style.display = "block";
  } else if (select.value !== "Não há") {
   outra.style.display = "none";
   data.style.display = "block";
  } else {
   outra.style.display = "none";
   data.style.display = "none";
  }
 }

 function carregarBairros() {
  let municipio = document.getElementById("municipio").value;
  let bairroSelect = document.getElementById("bairro");
  let bairroSalvo = <?= json_encode($processo['bairro_id']) ?>;
  bairroSelect.innerHTML = '<option value="">Selecione um Bairro</option>';
  if (municipio && bairrosPorMunicipio[municipio]) {
   bairrosPorMunicipio[municipio].forEach(b => {
    let opt = document.createElement("option");
    opt.value = b.id;
    opt.textContent = b.nome;
    if (b.id == bairroSalvo) opt.selected = true;
    bairroSelect.appendChild(opt);
   });
  }
 }

 function toggleOutraMedida(checkbox) {
  let campo = checkbox.parentElement.querySelector('input[type="text"]');
  campo.style.display = checkbox.checked ? "block" : "none";
 }

 function atualizarFormulario() {
  let natureza = document.getElementById("natureza").value;
  let status = document.getElementById("status").value;
  let dataDenuncia = document.getElementById("data_denuncia_container");
  let dataRecebimento = document.getElementById("data_recebimento_container");
  let labelDenunciado = document.getElementById("label_denunciado");
  let opcoesFinalizado = document.getElementById("opcoes_finalizado");

  dataDenuncia.style.display = "block";
  dataRecebimento.style.display = "block";
  labelDenunciado.innerText = "Denunciado";
  opcoesFinalizado.style.display = "none";
  opcoesFinalizado.innerHTML = "";

  if (natureza === "Inquérito Policial") {
   dataDenuncia.style.display = "none";
   dataRecebimento.style.display = "none";
   labelDenunciado.innerText = "Flagrado/Indiciado";
   if (status === "Finalizado") {
    opcoesFinalizado.style.display = "block";
    opcoesFinalizado.innerHTML =
     `<label>Marque:</label><br>
   <input type="checkbox" name="opcoes_finalizado[]" value="Oferecendo de Denúncia" ${oferecendo_denuncia ? 'checked' : ''}> Oferecendo de Denúncia<br>
   <input type="checkbox" name="opcoes_finalizado[]" value="Arquivamento" ${arquivamento ? 'checked' : ''}> Arquivamento`;
   }
  } else if (natureza === "PIC") {
   dataDenuncia.style.display = "none";
   dataRecebimento.style.display = "none";
   labelDenunciado.innerText = "Investigado";
   if (status === "Finalizado") {
    opcoesFinalizado.style.display = "block";
    opcoesFinalizado.innerHTML =
     `<label>Marque:</label><br>
   <input type="checkbox" name="opcoes_finalizado[]" value="Oferecendo de Denúncia" ${oferecendo_denuncia ? 'checked' : ''}> Oferecendo de Denúncia<br>
   <input type="checkbox" name="opcoes_finalizado[]" value="Realização de ANPP" ${realizacao_anpp ? 'checked' : ''}> Realização de ANPP<br>
   <input type="checkbox" name="opcoes_finalizado[]" value="Arquivamento" ${arquivamento ? 'checked' : ''}> Arquivamento`;
   }
  } else if (natureza === "NF") {
   labelDenunciado.innerText = "Noticiado";
   if (status === "Finalizado") {
    opcoesFinalizado.style.display = "block";
    opcoesFinalizado.innerHTML =
     `<label>Marque:</label><br>
   <input type="checkbox" name="opcoes_finalizado[]" value="Requisição de Inquérito" ${requisicao_inquerito ? 'checked' : ''}> Requisição de Inquérito<br>
   <input type="checkbox" name="opcoes_finalizado[]" value="Conversão em PIC" ${conversao_pic ? 'checked' : ''}> Conversão em PIC<br>
   <input type="checkbox" name="opcoes_finalizado[]" value="Arquivamento" ${arquivamento ? 'checked' : ''}> Arquivamento`;
   }
  } else if (natureza === "Outra") {
   dataDenuncia.style.display = "none";
   dataRecebimento.style.display = "none";
   labelDenunciado.innerText = "Investigado/Requerido";
   if (status === "Finalizado") {
    opcoesFinalizado.style.display = "block";
    opcoesFinalizado.innerHTML =
     `<label>Marque:</label><br>
   <input type="checkbox" name="opcoes_finalizado[]" value="Oferecendo de Denúncia" ${oferecendo_denuncia ? 'checked' : ''}> Oferecendo de Denúncia<br>
   <input type="checkbox" name="opcoes_finalizado[]" value="Arquivamento" ${arquivamento ? 'checked' : ''}> Arquivamento<br>
   <input type="checkbox" name="opcoes_finalizado[]" value="Outra Medida" onclick="toggleOutraMedida(this)" ${outra_medida ? 'checked' : ''}> Outra Medida
   <input type="text" name="especifique_outra_medida" class="form-control mt-2" value="${especifique_outra_medida ?? ''}" style="${outra_medida ? '' : 'display:none;'}" placeholder="Especifique...">`;
   }
  }
 }

 window.addEventListener("DOMContentLoaded", function() {
  toggleOutraNatureza();
  toggleOutroCrime();
  toggleSentenca();
  toggleVitima();
  carregarBairros();
  atualizarFormulario();
 });
 </script>



</head>

<body class="bg-light">
 <div class="container mt-5">
  <h2 class="text-center">Editar Processo</h2>
  <div class="row justify-content-center">
   <div class="col-md-8">
    <div class="card shadow-sm">
     <div class="card-body">
      <form method="POST">
       <div class="mb-3">
        <label class="form-label">Número do Processo</label>
        <input type="text" class="form-control" name="numero" value="<?= htmlspecialchars($processo['numero']) ?>">
       </div>

       <div class="mb-3" id="data_denuncia_container">
        <label class="form-label">Data da Denúncia</label>
        <input type="date" class="form-control" name="data_denuncia" value="<?= $processo['data_denuncia'] ?>">
       </div>


       <div class="mb-3" id="data_recebimento_container">
        <label class="form-label">Data do Recebimento da Denúncia</label>
        <input type="date" class="form-control" name="data_recebimento"
         value="<?= $processo['data_recebimento_denuncia'] ?>">
       </div>

       <div class="mb-3">
        <label class="form-label">Natureza</label>
        <select class="form-control" id="natureza" name="natureza"
         onchange="toggleOutraNatureza(); atualizarFormulario();">
         <option value="Ação Penal" <?= $processo['natureza'] == "Ação Penal" ? "selected" : "" ?>>Ação Penal</option>
         <option value="Inquérito Policial" <?= $processo['natureza'] == "Inquérito Policial" ? "selected" : "" ?>>
          Inquérito Policial</option>
         <option value="PIC" <?= $processo['natureza'] == "PIC" ? "selected" : "" ?>>PIC</option>
         <option value="NF" <?= $processo['natureza'] == "NF" ? "selected" : "" ?>>NF</option>
         <option value="Outra" <?= $processo['natureza'] == "Outra" ? "selected" : "" ?>>Outra</option>
        </select>

        <input type="text" class="form-control mt-2" id="outraNatureza" name="outra_natureza"
         placeholder="Especifique..." value="<?= htmlspecialchars($processo['outra_natureza'] ?? '') ?>"
         style="<?= $processo['natureza'] == "Outra" ? '' : 'display:none;' ?>">

       </div>

       <div class="mb-3">
        <label class="form-label">Crime</label>
        <select class="form-control" id="crime" name="crime" onchange="toggleOutroCrime()">
         <?php foreach ($crimes as $crime): ?>
         <option value="<?= $crime['id'] ?>" <?= ($crime['id'] == $processo['crime_id']) ? "selected" : "" ?>>
          <?= htmlspecialchars($crime['nome']) ?></option>
         <?php endforeach; ?>
         <option value="Outro">Outro Crime</option>
        </select>
        <input type="text" class="form-control mt-2" id="outroCrime" name="outro_crime"
         placeholder="Especifique o crime..." value="<?= htmlspecialchars($processo['outro_crime']) ?>"
         style="<?= empty($processo['outro_crime']) ? 'display:none;' : '' ?>">
       </div>

       <div class="mb-3">
        <label class="form-label">Município</label>
        <select class="form-control" id="municipio" name="municipio" onchange="carregarBairros()">
         <option value="">Selecione um Município</option>
         <?php foreach ($municipios as $municipio): ?>
         <option value="<?= $municipio['id'] ?>"
          <?= ($municipio['id'] == $processo['municipio_id']) ? "selected" : "" ?>>
          <?= htmlspecialchars($municipio['nome']) ?></option>
         <?php endforeach; ?>
        </select>
       </div>

       <div class="mb-3">
        <label class="form-label">Bairro</label>
        <select class="form-control" id="bairro" name="bairro"></select>
       </div>

       <div class="mb-3">
        <label class="form-label" id="label_denunciado">Denunciado</label>
        <textarea class="form-control" name="denunciado"><?= htmlspecialchars($processo['denunciado']) ?></textarea>
       </div>

       <div class="mb-3">
        <label class="form-label">Vítima</label>
        <textarea class="form-control" id="vitima" name="vitima"><?= htmlspecialchars($processo['vitima']) ?></textarea>
        <input type="checkbox" id="semVitima" name="sem_vitima"
         <?= ($processo['vitima'] == "Não há" ? "checked" : "") ?> onclick="toggleVitima()"> Não há vítima
       </div>

       <div class="mb-3">
        <label class="form-label">Sentença</label>
        <select class="form-control" id="sentenca" name="sentenca" onchange="toggleSentenca()">
         <option value="Condenatória" <?= $processo['sentenca'] == "Condenatória" ? "selected" : "" ?>>Condenatória
         </option>
         <option value="Absolutória" <?= $processo['sentenca'] == "Absolutória" ? "selected" : "" ?>>Absolutória
         </option>
         <option value="Prescrição" <?= $processo['sentenca'] == "Prescrição" ? "selected" : "" ?>>Prescrição</option>
         <option value="Outra" <?= !empty($processo['outra_sentenca']) ? "selected" : "" ?>>Outra</option>
         <option value="Não há" <?= $processo['sentenca'] == "Não há" ? "selected" : "" ?>>Não há</option>
        </select>
        <input type="text" class="form-control mt-2" id="outraSentenca" name="outra_sentenca"
         placeholder="Especifique..." value="<?= htmlspecialchars($processo['outra_sentenca'] ?? '') ?>"
         style="<?= empty($processo['outra_sentenca']) ? 'display:none;' : '' ?>">
        <input type="date" class="form-control mt-2" id="dataSentenca" name="data_sentenca"
         value="<?= htmlspecialchars($processo['data_sentenca'] ?? '') ?>">
       </div>


       <div class="mb-3">
        <label class="form-label">Recursos</label>
        <select class="form-control" name="recursos">
         <option value="Acusação" <?= $processo['recursos'] == "Acusação" ? "selected" : "" ?>>Acusação</option>
         <option value="Defesa" <?= $processo['recursos'] == "Defesa" ? "selected" : "" ?>>Defesa</option>
         <option value="Não há" <?= $processo['recursos'] == "Não há" ? "selected" : "" ?>>Não há</option>
        </select>
       </div>

       <div class="mb-3">
        <label class="form-label">Status</label>
        <select class="form-control" id="status" name="status" onchange="atualizarFormulario()">
         <option value="Ativo" <?= $processo['status'] == "Ativo" ? "selected" : "" ?>>Ativo</option>
         <option value="Finalizado" <?= $processo['status'] == "Finalizado" ? "selected" : "" ?>>Finalizado</option>
        </select>
       </div>



       <div class="mb-3" id="opcoes_finalizado" style="display:none;"></div>

       <div class="d-flex gap-2">
        <button type="submit" class="btn btn-success w-100" name="atualizar">Atualizar Processo</button>
        <button type="submit" class="btn btn-warning w-100" name="continuar_editando">Salvar e Continuar
         Editando</button>
        <a href="../views/listar_processos.php" class="btn btn-secondary w-100">Cancelar</a>
       </div>
      </form>
     </div>
    </div>
   </div>
  </div>
 </div>
</body>

</html>