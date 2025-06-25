<!--/controle_acervo/views/anpp.php-->
<?php
session_start();
require_once "../config/conexao.php";
global $pdo;

$perfil = $_SESSION['usuario_perfil'] ?? '';
$pagina_atual = basename($_SERVER['PHP_SELF']);

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Verifica se o perfil tem permissão de acesso (administrador, cadastrador ou cadastrador_consulta)
$perfis_permitidos = ['administrador', 'cadastrador', 'cadastrador_consulta'];

if (!in_array($_SESSION['usuario_perfil'], $perfis_permitidos)) {
    $_SESSION['mensagens'][] = "Acesso negado! Você não tem permissão para acessar esta página.";
    header("Location: dashboard.php");
    exit();
}


// Buscar crimes do ANPP para listagem
$crimes_anpp = $pdo->query("SELECT * FROM crimes_anpp ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">

<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Cadastro de ANPP</title>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

 <link rel="icon" href="../public/img/favicon-32x32.png" type="../img/favicon-32x32.png">

 <style>
 .table-responsive {
  overflow-x: auto;
 }

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
 </style>


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

     <!-- Listagem de ANPP: 1, 2, 3 -->
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

     <!-- Log de Atividades: apenas administrador -->
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

 <div class="container mt-5">
  <h2 class="text-center"><i class="fas fa-file-circle-plus"></i> Cadastrar ANPP</h2>
  <form action="../controllers/salvar_anpp.php" method="POST">
   <div class="card p-4 shadow">
    <div class="mb-3">
     <label for="numero_inquerito" class="form-label">Número do Inquérito</label>
     <input type="text" class="form-control" name="numero_inquerito" id="numero_inquerito" required>
     <small id="numero-feedback" class="form-text text-danger" style="display:none;"></small>

    </div>
    <div class="mb-3">
     <label for="indiciado" class="form-label">Indiciado</label>
     <input type="text" class="form-control" name="indiciado" required>
    </div>
    <div class="mb-3">
     <label for="crime" class="form-label">Crime</label>
     <select class="form-control" name="crime" required>
      <option value="">Selecione um Crime</option>
      <?php foreach ($crimes_anpp as $crime): ?>
      <option value="<?= $crime['id'] ?>"><?= htmlspecialchars($crime['nome']) ?></option>
      <?php endforeach; ?>
     </select>
    </div>
    <div class="mb-3">
     <label for="nome_vitima" class="form-label">Nome da Vítima</label>
     <input type="text" class="form-control" name="nome_vitima">
    </div>
    <div class="mb-3">
     <label for="data_audiencia" class="form-label">Data da Audiência</label>
     <input type="date" class="form-control" name="data_audiencia">
    </div>


    <div class="mb-3">
     <label class="form-label">Acordo</label>
     <div>

      <input type="radio" name="acordo" value="realizado" onclick="mostrarCampos(true)"> Realizado
      <input type="radio" name="acordo" value="nao_realizado" onclick="mostrarCampos(false)" checked>
      Não Realizado



     </div>
    </div>


    <div id="camposAcordo" style="display: none;">
     <div class="mb-3">
      <label for="reparacao" class="form-label">Reparação da Vítima</label>
      <select class="form-control" name="reparacao" onchange="toggleInput(this, 'valor_reparacao')">
       <option value="nao">Não</option>
       <option value="sim">Sim</option>
      </select>
      <input type="text" class="form-control mt-2" name="valor_reparacao" id="valor_reparacao" style="display: none;"
       placeholder="Valor da reparação">
     </div>
     <div class="mb-3">
      <label for="servico_comunitario" class="form-label">Prestação de Serviço Comunitário</label>
      <select class="form-control" name="servico_comunitario" onchange="toggleInput(this, 'tempo_servico')">
       <option value="nao">Não</option>
       <option value="sim">Sim</option>
      </select>
      <input type="text" class="form-control mt-2" name="tempo_servico" id="tempo_servico" style="display: none;"
       placeholder="Tempo de serviço">
     </div>
     <div class="mb-3">
      <label for="multa" class="form-label">Multa</label>
      <select class="form-control" name="multa" onchange="toggleInput(this, 'valor_multa')">
       <option value="nao">Não</option>
       <option value="sim">Sim</option>
      </select>
      <input type="text" class="form-control mt-2" name="valor_multa" id="valor_multa" style="display: none;"
       placeholder="Valor da multa">
     </div>
     <div class="mb-3">
      <label for="restituicao" class="form-label">Restituição da Coisa à Vítima</label>
      <input type="text" class="form-control" name="restituicao">
     </div>
    </div>
    <button type="submit" class="btn btn-primary">Salvar</button>
   </div>
  </form>
 </div>
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
 <script>
 function mostrarCampos(ativo) {
  document.getElementById('camposAcordo').style.display = ativo ? 'block' : 'none';
 }

 function toggleInput(select, inputId) {
  const input = document.getElementById(inputId);

  if (select.value === 'sim') {
   input.style.display = 'block';
   input.removeAttribute('disabled'); // ⬅️ importante
  } else {
   input.style.display = 'none';
   input.setAttribute('disabled', 'true'); // ⬅️ importante
   input.value = ''; // limpa o valor
  }
 }
 </script>

 <script>
 document.getElementById('numero_inquerito').addEventListener('input', function() {
  const numero = this.value.trim();
  const feedback = document.getElementById('numero-feedback');

  if (numero.length < 3) {
   feedback.style.display = 'none';
   return;
  }

  fetch(`../controllers/verificar_numero_anpp.php?numero=${encodeURIComponent(numero)}`)
   .then(response => response.json())
   .then(data => {
    if (data.existe) {
     feedback.innerText = 'Já existe um ANPP com esse número de inquérito.';
     feedback.style.display = 'block';
    } else {
     feedback.innerText = '';
     feedback.style.display = 'none';
    }
   })
   .catch(error => {
    console.error('Erro na verificação:', error);
   });
 });
 </script>

</body>

</html>