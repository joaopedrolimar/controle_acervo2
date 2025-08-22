<?php
session_start();
require_once "../config/conexao.php";
global $pdo; // Garante que a conexão PDO está acessível

// Verifica se o perfil tem permissão de acesso (administrador, cadastrador ou cadastrador_consulta)
$perfis_permitidos = ['administrador', 'cadastrador', 'cadastrador_consulta'];

if (!in_array($_SESSION['usuario_perfil'], $perfis_permitidos)) {
    $_SESSION['mensagens'][] = "Acesso negado! Você não tem permissão para acessar esta página.";
    header("Location: dashboard.php");
    exit();
}


// Busca usuários do banco
$sql = "SELECT * FROM usuarios";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$perfil = $_SESSION['usuario_perfil'] ?? '';

$pagina_atual = basename($_SERVER['PHP_SELF']);
?>


<!DOCTYPE html>
<html lang="pt">

<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Gerenciar Usuários</title>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
 <link rel="icon" href="../public/img/favicon-32x32.png" type="../img/favicon-32x32.png">

 <style>
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

 .btn-action:not(:last-child) {
  margin-right: 5px;
  /* Espaço entre os botões */
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
       <i class="fas fa-list"></i> <br> Listar Processos
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

     <!-- Listagem ANPP: 1, 2, 3 -->
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

     <!-- Mural de Atualizações: todos -->
     <?php if (in_array($perfil, ['administrador', 'consultor', 'cadastrador', 'cadastrador_consulta'])): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'mural.php') ? 'active' : '' ?>" href="mural.php">
       <i class="fas fa-bullhorn"></i> <br> Mural de Atualizações
      </a>
     </li>
     <?php endif; ?>

     <!-- Log de Atividades: apenas admin -->
     <?php if ($perfil === 'administrador'): ?>
     <li class="nav-item">
      <a class="nav-link <?= ($pagina_atual == 'log_atividades.php') ? 'active' : '' ?>" href="log_atividades.php">
       <i class="fas fa-history"></i> <br> Log de Atividades
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

  <h2 class="text-center "> <i class="fas fa-users-cog"></i> Gerenciar Usuários</h2>

  <!-- Formulário para adicionar usuário -->
  <div class="card shadow-sm mb-4">
   <div class="card-header bg-primary text-white">Adicionar Novo Usuário</div>
   <div class="card-body">
    <form action="../controllers/usuario_controller.php" method="POST">
     <div class="mb-3">
      <label for="nome" class="form-label">Nome</label>
      <input type="text" class="form-control" id="nome" name="nome" required>
     </div>
     <div class="mb-3">
      <label for="email" class="form-label">E-mail</label>
      <input type="email" class="form-control" id="email" name="email" required>
     </div>
     <div class="mb-3">
      <label for="senha" class="form-label">Senha</label>
      <input type="password" class="form-control" id="senha" name="senha" required>
     </div>
     <div class="mb-3">
      <label for="perfil" class="form-label">Perfil</label>
      <select class="form-control" id="perfil" name="perfil" required>
       <option value="administrador">Administrador</option>
       <option value="cadastrador">Cadastrador</option>
       <option value="consultor">Consultor</option>
       <option value="cadastrador_consulta">Cadastrador com Consulta</option>

      </select>
     </div>
     <button type="submit" class="btn btn-success w-100" name="adicionar_usuario">Adicionar
      Usuário</button>
    </form>
   </div>
  </div>

  <!-- Tabela de Usuários -->
  <div class="table-responsive">
   <table class="table table-striped">
    <thead>
     <tr>
      <th>ID</th>
      <th>Nome</th>
      <th>E-mail</th>
      <th>Perfil</th>
      <th>Status</th>
      <th>Ações</th>
     </tr>
    </thead>
    <tbody>
     <?php foreach ($usuarios as $usuario): ?>
     <tr>
      <td><?= $usuario['id'] ?></td>
      <td><?= htmlspecialchars($usuario['nome']) ?></td>
      <td><?= htmlspecialchars($usuario['email']) ?></td>
      <td><?= ucfirst($usuario['perfil']) ?></td>
      <td><?= $usuario['aprovado'] ? 'Ativado' : 'Desativado' ?></td>
      <td>

       <!-- Botão para abrir modal -->
       <button class="btn btn-info btn-sm btn-action" data-bs-toggle="modal"
        data-bs-target="#modalSenha<?= $usuario['id'] ?>">
        <i class="fas fa-key"></i> Senha
       </button>

       <!-- Modal de Alterar Senha -->
       <div class="modal fade" id="modalSenha<?= $usuario['id'] ?>" tabindex="-1"
        aria-labelledby="modalLabel<?= $usuario['id'] ?>" aria-hidden="true">
        <div class="modal-dialog">
         <form method="POST" action="../controllers/alterar_senha_usuario.php">
          <div class="modal-content">
           <div class="modal-header">
            <h5 class="modal-title">Alterar Senha de
             <?= htmlspecialchars($usuario['nome']) ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
           </div>
           <div class="modal-body">
            <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
            <label>Nova Senha</label>
            <input type="password" name="nova_senha" class="form-control" required>
           </div>
           <div class="modal-footer">
            <button type="submit" name="alterar_senha" class="btn btn-success">Salvar</button>
           </div>
          </div>
         </form>
        </div>
       </div>


       <a href="../controllers/editar_usuario.php?id=<?= $usuario['id'] ?>" class="btn btn-warning btn-sm btn-action"><i
         class="fas fa-edit"></i> Editar
       </a>

       <a href="../controllers/deletar_usuario.php?id=<?= $usuario['id'] ?>" class="btn btn-danger btn-sm btn-action"
        onclick="return confirm('Tem certeza que deseja excluir?');"><i class="fas fa-trash"></i> Excluir
       </a>

       <a href="../controllers/ativar_usuario.php?id=<?= $usuario['id'] ?>"
        class="btn btn-sm btn-action <?= $usuario['aprovado'] ? 'btn-success' : 'btn-secondary' ?>">
        <i class="fas <?= $usuario['aprovado'] ? 'fa-toggle-on' : 'fa-toggle-off' ?>"></i>
        <?= $usuario['aprovado'] ? 'Desativar' : 'Ativar' ?>
       </a>

      </td>
     </tr>
     <?php endforeach; ?>
    </tbody>
   </table>
  </div>
 </div>

 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>