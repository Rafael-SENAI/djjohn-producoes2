<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DJ JOHN PRODUÇÕES - Eventos Profissionais</title>
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-slider">
            <div class="hero-slide active">
                <div class="hero-overlay"></div>
                <div class="hero-content">
                    <h1 class="hero-title">DJ JOHN PRODUÇÕES</h1>
                    <p class="hero-subtitle">Eventos Corporativos | Casamentos | Festas | Shows de Alto Nível</p>
                    <div class="hero-buttons">
                        <a href="orcamento.php" class="btn btn-primary">Solicitar Orçamento</a>
                        <a href="portfolio.php" class="btn btn-secondary">Ver Portfólio</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Serviços -->
    <section class="services">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Nossos Serviços</h2>
                <p class="section-subtitle">Soluções completas para todos os tipos de eventos</p>
            </div>
            
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h3>Eventos Corporativos</h3>
                    <p>Convenções, lançamentos de produtos, confraternizações e eventos empresariais com toda estrutura profissional.</p>
                    <a href="servicos.php#corporativos" class="service-link">Saiba mais <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Casamentos</h3>
                    <p>Do planejamento à execução, cuidamos de cada detalhe para tornar seu dia especial perfeito e memorável.</p>
                    <a href="servicos.php#casamentos" class="service-link">Saiba mais <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Festas de 15 Anos</h3>
                    <p>Celebramos essa data especial com elegância, diversão e toda a magia que esse momento merece.</p>
                    <a href="servicos.php#15anos" class="service-link">Saiba mais <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-music"></i>
                    </div>
                    <h3>Shows e Atrações</h3>
                    <p>Artistas renomados, bandas, DJs e atrações exclusivas para tornar seu evento único e inesquecível.</p>
                    <a href="servicos.php#shows" class="service-link">Saiba mais <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Números -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Eventos Realizados</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">15+</div>
                    <div class="stat-label">Anos de Experiência</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">98%</div>
                    <div class="stat-label">Clientes Satisfeitos</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">50+</div>
                    <div class="stat-label">Atrações Exclusivas</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>Pronto para criar um evento memorável?</h2>
                <p>Entre em contato conosco e transforme sua ideia em realidade</p>
                <a href="orcamento.php" class="btn btn-light">Solicitar Orçamento Gratuito</a>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>
