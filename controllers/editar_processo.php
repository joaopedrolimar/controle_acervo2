<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../config/conexao.php";
require_once "logs_controller.php"; // Importa função de logs
global $pdo;

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../views/login.php");
    exit();
}

// Verifica se o ID do processo foi passado na URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $stmt_crimes = $pdo->prepare("SELECT id, nome FROM crimes");
        $stmt_crimes->execute();
        $crimes = $stmt_crimes->fetchAll(PDO::FETCH_ASSOC);

        $stmt_municipios = $pdo->prepare("SELECT id, nome FROM municipios");
        $stmt_municipios->execute();
        $municipios = $stmt_municipios->fetchAll(PDO::FETCH_ASSOC);

        $stmt_bairros = $pdo->prepare("SELECT id, nome, municipio_id FROM bairros");
        $stmt_bairros->execute();
        $bairros = $stmt_bairros->fetchAll(PDO::FETCH_ASSOC);

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

if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['atualizar']) || isset($_POST['continuar_editando']))) {

    $novo = [
        'numero' => trim($_POST['numero']),
        'data_recebimento_denuncia' => $_POST['data_recebimento'] ?? null,

        'natureza' => trim($_POST['natureza']),
        'outra_natureza' => $_POST['outra_natureza'] ?? null,
        'data_denuncia' => $_POST['data_denuncia'],
        'crime_id' => ($_POST['crime'] !== "Outro") ? intval($_POST['crime']) : null,
        'outro_crime' => $_POST['outro_crime'] ?? null,
        'denunciado' => trim($_POST['denunciado']),
        'vitima' => $_POST['vitima'] ?? null,
        'local_municipio' => $_POST['local_municipio'],
        'local_bairro' => $_POST['local_bairro'],
        'sentenca' => $_POST['sentenca'],
        'outra_sentenca' => $_POST['outra_sentenca'] ?? null,
        'data_sentenca' => $_POST['data_sentenca'] ?? null,
        'recursos' => $_POST['recursos'],
        'status' => isset($_POST['continuar_editando']) ? 'Incompleto' : $_POST['status']

    ];

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
        outra_sentenca = :outra_sentenca, data_sentenca = :data_sentenca, recursos = :recursos, status = :status 
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
            ':id' => $id
        ]);

        if (!empty($valores_anteriores)) {
            registrar_log(
                $_SESSION['usuario_id'],
                "Editou um processo",
                "processos",
                $id,
                json_encode($valores_anteriores, JSON_UNESCAPED_UNICODE),
                json_encode($valores_novos, JSON_UNESCAPED_UNICODE)
            );
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
 // Função para mostrar/esconder o campo "Outra Natureza"
 function toggleOutraNatureza() {
  let select = document.getElementById("natureza");
  let outraNatureza = document.getElementById("outraNatureza");
  outraNatureza.style.display = (select.value === "Outra") ? "block" : "none";
 }

 // Função para mostrar/esconder o campo "Outro Crime"
 function toggleOutroCrime() {
  let crimeSelect = document.getElementById("crime");
  let outroCrimeInput = document.getElementById("outroCrime");
  outroCrimeInput.style.display = (crimeSelect.value === "Outro") ? "block" : "none";
 }

 // Ativar ao carregar a página para mostrar corretamente se o campo "Outro Crime" deve estar visível
 document.addEventListener("DOMContentLoaded", function() {
  toggleOutroCrime();
 });


 // Função para mostrar/esconder o campo de sentença e data da sentença
 function toggleSentenca() {
  let select = document.getElementById("sentenca");
  let outraSentenca = document.getElementById("outraSentenca");
  let dataSentenca = document.getElementById("dataSentenca");

  if (select.value === "Outra") {
   outraSentenca.style.display = "block";
   dataSentenca.style.display = "block";
  } else if (select.value !== "Não há") {
   outraSentenca.style.display = "none";
   dataSentenca.style.display = "block";
  } else {
   outraSentenca.style.display = "none";
   dataSentenca.style.display = "none";
  }
 }

 let bairrosPorMunicipio = <?= json_encode($bairrosPorMunicipio) ?>;


 function carregarBairros() {
  let municipioSelect = document.getElementById("municipio");
  let bairroSelect = document.getElementById("bairro");
  let municipioSelecionado = municipioSelect.value;
  let bairroSalvo = <?= json_encode($processo['bairro_id'] ?? '') ?>;

  bairroSelect.innerHTML = '<option value="">Selecione um bairro</option>';

  if (municipioSelecionado && bairrosPorMunicipio.hasOwnProperty(municipioSelecionado)) {
   bairrosPorMunicipio[municipioSelecionado].forEach(bairro => {
    let option = document.createElement("option");
    option.value = bairro.id;
    option.textContent = bairro.nome;

    if (bairro.id == bairroSalvo) {
     option.selected = true;
    }

    bairroSelect.appendChild(option);
   });
  }
 }

 // Chama a função no carregamento da página
 document.addEventListener("DOMContentLoaded", function() {
  carregarBairros();
 });
 </script>
</head>

<body class="bg-light">
 <div class="container mt-5">
  <div class="row justify-content-center">
   <div class="col-md-8">
    <div class="card shadow-sm">
     <div class="card-header bg-warning text-white text-center">
      <h4>Editar Processo</h4>
     </div>
     <div class="card-body">
      <form action="" method="POST">

       <!-- Número do Processo -->
       <div class="mb-3">
        <label for="numero" class="form-label">Número do Processo</label>
        <input type="text" class="form-control" id="numero" name="numero"
         value="<?= htmlspecialchars($processo['numero'] ?? '') ?>">
       </div>

       <div class="mb-3">
        <label for="data_recebimento" class="form-label">Data do Recebimento da Denúncia</label>
        <input type="date" class="form-control" id="data_recebimento" name="data_recebimento"
         value="<?= $processo['data_recebimento_denuncia'] ?? '' ?>">
       </div>


       <div class="mb-3">
        <label for="data_denuncia" class="form-label">Data da Denúncia</label>
        <input type="date" class="form-control" id="data_denuncia" name="data_denuncia"
         value="<?= $processo['data_denuncia'] ?>">
       </div>

       <!-- Natureza -->
       <div class="mb-3">
        <label for="natureza" class="form-label">Natureza Processual/Procedimental</label>
        <select class="form-control" id="natureza" name="natureza" onchange="toggleOutraNatureza()">
         <option value="Ação Penal" <?= $processo['natureza'] == "Ação Penal" ? "selected" : "" ?>>Ação Penal
         </option>
         <option value="Inquérito Policial" <?= $processo['natureza'] == "Inquérito Policial" ? "selected" : "" ?>>
          Inquérito
          Policial</option>

         <option value="PIC" <?= $processo['natureza'] == "PIC" ? "selected" : "" ?>>
          PIC</option>

         <option value="NF" <?= $processo['natureza'] == "NF" ? "selected" : "" ?>>
          NF</option>

         <option value="Outra" <?= !empty($processo['outra_natureza']) ? "selected" : "" ?>>
          Outra</option>
        </select>
        <input type="text" class="form-control mt-2" id="outraNatureza" name="outra_natureza"
         placeholder="Especifique..." value="<?= htmlspecialchars($processo['outra_natureza'] ?? '') ?>"
         style="<?= empty($processo['outra_natureza']) ? 'display:none;' : '' ?>">
       </div>

       <!--Crime -->
       <div class="mb-3">
        <label for="crime" class="form-label">Crime</label>
        <select class="form-control" id="crime" name="crime" onchange="toggleOutroCrime()">
         <?php foreach ($crimes as $crime): ?>
         <option value="<?= $crime['id'] ?>" <?= ($crime['id'] == $processo['crime_id']) ? "selected" : "" ?>>
          <?= htmlspecialchars($crime['nome']) ?>
         </option>
         <?php endforeach; ?>
         <option value="Outro">Outro Crime</option>
        </select>

        <!-- Campo para outro crime, aparece apenas se 'Outro' for selecionado -->
        <input type="text" class="form-control mt-2" id="outroCrime" name="outro_crime"
         placeholder="Especifique o crime..." value="<?= htmlspecialchars($processo['outro_crime'] ?? '') ?>"
         style="<?= empty($processo['outro_crime']) ? 'display:none;' : '' ?>">
       </div>



       <!-- Local do Crime -->
       <div class="mb-3">
        <label for="municipio" class="form-label">Município</label>
        <select class="form-control" id="municipio" name="local_municipio" onchange="carregarBairros()">
         <option value="">Selecione um município</option>
         <?php foreach ($municipios as $municipio): ?>
         <option value="<?= $municipio['id'] ?>"
          <?= ($municipio['id'] == ($processo['municipio_id'] ?? '')) ? "selected" : "" ?>>
          <?= htmlspecialchars($municipio['nome']) ?>
         </option>
         <?php endforeach; ?>
        </select>
       </div>

       <div class="mb-3">
        <label for="bairro" class="form-label">Bairro</label>
        <select class="form-control" id="bairro" name="local_bairro">
         <option value="">Selecione um bairro</option>
        </select>
       </div>

       <!-- Vítima -->
       <div class="mb-3">
        <label for="denunciado" class="form-label">Denunciado</label>
        <input type="text" class="form-control" id="denunciado" name="denunciado"
         value="<?= htmlspecialchars($processo['denunciado'] ?? '') ?>">

       </div>

       <!-- Vítima -->
       <div class="mb-3">
        <label for="vitima" class="form-label">Vítima</label>
        <input type="text" class="form-control" id="vitima" name="vitima"
         value="<?= htmlspecialchars($processo['vitima'] ?? '') ?>">
        <input type="checkbox" id="semVitima" onclick="toggleVitima()"> Não há vítima
       </div>

       <!-- Sentença -->
       <div class="mb-3">
        <label for="sentenca" class="form-label">Sentença</label>
        <select class="form-control" id="sentenca" name="sentenca" onchange="toggleSentenca()" required>
         <option value="Condenatória" <?= $processo['sentenca'] == "Condenatória" ? "selected" : "" ?>>Condenatória
         </option>
         <option value="Absolutória" <?= $processo['sentenca'] == "Absolutória" ? "selected" : "" ?>>Absolutória
         </option>
         <option value="Prescrição" <?= $processo['sentenca'] == "Prescrição" ? "selected" : "" ?>>Prescrição
         </option>
         <option value="Outra" <?= !empty($processo['outra_sentenca']) ? "selected" : "" ?>>
          Outra</option>
         <option value="Não há" <?= $processo['sentenca'] == "Não há" ? "selected" : "" ?>>
          Não há</option>
        </select>
        <input type="text" class="form-control mt-2" id="outraSentenca" name="outra_sentenca"
         placeholder="Especifique outra sentença..." value="<?= htmlspecialchars($processo['outra_sentenca'] ?? '') ?>"
         style="<?= empty($processo['outra_sentenca']) ? 'display:none;' : '' ?>">
        <input type="date" class="form-control mt-2" id="dataSentenca" name="data_sentenca"
         value="<?= $processo['data_sentenca'] ?>">
       </div>

       <!-- Recursos -->
       <div class="mb-3">
        <label for="recursos" class="form-label">Recursos</label>
        <select class="form-control" id="recursos" name="recursos" required>
         <option value="Acusação" <?= $processo['recursos'] == "Acusação" ? "selected" : "" ?>>Acusação</option>
         <option value="Defesa" <?= $processo['recursos'] == "Defesa" ? "selected" : "" ?>>
          Defesa</option>
         <option value="Não há" <?= $processo['recursos'] == "Não há" ? "selected" : "" ?>>
          Não há</option>
        </select>
       </div>

       <div class="mb-3">
        <label for="status" class="form-label">Status</label>
        <select class="form-control" name="status" required>
         <option value="Ativo" <?= ($processo['status'] === 'Ativo') ? 'selected' : '' ?>>
          Ativo
         </option>
         <option value="Finalizado" <?= ($processo['status'] === 'Finalizado') ? 'selected' : '' ?>>Finalizado
         </option>
        </select>
       </div>




       <!-- Botões -->
       <div class="d-flex gap-2">
        <button type="submit" class="btn btn-success w-100" name="atualizar">Atualizar Processo</button>
        <button type="submit" class="btn btn-warning w-100" name="continuar_editando">Continuar Editando</button>
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