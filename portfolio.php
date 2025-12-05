<?php
require_once 'config/config.php';
$db = getDB();
$filtro = $_GET['categoria'] ?? 'todos';

// Buscar categorias
$categorias = [];
try {
    $categorias = $db->query("SELECT DISTINCT categoria FROM galeria WHERE categoria IS NOT NULL ORDER BY categoria")->fetchAll();
} catch (PDOException $e) {}

// Buscar fotos
$fotos = [];
try {
    if ($filtro === 'todos') {
        $stmt = $db->query("SELECT g.*, e.titulo as evento_titulo FROM galeria g LEFT JOIN eventos e ON g.evento_id = e.id ORDER BY g.criado_em DESC");
    } else {
        $stmt = $db->prepare("SELECT g.*, e.titulo as evento_titulo FROM galeria g LEFT JOIN eventos e ON g.evento_id = e.id WHERE g.categoria = ? ORDER BY g.criado_em DESC");
        $stmt->execute([$filtro]);
    }
    $fotos = $stmt->fetchAll();
} catch (PDOException $e) {}

include 'includes/header.php';
?>

<!-- Hero Section -->
<section style="padding: 140px 20px 80px; background: linear-gradient(180deg, #0a0a0a 0%, #000 100%); text-align: center;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h1 style="font-size: clamp(42px, 8vw, 72px); font-weight: 900; color: white; margin-bottom: 20px; letter-spacing: -1px;">
            Nosso <span style="color: var(--cor-primaria, #FF0040);">Portfólio</span>
        </h1>
        <p style="font-size: clamp(16px, 3vw, 20px); color: #B0B0B0; max-width: 600px; margin: 0 auto;">
            Confira os momentos incríveis que já produzimos e se inspire para o seu próximo evento
        </p>
    </div>
</section>

<!-- Filtros -->
<section style="padding: 40px 20px; background: #000; border-bottom: 1px solid rgba(255,255,255,0.05); position: sticky; top: 70px; z-index: 10; backdrop-filter: blur(10px);">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap; margin-bottom: 20px;">
            <a href="portfolio.php" 
               class="filter-btn <?php echo $filtro === 'todos' ? 'active' : ''; ?>"
               style="padding: 14px 32px; border-radius: 30px; text-decoration: none; font-weight: 700; font-size: 15px; transition: all 0.3s ease; border: 2px solid transparent; <?php echo $filtro === 'todos' ? 'background: var(--cor-primaria, #FF0040); color: white; box-shadow: 0 4px 15px rgba(255,0,64,0.4);' : 'background: rgba(255,255,255,0.05); color: #808080; border-color: rgba(255,255,255,0.1);'; ?>">
                <i class="fas fa-th" style="margin-right: 8px;"></i> Todos
            </a>
            
            <?php if(empty($categorias)): ?>
                <!-- Categorias padrão caso não tenha no banco -->
                <a href="portfolio.php?categoria=Casamentos" 
                   class="filter-btn <?php echo $filtro === 'Casamentos' ? 'active' : ''; ?>"
                   style="padding: 14px 32px; border-radius: 30px; text-decoration: none; font-weight: 700; font-size: 15px; transition: all 0.3s ease; border: 2px solid transparent; <?php echo $filtro === 'Casamentos' ? 'background: var(--cor-primaria, #FF0040); color: white; box-shadow: 0 4px 15px rgba(255,0,64,0.4);' : 'background: rgba(255,255,255,0.05); color: #808080; border-color: rgba(255,255,255,0.1);'; ?>">
                    <i class="fas fa-ring" style="margin-right: 8px;"></i> Casamentos
                </a>
                <a href="portfolio.php?categoria=15 Anos" 
                   class="filter-btn <?php echo $filtro === '15 Anos' ? 'active' : ''; ?>"
                   style="padding: 14px 32px; border-radius: 30px; text-decoration: none; font-weight: 700; font-size: 15px; transition: all 0.3s ease; border: 2px solid transparent; <?php echo $filtro === '15 Anos' ? 'background: var(--cor-primaria, #FF0040); color: white; box-shadow: 0 4px 15px rgba(255,0,64,0.4);' : 'background: rgba(255,255,255,0.05); color: #808080; border-color: rgba(255,255,255,0.1);'; ?>">
                    <i class="fas fa-crown" style="margin-right: 8px;"></i> 15 Anos
                </a>
                <a href="portfolio.php?categoria=Corporativo" 
                   class="filter-btn <?php echo $filtro === 'Corporativo' ? 'active' : ''; ?>"
                   style="padding: 14px 32px; border-radius: 30px; text-decoration: none; font-weight: 700; font-size: 15px; transition: all 0.3s ease; border: 2px solid transparent; <?php echo $filtro === 'Corporativo' ? 'background: var(--cor-primaria, #FF0040); color: white; box-shadow: 0 4px 15px rgba(255,0,64,0.4);' : 'background: rgba(255,255,255,0.05); color: #808080; border-color: rgba(255,255,255,0.1);'; ?>">
                    <i class="fas fa-briefcase" style="margin-right: 8px;"></i> Corporativo
                </a>
                <a href="portfolio.php?categoria=Shows" 
                   class="filter-btn <?php echo $filtro === 'Shows' ? 'active' : ''; ?>"
                   style="padding: 14px 32px; border-radius: 30px; text-decoration: none; font-weight: 700; font-size: 15px; transition: all 0.3s ease; border: 2px solid transparent; <?php echo $filtro === 'Shows' ? 'background: var(--cor-primaria, #FF0040); color: white; box-shadow: 0 4px 15px rgba(255,0,64,0.4);' : 'background: rgba(255,255,255,0.05); color: #808080; border-color: rgba(255,255,255,0.1);'; ?>">
                    <i class="fas fa-music" style="margin-right: 8px;"></i> Shows
                </a>
                <a href="portfolio.php?categoria=Formaturas" 
                   class="filter-btn <?php echo $filtro === 'Formaturas' ? 'active' : ''; ?>"
                   style="padding: 14px 32px; border-radius: 30px; text-decoration: none; font-weight: 700; font-size: 15px; transition: all 0.3s ease; border: 2px solid transparent; <?php echo $filtro === 'Formaturas' ? 'background: var(--cor-primaria, #FF0040); color: white; box-shadow: 0 4px 15px rgba(255,0,64,0.4);' : 'background: rgba(255,255,255,0.05); color: #808080; border-color: rgba(255,255,255,0.1);'; ?>">
                    <i class="fas fa-graduation-cap" style="margin-right: 8px;"></i> Formaturas
                </a>
            <?php else: ?>
                <?php foreach($categorias as $cat): ?>
                <a href="portfolio.php?categoria=<?php echo urlencode($cat['categoria']); ?>" 
                   class="filter-btn <?php echo $filtro === $cat['categoria'] ? 'active' : ''; ?>"
                   style="padding: 14px 32px; border-radius: 30px; text-decoration: none; font-weight: 700; font-size: 15px; transition: all 0.3s ease; border: 2px solid transparent; <?php echo $filtro === $cat['categoria'] ? 'background: var(--cor-primaria, #FF0040); color: white; box-shadow: 0 4px 15px rgba(255,0,64,0.4);' : 'background: rgba(255,255,255,0.05); color: #808080; border-color: rgba(255,255,255,0.1);'; ?>">
                    <?php echo htmlspecialchars($cat['categoria']); ?>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div style="text-align: center;">
            <p style="color: #808080; font-size: 14px; font-weight: 600;">
                <i class="fas fa-images" style="margin-right: 6px; color: var(--cor-primaria, #FF0040);"></i>
                <?php echo count($fotos); ?> foto<?php echo count($fotos) != 1 ? 's' : ''; ?> 
                <?php echo $filtro !== 'todos' ? 'em <span style="color: var(--cor-primaria, #FF0040);">' . htmlspecialchars($filtro) . '</span>' : 'no total'; ?>
            </p>
        </div>
    </div>
</section>

<!-- Galeria -->
<section style="padding: 80px 20px 120px; background: #0a0a0a; min-height: 60vh;">
    <div style="max-width: 1400px; margin: 0 auto;">
        <?php if(empty($fotos)): ?>
        <!-- Estado Vazio -->
        <div style="text-align: center; padding: 120px 20px; color: #808080;">
            <div style="width: 140px; height: 140px; margin: 0 auto 30px; background: rgba(255,255,255,0.02); border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px dashed rgba(255,255,255,0.1);">
                <i class="fas fa-images" style="font-size: 60px; opacity: 0.3; color: var(--cor-primaria, #FF0040);"></i>
            </div>
            <h3 style="font-size: 28px; color: white; margin-bottom: 12px; font-weight: 800;">Nenhuma foto encontrada</h3>
            <p style="font-size: 16px; margin-bottom: 35px; color: #999;">
                <?php echo $filtro !== 'todos' ? 'Não há fotos nesta categoria ainda.' : 'Comece adicionando fotos dos seus eventos.'; ?>
            </p>
            <a href="admin/gallery.php" style="display: inline-flex; align-items: center; gap: 10px; padding: 16px 36px; background: var(--cor-primaria, #FF0040); color: white; text-decoration: none; border-radius: 30px; font-weight: 800; font-size: 16px; transition: all 0.3s ease; box-shadow: 0 6px 20px rgba(255,0,64,0.3);">
                <i class="fas fa-plus-circle"></i> Adicionar Fotos
            </a>
        </div>
        <?php else: ?>
        <!-- Grid de Fotos com Animação -->
        <div id="gallery-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px;">
            <?php 
            $index = 0;
            foreach($fotos as $foto):
                $img = UPLOAD_URL . $foto['imagem'];
                if(!file_exists(UPLOAD_DIR . $foto['imagem'])) continue;
                $index++;
            ?>
            <div class="gallery-item" 
                 style="background: rgba(26,26,26,0.6); border-radius: 20px; overflow: hidden; border: 1px solid rgba(255,255,255,0.08); cursor: pointer; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); opacity: 0; animation: fadeInUp 0.6s ease-out forwards; animation-delay: <?php echo ($index * 0.05); ?>s;"
                 onclick="openLightbox(<?php echo $index - 1; ?>)">
                <div style="aspect-ratio: 4/3; overflow: hidden; background: #1a1a1a; position: relative;">
                    <img src="<?php echo $img; ?>" 
                         alt="<?php echo htmlspecialchars($foto['evento_titulo'] ?? $foto['categoria']); ?>"
                         style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease;"
                         loading="lazy">
                    <div class="overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(0deg, rgba(0,0,0,0.7) 0%, transparent 50%); opacity: 0; transition: opacity 0.3s ease; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-search-plus" style="font-size: 32px; color: white;"></i>
                    </div>
                </div>
                <div style="padding: 20px;">
                    <span style="background: rgba(255,0,64,0.15); color: var(--cor-primaria, #FF0040); padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                        <?php echo htmlspecialchars($foto['categoria']); ?>
                    </span>
                    <?php if($foto['evento_titulo']): ?>
                    <h5 style="color: white; font-size: 17px; font-weight: 700; margin: 12px 0 0; line-height: 1.4;">
                        <?php echo htmlspecialchars($foto['evento_titulo']); ?>
                    </h5>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Lightbox Profissional -->
<div id="lightbox" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 10000; padding: 20px; opacity: 0; transition: opacity 0.3s ease;">
    <button onclick="closeLightbox()" style="position: absolute; top: 30px; right: 30px; background: rgba(255,255,255,0.1); border: 2px solid rgba(255,255,255,0.2); color: white; width: 50px; height: 50px; border-radius: 50%; cursor: pointer; font-size: 24px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; backdrop-filter: blur(10px); z-index: 10002;">
        <i class="fas fa-times"></i>
    </button>
    
    <button id="prevBtn" onclick="changeImage(-1)" style="position: absolute; left: 30px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.1); border: 2px solid rgba(255,255,255,0.2); color: white; width: 60px; height: 60px; border-radius: 50%; cursor: pointer; font-size: 24px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; backdrop-filter: blur(10px); z-index: 10002;">
        <i class="fas fa-chevron-left"></i>
    </button>
    
    <button id="nextBtn" onclick="changeImage(1)" style="position: absolute; right: 30px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.1); border: 2px solid rgba(255,255,255,0.2); color: white; width: 60px; height: 60px; border-radius: 50%; cursor: pointer; font-size: 24px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; backdrop-filter: blur(10px); z-index: 10002;">
        <i class="fas fa-chevron-right"></i>
    </button>
    
    <div style="width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
        <img id="lightbox-img" src="" style="max-width: 90%; max-height: 80vh; object-fit: contain; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.5);">
        <div id="lightbox-info" style="margin-top: 25px; text-align: center; max-width: 600px;">
            <span id="lightbox-categoria" style="background: rgba(255,0,64,0.15); color: var(--cor-primaria, #FF0040); padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 800; text-transform: uppercase;"></span>
            <h3 id="lightbox-titulo" style="color: white; font-size: 22px; font-weight: 700; margin: 15px 0 0;"></h3>
            <p id="lightbox-counter" style="color: #808080; font-size: 14px; margin-top: 10px; font-weight: 600;"></p>
        </div>
    </div>
</div>

<!-- CTA Section -->
<section style="padding: 80px 20px; background: linear-gradient(135deg, var(--cor-primaria, #FF0040), #CC0033); text-align: center; position: relative; overflow: hidden;">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: url('data:image/svg+xml,<svg width=\"100\" height=\"100\" xmlns=\"http://www.w3.org/2000/svg\"><circle cx=\"50\" cy=\"50\" r=\"40\" fill=\"white\" opacity=\"0.03\"/></svg>'); opacity: 0.1;"></div>
    <div style="position: relative; z-index: 1; max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: clamp(32px, 6vw, 48px); font-weight: 900; color: white; margin-bottom: 18px; letter-spacing: -0.5px;">
            Gostou do que viu?
        </h2>
        <p style="font-size: clamp(16px, 3vw, 20px); color: rgba(255,255,255,0.95); margin-bottom: 35px; line-height: 1.6;">
            Transforme sua visão em realidade. Vamos criar momentos inesquecíveis juntos!
        </p>
        <a href="orcamento.php" style="display: inline-flex; align-items: center; gap: 12px; padding: 18px 42px; background: white; color: var(--cor-primaria, #FF0040); text-decoration: none; border-radius: 35px; font-weight: 900; font-size: 17px; transition: all 0.3s ease; box-shadow: 0 8px 25px rgba(0,0,0,0.2);">
            <i class="fas fa-calendar-check"></i> Solicitar Orçamento Grátis
        </a>
    </div>
</section>

<style>
/* Animações */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Hover Effects */
.gallery-item:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(255,0,64,0.3);
    border-color: rgba(255,0,64,0.3);
}

.gallery-item:hover img {
    transform: scale(1.08);
}

.gallery-item:hover .overlay {
    opacity: 1;
}

.filter-btn:hover {
    background: rgba(255,0,64,0.1) !important;
    color: var(--cor-primaria, #FF0040) !important;
    border-color: var(--cor-primaria, #FF0040) !important;
    transform: translateY(-2px);
}

.filter-btn.active:hover {
    background: var(--cor-primaria, #FF0040) !important;
    color: white !important;
}

#lightbox button:hover {
    background: rgba(255,255,255,0.2);
    border-color: rgba(255,255,255,0.4);
    transform: scale(1.1);
}

#prevBtn:hover,
#nextBtn:hover {
    transform: translateY(-50%) scale(1.1);
}

/* Responsivo */
@media (max-width: 768px) {
    #gallery-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)) !important;
        gap: 20px !important;
    }
    
    #prevBtn, #nextBtn {
        width: 45px !important;
        height: 45px !important;
        font-size: 18px !important;
    }
    
    #prevBtn {
        left: 15px !important;
    }
    
    #nextBtn {
        right: 15px !important;
    }
}

@media (max-width: 480px) {
    #gallery-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
// Dados das fotos para o lightbox
const photos = <?php echo json_encode(array_values(array_filter($fotos, function($f) {
    return file_exists(UPLOAD_DIR . $f['imagem']);
}))); ?>;

let currentIndex = 0;

function openLightbox(index) {
    currentIndex = index;
    updateLightbox();
    const lightbox = document.getElementById('lightbox');
    lightbox.style.display = 'block';
    setTimeout(() => {
        lightbox.style.opacity = '1';
    }, 10);
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    const lightbox = document.getElementById('lightbox');
    lightbox.style.opacity = '0';
    setTimeout(() => {
        lightbox.style.display = 'none';
        document.body.style.overflow = 'auto';
    }, 300);
}

function changeImage(direction) {
    currentIndex += direction;
    if (currentIndex < 0) currentIndex = photos.length - 1;
    if (currentIndex >= photos.length) currentIndex = 0;
    updateLightbox();
}

function updateLightbox() {
    if (!photos[currentIndex]) return;
    
    const photo = photos[currentIndex];
    const img = document.getElementById('lightbox-img');
    
    // Fade out
    img.style.opacity = '0';
    
    setTimeout(() => {
        img.src = '<?php echo UPLOAD_URL; ?>' + photo.imagem;
        document.getElementById('lightbox-categoria').textContent = photo.categoria || '';
        document.getElementById('lightbox-titulo').textContent = photo.evento_titulo || '';
        document.getElementById('lightbox-counter').textContent = `Foto ${currentIndex + 1} de ${photos.length}`;
        
        // Fade in
        img.style.opacity = '1';
    }, 150);
}

// Navegação por teclado
document.addEventListener('keydown', function(e) {
    const lightbox = document.getElementById('lightbox');
    if (lightbox.style.display === 'block') {
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') changeImage(-1);
        if (e.key === 'ArrowRight') changeImage(1);
    }
});

// Fechar ao clicar fora da imagem
document.getElementById('lightbox').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLightbox();
    }
});

// Adicionar transição suave na imagem
document.getElementById('lightbox-img').style.transition = 'opacity 0.3s ease';
</script>

<?php include 'includes/footer.php'; ?>