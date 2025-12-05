<?php
require_once '../config/config.php';
requireAdmin();

$db = getDB();
$action = $_GET['action'] ?? 'list';
$imageId = $_GET['id'] ?? null;

// Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'upload') {
    $evento_id = (int)$_POST['evento_id'];
    $categoria = sanitize($_POST['categoria']);
    
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['imagem']);
        
        if ($upload['success']) {
            try {
                $stmt = $db->prepare("INSERT INTO galeria (evento_id, imagem, categoria, enviado_por) VALUES (?, ?, ?, ?)");
                $stmt->execute([$evento_id, $upload['filename'], $categoria, $_SESSION['user_id']]);
                redirect('admin/gallery.php?success=' . urlencode('Imagem enviada!'));
            } catch (PDOException $e) {
                $error = 'Erro ao salvar: ' . $e->getMessage();
            }
        } else {
            $error = $upload['error'];
        }
    }
}

// Deletar
if ($action === 'delete' && $imageId) {
    try {
        $stmt = $db->prepare("SELECT imagem FROM galeria WHERE id = ?");
        $stmt->execute([$imageId]);
        $image = $stmt->fetch();
        
        if ($image) {
            deleteFile($image['imagem']);
            $stmt = $db->prepare("DELETE FROM galeria WHERE id = ?");
            $stmt->execute([$imageId]);
            redirect('admin/gallery.php?success=' . urlencode('Imagem excluída!'));
        }
    } catch (PDOException $e) {
        $error = 'Erro: ' . $e->getMessage();
    }
}

// Listar
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$eventFilter = $_GET['event'] ?? '';

$sql = "SELECT g.*, e.titulo as evento_titulo 
        FROM galeria g 
        LEFT JOIN eventos e ON g.evento_id = e.id 
        WHERE 1=1";
$params = [];

if ($category) {
    $sql .= " AND g.categoria = ?";
    $params[] = $category;
}

if ($eventFilter) {
    $sql .= " AND g.evento_id = ?";
    $params[] = $eventFilter;
}

$sql .= " ORDER BY g.criado_em DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$images = $stmt->fetchAll();

// Buscar eventos
$eventos = $db->query("SELECT id, titulo FROM eventos ORDER BY titulo")->fetchAll();

$categorias = ['Casamentos', 'Festas', 'Corporativo', 'Shows', 'Outros'];

$pageTitle = 'Galeria';
include 'includes/header.php';
?>

<div class="admin-content">
    <div class="data-table-container">
        <div class="table-header">
            <h3><i class="fas fa-images"></i> Galeria</h3>
            <button onclick="document.getElementById('uploadModal').style.display='flex'" class="btn btn-primary">
                <i class="fas fa-upload"></i> Upload
            </button>
        </div>

        <div style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.05);">
            <form method="GET" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 15px;">
                <select name="category" class="form-control">
                    <option value="">Todas Categorias</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php echo $category === $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                    <?php endforeach; ?>
                </select>
                
                <select name="event" class="form-control">
                    <option value="">Todos Eventos</option>
                    <?php foreach ($eventos as $evt): ?>
                        <option value="<?php echo $evt['id']; ?>" <?php echo $eventFilter == $evt['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($evt['titulo']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </form>
        </div>

        <div style="padding: 30px;">
            <?php if (empty($images)): ?>
                <div style="text-align: center; padding: 60px; color: #808080;">
                    <i class="fas fa-images" style="font-size: 60px; margin-bottom: 20px; display: block;"></i>
                    Nenhuma imagem
                </div>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
                    <?php foreach ($images as $img): ?>
                        <div style="background: rgba(26,26,26,0.5); border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05);">
                            <div style="aspect-ratio: 16/9; background: #1a1a1a; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-image" style="font-size: 40px; color: #404040;"></i>
                            </div>
                            <div style="padding: 15px;">
                                <span class="badge badge-info"><?php echo htmlspecialchars($img['categoria']); ?></span>
                                <?php if ($img['evento_titulo']): ?>
                                    <p style="color: #B0B0B0; font-size: 13px; margin: 8px 0;">
                                        <?php echo htmlspecialchars($img['evento_titulo']); ?>
                                    </p>
                                <?php endif; ?>
                                <a href="gallery.php?action=delete&id=<?php echo $img['id']; ?>" 
                                   class="btn btn-sm btn-delete" style="width: 100%; margin-top: 10px;"
                                   onclick="return confirmDelete('esta imagem')">
                                    <i class="fas fa-trash"></i> Excluir
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="uploadModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #1a1a1a; border-radius: 20px; padding: 40px; max-width: 500px; width: 90%; border: 1px solid rgba(255,0,64,0.3);">
        <h3 style="color: white; margin-bottom: 25px;">Upload de Imagem</h3>
        
        <form method="POST" action="?action=upload" enctype="multipart/form-data">
            <div class="form-group">
                <label>Evento</label>
                <select name="evento_id" class="form-control">
                    <option value="0">Nenhum</option>
                    <?php foreach ($eventos as $evt): ?>
                        <option value="<?php echo $evt['id']; ?>"><?php echo htmlspecialchars($evt['titulo']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Categoria *</label>
                <select name="categoria" class="form-control" required>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Imagem *</label>
                <input type="file" name="imagem" class="form-control" accept="image/*" required>
            </div>

            <div style="display: flex; gap: 15px; margin-top: 30px;">
                <button type="button" onclick="document.getElementById('uploadModal').style.display='none'" class="btn btn-secondary" style="flex: 1;">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i class="fas fa-upload"></i> Enviar
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>