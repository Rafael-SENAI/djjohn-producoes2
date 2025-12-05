<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin'; ?> - Eventos Premium</title>
    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/select-dark-theme.css">
    <link rel="stylesheet" href="../assets/css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../assets/js/notifications.js"></script>
    <script src="../assets/js/confirm-delete.js" defer></script>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-logo">
                    DJ JOHN PRODUÇÕES
                </a>
            </div>

            <div class="sidebar-user">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?>
                </div>
                <div class="user-info">
                    <h4><?php echo htmlspecialchars($_SESSION['user_name']); ?></h4>
                    <p><?php echo $_SESSION['user_role'] === 'admin' ? 'Administrador' : 'Funcionário'; ?></p>
                </div>
            </div>

            <nav class="sidebar-menu">
                <a href="dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>

                <?php if (isAdmin()): ?>
                    <div class="menu-label">Gestão</div>
                    
                    <a href="events.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'events.php' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Eventos</span>
                    </a>

                    <a href="attractions.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'attractions.php' ? 'active' : ''; ?>">
                        <i class="fas fa-star"></i>
                        <span>Atrações</span>
                    </a>

                    <a href="venues.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'venues.php' ? 'active' : ''; ?>">
                        <i class="fas fa-building"></i>
                        <span>Salões</span>
                    </a>

                    <a href="gallery.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'gallery.php' ? 'active' : ''; ?>">
                        <i class="fas fa-images"></i>
                        <span>Galeria</span>
                    </a>

                    <a href="quotes.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'quotes.php' ? 'active' : ''; ?>">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Orçamentos</span>
                    </a>

                    <a href="users.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span>Usuários</span>
                    </a>

                    <a href="materials.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'materials.php' ? 'active' : ''; ?>">
                        <i class="fas fa-boxes"></i>
                        <span>Materiais</span>
                    </a>
                <?php endif; ?>

                <div class="menu-label">Sistema</div>

                <a href="calendar.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'calendar.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar"></i>
                    <span>Calendário</span>
                </a>

                <a href="tasks.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'tasks.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tasks"></i>
                    <span>Tarefas</span>
                </a>

                <a href="announcements.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'announcements.php' ? 'active' : ''; ?>">
                    <i class="fas fa-bullhorn"></i>
                    <span>Comunicados</span>
                </a>

                <div class="menu-label">Conta</div>

                <a href="profile.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-circle"></i>
                    <span>Meu Perfil</span>
                </a>

                <a href="../index.php" class="menu-item">
                    <i class="fas fa-globe"></i>
                    <span>Ver Site</span>
                </a>

                <a href="logout.php" class="menu-item" style="color: #FF0040;">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Sair</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <div class="header-title">
                    <button class="icon-btn" id="sidebarToggle" style="margin-right: 15px;">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
                </div>

                <div class="header-actions">
                    <button class="icon-btn" title="Notificações">
                        <i class="fas fa-bell"></i>
                        <span class="badge">3</span>
                    </button>

                    <button class="icon-btn" title="Mensagens">
                        <i class="fas fa-envelope"></i>
                    </button>

                    <a href="profile.php" class="icon-btn" title="Perfil">
                        <i class="fas fa-user"></i>
                    </a>
                </div>
            </header>

<!-- DESABILITAR CONFIRMAÇÕES NATIVAS DO NAVEGADOR -->
<script>
// Sobrescrever confirm() para sempre retornar true
// Assim remove o popup do Google Chrome/Firefox/etc
window.confirm = function(message) {
    console.log('Confirmação automática:', message);
    return true; // Sempre confirma
};

// Opcional: Desabilitar alert() nativo também
window.alert = function(message) {
    console.log('Alert bloqueado:', message);
    // Não faz nada, suas notificações customizadas vão aparecer normalmente
};

// Opcional: Desabilitar prompt() nativo
window.prompt = function(message, defaultValue) {
    console.log('Prompt bloqueado:', message);
    return defaultValue || null;
};

console.log('✓ Confirmações nativas do navegador desabilitadas!');
</script>