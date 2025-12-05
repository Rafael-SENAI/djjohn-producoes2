<?php
require_once '../config/config.php';

echo "<h2>🔧 RESETAR SENHA DO ADMIN</h2>";

try {
    $db = getDB();
    
    // Criar senha nova
    $novaSenha = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Tentar atualizar na tabela 'users' (inglês)
    try {
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = 'admin@djjohn.com'");
        $stmt->execute([$novaSenha]);
        echo "✅ Senha atualizada na tabela 'users'<br>";
    } catch (PDOException $e) {
        echo "⚠️ Tabela 'users' não encontrada<br>";
    }
    
    // Tentar atualizar na tabela 'usuarios' (português)
    try {
        $stmt = $db->prepare("UPDATE usuarios SET senha = ? WHERE email = 'admin@djjohn.com'");
        $stmt->execute([$novaSenha]);
        echo "✅ Senha atualizada na tabela 'usuarios'<br>";
    } catch (PDOException $e) {
        echo "⚠️ Tabela 'usuarios' não encontrada<br>";
    }
    
    echo "<br><h3 style='color: green;'>✅ PRONTO! SENHA RESETADA!</h3>";
    echo "<br>Agora tenta logar com:<br>";
    echo "<strong>Email:</strong> admin@djjohn.com<br>";
    echo "<strong>Senha:</strong> admin123<br>";
    echo "<br><a href='login.php' style='background: #FF0040; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; display: inline-block;'>IR PARA LOGIN</a>";
    
} catch (PDOException $e) {
    echo "❌ ERRO: " . $e->getMessage();
}
?>
