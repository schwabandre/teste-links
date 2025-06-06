<?php
require_once "config.php";
$links = $pdo->query("SELECT * FROM links")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Links - Spacecom</title>
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
        <h1>Lista de Links</h1>
    </div>

    <div class="menu-lateral" id="menu">
        <nav>
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <a href="cadastrar.php"><i class="fas fa-plus-circle"></i> Cadastrar Link</a>
            <a href="list_links.php" class="active"><i class="fas fa-chart-line"></i> Lista de Links</a>
            <a href="teste_ping.php"><i class="fas fa-network-wired"></i> Teste de Ping</a>
            <a href="mapa.php"><i class="fas fa-map-marked-alt"></i> Mapa da Rede</a>
            <a href="historico.php"><i class="fas fa-history"></i> Histórico</a>
        </nav>
    </div>

    <div class="conteudo">
        <div class="card">
            <div class="card-header">
                <h2>Status dos Links</h2>
            </div>
            <div class="card-body">
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>IP</th>
                                <th>Endereço</th>
                                <th>UF</th>
                                <th>Contato</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($links as $link): ?>
                            <tr data-id="<?= $link['id'] ?>">
                                <td>
                                    <div class="view-mode"><?= htmlspecialchars($link['nome']) ?></div>
                                    <input class="edit-form input-field" name="nome" value="<?= htmlspecialchars($link['nome']) ?>" style="display: none;">
                                </td>
                                <td>
                                    <div class="view-mode"><?= htmlspecialchars($link['ip']) ?></div>
                                    <input class="edit-form input-field" name="ip" value="<?= htmlspecialchars($link['ip']) ?>" style="display: none;">
                                </td>
                                <td>
                                    <div class="view-mode"><?= htmlspecialchars($link['endereco']) ?></div>
                                    <input class="edit-form input-field" name="endereco" value="<?= htmlspecialchars($link['endereco']) ?>" style="display: none;">
                                </td>
                                <td>
                                    <div class="view-mode"><?= htmlspecialchars($link['uf']) ?></div>
                                    <input class="edit-form input-field uf-input" name="uf" value="<?= htmlspecialchars($link['uf']) ?>" maxlength="3" style="display: none;">
                                </td>
                                <td>
                                    <div class="view-mode"><?= htmlspecialchars($link['contato']) ?></div>
                                    <input class="edit-form input-field" name="contato" value="<?= htmlspecialchars($link['contato']) ?>" style="display: none;">
                                </td>
                                <td>
                                    <span class="status-indicator" data-ip="<?= $link['ip'] ?>"></span>
                                    <span class="status-text">Verificando...</span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 8px;">
                                        <button class="button edit-btn" style="padding: 6px 12px; font-size: 12px;">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="button success save-btn" style="display: none; padding: 6px 12px; font-size: 12px;">
                                            <i class="fas fa-save"></i>
                                        </button>
                                        <button class="button danger delete-btn" style="padding: 6px 12px; font-size: 12px;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="rodape">
        <span>Spacecom Monitoramento S/A © 2025</span>
    </div>

    <script>
        // Controle do Menu
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

        // Verificação de Status
        document.querySelectorAll('.status-indicator').forEach(indicator => {
            const checkStatus = async () => {
                try {
                    const response = await fetch(`ping.php?ip=${encodeURIComponent(indicator.dataset.ip)}`);
                    const data = await response.json();
                    
                    const row = indicator.closest('tr');
                    row.classList.remove('online', 'offline');
                    row.classList.add(data.status);
                    
                    indicator.classList.remove('online', 'offline');
                    indicator.classList.add(data.status);
                    
                    row.querySelector('.status-text').textContent = 
                        data.status.charAt(0).toUpperCase() + data.status.slice(1);
                } catch (error) {
                    console.error('Erro na verificação:', error);
                }
            };
            
            checkStatus();
            setInterval(checkStatus, 15000);
        });

        // Controle de Edição
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const row = btn.closest('tr');
                row.classList.add('editing');
                
                // Mostrar campos de edição
                row.querySelectorAll('.view-mode').forEach(el => el.style.display = 'none');
                row.querySelectorAll('.edit-form').forEach(el => el.style.display = 'block');
                
                btn.style.display = 'none';
                row.querySelector('.save-btn').style.display = 'inline-flex';
            });
        });

        // Controle de Salvamento
        document.querySelectorAll('.save-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const row = btn.closest('tr');
                const inputs = row.querySelectorAll('.edit-form');
                const data = {
                    id: row.dataset.id,
                    nome: inputs[0].value,
                    ip: inputs[1].value,
                    endereco: inputs[2].value,
                    uf: inputs[3].value,
                    contato: inputs[4].value
                };

                try {
                    const response = await fetch('editar_link.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify(data)
                    });
                    
                    const result = await response.json();
                    if (result.success) {
                        inputs.forEach((input, index) => {
                            input.previousElementSibling.textContent = input.value;
                        });
                        
                        // Voltar ao modo visualização
                        row.querySelectorAll('.view-mode').forEach(el => el.style.display = 'block');
                        row.querySelectorAll('.edit-form').forEach(el => el.style.display = 'none');
                        
                        row.classList.remove('editing');
                        btn.style.display = 'none';
                        row.querySelector('.edit-btn').style.display = 'inline-flex';
                    }
                } catch (error) {
                    console.error('Erro:', error);
                }
            });
        });

        // Controle de Exclusão
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (confirm('Tem certeza que deseja excluir este link permanentemente?')) {
                    const row = btn.closest('tr');
                    try {
                        const response = await fetch('excluir_link.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify({ id: row.dataset.id })
                        });
                        
                        if (response.ok) {
                            row.style.animation = 'slideOut 0.3s ease-out forwards';
                            setTimeout(() => row.remove(), 300);
                        }
                    } catch (error) {
                        console.error('Erro:', error);
                        alert('Erro ao excluir o link');
                    }
                }
            });
        });
    </script>
</body>
</html>