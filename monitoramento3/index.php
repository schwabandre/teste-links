<?php
require_once "config.php";
$links = $pdo->query("SELECT * FROM links")->fetchAll(PDO::FETCH_ASSOC);

$estados = [];
foreach ($links as $link) {
    $uf = $link['uf'];
    $estados[$uf][] = $link;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoramento de Links Spacecom</title>
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
        <h1>Monitoramento de Links Spacecom</h1>
        <div class="status-container">
            <div class="status-item online">
                <i class="fas fa-link"></i>
                <span id="total-online">0</span>
            </div>
            <div class="status-item offline">
                <i class="fas fa-unlink"></i>
                <span id="total-offline">0</span>
            </div>
        </div>
    </div>

    <div class="menu-lateral" id="menu">
        <nav>
            <a href="index.php" class="active"><i class="fas fa-home"></i> Home</a>
            <a href="cadastrar.php"><i class="fas fa-plus-circle"></i> Cadastrar Link</a>
            <a href="list_links.php"><i class="fas fa-chart-line"></i> Lista de Links</a>
            <a href="teste_ping.php"><i class="fas fa-network-wired"></i> Teste de Ping</a>
            <a href="mapa.php"><i class="fas fa-map-marked-alt"></i> Mapa de Rede</a>
            <a href="historico.php"><i class="fas fa-history"></i> Histórico</a>
        </nav>
    </div>

    <div class="conteudo">
        <div class="cards-container">
            <?php foreach ($estados as $uf => $linksEstado): ?>
                <a href="detalhes_estado.php?uf=<?= urlencode($uf) ?>" class="estado-card" 
                   data-ips="<?= htmlspecialchars(json_encode(array_column($linksEstado, 'ip'))) ?>">
                    <div class="uf"><?= htmlspecialchars($uf) ?></div>
                    <div class="count"><?= count($linksEstado) ?> link(s)</div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="rodape">
        <span>Spacecom Monitoramento S/A © 2025</span>
        <span class="atualizacao-contador" id="contador">Atualizando em: 5s</span>
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

        // Sistema de Verificação de Status usando API centralizada
        let tempoRestante = 5;
        let intervaloAtualizacao;
        let verificacaoEmAndamento = false;
        
        // Mapeamento de estado para cards
        const estadoCards = {};
        document.querySelectorAll('.estado-card').forEach(card => {
            const uf = card.querySelector('.uf').textContent;
            estadoCards[uf] = card;
        });

        async function verificarStatus() {
            if(verificacaoEmAndamento) return;
            verificacaoEmAndamento = true;
            
            try {
                // Adicionar classe de atualização aos cards
                Object.values(estadoCards).forEach(card => {
                    card.classList.add('updating');
                });
                
                const response = await fetch('api/status.php');
                const links = await response.json();
                
                // Agrupar por estado e calcular status
                const statusPorEstado = {};
                let totalOnline = 0;
                let totalOffline = 0;
                
                links.forEach(link => {
                    const uf = link.uf;
                    if(!statusPorEstado[uf]) {
                        statusPorEstado[uf] = {
                            online: 0,
                            offline: 0,
                            links: []
                        };
                    }
                    
                    if(link.status === 'online') {
                        statusPorEstado[uf].online++;
                        totalOnline++;
                    } else {
                        statusPorEstado[uf].offline++;
                        totalOffline++;
                    }
                    
                    statusPorEstado[uf].links.push(link);
                });
                
                // Atualizar UI
                for(const uf in statusPorEstado) {
                    const card = estadoCards[uf];
                    if(card) {
                        const status = statusPorEstado[uf].offline > 0 ? 'offline' : 'online';
                        
                        // Atualizar classes
                        card.classList.remove('online', 'offline', 'updating');
                        card.classList.add(status);
                        
                        // Atualizar contador
                        const countElement = card.querySelector('.count');
                        if(countElement) {
                            const total = statusPorEstado[uf].online + statusPorEstado[uf].offline;
                            const offlineText = statusPorEstado[uf].offline > 0 ? 
                                ` (<span style="color:#ff6b6b">${statusPorEstado[uf].offline} off</span>)` : '';
                            countElement.innerHTML = `${total} link(s)${offlineText}`;
                        }
                    }
                }
                
                // Atualizar contadores globais
                document.getElementById('total-online').textContent = totalOnline;
                document.getElementById('total-offline').textContent = totalOffline;
                
                // Atualizar classes dos status items
                const onlineItem = document.querySelector('.status-item.online');
                const offlineItem = document.querySelector('.status-item.offline');
                
                onlineItem.classList.remove('offline');
                onlineItem.classList.add('online');
                
                if(totalOffline > 0) {
                    offlineItem.classList.add('offline');
                } else {
                    offlineItem.classList.remove('offline');
                }
                
            } catch(error) {
                console.error('Erro na verificação de status:', error);
            } finally {
                verificacaoEmAndamento = false;
                
                // Remover classe de atualização
                Object.values(estadoCards).forEach(card => {
                    card.classList.remove('updating');
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

        // Inicialização
        document.addEventListener('DOMContentLoaded', async () => {
            await verificarStatus();
            iniciarCicloAtualizacao();
            setInterval(verificarStatus, 5000); // Atualizar a cada 5 segundos
        });

        // Adicionar efeito de hover com mouse tracking
        document.querySelectorAll('.estado-card').forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                const rotateX = (y - centerY) / 10;
                const rotateY = (centerX - x) / 10;
                
                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-8px) scale(1.05)`;
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
            });
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