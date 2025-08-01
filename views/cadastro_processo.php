<!--controle_acervo/views/cadastro_processo.php-->
<?php
session_start();
require_once "../config/conexao.php"; // Conexão com o banco
global $pdo; // Torna a conexão acessível

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$pagina_atual = basename($_SERVER['PHP_SELF']);

$perfil = $_SESSION['usuario_perfil'] ?? '';

// Bloqueia acesso para consultores (não podem acessar essa página)
if ($perfil === 'consultor') {
    $_SESSION['mensagem'] = "Acesso não autorizado!";
    header("Location: dashboard.php");
    exit();
}

// Buscar municípios do banco
$municipios = $pdo->query("SELECT * FROM municipios ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

// Buscar bairros e associar com os municípios
$bairros = $pdo->query("SELECT bairros.id, bairros.nome, bairros.municipio_id, municipios.nome as municipio 
                         FROM bairros 
                         JOIN municipios ON bairros.municipio_id = municipios.id 
                         ORDER BY municipios.nome ASC, bairros.nome ASC")
               ->fetchAll(PDO::FETCH_ASSOC);

// Busca os crimes do banco de dados
try {
    $stmt = $pdo->query("SELECT * FROM crimes ORDER BY nome ASC");
    $crimes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar crimes: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="pt">

<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Cadastrar Processo</title>
 <!-- Bootstrap CSS -->
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

 <link rel="icon" href="../public/img/favicon-32x32.png" type="../img/favicon-32x32.png">

 <style>
 /* Ajuste da logo na navbar */
 .logo-navbar {
  max-width: 300px;
  /* Define um tamanho máximo */
  height: auto;
  /* Mantém a proporção correta */
 }

 /* Ajuste para telas menores */
 @media (max-width: 576px) {
  .logo-navbar {
   max-width: 250px;
   /* Reduz a logo para melhor encaixe */
   display: block;
   /* Evita que fique desalinhada */
   margin: auto;
   /* Centraliza no mobile */
  }
 }
 </style>
 <script>
 // Função para habilitar/desabilitar campo "Outra Natureza"
 function toggleOutraNatureza() {
  let select = document.getElementById("natureza");
  let outraNatureza = document.getElementById("outraNatureza");
  outraNatureza.style.display = (select.value === "Outra") ? "block" : "none";
 }

 // Função para habilitar/desabilitar campo "Vítima"
 function toggleVitima() {
  let check = document.getElementById("semVitima");
  let vitimaInput = document.getElementById("vitima");

  if (check.checked) {
   vitimaInput.value = "Não há";
   vitimaInput.readOnly = true;
  } else {
   vitimaInput.value = "";
   vitimaInput.readOnly = false;
  }
 }


 // Função para exibir/esconder campos de sentença
 function toggleSentenca() {
  let select = document.getElementById("sentenca");
  let dataSentenca = document.getElementById("dataSentenca");
  let outraSentenca = document.getElementById("outraSentenca");

  if (select.value === "Outra") {
   outraSentenca.style.display = "block";
   dataSentenca.style.display = "block"; // Agora a data também aparece
  } else if (select.value !== "Não há") {
   outraSentenca.style.display = "none";
   dataSentenca.style.display = "block";
  } else {
   outraSentenca.style.display = "none";
   dataSentenca.style.display = "none";
  }
 }
 </script>
</head>

<body class="bg-light">

 <!-- Navbar -->
 <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #900020;">
  <div class="container">
   <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
    <img src="../public/img/logoWhite.png" alt="Logo" class="logo-navbar">
   </a>

   <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
    <span class="navbar-toggler-icon"></span>
   </button>

   <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav ms-auto">

     <!-- Início: todos -->
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'dashboard.php') ? 'active' : '' ?>" href="dashboard.php">
       <i class="fas fa-home"></i> Início
      </a>
     </li>

     <!-- Listar Processos: 1, 2, 3 -->
     <?php if (in_array($perfil, ['administrador', 'consultor', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'listar_processos.php') ? 'active' : '' ?>" href="listar_processos.php">
       <i class="fas fa-list"></i> Listar Processos
      </a>
     </li>
     <?php endif; ?>

     <!-- Cadastrar Processos: 1, 3, 4 -->
     <?php if (in_array($perfil, ['administrador', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'cadastro_processo.php') ? 'active' : '' ?>"
       href="cadastro_processo.php">
       <i class="fas fa-plus"></i> Cadastrar Processos
      </a>
     </li>
     <?php endif; ?>

     <!-- Listar ANPP: 1, 2, 3 -->
     <?php if (in_array($perfil, ['administrador', 'consultor', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'listar_anpp.php') ? 'active' : '' ?>" href="listar_anpp.php">
       <i class="fas fa-scale-balanced"></i> Listagem de ANPPs
      </a>
     </li>
     <?php endif; ?>

     <!-- Cadastrar ANPP: 1, 3, 4 -->
     <?php if (in_array($perfil, ['administrador', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'anpp.php') ? 'active' : '' ?>" href="anpp.php">
       <i class="fas fa-file-circle-plus"></i> Cadastrar ANPP
      </a>
     </li>
     <?php endif; ?>

     <!-- Gerenciar Usuários: 1, 3, 4 -->
     <?php if (in_array($perfil, ['administrador', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'gerenciar_usuarios.php') ? 'active' : '' ?>"
       href="gerenciar_usuarios.php">
       <i class="fas fa-users-cog"></i> Gerenciar Usuários
      </a>
     </li>
     <?php endif; ?>

     <!-- Atos: todos -->
     <?php if (in_array($perfil, ['administrador', 'consultor', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'atos.php') ? 'active' : '' ?>" href="atos.php">
       <i class="fas fa-file-alt"></i> Atos
      </a>
     </li>
     <?php endif; ?>

     <!-- Log de Atividades: apenas admin -->
     <?php if ($perfil === 'administrador'): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'log_atividades.php') ? 'active' : '' ?>" href="log_atividades.php">
       <i class="fas fa-history"></i> Log de Atividades
      </a>
     </li>
     <?php endif; ?>

     <!-- Cadastro Básico: 1, 2, 3 -->
     <?php if (in_array($perfil, ['administrador', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'cadastro_basico.php') ? 'active' : '' ?>" href="cadastro_basico.php">
       <i class="fas fa-address-book"></i> Cadastro Básico
      </a>
     </li>
     <?php endif; ?>

     <?php if (in_array($perfil, ['administrador', 'consultor', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'relatorios.php') ? 'active' : '' ?>" href="relatorios.php">
       <i class="fas fa-chart-bar"></i> Relatórios
      </a>
     </li>
     <?php endif; ?>


     <!-- Sair: todos -->
     <li class="nav-item">
      <a class="nav-link text-white" href="../controllers/logout.php">
       <i class="fas fa-sign-out-alt"></i> Sair
      </a>
     </li>

    </ul>
   </div>
  </div>
 </nav>



 <!-- Conteúdo Principal -->
 <div class="container mt-5">

  <h2 class="text-center"><i class="fas fa-plus"></i> Cadastrar Processos</h2>

  <div class="row justify-content-center">
   <div class="col-md-8">
    <div class="card shadow-sm">
     <div class="card-header bg-primary text-white text-center">
      <h4>Cadastrar Novo Processo</h4>
     </div>
     <div class="card-body">
      <?php if (isset($_SESSION['mensagem'])): ?>
      <div class="alert alert-info">
       <?= $_SESSION['mensagem']; ?>
      </div>
      <?php unset($_SESSION['mensagem']); ?>
      <?php endif; ?>

      <form action="../controllers/processo_controller.php" method="POST">
       <!--Número do processo-->
       <div class="mb-3">
        <label for="numero" class="form-label">Número do Processo</label>
        <input type="text" class="form-control" id="numero" name="numero">
       </div>

       <!-- Data da Denúncia -->
       <div class="mb-3 data_denuncia">
        <label for="data_denuncia" class="form-label">Data da Denúncia</label>
        <input type="date" class="form-control" id="data_denuncia" name="data_denuncia">
       </div>

       <!-- Data do Recebimento -->
       <div class="mb-3 data_recebimento">
        <label for="data_recebimento" class="form-label">Data do Recebimento da Denúncia</label>
        <input type="date" class="form-control" id="data_recebimento" name="data_recebimento">
       </div>



       <!-- Natureza -->
       <div class="mb-3">
        <label for="natureza" class="form-label">Natureza Processual/Procedimental</label>
        <select class="form-control" id="natureza" name="natureza" onchange="atualizarFormulario()">
         <option value="Ação Penal">Ação Penal</option>
         <option value="Inquérito Policial">Inquérito Policial</option>
         <option value="PIC">PIC</option>
         <option value="NF">NF</option>
         <option value="Outra">Outra Natureza</option>
        </select>
        <input type="text" class="form-control mt-2" id="outraNatureza" name="outra_natureza"
         placeholder="Especifique..." style="display:none;">
       </div>

       <!-- Crime -->
       <div class="mb-3">
        <label for="crime" class="form-label">Crime</label>
        <select class="form-control" id="crime" name="crime">
         <?php foreach ($crimes as $crime): ?>
         <option value="<?= htmlspecialchars($crime['id']) ?>"><?= htmlspecialchars($crime['nome']) ?></option>
         <?php endforeach; ?>
        </select>
       </div>

       <!-- Município e Bairro -->
       <div class="mb-3">
        <label class="form-label">Município</label>
        <select class="form-control" id="municipio" name="municipio">
         <option value="">Selecione um Município</option>
         <?php foreach ($municipios as $municipio): ?>
         <option value="<?= $municipio['id'] ?>"><?= htmlspecialchars($municipio['nome']) ?></option>
         <?php endforeach; ?>
        </select>
       </div>

       <div class="mb-3">
        <label class="form-label">Bairro</label>
        <select class="form-control" id="bairro" name="bairro">
         <option value="">Selecione um Bairro</option>
         <?php foreach ($bairros as $bairro): ?>
         <option value="<?= $bairro['id'] ?>" data-municipio="<?= $bairro['municipio_id'] ?>">
          <?= htmlspecialchars($bairro['nome']) ?>
         </option>
         <?php endforeach; ?>
        </select>
       </div>

       <!-- Denunciado -->
       <div class="mb-3">
        <label for="denunciado" class="form-label" id="label_denunciado">Denunciado</label>
        <textarea class="form-control" id="denunciado" name="denunciado" rows="3"></textarea>
       </div>

       <!-- Vítima -->
       <div class="mb-3">
        <label for="vitima" class="form-label">Vítima</label>
        <textarea class="form-control" id="vitima" name="vitima" rows="3"></textarea>
        <input type="checkbox" id="semVitima" onclick="toggleVitima()"> Não há vítima
       </div>

       <!-- Sentença -->
       <div class="mb-3">
        <label for="sentenca" class="form-label">Sentença</label>
        <select class="form-control" id="sentenca" name="sentenca" onchange="toggleSentenca()">
         <option value="Condenatória">Condenatória</option>
         <option value="Absolutória">Absolutória</option>
         <option value="Prescrição">Prescrição</option>
         <option value="Outra">Outra</option>
         <option value="Não há">Não há</option>
        </select>
        <input type="text" class="form-control mt-2" id="outraSentenca" name="outra_sentenca"
         placeholder="Especifique outra sentença..." style="display:none;">
        <input type="date" class="form-control mt-2" id="dataSentenca" name="data_sentenca" style="display:none;">
       </div>

       <!-- Recursos -->
       <div class="mb-3">
        <label for="recursos" class="form-label">Recursos</label>
        <select class="form-control" id="recursos" name="recursos">
         <option value="Acusação">Acusação</option>
         <option value="Defesa">Defesa</option>
         <option value="Não há">Não há</option>
        </select>
       </div>

       <!-- Status -->
       <div class="mb-3">
        <label for="status" class="form-label">Status</label>
        <select class="form-control" id="status" name="status" onchange="atualizarFormulario()">
         <option value="Ativo" selected>Ativo</option>
         <option value="Finalizado">Finalizado</option>
        </select>
       </div>

       <!-- Campos Dinâmicos para Finalizado -->
       <div class="mb-3" id="opcoes_finalizado" style="display:none;"></div>

       <div class="d-flex gap-2">
        <button type="submit" class="btn btn-success w-100" name="cadastrar">Cadastrar</button>
        <button type="submit" class="btn btn-warning w-100" name="continuar_editando">Continuar Editando</button>
       </div>
      </form>
     </div>
    </div>
   </div>
  </div>
 </div>

 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
 <script>
 document.getElementById("numero").addEventListener("blur", function() {
  const numero = this.value;

  if (numero.trim() === "") return;

  fetch("../controllers/verificar_numero_processo.php", {
    method: "POST",
    headers: {
     "Content-Type": "application/x-www-form-urlencoded"
    },
    body: `numero=${encodeURIComponent(numero)}`
   })
   .then(response => response.json())
   .then(data => {
    if (data.existe) {
     alert("Já existe um processo com esse número!");
     document.getElementById("numero").value = "";
     document.getElementById("numero").focus();
    }
   })
   .catch(error => console.error("Erro ao verificar número:", error));
 });

 // Executa toggleSentenca ao carregar a página para exibir corretamente a data
 window.addEventListener("DOMContentLoaded", function() {
  toggleSentenca();
 });

 document.getElementById("municipio").addEventListener("change", function() {
  let municipioSelecionado = this.value;
  let bairroSelect = document.getElementById("bairro");
  bairroSelect.innerHTML = '<option value="">Selecione um Bairro</option>';
  <?php foreach ($bairros as $bairro): ?>
  if ("<?= $bairro['municipio_id'] ?>" === municipioSelecionado) {
   let option = document.createElement("option");
   option.value = "<?= $bairro['id'] ?>";
   option.textContent = "<?= htmlspecialchars($bairro['nome']) ?>";
   bairroSelect.appendChild(option);
  }
  <?php endforeach; ?>
 });

 function toggleVitima() {
  let check = document.getElementById("semVitima");
  let vitimaInput = document.getElementById("vitima");
  if (check.checked) {
   vitimaInput.value = "Não há";
   vitimaInput.readOnly = true;
  } else {
   vitimaInput.value = "";
   vitimaInput.readOnly = false;
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

 function atualizarFormulario() {
  let natureza = document.getElementById("natureza").value;
  let status = document.getElementById("status").value;
  let dataDenuncia = document.querySelector(".data_denuncia");
  let dataRecebimento = document.querySelector(".data_recebimento");
  let labelDenunciado = document.getElementById("label_denunciado");
  let opcoesFinalizado = document.getElementById("opcoes_finalizado");

  // Reset
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
    opcoesFinalizado.innerHTML = `<label>Marque:</label><br>
   <input type="checkbox" name="opcoes_finalizado[]" value="Oferecendo de Denúncia"> Oferecendo de Denúncia<br>
   <input type="checkbox" name="opcoes_finalizado[]" value="Arquivamento"> Arquivamento`;
   }
  } else if (natureza === "PIC") {
   dataDenuncia.style.display = "none";
   dataRecebimento.style.display = "none";
   labelDenunciado.innerText = "Investigado";
   if (status === "Finalizado") {
    opcoesFinalizado.style.display = "block";
    opcoesFinalizado.innerHTML = `<label>Marque:</label><br>
   <input type="checkbox" name="opcoes_finalizado[]" value="Oferecendo de Denúncia"> Oferecendo de Denúncia<br>
   <input type="checkbox" name="opcoes_finalizado[]" value="Realização de ANPP"> Realização de ANPP<br>
   <input type="checkbox" name="opcoes_finalizado[]" value="Arquivamento"> Arquivamento`;
   }
  } else if (natureza === "NF") {
   labelDenunciado.innerText = "Noticiado";
   if (status === "Finalizado") {
    opcoesFinalizado.style.display = "block";
    opcoesFinalizado.innerHTML = `<label>Marque:</label><br>
   <input type="checkbox" name="opcoes_finalizado[]" value="Requisição de Inquérito"> Requisição de Inquérito<br>
   <input type="checkbox" name="opcoes_finalizado[]" value="Conversão em PIC"> Conversão em PIC<br>
   <input type="checkbox" name="opcoes_finalizado[]" value="Arquivamento"> Arquivamento`;
   }
  } else if (natureza === "Outra") {
   dataDenuncia.style.display = "none";
   dataRecebimento.style.display = "none";
   labelDenunciado.innerText = "Investigado/Requerido";
   if (status === "Finalizado") {
    opcoesFinalizado.style.display = "block";
    opcoesFinalizado.innerHTML =
     `<label>Marque:</label><br>
   <input type="checkbox" name="opcoes_finalizado[]" value="Oferecendo de Denúncia"> Oferecendo de Denúncia<br>
   <input type="checkbox" name="opcoes_finalizado[]" value="Arquivamento"> Arquivamento<br>
   <input type="checkbox" name="opcoes_finalizado[]" value="Outra Medida" onclick="toggleOutraMedida(this)"> Outra Medida
   <input type="text" name="especifique_outra_medida" class="form-control mt-2" style="display:none;" placeholder="Especifique...">`;
   }
  }
 }

 function toggleOutraMedida(checkbox) {
  let campo = checkbox.parentElement.querySelector('input[type="text"]');
  campo.style.display = checkbox.checked ? "block" : "none";
 }
 </script>

</body>

</html>