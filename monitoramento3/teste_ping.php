<?php
$resultado = "";
$classeStatus = "";
$ip = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ip = htmlspecialchars($_POST["ip"]);
    $ping = shell_exec("ping -c 4 " . escapeshellarg($ip));
    $resultado = (strpos($ping, "0 received") === false) ? "ONLINE" : "OFFLINE";
    $classeStatus = ($resultado == "ONLINE") ? "success" : "error";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Ping - Spacecom</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/modern-style.css">
</head>
<body>
    <div class="header">
        <button class="botao-menu" id="botao-menu">
            <span class="botao-menu__linha"></span>
            <span class="botao-menu__linha"></span>
            <span class="botao-menu__linha"></span>
        </button>
        <h1>Teste de Ping</h1>
    </div>

    <div class="menu-lateral" id="menu">
        <nav>
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <a href="cadastrar.php"><i class="fas fa-plus-circle"></i> Cadastrar Link</a>
            <a href="list_links.php"><i class="fas fa-chart-line"></i> Lista de Links</a>
            <a href="teste_ping.php" class="active"><i class="fas fa-network-wired"></i> Teste de Ping</a>
            <a href="mapa.php"><i class="fas fa-map-marked-alt"></i> Mapa da Rede</a>
            <a href="historico.php"><i class="fas fa-history"></i> Histórico</a>
        </nav>
    </div>

    <div class="conteudo">
        <div class="card">
            <div class="card-body">
                <form method="POST" class="form-container">
                    <div class="form-group">
                        <label>Endereço IP para Teste</label>
                        <div class="input-with-icon">
                            <i class="fas fa-network-wired input-icon"></i>
                            <input class="input-field" type="text" name="ip" required 
                                placeholder="Ex: 192.168.1.1 ou dominio.com.br"
                                value="<?= htmlspecialchars($ip) ?>">
                        </div>
                    </div>

                    <?php if(!empty($resultado)): ?>
                    <div class="alert <?= $classeStatus ?>">
                        <i class="fas fa-<?= ($resultado == 'ONLINE') ? 'check-circle' : 'exclamation-circle' ?>"></i>
                        IP <?= htmlspecialchars($ip) ?> está <strong><?= $resultado ?></strong>
                    </div>
                    <?php endif; ?>

                    <div class="form-actions">
                        <button type="submit" class="button primary">
                            <i class="fas fa-satellite-dish"></i> Executar Teste
                        </button>
                        <a href="index.php" class="button secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="rodape">
        <span>Spacecom Monitoramento S/A © 2025</span>
    </div>

    <script>
        const menuBtn = document.getElementById('botao-menu');
        const menu = document.getElementById('menu');
        const body = document.body;

        menuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            menu.classList.toggle('aberto');
            body.classList.toggle('menu-aberto');
            menuBtn.classList.toggle('ativo');
        });

        document.addEventListener('click', (e) => {
            if (!menu.contains(e.target) && !menuBtn.contains(e.target)) {
                menu.classList.remove('aberto');
                body.classList.remove('menu-aberto');
                menuBtn.classList.remove('ativo');
            }
        });
    </script>
</body>
</html>