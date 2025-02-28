<!--/controle_acervo/views/anpp.php-->

<?php
session_start();
require_once "../config/conexao.php";
global $pdo;

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$perfil = $_SESSION['usuario_perfil'] ?? '';

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro ANPP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h2 class="text-center">Acordo de Não Persecução Penal (ANPP)</h2>
        <div class="card shadow-sm p-4">
            <form action="../controllers/cadastrar_anpp.php" method="POST">
                
                <div class="mb-3">
                    <label for="num_ip" class="form-label">N° IP</label>
                    <input type="text" class="form-control" id="num_ip" name="num_ip" required>
                </div>
                
                <div class="mb-3">
                    <label for="crime" class="form-label">Crime</label>
                    <input type="text" class="form-control" id="crime" name="crime" required>
                </div>
                
                <div class="mb-3">
                    <label for="indiciado" class="form-label">Indiciado</label>
                    <input type="text" class="form-control" id="indiciado" name="indiciado" required>
                </div>
                
                <div class="mb-3">
                    <label for="vitima" class="form-label">Vítima</label>
                    <input type="text" class="form-control" id="vitima" name="vitima">
                    <input type="checkbox" id="sem_vitima" onclick="toggleVitima()"> Não há vítima
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Acordo</label><br>
                    <input type="radio" id="realizado" name="acordo" value="Realizado" required> <label for="realizado">Realizado</label>
                    <input type="radio" id="nao_realizado" name="acordo" value="Não Realizado"> <label for="nao_realizado">Não Realizado</label>
                </div>
                
                <div class="mb-3">
                    <label for="data_acordo" class="form-label">Data do Acordo</label>
                    <input type="date" class="form-control" id="data_acordo" name="data_acordo">
                </div>
                
                <div class="mb-3">
                    <label for="reparacao_vitima" class="form-label">Reparação da Vítima</label>
                    <input type="text" class="form-control" id="reparacao_vitima" name="reparacao_vitima">
                </div>
                
                <div class="mb-3">
                    <label for="valor_reparacao" class="form-label">Valor</label>
                    <input type="number" class="form-control" id="valor_reparacao" name="valor_reparacao" step="0.01">
                </div>
                
                <div class="mb-3">
                    <label for="restituicao" class="form-label">Restituição da Coisa à Vítima</label>
                    <input type="text" class="form-control" id="restituicao" name="restituicao">
                </div>
                
                <div class="mb-3">
                    <label for="prestacao_servico" class="form-label">Prestação de Serviço Comunitário</label>
                    <input type="text" class="form-control" id="prestacao_servico" name="prestacao_servico">
                </div>
                
                <div class="mb-3">
                    <label for="tempo_prestacao" class="form-label">Tempo</label>
                    <input type="text" class="form-control" id="tempo_prestacao" name="tempo_prestacao">
                </div>
                
                <div class="mb-3">
                    <label for="prestacao_pecuniaria" class="form-label">Prestação Pecuniária</label>
                    <input type="text" class="form-control" id="prestacao_pecuniaria" name="prestacao_pecuniaria">
                </div>
                
                <div class="mb-3">
                    <label for="valor_pecuniario" class="form-label">Valor</label>
                    <input type="number" class="form-control" id="valor_pecuniario" name="valor_pecuniario" step="0.01">
                </div>
                
                <div class="mb-3">
                    <label for="inicio_execucao" class="form-label">Início da Execução</label>
                    <input type="date" class="form-control" id="inicio_execucao" name="inicio_execucao">
                </div>
                
                <button type="submit" class="btn btn-success w-100">Cadastrar</button>
            </form>
        </div>
    </div>

    <script>
        function toggleVitima() {
            let vitimaInput = document.getElementById("vitima");
            let semVitima = document.getElementById("sem_vitima");
            vitimaInput.disabled = semVitima.checked;
            vitimaInput.value = semVitima.checked ? "Não há" : "";
        }
    </script>

</body>
</html>
