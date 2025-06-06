<?php
require_once "config.php";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa de Rede - Spacecom</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="assets/css/modern-style.css">
    <style>
        #map { 
            height: calc(100vh - 72px);
            width: 100%;
        }

        .offline-marker {
            animation: pulse-error 1.2s infinite;
        }

        .leaflet-marker-icon {
            background: transparent;
            border: none;
        }

        .leaflet-control-attribution {
            background: rgba(255,255,255,0.9) !important;
            border-radius: var(--radius-md) !important;
            padding: var(--space-2) var(--space-3) !important;
            font-family: var(--font-family-sans) !important;
            font-size: var(--text-xs) !important;
        }

        .leaflet-popup-content-wrapper {
            border-radius: var(--radius-lg) !important;
            box-shadow: var(--shadow-lg) !important;
            border: 1px solid var(--border) !important;
        }

        .leaflet-popup-content {
            font-family: var(--font-family-sans) !important;
            font-size: var(--text-sm) !important;
            line-height: 1.5 !important;
        }

        .leaflet-popup-content h3 {
            margin: 0 0 var(--space-2) 0 !important;
            color: var(--text-primary) !important;
            font-weight: 600 !important;
        }

        .leaflet-popup-content .status {
            display: inline-flex;
            align-items: center;
            gap: var(--space-1);
            padding: var(--space-1) var(--space-2);
            border-radius: var(--radius-full);
            font-size: var(--text-xs);
            font-weight: 600;
            text-transform: uppercase;
        }

        .leaflet-popup-content .status.online {
            background: var(--success-100);
            color: var(--success-700);
        }

        .leaflet-popup-content .status.offline {
            background: var(--error-100);
            color: var(--error-700);
        }
    </style>
</head>

<body>
    <div class="header">
        <button class="botao-menu" id="botao-menu">
            <span class="botao-menu__linha"></span>
            <span class="botao-menu__linha"></span>
            <span class="botao-menu__linha"></span>
        </button>
        <h1>Mapa de Rede</h1>
    </div>

    <div class="menu-lateral" id="menu">
        <nav>
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <a href="cadastrar.php"><i class="fas fa-plus-circle"></i> Cadastrar Link</a>
            <a href="list_links.php"><i class="fas fa-chart-line"></i> Lista de Links</a>
            <a href="teste_ping.php"><i class="fas fa-network-wired"></i> Teste de Ping</a>
            <a href="mapa.php" class="active"><i class="fas fa-map-marked-alt"></i> Mapa da Rede</a>
            <a href="historico.php"><i class="fas fa-history"></i> Histórico</a>
        </nav>
    </div>

    <div class="conteudo">
        <div id="map"></div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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

        // Centralização do Brasil
        const map = L.map('map').setView([-14.2350, -51.9253], 4.5);
        
        // Camada de mapa
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Ícones personalizados
        const onlineIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34]
        });

        const offlineIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34]
        });

        // Gerenciamento de Marcadores
        let markers = new Map();

        async function updateNetwork() {
            try {
                const response = await fetch('api/status.php');
                const links = await response.json();

                // Limpar marcadores antigos
                markers.forEach(marker => map.removeLayer(marker));
                markers.clear();

                // Adicionar novos marcadores
                links.forEach(link => {
                    const marker = L.marker([link.lat, link.lon], {
                        icon: link.status === 'online' ? onlineIcon : offlineIcon,
                        className: link.status === 'online' ? '' : 'offline-marker'
                    }).bindPopup(`
                        <div style="min-width: 200px;">
                            <h3>${link.nome}</h3>
                            <p style="margin: 4px 0; color: #666; font-size: 13px;">
                                <i class="fas fa-network-wired" style="margin-right: 6px;"></i>
                                ${link.ip}
                            </p>
                            <p style="margin: 4px 0; color: #666; font-size: 13px;">
                                <i class="fas fa-map-marker-alt" style="margin-right: 6px;"></i>
                                ${link.cidade}, ${link.uf}
                            </p>
                            <div style="margin-top: 8px;">
                                <span class="status ${link.status}">
                                    <i class="fas fa-circle" style="font-size: 8px;"></i>
                                    ${link.status.toUpperCase()}
                                </span>
                            </div>
                        </div>
                    `).addTo(map);
                    
                    markers.set(link.id, marker);
                });

                // Ajuste de zoom para melhor visualização
                if(links.length > 0) {
                    const bounds = L.latLngBounds(links.map(link => [link.lat, link.lon]));
                    map.fitBounds(bounds, {padding: [50, 50]});
                }

            } catch (error) {
                console.error('Erro na atualização:', error);
            }
        }

        // Atualizar a cada 5 segundos
        setInterval(updateNetwork, 5000);
        updateNetwork();
    </script>
</body>
</html>