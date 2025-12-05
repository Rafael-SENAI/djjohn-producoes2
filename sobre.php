<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nós - Eventos Premium</title>
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .page-hero {
            position: relative;
            height: 50vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            margin-top: 80px;
            color: white;
            text-align: center;
        }
        .about-section { padding: 100px 0; }
        .about-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
        .about-text h2 { font-size: 42px; margin-bottom: 30px; }
        .about-text p { font-size: 18px; line-height: 1.8; margin-bottom: 20px; color: var(--text-secondary); }
        .mvv-section { padding: 80px 0; background: var(--bg-secondary); }
        .mvv-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 40px; }
        .mvv-card { background: white; padding: 40px; border-radius: 20px; box-shadow: var(--shadow-md); }
        .mvv-icon { width: 80px; height: 80px; margin: 0 auto 20px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; color: white; }
        .team-section { padding: 100px 0; }
        .team-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 30px; }
        .team-member { text-align: center; }
        .member-photo { width: 150px; height: 150px; margin: 0 auto 20px; border-radius: 50%; overflow: hidden; }
        .member-photo img { width: 100%; height: 100%; object-fit: cover; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="page-hero">
        <div class="container">
            <h1 style="font-size: 56px; margin-bottom: 20px;">Sobre Nós</h1>
            <p style="font-size: 24px; opacity: 0.9;">Mais de 15 anos transformando sonhos em realidade</p>
        </div>
    </section>

    <section class="about-section">
        <div class="container">
            <div class="about-grid">
                <div class="about-text">
                    <h2>Nossa História</h2>
                    <p>Fundada há mais de 15 anos, a <strong>Eventos Premium</strong> nasceu com a missão de criar experiências inesquecíveis. Nossa trajetória é marcada por momentos especiais e eventos que superaram expectativas.</p>
                    <p>Somos uma equipe multidisciplinar apaixonada por criar, planejar e executar eventos perfeitos. Cada projeto recebe nossa dedicação integral.</p>
                </div>
                <div>
                    <img src="https://images.unsplash.com/photo-1511578314322-379afb476865?w=600" style="width: 100%; border-radius: 20px; box-shadow: var(--shadow-xl);" alt="Equipe">
                </div>
            </div>
        </div>
    </section>

    <section class="mvv-section">
        <div class="container">
            <div class="mvv-grid">
                <div class="mvv-card">
                    <div class="mvv-icon"><i class="fas fa-bullseye"></i></div>
                    <h3 style="text-align: center; margin-bottom: 15px;">Missão</h3>
                    <p style="text-align: center; color: var(--text-secondary);">Criar experiências memoráveis através de eventos impecáveis, superando expectativas.</p>
                </div>
                <div class="mvv-card">
                    <div class="mvv-icon"><i class="fas fa-eye"></i></div>
                    <h3 style="text-align: center; margin-bottom: 15px;">Visão</h3>
                    <p style="text-align: center; color: var(--text-secondary);">Ser referência em qualidade e inovação no mercado de eventos.</p>
                </div>
                <div class="mvv-card">
                    <div class="mvv-icon"><i class="fas fa-heart"></i></div>
                    <h3 style="text-align: center; margin-bottom: 15px;">Valores</h3>
                    <p style="text-align: center; color: var(--text-secondary);">Excelência, compromisso, criatividade, ética e trabalho em equipe.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="team-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Nossa Equipe</h2>
            </div>
            <div class="team-grid">
                <div class="team-member">
                    <div class="member-photo"><img src="https://i.pravatar.cc/300?img=33" alt="Ana Paula"></div>
                    <h4>Ana Paula Silva</h4>
                    <p style="color: var(--primary-color); font-weight: 600;">CEO & Fundadora</p>
                </div>
                <div class="team-member">
                    <div class="member-photo"><img src="https://i.pravatar.cc/300?img=12" alt="Carlos"></div>
                    <h4>Carlos Mendes</h4>
                    <p style="color: var(--primary-color); font-weight: 600;">Diretor de Operações</p>
                </div>
                <div class="team-member">
                    <div class="member-photo"><img src="https://i.pravatar.cc/300?img=45" alt="Marina"></div>
                    <h4>Marina Costa</h4>
                    <p style="color: var(--primary-color); font-weight: 600;">Designer de Eventos</p>
                </div>
                <div class="team-member">
                    <div class="member-photo"><img src="https://i.pravatar.cc/300?img=51" alt="Roberto"></div>
                    <h4>Roberto Santos</h4>
                    <p style="color: var(--primary-color); font-weight: 600;">Coordenador</p>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
