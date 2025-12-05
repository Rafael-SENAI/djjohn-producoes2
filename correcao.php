<?php
// Coloque este arquivo em: /admin/verificar_estrutura.php

echo "<h1>🔍 Verificação de Estrutura</h1>";
echo "<style>body{font-family:Arial;background:#0a0a0a;color:white;padding:40px} .ok{color:#0f0} .error{color:#f00} code{background:#222;padding:2px 6px;border-radius:4px}</style>";

echo "<h2>📁 Verificando arquivos necessários:</h2>";

$arquivos_necessarios = [
    'config/config.php' => 'Config principal',
    'includes/get_eventos.php' => 'Buscar eventos',
    'includes/get_evento.php' => 'Buscar 1 evento',
    'includes/save_evento.php' => 'Salvar evento',
    'includes/header.php' => 'Header',
    'includes/footer.php' => 'Footer'
];

echo "<table border='1' cellpadding='10' style='border-collapse:collapse;width:100%;background:#1a1a1a'>";
echo "<tr><th>Arquivo</th><th>Status</th><th>Caminho Completo</th></tr>";

foreach ($arquivos_necessarios as $arquivo => $descricao) {
    $caminho_completo = __DIR__ . '/' . $arquivo;
    $existe = file_exists($caminho_completo);
    
    echo "<tr>";
    echo "<td><strong>$descricao</strong><br><code>$arquivo</code></td>";
    echo "<td>" . ($existe ? "<span class='ok'>✓ EXISTE</span>" : "<span class='error'>✗ NÃO EXISTE</span>") . "</td>";
    echo "<td style='font-size:11px;color:#666'>$caminho_completo</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>📍 Diretório atual:</h2>";
echo "<code>" . __DIR__ . "</code>";

echo "<h2>📂 Arquivos na pasta includes/:</h2>";
if (is_dir('includes')) {
    $arquivos = scandir('includes');
    echo "<ul>";
    foreach ($arquivos as $arquivo) {
        if ($arquivo != '.' && $arquivo != '..') {
            echo "<li><code>$arquivo</code></li>";
        }
    }
    echo "</ul>";
} else {
    echo "<span class='error'>✗ Pasta includes/ NÃO EXISTE!</span>";
}

echo "<h2>✅ Próximos passos:</h2>";
echo "<ol>";
echo "<li>Verifique se todos os arquivos têm ✓ EXISTE</li>";
echo "<li>Se algum estiver faltando, copie novamente</li>";
echo "<li>Certifique-se que os nomes estão EXATAMENTE como mostrado</li>";
echo "</ol>";
?>