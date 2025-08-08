<!--controle_acervo/views/listar_processos.php-->
<?php
session_start();
require_once "../config/conexao.php"; 
global $pdo; 

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Define o perfil do usuário logado
$perfil = $_SESSION['usuario_perfil'] ?? '';

// Definições de paginação
$registros_por_pagina = 10;
$paginaAtual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$offset = ($paginaAtual - 1) * $registros_por_pagina;

$pagina_atual = basename($_SERVER['PHP_SELF']);

// Captura os filtros do formulário
$search = $_GET['search'] ?? '';
$advanced_search = isset($_GET['advanced_search']);

$data_fato_inicio = $_GET['data_fato_inicio'] ?? '';
$data_fato_fim = $_GET['data_fato_fim'] ?? '';

$id_filter = $_GET['id_filter'] ?? '';
$date_filter = $_GET['date_filter'] ?? '';
$municipio_filter = $_GET['municipio_filter'] ?? '';
$bairro_filter = $_GET['bairro_filter'] ?? '';
$vitima_filter = $_GET['vitima_filter'] ?? '';
$denunciado_filter = $_GET['denunciado_filter'] ?? '';
$sentenca_filter = $_GET['sentenca_filter'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';

// Construção da Query SQL dinâmica com PDO
$sql = "SELECT processos.*, 
               crimes.nome AS nome_crime, 
               municipios.nome AS nome_municipio, 
               bairros.nome AS nome_bairro 
        FROM processos 
        LEFT JOIN crimes ON processos.crime_id = crimes.id
        LEFT JOIN municipios ON processos.local_municipio = municipios.id
        LEFT JOIN bairros ON processos.local_bairro = bairros.id
        WHERE 1=1";


$params = [];

if (!empty($search)) {
    $search_normalizado = iconv('UTF-8', 'ASCII//TRANSLIT', mb_strtolower(trim($search)));

    $is_nao_ha = trim($search_normalizado) === 'nao ha';

    $sql .= " AND (";

    // Condições de LIKE
    if (!$is_nao_ha) {
        $sql .= "
            CAST(processos.id AS CHAR) LIKE :search
            OR processos.numero LIKE :search 
            OR crimes.nome LIKE :search
            OR processos.natureza LIKE :search
            OR processos.denunciado LIKE :search 
            OR COALESCE(processos.vitima, '') LIKE :search
            OR COALESCE(processos.local_municipio, '') LIKE :search 
            OR COALESCE(processos.local_bairro, '') LIKE :search 
            OR COALESCE(processos.sentenca, '') LIKE :search 
            OR COALESCE(processos.recursos, '') LIKE :search
            OR COALESCE(processos.status, '') LIKE :search
        ";
    }

    // Se buscar "não há", adiciona verificação de campos nulos ou vazios
    if ($is_nao_ha) {
        $sql .= "
            processos.vitima IS NULL OR TRIM(processos.vitima) = ''
            OR processos.recursos IS NULL OR TRIM(processos.recursos) = ''
            OR processos.status IS NULL OR TRIM(processos.status) = ''
        ";
    }

    $sql .= ")";

    if (!$is_nao_ha) {
        $params[':search'] = "%$search%";
    }
}





if ($advanced_search) {
    if (!empty($id_filter)) {
        $sql .= " AND processos.id = :id_filter";
        $params[':id_filter'] = $id_filter;
    }
    if (!empty($date_filter)) {
        $sql .= " AND processos.data_denuncia = :date_filter";
        $params[':date_filter'] = $date_filter;
    }

    if (!empty($data_fato_inicio)) {
        $sql .= " AND processos.data_denuncia >= :data_fato_inicio";
        $params[':data_fato_inicio'] = $data_fato_inicio;
    }
    
    if (!empty($data_fato_fim)) {
        $sql .= " AND processos.data_denuncia <= :data_fato_fim";
        $params[':data_fato_fim'] = $data_fato_fim;
    }
    

    if (!empty($municipio_filter)) {
        $sql .= " AND processos.local_municipio LIKE :municipio_filter";
        $params[':municipio_filter'] = "%$municipio_filter%";
    }
    if (!empty($bairro_filter)) {
        $sql .= " AND processos.local_bairro LIKE :bairro_filter";
        $params[':bairro_filter'] = "%$bairro_filter%";
    }
    if (!empty($vitima_filter)) {
        $sql .= " AND processos.vitima LIKE :vitima_filter";
        $params[':vitima_filter'] = "%$vitima_filter%";
    }
    if (!empty($denunciado_filter)) {
        $sql .= " AND processos.denunciado LIKE :denunciado_filter";
        $params[':denunciado_filter'] = "%$denunciado_filter%";
    }
    if (!empty($sentenca_filter)) {
        $sql .= " AND processos.sentenca LIKE :sentenca_filter";
        $params[':sentenca_filter'] = "%$sentenca_filter%";
    }
    if (!empty($status_filter)) {
        $sql .= " AND processos.status LIKE :status_filter";
        $params[':status_filter'] = "%$status_filter%";
    }
    if (!empty($_GET['crime_filter'])) { // Verifica se há um filtro de crime
        $sql .= " AND crimes.nome LIKE :crime_filter";
        $params[':crime_filter'] = "%{$_GET['crime_filter']}%";
    }
}

// Contagem total de registros para a paginação (mantendo os mesmos filtros)
$sql_count = "SELECT COUNT(*) AS total 
              FROM processos 
              LEFT JOIN crimes ON processos.crime_id = crimes.id 
              WHERE 1=1";

if (!empty($search)) {
    $search_normalizado = iconv('UTF-8', 'ASCII//TRANSLIT', mb_strtolower(trim($search)));
    $is_nao_ha = trim($search_normalizado) === 'nao ha';

    $sql_count .= " AND (";

    if (!$is_nao_ha) {
        $sql_count .= "
            CAST(processos.id AS CHAR) LIKE :search
            OR processos.numero LIKE :search 
            OR crimes.nome LIKE :search
            OR processos.natureza LIKE :search
            OR processos.denunciado LIKE :search 
            OR COALESCE(processos.vitima, '') LIKE :search 
            OR COALESCE(processos.local_municipio, '') LIKE :search 
            OR COALESCE(processos.local_bairro, '') LIKE :search 
            OR COALESCE(processos.sentenca, '') LIKE :search 
            OR COALESCE(processos.recursos, '') LIKE :search
            OR COALESCE(processos.status, '') LIKE :search
        ";
    }

    if ($is_nao_ha) {
        $sql_count .= "
            processos.vitima IS NULL OR TRIM(processos.vitima) = ''
            OR processos.recursos IS NULL OR TRIM(processos.recursos) = ''
            OR processos.status IS NULL OR TRIM(processos.status) = ''
        ";
    }

    $sql_count .= ")";

    if (!$is_nao_ha) {
        $params[':search'] = "%$search%";
    }
}




if ($advanced_search) {
    if (!empty($id_filter)) {
        $sql_count .= " AND processos.id = :id_filter";
    }
    if (!empty($date_filter)) {
        $sql_count .= " AND processos.data_denuncia = :date_filter";
    }

    if (!empty($data_fato_inicio)) {
        $sql_count .= " AND processos.data_denuncia >= :data_fato_inicio";
    }
    
    if (!empty($data_fato_fim)) {
        $sql_count .= " AND processos.data_denuncia <= :data_fato_fim";
    }
    

    if (!empty($municipio_filter)) {
        $sql_count .= " AND processos.local_municipio LIKE :municipio_filter";
    }
    if (!empty($bairro_filter)) {
        $sql_count .= " AND processos.local_bairro LIKE :bairro_filter";
    }
    if (!empty($vitima_filter)) {
        $sql_count .= " AND processos.vitima LIKE :vitima_filter";
    }
    if (!empty($denunciado_filter)) {
        $sql_count .= " AND processos.denunciado LIKE :denunciado_filter";
    }
    if (!empty($sentenca_filter)) {
        $sql_count .= " AND processos.sentenca LIKE :sentenca_filter";
    }
    if (!empty($status_filter)) {
        $sql_count .= " AND processos.status LIKE :status_filter";
    }
    if (!empty($_GET['crime_filter'])) {
        $sql_count .= " AND crimes.nome LIKE :crime_filter";
    }
}

// Prepara e executa a contagem
$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params);
$total_registros = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];


$total_paginas = ceil($total_registros / $registros_por_pagina);

// Adiciona o LIMIT para paginação
$sql .= " LIMIT $offset, $registros_por_pagina";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$processos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">

<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Lista de Processos</title>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
 <link rel="icon" href="../public/img/favicon-32x32.png" type="../img/favicon-32x32.png">

 <style>
 .table-responsive {
  overflow-x: unset;
  /* remove o scroll */
 }

 .table {
  width: 100%;
  /* faz ocupar toda a largura */
  table-layout: auto;
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

 .btn-action {
  width: 100px;
  /* Mantém o tamanho padrão */
  text-align: center;
  /* Centraliza o conteúdo */
  display: inline-flex;
  /* Mantém alinhamento entre ícone e texto */
  align-items: center;
  /* Centraliza verticalmente */
  justify-content: center;
  /* Centraliza horizontalmente */
  white-space: nowrap;
  /* Impede que o texto quebre */
  margin: 3px;
  /* Adiciona um espaçamento entre os botões */
 }
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
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'dashboard.php') ? 'active' : '' ?>" href="dashboard.php">
       <i class="fas fa-home"></i> Início
      </a>
     </li>

     <?php if (in_array($perfil, ['administrador', 'consultor', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'listar_processos.php') ? 'active' : '' ?>" href="listar_processos.php">
       <i class="fas fa-list"></i> <br> Listar Processos
      </a>
     </li>
     <?php endif; ?>

     <?php if (in_array($perfil, ['administrador', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'cadastro_processo.php') ? 'active' : '' ?>"
       href="cadastro_processo.php">
       <i class="fas fa-plus"></i> Cadastrar Processos
      </a>
     </li>
     <?php endif; ?>

     <?php if (in_array($perfil, ['administrador', 'consultor', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'listar_anpp.php') ? 'active' : '' ?>" href="listar_anpp.php">
       <i class="fas fa-scale-balanced"></i> Listagem de ANPPs
      </a>
     </li>
     <?php endif; ?>

     <?php if (in_array($perfil, ['administrador', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'anpp.php') ? 'active' : '' ?>" href="anpp.php">
       <i class="fas fa-file-circle-plus"></i> Cadastrar ANPP
      </a>
     </li>
     <?php endif; ?>

     <?php if (in_array($perfil, ['administrador', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'gerenciar_usuarios.php') ? 'active' : '' ?>"
       href="gerenciar_usuarios.php">
       <i class="fas fa-users-cog"></i> Gerenciar Usuários
      </a>
     </li>
     <?php endif; ?>

     <?php if (in_array($perfil, ['administrador', 'consultor', 'cadastrador_consulta', 'cadastrador'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'atos.php') ? 'active' : '' ?>" href="atos.php">
       <i class="fas fa-file-alt"></i> Atos
      </a>
     </li>
     <?php endif; ?>

     <!-- Mural de Atualizações: todos -->
     <?php if (in_array($perfil, ['administrador', 'consultor', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'mural.php') ? 'active' : '' ?>" href="mural.php">
       <i class="fas fa-bullhorn"></i> <br> Mural de Atualizações
      </a>
     </li>
     <?php endif; ?>

     <?php if ($perfil === 'administrador'): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'log_atividades.php') ? 'active' : '' ?>" href="log_atividades.php">
       <i class="fas fa-history"></i> <br> Log de Atividades
      </a>
     </li>
     <?php endif; ?>

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


     <li class="nav-item">
      <a class="nav-link text-white" href="../controllers/logout.php">
       <i class="fas fa-sign-out-alt"></i> Sair
      </a>
     </li>
    </ul>
   </div>
  </div>
 </nav>




 <!-- Conteúdo -->
 <div class="container mt-4">
  <h2 class="text-center"><i class="fas fa-folder-open"></i> Acervo Processual</h2>

  <!-- Pesquisa Simples -->
  <form method="GET">
   <div class="input-group mb-3">
    <input type="text" name="search" class="form-control" placeholder="🔍 Pesquisa Simples"
     value="<?= htmlspecialchars($search) ?>">
    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
   </div>
  </form>

  <!-- Botão para Pesquisa Avançada -->
  <button class="btn btn-secondary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#advancedSearch">🔍
   Pesquisa Avançada</button>

  <!-- Pesquisa Avançada -->
  <div class="collapse" id="advancedSearch">
   <form method="GET" class="row g-3">
    <input type="hidden" name="advanced_search" value="1">
    <div class="col-md-3"><input type="text" name="id_filter" class="form-control" placeholder="ID"></div>
    <div class="col-md-3"><input type="date" name="date_filter" class="form-control"></div>

    <div class="col-md-3 form-floating">
     <input type="date" name="data_fato_inicio" class="form-control" id="data_fato_inicio"
      value="<?= htmlspecialchars($_GET['data_fato_inicio'] ?? '') ?>">
     <label for="data_fato_inicio">📅 Data Fato - Início</label>
    </div>

    <div class="col-md-3 form-floating">
     <input type="date" name="data_fato_fim" class="form-control" id="data_fato_fim"
      value="<?= htmlspecialchars($_GET['data_fato_fim'] ?? '') ?>">
     <label for="data_fato_fim">📅 Data Fato - Fim</label>
    </div>

    <div class="col-md-3"><input type="text" name="municipio_filter" class="form-control" placeholder="Município"></div>
    <div class="col-md-3"><input type="text" name="bairro_filter" class="form-control" placeholder="Bairro">
    </div>
    <div class="col-md-3"><input type="text" name="vitima_filter" class="form-control" placeholder="Nome da Vítima">
    </div>
    <div class="col-md-3"><input type="text" name="denunciado_filter" class="form-control" placeholder="Denunciado">
    </div>
    <div class="col-md-3"><input type="text" name="sentenca_filter" class="form-control" placeholder="Sentença"></div>
    <div class="col-md-3"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i>
      Buscar</button></div>
   </form>
  </div>

  <!-- Tabela Responsiva -->
  <div class="table-responsive">
   <table class="table table-striped">
    <thead>
     <tr>
      <th>ID</th>
      <th>Número</th>
      <th>Natureza</th>

      <th>Denúncia</th>
      <th>Data do Recebimento da Denúncia</th>
      <th>Crime</th>
      <th>Denunciado</th>
      <th>Vítima</th>
      <th>Local</th>
      <th>Sentença</th>
      <th>Data Sentença</th>
      <th>Recursos</th>
      <th>Status</th>
      <th>Decisões</th>
      <th>Ações</th>
     </tr>
    </thead>
    <tbody>
     <?php foreach ($processos as $p): ?>
     <?php
     // Novo label para tipo do processo
     $label = match($p['natureza']) {
        'Inquérito Policial' => 'Flagrado/Indiciado',
        'PIC' => 'Investigado',
        'NF' => 'Noticiado',
        'Outra' => 'Investigado/Requerido',
        default => 'Denunciado'
     };
     // Monta array de decisões finais
     $decisoes = [];
     if ($p['oferecendo_denuncia']) $decisoes[] = 'Oferecimento de Denúncia';
     if ($p['arquivamento']) $decisoes[] = 'Arquivamento';
     if ($p['realizacao_anpp']) $decisoes[] = 'Realização ANPP';
     if ($p['requisicao_inquerito']) $decisoes[] = 'Requisição Inquérito';
     if ($p['conversao_pic']) $decisoes[] = 'Conversão PIC';
     if ($p['outra_medida']) $decisoes[] = $p['especifique_outra_medida'] ?: 'Outra Medida';
   ?>
     <tr>
      <td><?= $p['id'] ?></td>
      <td><?= htmlspecialchars($p['numero']) ?></td>
      <td><?= htmlspecialchars($p['natureza']) ?></td>

      <td>
       <?= ($p['data_denuncia'] && $p['data_denuncia']!='0000-00-00') ? date('d/m/Y', strtotime($p['data_denuncia'])) : 'Não informado' ?>
      </td>
      <td>
       <?= ($p['data_recebimento_denuncia'] && $p['data_recebimento_denuncia']!='0000-00-00') ? date('d/m/Y', strtotime($p['data_recebimento_denuncia'])) : 'Não informado' ?>
      </td>
      <td><?= htmlspecialchars($p['nome_crime']) ?></td>
      <td><b><?= $label ?>:</b> <?= htmlspecialchars($p['denunciado']) ?></td>
      <td><?= htmlspecialchars($p['vitima']) ?></td>
      <td><?= htmlspecialchars(($p['nome_municipio']??'Não informado').'-'.($p['nome_bairro']??'')) ?></td>
      <td><?= htmlspecialchars($p['sentenca']) ?></td>
      <td>
       <?= ($p['data_sentenca'] && $p['data_sentenca']!='0000-00-00') ? date('d/m/Y', strtotime($p['data_sentenca'])) : 'Não informado' ?>
      </td>
      <td><?= htmlspecialchars($p['recursos']) ?></td>
      <td>
       <?= $p['status']=='Ativo'?'<span class="badge bg-primary">Ativo</span>':($p['status']=='Finalizado'?'<span class="badge bg-success">Finalizado</span>':'<span class="badge bg-warning">Incompleto</span>') ?>
      </td>
      <td><?= $decisoes ? implode(', ', $decisoes) : '-' ?></td>
      <td>
       <div class="d-flex flex-column align-items-center">
        <button class="btn btn-info btn-sm mb-1 w-100" data-bs-toggle="modal" data-bs-target="#modal<?= $p['id'] ?>">
         <i class="fas fa-eye"></i> Exibir
        </button>
        <?php if (in_array($perfil, ['administrador', 'cadastrador', 'cadastrador_consulta'])): ?>
        <a href="../controllers/editar_processo.php?id=<?= $p['id'] ?>" class="btn btn-warning btn-sm mb-1 w-100">
         <i class="fas fa-edit"></i> Editar
        </a>
        <a href="../controllers/deletar_processo.php?id=<?= $p['id'] ?>" class="btn btn-danger btn-sm mb-1 w-100"
         onclick="return confirm('Tem certeza?')">
         <i class="fas fa-trash"></i> Excluir
        </a>
        <?php endif; ?>
        <a target="_blank" href="../controllers/gerar_pdf.php?id=<?= $p['id'] ?>" class="btn btn-success btn-sm w-100">
         <i class="fas fa-file-pdf"></i> PDF
        </a>
       </div>
      </td>
     </tr>

     <!-- Calcula tempo de vida  do Processo -->
     <?php
$dias_ativo = '';
if (!empty($p['status']) && $p['status'] === 'Ativo' && !empty($p['data_denuncia']) && $p['data_denuncia'] != '0000-00-00') {
    $data_inicio = new DateTime($p['data_denuncia']);
    $hoje = new DateTime();
    $dias = $data_inicio->diff($hoje)->days;
    $dias_ativo = "$dias dias em Ativo";
}

?>


     <!-- Modal para Exibir Detalhes do Processo -->
     <div class="modal fade" id="modal<?= $p['id'] ?>" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
       <div class="modal-content">
        <div class="modal-header bg-primary text-white">
         <h5 class="modal-title">Detalhes do Processo #<?= htmlspecialchars($p['numero']) ?></h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">

         <?php if ($p['numero']): ?>
         <p><strong>Número do Processo:</strong> <?= htmlspecialchars($p['numero']) ?></p>
         <?php endif; ?>

         <?php if ($p['natureza']): ?>
         <p><strong>Natureza:</strong> <?= htmlspecialchars($p['natureza']) ?></p>
         <?php endif; ?>

         <?php if ($p['nome_crime']): ?>
         <p><strong>Crime:</strong> <?= htmlspecialchars($p['nome_crime']) ?></p>
         <?php endif; ?>

         <?php if ($p['denunciado']): ?>
         <p><strong><?= $label ?>:</strong> <?= htmlspecialchars($p['denunciado']) ?></p>
         <?php endif; ?>

         <?php if ($p['vitima']): ?>
         <p><strong>Vítima:</strong> <?= htmlspecialchars($p['vitima']) ?></p>
         <?php endif; ?>

         <?php if ($p['sentenca']): ?>
         <p><strong>Sentença:</strong> <?= htmlspecialchars($p['sentenca']) ?></p>
         <?php endif; ?>

         <?php if ($p['data_recebimento_denuncia'] && $p['data_recebimento_denuncia'] != '0000-00-00'): ?>
         <p><strong>Data do Recebimento:</strong> <?= date('d/m/Y', strtotime($p['data_recebimento_denuncia'])) ?></p>
         <?php endif; ?>

         <?php if ($p['data_denuncia'] && $p['data_denuncia'] != '0000-00-00'): ?>
         <p><strong>Data da Denúncia:</strong> <?= date('d/m/Y', strtotime($p['data_denuncia'])) ?></p>
         <?php endif; ?>

         <?php if ($p['data_sentenca'] && $p['data_sentenca'] != '0000-00-00'): ?>
         <p><strong>Data da Sentença:</strong> <?= date('d/m/Y', strtotime($p['data_sentenca'])) ?></p>
         <?php endif; ?>

         <?php if ($p['nome_municipio'] || $p['nome_bairro']): ?>
         <p><strong>Local do Fato:</strong>
          <?= htmlspecialchars(($p['nome_municipio']??'').'-'.($p['nome_bairro']??'')) ?></p>
         <?php endif; ?>

         <?php if ($p['recursos']): ?>
         <p><strong>Recursos:</strong> <?= htmlspecialchars($p['recursos']) ?></p>
         <?php endif; ?>

         <?php if ($p['status']): ?>
         <p><strong>Status:</strong> <?= htmlspecialchars($p['status']) ?></p>
         <?php endif; ?>

         <?php if (!empty($dias_ativo)): ?>
         <p><strong>Tempo no status Ativo:</strong> <?= $dias_ativo ?></p>
         <?php endif; ?>


         <?php if ($decisoes): ?>
         <p><strong>Decisões:</strong> <?= implode(', ', $decisoes) ?></p>
         <?php endif; ?>

        </div>
       </div>
      </div>
     </div>


     <?php endforeach; ?>
    </tbody>

   </table>
  </div>
 </div>

 <!-- Paginação -->
 <nav>
  <ul class="pagination justify-content-center mt-4">
   <?php
  $max_links = 2; // Quantas páginas antes e depois da atual
  $start = max(1, $paginaAtual - $max_links);
  $end = min($total_paginas, $paginaAtual + $max_links);


  $base_url = "?pagina=";
  $filtros = "";
  $filtros .= $search ? "&search=$search" : '';
  $filtros .= $advanced_search ? '&advanced_search=1' : '';
  $filtros .= $id_filter ? "&id_filter=$id_filter" : '';
  $filtros .= $date_filter ? "&date_filter=$date_filter" : '';
  $filtros .= $municipio_filter ? "&municipio_filter=$municipio_filter" : '';
  $filtros .= $bairro_filter ? "&bairro_filter=$bairro_filter" : '';

  // Primeira página
  if ($start > 1) {
    echo '<li class="page-item"><a class="page-link" href="' . $base_url . '1' . $filtros . '">1</a></li>';
    if ($start > 2) {
      echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
  }

  // Páginas do intervalo
  for ($i = $start; $i <= $end; $i++) {
    $active = ($i == $paginaAtual) ? 'active' : '';
    echo '<li class="page-item ' . $active . '"><a class="page-link" href="' . $base_url . $i . $filtros . '">' . $i . '</a></li>';
  }

  // Última página
if ($end < $total_paginas) {
    if ($end < $total_paginas - 1) {
        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
    echo '<li class="page-item"><a class="page-link" href="' . $base_url . $total_paginas . $filtros . '">' . $total_paginas . '</a></li>';
}

  ?>
  </ul>
 </nav>


 <div class="text-center mt-3 mb-5 text-muted">
  Página <?= $paginaAtual ?> de <?= $total_paginas ?>,
  <?= count($processos) ?> registros nesta página de um total de <?= $total_registros ?> registros.
 </div>

 </div>


 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>