<?php
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $nome = htmlspecialchars($_POST["nome"]);
        $ip = htmlspecialchars($_POST["ip"]);
        $endereco = htmlspecialchars($_POST["endereco"]);
        $cidade = htmlspecialchars($_POST["cidade"]);
        $uf = htmlspecialchars($_POST["uf"]);
        $contato = htmlspecialchars($_POST["contato"]);
        $lat = filter_input(INPUT_POST, 'lat', FILTER_VALIDATE_FLOAT);
        $lon = filter_input(INPUT_POST, 'lon', FILTER_VALIDATE_FLOAT);

        $sql = "INSERT INTO links (nome, ip, endereco, cidade, uf, contato, lat, lon) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $ip, $endereco, $cidade, $uf, $contato, $lat, $lon]);

        $success = "Link cadastrado com sucesso!";
    } catch (PDOException $e) {
        $error = "Erro ao cadastrar: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Novo Link - Spacecom</title>
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
        <h1>Cadastrar Novo Link</h1>
    </div>

    <div class="menu-lateral" id="menu">
        <nav>
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <a href="cadastrar.php" class="active"><i class="fas fa-plus-circle"></i> Cadastrar Link</a>
            <a href="list_links.php"><i class="fas fa-chart-line"></i> Lista de Links</a>
            <a href="teste_ping.php"><i class="fas fa-network-wired"></i> Teste de Ping</a>
            <a href="mapa.php"><i class="fas fa-map-marked-alt"></i> Mapa da Rede</a>
            <a href="historico.php"><i class="fas fa-history"></i> Histórico</a>
        </nav>
    </div>

    <div class="conteudo">
        <div class="card">
            <div class="card-body">
                <?php if(isset($success)): ?>
                    <div class="alert success">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($error)): ?>
                    <div class="alert error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="form-container">
                    <div class="form-group">
                        <label>Nome do Link</label>
                        <div class="input-with-icon">
                            <i class="fas fa-link input-icon"></i>
                            <input class="input-field" type="text" name="nome" required 
                                placeholder="Ex: Link Matriz-SP">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Endereço IP</label>
                            <div class="input-with-icon">
                                <i class="fas fa-network-wired input-icon"></i>
                                <input class="input-field" type="text" name="ip" required 
                                    placeholder="192.168.1.1">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Contato Responsável</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user input-icon"></i>
                                <input class="input-field" type="text" name="contato" required 
                                    placeholder="João Silva">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Endereço Físico</label>
                        <div class="input-with-icon">
                            <i class="fas fa-map-marker-alt input-icon"></i>
                            <input class="input-field" type="text" name="endereco" required 
                                placeholder="Rua Principal, 123">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Cidade</label>
                            <div class="input-with-icon">
                                <i class="fas fa-city input-icon"></i>
                                <input class="input-field" type="text" name="cidade" required 
                                    placeholder="São Paulo">
                            </div>
                        </div>

                        <div class="form-group" style="max-width: 120px">
                            <label>UF</label>
                            <input class="input-field" type="text" name="uf" maxlength="2" required 
                                placeholder="SP">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Latitude</label>
                            <div class="input-with-icon">
                                <i class="fas fa-globe-americas input-icon"></i>
                                <input class="input-field" type="number" step="any" name="lat" required 
                                    placeholder="-23.5506507">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Longitude</label>
                            <div class="input-with-icon">
                                <i class="fas fa-globe-americas input-icon"></i>
                                <input class="input-field" type="number" step="any" name="lon" required 
                                    placeholder="-46.6333824">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top: 1.5rem">
                        <button type="submit" class="button primary">
                            <i class="fas fa-save"></i> Cadastrar
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

        document.querySelectorAll('.input-field').forEach(input => {
            input.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        });
    </script>
</body>
</html>