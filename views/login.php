<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Login</title>

 <!-- Bootstrap e FontAwesome -->
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

 <style>
 body {
  background-color: #f4f5f7;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
 }

 .logo-navbar {
  max-width: 300px;
  height: auto;
 }

 @media (max-width: 576px) {
  .logo-navbar {
   max-width: 220px;
   margin: auto;
   display: block;
  }
 }

 .login-card {
  border-radius: 1rem;
  box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
  background-color: #fff;
 }

 .form-control:focus {
  border-color: #900020;
  box-shadow: 0 0 0 0.2rem rgba(144, 0, 32, 0.25);
 }



 .btn-primary {
  background-color: #007bff;
  border-color: #007bff;
 }

 .btn-primary:hover {
  background-color: #0056b3;
  border-color: #0056b3;
 }

 .btn-secondary:hover {
  background-color: #6c757d;
  border-color: #5c636a;
 }
 </style>
</head>

<body>
 <!-- Navbar fixa com logo -->
 <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #900020;">
  <div class="container">
   <img src="../public/img/logoWhite.png" alt="Logo" class="logo-navbar">
  </div>
 </nav>

 <!-- Conteúdo central -->
 <div class="container d-flex align-items-center justify-content-center vh-100">
  <div class="col-md-4">
   <div class="card login-card p-4">
    <h3 class="text-center mb-4">Login</h3>

    <?php if (isset($_SESSION['erro_login'])): ?>
    <div class="alert alert-danger text-center">
     <?= $_SESSION['erro_login']; ?>
    </div>
    <?php unset($_SESSION['erro_login']); ?>
    <?php endif; ?>

    <form action="../controllers/login_controller.php" method="POST">
     <div class="mb-3">
      <div class="input-group">
       <span class="input-group-text bg-white"><i class="fa fa-envelope"></i></span>
       <input type="email" name="email" class="form-control" placeholder="E-mail" required>
      </div>
     </div>

     <div class="mb-3">
      <div class="input-group">
       <span class="input-group-text bg-white"><i class="fa fa-lock"></i></span>
       <input type="password" name="senha" class="form-control" placeholder="Senha" required>
      </div>
     </div>


     <button type="submit" class="btn btn-primary w-100">Entrar</button>
    </form>

    <div class="text-center mt-3">
     <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAtualizacoes">
      Últimas Atualizações
     </button>
    </div>
   </div>
  </div>
 </div>

 <!-- Modal de Atualizações -->
 <div class="modal fade" id="modalAtualizacoes" tabindex="-1" aria-labelledby="modalAtualizacoesLabel"
  aria-hidden="true">
  <div class="modal-dialog">
   <div class="modal-content">
    <div class="modal-header">
     <h5 class="modal-title" id="modalAtualizacoesLabel">Últimas Atualizações</h5>
     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
    </div>
    <div class="modal-body">
     <ul class="list-group">
      <li class="list-group-item">
       <strong>Versão 1.1:</strong>
       <ul class="mt-2 mb-0">
        <li>Cadastro de processos sem necessidade de preencher todos os campos.</li>
        <li>Opção para cadastrar processos já como "Finalizado".</li>
        <li>Campo "Data do Recebimento da Denúncia" adicionado no formulário de processos.</li>
        <li>Nova funcionalidade: botão "Continuar Editando" após cadastrar.</li>
        <li>Dashboard separado por natureza processual.</li>
        <li>Nova página de relatórios com geração de PDF.</li>
       </ul>
      </li>
      <li class="list-group-item">
       <strong>Versão 1.0:</strong> Primeira versão com as funções necessárias para o sistema.
      </li>
     </ul>
    </div>
    <div class="modal-footer">
     <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
    </div>
   </div>
  </div>
 </div>


 <!-- Bootstrap -->
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>