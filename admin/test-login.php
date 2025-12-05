<?php
require_once '../config/config.php';

echo "<h2>Teste de Login - DJ JOHN</h2>";

try {
    $db = getDB();
    echo "✅ Conexão com banco OK<br><br>";
    
    // Verificar se tabela existe
    $tables = $db->query("SHOW TABLES LIKE 'usuarios'")->fetchAll();
    if (empty($tables)) {
        echo "❌ ERRO: Tabela 'usuarios' não existe!<br>";
        echo "Tabelas disponíveis:<br>";
        $allTables = $db->query("SHOW TABLES")->fetchAll();
        foreach ($allTables as $t) {
            echo "- " . implode(', ', $t) . "<br>";
        }
        exit;
    }
    echo "✅ Tabela 'usuarios' existe<br><br>";
    
    // Listar usuários
    echo "<strong>Usuários no banco:</strong><br>";
    $users = $db->query("SELECT id, nome, email, funcao, status FROM usuarios")->fetchAll();
    
    if (empty($users)) {
        echo "❌ NENHUM USUÁRIO ENCONTRADO!<br>";
        echo "<br>Criando usuário admin...<br>";
        
        $senha = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO usuarios (nome, email, senha, funcao, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['DJ John Admin', 'admin@djjohn.com', $senha, 'admin', 'ativo']);
        
        echo "✅ Usuário criado!<br>";
        $users = $db->query("SELECT id, nome, email, funcao, status FROM usuarios")->fetchAll();
    }
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Função</th><th>Status</th></tr>";
    foreach ($users as $u) {
        echo "<tr>";
        echo "<td>{$u['id']}</td>";
        echo "<td>{$u['nome']}</td>";
        echo "<td>{$u['email']}</td>";
        echo "<td>{$u['funcao']}</td>";
        echo "<td>{$u['status']}</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    // Testar login
    echo "<br><strong>Testando login com:</strong><br>";
    echo "Email: admin@djjohn.com<br>";
    echo "Senha: admin123<br><br>";
    
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute(['admin@djjohn.com']);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo "❌ Usuário não encontrado com este email<br>";
    } else {
        echo "✅ Usuário encontrado: {$user['nome']}<br>";
        echo "Status: {$user['status']}<br>";
        
        if ($user['status'] !== 'ativo') {
            echo "❌ Usuário não está ativo!<br>";
        } else {
            echo "✅ Usuário está ativo<br>";
        }
        
        if (password_verify('admin123', $user['senha'])) {
            echo "✅ SENHA CORRETA!<br>";
            echo "<br><h3 style='color: green;'>LOGIN FUNCIONARIA!</h3>";
        } else {
            echo "❌ SENHA INCORRETA!<br>";
            echo "<br>Hash no banco: {$user['senha']}<br>";
            echo "Hash correto seria: " . password_hash('admin123', PASSWORD_DEFAULT) . "<br>";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ ERRO: " . $e->getMessage();
}

echo "<br><br><a href='login.php'>Voltar para Login</a>";
?>
