<?php
// IMPORTANTE: Configurar sessão ANTES de iniciar
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.use_only_cookies', 1);
ini_set('session.gc_maxlifetime', 7200);

session_start();
require_once '../config/config.php';

// Se já estiver logado, redirecionar
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    try {
        $db = getDB();
        
        // TENTAR PRIMEIRO COM 'usuarios' (português)
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ? AND status = 'ativo'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        // SE NÃO ENCONTRAR, TENTAR COM 'users' (inglês)
        if (!$user) {
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            // Adaptar campos em inglês para português
            if ($user) {
                $user['nome'] = $user['name'] ?? '';
                $user['senha'] = $user['password'] ?? '';
                $user['funcao'] = $user['role'] ?? '';
            }
        }
        
        if ($user && password_verify($password, $user['senha'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nome'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['funcao'];
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Email ou senha incorretos';
        }
    } catch (PDOException $e) {
        $error = 'Erro ao processar login: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DJ JOHN PRODUÇÕES</title>
    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-left">
                <div class="login-branding">
                    <div class="brand-logo">
                        <i class="fas fa-music" style="margin-right: 15px;"></i>
                        DJ JOHN
                    </div>
                    <div class="brand-tagline">PRODUÇÕES DE EVENTOS</div>
                </div>

                <div class="login-features">
                    <div class="feature">
                        <i class="fas fa-shield-alt"></i>
                        <span>Sistema Seguro</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-chart-line"></i>
                        <span>Gestão Completa</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-users"></i>
                        <span>Controle Total</span>
                    </div>
                </div>
            </div>

            <div class="login-right">
                <div class="login-box">
                    <div class="login-header">
                        <h2>Bem-vindo de volta!</h2>
                        <p>Faça login para acessar o painel</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label>
                                <i class="fas fa-envelope"></i>
                                Email
                            </label>
                            <input type="email" name="email" class="form-control" required 
                                   placeholder="seu@email.com" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-lock"></i>
                                Senha
                            </label>
                            <input type="password" name="password" class="form-control" required 
                                   placeholder="••••••••">
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-sign-in-alt"></i> Entrar
                        </button>
                    </form>

                    <div style="margin-top: 30px; text-align: center; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
                        <a href="../index.php" style="color: #FF0040; text-decoration: none; font-size: 13px; margin-top: 15px; display: inline-block;">
                            <i class="fas fa-arrow-left"></i> Voltar ao site
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
