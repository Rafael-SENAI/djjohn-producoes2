<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Diagnóstico - Portfólio DJ JOHN</h1>";
echo "<style>body{font-family:Arial;background:#111;color:white;padding:40px} h2{color:#FF0040;margin-top:30px} pre{background:#222;padding:15px;border-radius:8px;overflow:auto} .ok{color:#0f0} .error{color:#f00} table{width:100%;border-collapse:collapse;margin:20px 0} td,th{padding:10px;border:1px solid #333;text-align:left} th{background:#222}</style>";

// 1. VERIFICAR CONFIG
echo "<h2>1️⃣ Verificando Configurações</h2>";
if(file_exists('config/config.php')) {
    echo "<span class='ok'>✓ config.php encontrado</span><br>";
    require_once 'config/config.php';
    
    echo "<table>";
    echo "<tr><th>Constante</th><th>Valor</th><th>Status</th></tr>";
    
    // UPLOAD_DIR
    $upload_dir_exists = defined('UPLOAD_DIR') && is_dir(UPLOAD_DIR);
    echo "<tr>";
    echo "<td>UPLOAD_DIR</td>";
    echo "<td>" . (defined('UPLOAD_DIR') ? UPLOAD_DIR : '<span class="error">NÃO DEFINIDO</span>') . "</td>";
    echo "<td>" . ($upload_dir_exists ? '<span class="ok">✓ Pasta existe</span>' : '<span class="error">✗ Pasta NÃO existe</span>') . "</td>";
    echo "</tr>";
    
    // UPLOAD_URL
    echo "<tr>";
    echo "<td>UPLOAD_URL</td>";
    echo "<td>" . (defined('UPLOAD_URL') ? UPLOAD_URL : '<span class="error">NÃO DEFINIDO</span>') . "</td>";
    echo "<td>-</td>";
    echo "</tr>";
    echo "</table>";
    
} else {
    echo "<span class='error'>✗ config.php NÃO encontrado!</span><br>";
    echo "Caminho esperado: " . __DIR__ . "/config/config.php<br>";
}

// 2. VERIFICAR CONEXÃO COM BANCO
echo "<h2>2️⃣ Verificando Conexão com Banco de Dados</h2>";
try {
    $db = getDB();
    echo "<span class='ok'>✓ Conexão com banco estabelecida</span><br>";
    
    // Verificar se tabela galeria existe
    $tables = $db->query("SHOW TABLES LIKE 'galeria'")->fetchAll();
    if(count($tables) > 0) {
        echo "<span class='ok'>✓ Tabela 'galeria' existe</span><br>";
        
        // Contar fotos
        $count = $db->query("SELECT COUNT(*) as total FROM galeria")->fetch();
        echo "<strong>Total de fotos no banco:</strong> " . $count['total'] . "<br>";
        
        if($count['total'] > 0) {
            echo "<h3>📸 Fotos cadastradas:</h3>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Imagem</th><th>Categoria</th><th>Evento ID</th><th>Arquivo Existe?</th><th>Preview</th></tr>";
            
            $fotos = $db->query("SELECT * FROM galeria LIMIT 10")->fetchAll();
            foreach($fotos as $foto) {
                $arquivo_existe = false;
                $caminho_completo = '';
                
                if(defined('UPLOAD_DIR')) {
                    $caminho_completo = UPLOAD_DIR . $foto['imagem'];
                    $arquivo_existe = file_exists($caminho_completo);
                }
                
                echo "<tr>";
                echo "<td>" . $foto['id'] . "</td>";
                echo "<td>" . htmlspecialchars($foto['imagem']) . "</td>";
                echo "<td>" . htmlspecialchars($foto['categoria'] ?? 'N/A') . "</td>";
                echo "<td>" . ($foto['evento_id'] ?? 'N/A') . "</td>";
                echo "<td>" . ($arquivo_existe ? '<span class="ok">✓ SIM</span>' : '<span class="error">✗ NÃO</span>') . "</td>";
                echo "<td>";
                if($arquivo_existe && defined('UPLOAD_URL')) {
                    $url = UPLOAD_URL . $foto['imagem'];
                    echo "<img src='$url' width='80' style='border-radius:8px'>";
                } else {
                    echo "-";
                }
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<span class='error'>⚠ Nenhuma foto cadastrada no banco!</span><br>";
            echo "<strong>Solução:</strong> Acesse o painel admin e faça upload de fotos.<br>";
        }
        
    } else {
        echo "<span class='error'>✗ Tabela 'galeria' NÃO existe!</span><br>";
        echo "<strong>SQL para criar:</strong><br>";
        echo "<pre>CREATE TABLE galeria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    imagem VARCHAR(255) NOT NULL,
    categoria VARCHAR(100),
    evento_id INT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);</pre>";
    }
    
} catch (PDOException $e) {
    echo "<span class='error'>✗ Erro ao conectar: " . $e->getMessage() . "</span><br>";
}

// 3. VERIFICAR ESTRUTURA DE PASTAS
echo "<h2>3️⃣ Verificando Estrutura de Pastas</h2>";
$pastas_verificar = [
    'config',
    'includes',
    'admin',
    'uploads',
    'assets'
];

echo "<table>";
echo "<tr><th>Pasta</th><th>Status</th><th>Permissões</th></tr>";
foreach($pastas_verificar as $pasta) {
    $existe = is_dir($pasta);
    $permissoes = $existe ? substr(sprintf('%o', fileperms($pasta)), -4) : 'N/A';
    
    echo "<tr>";
    echo "<td>/$pasta</td>";
    echo "<td>" . ($existe ? '<span class="ok">✓ Existe</span>' : '<span class="error">✗ Não existe</span>') . "</td>";
    echo "<td>$permissoes</td>";
    echo "</tr>";
}
echo "</table>";

// 4. VERIFICAR ARQUIVOS IMPORTANTES
echo "<h2>4️⃣ Verificando Arquivos Importantes</h2>";
$arquivos_verificar = [
    'config/config.php',
    'includes/header.php',
    'includes/footer.php',
    'portfolio.php',
    'index.php'
];

echo "<table>";
echo "<tr><th>Arquivo</th><th>Status</th></tr>";
foreach($arquivos_verificar as $arquivo) {
    $existe = file_exists($arquivo);
    echo "<tr>";
    echo "<td>/$arquivo</td>";
    echo "<td>" . ($existe ? '<span class="ok">✓ Existe</span>' : '<span class="error">✗ Não existe</span>') . "</td>";
    echo "</tr>";
}
echo "</table>";

// 5. TESTE DE UPLOAD
echo "<h2>5️⃣ Teste de Permissões de Upload</h2>";
if(defined('UPLOAD_DIR')) {
    if(is_dir(UPLOAD_DIR)) {
        if(is_writable(UPLOAD_DIR)) {
            echo "<span class='ok'>✓ Pasta de upload tem permissão de escrita</span><br>";
        } else {
            echo "<span class='error'>✗ Pasta de upload NÃO tem permissão de escrita</span><br>";
            echo "<strong>Solução:</strong> Execute: <code>chmod 755 " . UPLOAD_DIR . "</code><br>";
        }
        
        // Listar arquivos na pasta de upload
        $arquivos = scandir(UPLOAD_DIR);
        $arquivos = array_diff($arquivos, ['.', '..']);
        echo "<strong>Arquivos na pasta de upload:</strong> " . count($arquivos) . "<br>";
        
        if(count($arquivos) > 0) {
            echo "<ul style='max-height:200px;overflow:auto;background:#222;padding:15px;border-radius:8px'>";
            foreach($arquivos as $arq) {
                $tamanho = filesize(UPLOAD_DIR . $arq);
                $tamanho_mb = round($tamanho / 1024 / 1024, 2);
                echo "<li>$arq <span style='color:#888'>($tamanho_mb MB)</span></li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<span class='error'>✗ Pasta de upload não existe</span><br>";
        echo "<strong>Solução:</strong> Crie a pasta: <code>mkdir " . UPLOAD_DIR . "</code><br>";
    }
} else {
    echo "<span class='error'>✗ UPLOAD_DIR não definido no config.php</span><br>";
}

// 6. INFORMAÇÕES DO PHP
echo "<h2>6️⃣ Informações do PHP</h2>";
echo "<table>";
echo "<tr><th>Configuração</th><th>Valor</th></tr>";
echo "<tr><td>Versão PHP</td><td>" . phpversion() . "</td></tr>";
echo "<tr><td>upload_max_filesize</td><td>" . ini_get('upload_max_filesize') . "</td></tr>";
echo "<tr><td>post_max_size</td><td>" . ini_get('post_max_size') . "</td></tr>";
echo "<tr><td>GD Library (para imagens)</td><td>" . (extension_loaded('gd') ? '<span class="ok">✓ Instalado</span>' : '<span class="error">✗ Não instalado</span>') . "</td></tr>";
echo "<tr><td>PDO MySQL</td><td>" . (extension_loaded('pdo_mysql') ? '<span class="ok">✓ Instalado</span>' : '<span class="error">✗ Não instalado</span>') . "</td></tr>";
echo "</table>";

echo "<h2>✅ Diagnóstico Completo!</h2>";
echo "<p><strong>Próximos passos:</strong></p>";
echo "<ol>";
echo "<li>Revise os itens marcados com <span class='error'>✗</span> acima</li>";
echo "<li>Se não há fotos no banco, acesse o painel admin para fazer upload</li>";
echo "<li>Verifique se as pastas e arquivos existem</li>";
echo "<li>Verifique as permissões das pastas</li>";
echo "</ol>";
?>