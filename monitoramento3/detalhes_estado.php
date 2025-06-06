<?php
require_once "config.php";

if(!isset($_GET['uf'])) {
    die("UF não especificado!");
}

$uf = $_GET['uf'];
$nomeEstado = getNomeEstado($uf);

$stmt = $pdo->prepare("SELECT * FROM links WHERE uf = :uf");
$stmt->bindParam(':uf', $uf, PDO::PARAM_STR);
$stmt->execute();
$links = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(count($links) === 0) {
    die("Nenhum link encontrado para o estado: " . htmlspecialchars($uf));
}

$linkIds = array_column($links, 'id');
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Links do Estado - <?= $nomeEstado ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/modern-style.css">
    <style>
        .titulo-estado {
            text-align: center;
            margin-bottom: var(--space-8);
            color: var(--text-primary);
            font-size: var(--text-3xl);
            font-weight: 700;
        }

        .links-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: var(--space-6);
        }

        .link-card {
            background: var(--surface);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-base);
            border: 2px solid var(--border);
            padding: var(--space-6);
            transition: all var(--transition-base);
            position: relative;
            overflow: hidden;
        }

        .link-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-500);
            transition: all var(--transition-base);
        }

        .link-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-300);
        }

        .link-card.online::before {
            background: linear-gradient(90deg, var(--success-500), var(--success-400));
        }

        .link-card.offline::before {
            background: linear-gradient(90deg, var(--error-500), var(--error-400));
            animation: pulse-error 1.2s infinite;
        }

        .link-card .nome {
            font-size: var(--text-xl);
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: var(--space-4);
            line-height: 1.3;
        }

        .link-card .detalhe {
            display: flex;
            align-items: center;
            margin-bottom: var(--space-3);
            font-size: var(--text-sm);
        }

        .link-card .detalhe .rotulo {
            font-weight: 600;
            color: var(--text-secondary);
            width: 80px;
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .link-card .detalhe .valor {
            flex: 1;
            color: var(--text-primary);
            font-weight: 500;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-2) var(--space-3);
            border-radius: var(--radius-full);
            font-weight: 600;
            font-size: var(--text-xs);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status.online {
            background: var(--success-100);
            color: var(--success-700);
            border: 1px solid var(--success-200);
        }

        .status.offline {
            background: var(--error-100);
            color: var(--error-700);
            border: 1px solid var(--error-200);
            animation: pulse-error 1.5s infinite;
        }

        .status-icon {
            font-size: 8px;
        }

        .voltar-btn {
            background: var(--primary-500);
            color: white;
            border: none;
            padding: var(--space-3) var(--space-4);
            border-radius: var(--radius-md);
            cursor: pointer;
            font-size: var(--text-sm);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: var(--space-2);
            transition: all var(--transition-base);
            margin-right: var(--space-4);
        }

        .voltar-btn:hover {
            background: var(--primary-600);
            transform: translateY(-1px);
        }

        .updating {
            filter: brightness(0.9);
            transform: scale(0.98);
        }
    </style>
</head>
<body>
    <div class="header">
        <button class="voltar-btn" onclick="window.history.back();">
            <i class="fas fa-arrow-left"></i> Voltar
        </button>
        <h1>Links do Estado: <?= htmlspecialchars($nomeEstado) ?></h1>
    </div>

    <div class="conteudo">
        <h2 class="titulo-estado">Detalhes dos Links - <?= htmlspecialchars($uf) ?></h2>
        <div class="links-container" id="links-container">
            <?php foreach ($links as $link): ?>
                <div class="link-card" data-id="<?= $link['id'] ?>">
                    <div class="nome"><?= htmlspecialchars($link['nome']) ?></div>
                    <div class="detalhe">
                        <span class="rotulo">
                            <i class="fas fa-network-wired"></i>
                            IP:
                        </span>
                        <span class="valor"><?= htmlspecialchars($link['ip']) ?></span>
                    </div>
                    <div class="detalhe">
                        <span class="rotulo">
                            <i class="fas fa-map-marker-alt"></i>
                            Local:
                        </span>
                        <span class="valor"><?= htmlspecialchars($link['endereco']) ?></span>
                    </div>
                    <div class="detalhe">
                        <span class="rotulo">
                            <i class="fas fa-user"></i>
                            Contato:
                        </span>
                        <span class="valor"><?= htmlspecialchars($link['contato']) ?></span>
                    </div>
                    <div class="detalhe">
                        <span class="rotulo">
                            <i class="fas fa-signal"></i>
                            Status:
                        </span>
                        <span class="valor">
                            <span class="status <?= $link['status'] ?>" id="status-<?= $link['id'] ?>">
                                <i class="fas fa-circle status-icon"></i> 
                                <?= ucfirst($link['status']) ?>
                            </span>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="rodape">
        <span>Spacecom Monitoramento S/A © 2025</span>
        <span class="atualizacao-contador" id="contador">Atualizando em: 5s</span>
    </div>

    <script>
        let tempoRestante = 5;
        let intervaloAtualizacao;
        let verificacaoEmAndamento = false;
        const linkIds = <?= json_encode($linkIds) ?>;

        async function verificarStatus() {
            if(verificacaoEmAndamento) return;
            verificacaoEmAndamento = true;
            
            try {
                linkIds.forEach(id => {
                    const card = document.querySelector(`.link-card[data-id="${id}"]`);
                    if(card) card.classList.add('updating');
                });
                
                const response = await fetch('api/status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ ids: linkIds })
                });
                
                const linksStatus = await response.json();
                
                linksStatus.forEach(link => {
                    const statusElement = document.getElementById(`status-${link.id}`);
                    const card = document.querySelector(`.link-card[data-id="${link.id}"]`);
                    
                    if(statusElement && card) {
                        // Atualizar o texto do status
                        statusElement.innerHTML = `<i class="fas fa-circle status-icon"></i> ${link.status.charAt(0).toUpperCase() + link.status.slice(1)}`;
                        
                        // Atualizar a classe do elemento de status
                        statusElement.className = `status ${link.status}`;
                        
                        // Atualizar a classe do card
                        card.classList.remove('online', 'offline');
                        card.classList.add(link.status);
                    }
                });
                
            } catch(error) {
                console.error('Erro na verificação de status:', error);
            } finally {
                verificacaoEmAndamento = false;
                
                // Remover classe de atualização
                linkIds.forEach(id => {
                    const card = document.querySelector(`.link-card[data-id="${id}"]`);
                    if(card) card.classList.remove('updating');
                });
            }
        }

        function iniciarCicloAtualizacao() {
            intervaloAtualizacao = setInterval(() => {
                tempoRestante--;
                document.getElementById('contador').textContent = `Atualizando em: ${tempoRestante}s`;
                
                if(tempoRestante <= 0) {
                    tempoRestante = 5;
                    verificarStatus();
                }
            }, 1000);
        }

        document.addEventListener('DOMContentLoaded', async () => {
            await verificarStatus();
            iniciarCicloAtualizacao();
            setInterval(verificarStatus, 5000);
        });
    </script>
</body>
</html>

<?php
function getNomeEstado($uf) {
    $estados = [
        'AC' => 'Acre',
        'AL' => 'Alagoas',
        'AP' => 'Amapá',
        'AM' => 'Amazonas',
        'BA' => 'Bahia',
        'CE' => 'Ceará',
        'DF' => 'Distrito Federal',
        'ES' => 'Espírito Santo',
        'GO' => 'Goiás',
        'MA' => 'Maranhão',
        'MT' => 'Mato Grosso',
        'MS' => 'Mato Grosso do Sul',
        'MG' => 'Minas Gerais',
        'PA' => 'Pará',
        'PB' => 'Paraíba',
        'PR' => 'Paraná',
        'PE' => 'Pernambuco',
        'PI' => 'Piauí',
        'RJ' => 'Rio de Janeiro',
        'RN' => 'Rio Grande do Norte',
        'RS' => 'Rio Grande do Sul',
        'RO' => 'Rondônia',
        'RR' => 'Roraima',
        'SC' => 'Santa Catarina',
        'SP' => 'São Paulo',
        'SPC'=> 'Spacecom',
        'SE' => 'Sergipe',
        'TO' => 'Tocantins'
    ];
    return $estados[strtoupper($uf)] ?? 'Estado Desconhecido';
}
?>