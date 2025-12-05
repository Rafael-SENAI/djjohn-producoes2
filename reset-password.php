<?php
// Script para resetar senha do administrador
// Execute este arquivo UMA VEZ e depois DELETE

require_once 'config/config.php';

$db = getDB();

// Nova senha
$novaSenha = 'admin123';
$senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

try {
    // Atualizar senha do admin
    $stmt = $db->prepare("UPDATE usuarios SET senha = ? WHERE email = 'admin@djjohn.com'");
    $stmt->execute([$senhaHash]);
    
    echo '<!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Senha Resetada</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .container {
                background: white;
                border-radius: 20px;
                padding: 50px;
                text-align: center;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                max-width: 500px;
            }
            .success-icon {
                width: 80px;
                height: 80px;
                background: #28a745;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 30px;
                color: white;
                font-size: 40px;
            }
            h1 {
                color: #333;
                margin-bottom: 20px;
                font-size: 32px;
            }
            .info-box {
                background: #f8f9fa;
                padding: 25px;
                border-radius: 12px;
                margin: 30px 0;
                text-align: left;
            }
            .info-box p {
                margin: 10px 0;
                color: #666;
            }
            .info-box strong {
                color: #333;
            }
            .warning {
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 15px;
                border-radius: 8px;
                margin: 20px 0;
                color: #856404;
            }
            .btn {
                display: inline-block;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 15px 40px;
                border-radius: 10px;
                text-decoration: none;
                font-weight: 600;
                margin-top: 20px;
                transition: all 0.3s;
            }
            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="success-icon">✓</div>
            <h1>Senha Resetada com Sucesso!</h1>
            <p style="color: #666; margin-bottom: 30px;">Agora você pode fazer login com as credenciais abaixo</p>
            
            <div class="info-box">
                <p><strong>📧 Email:</strong></p>
                <p style="font-size: 18px; color: #667eea;">admin@djjohn.com</p>
                
                <p style="margin-top: 20px;"><strong>🔑 Senha:</strong></p>
                <p style="font-size: 18px; color: #667eea;">admin123</p>
            </div>
            
            <div class="warning">
                <strong>⚠️ IMPORTANTE:</strong><br>
                DELETE este arquivo (reset-password.php) por segurança!
            </div>
            
            <a href="admin/login.php" class="btn">Fazer Login Agora</a>
        </div>
    </body>
    </html>';
    
} catch (PDOException $e) {
    echo '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Erro</title>
        <style>
            body { font-family: Arial; background: #f5f5f5; padding: 50px; }
            .error { background: #f8d7da; color: #721c24; padding: 30px; border-radius: 10px; max-width: 600px; margin: 0 auto; }
        </style>
    </head>
    <body>
        <div class="error">
            <h2>❌ Erro ao resetar senha</h2>
            <p>' . $e->getMessage() . '</p>
            <p><strong>Verifique se:</strong></p>
            <ul>
                <li>O MySQL está rodando</li>
                <li>O banco de dados foi criado</li>
                <li>As tabelas foram importadas</li>
                <li>As configurações em config/config.php estão corretas</li>
            </ul>
        </div>
    </body>
    </html>';
}
?>
