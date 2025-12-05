<?php
require_once '../config/config.php';
requireAdmin();

$db = getDB();
$action = $_GET['action'] ?? 'list';
$attractionId = $_GET['id'] ?? null;

// Criar/Editar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['create', 'edit'])) {
    $nome = sanitize($_POST['nome']);
    $categoria = sanitize($_POST['categoria']);
    $preco = floatval($_POST['preco']);
    $descricao = sanitize($_POST['descricao'] ?? '');
    $status_atracao = sanitize($_POST['status_atracao']);
    
    try {
        if ($action === 'create') {
            $stmt = $db->prepare("INSERT INTO atracoes (nome, categoria, preco, descricao, status_atracao) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $categoria, $preco, $descricao, $status_atracao]);
            redirect('admin/attractions.php?success=' . urlencode('Atração criada!'));
        } else {
            $stmt = $db->prepare("UPDATE atracoes SET nome=?, categoria=?, preco=?, descricao=?, status_atracao=? WHERE id=?");
            $stmt->execute([$nome, $categoria, $preco, $descricao, $status_atracao, $attractionId]);
            redirect('admin/attractions.php?success=' . urlencode('Atração atualizada!'));
        }
    } catch (PDOException $e) {
        $error = 'Erro: ' . $e->getMessage();
    }
}

// Deletar
if ($action === 'delete' && $attractionId) {
    try {
        $stmt = $db->prepare("DELETE FROM atracoes WHERE id = ?");
        $stmt->execute([$attractionId]);
        redirect('admin/attractions.php?success=' . urlencode('Atração excluída!'));
    } catch (PDOException $e) {
        $error = 'Erro: ' . $e->getMessage();
    }
}

// Buscar atração
$attraction = null;
if ($action === 'edit' && $attractionId) {
    $stmt = $db->prepare("SELECT * FROM atracoes WHERE id = ?");
    $stmt->execute([$attractionId]);
    $attraction = $stmt->fetch();
}

// Listar
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$sql = "SELECT * FROM atracoes WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND nome LIKE ?";
    $params[] = "%$search%";
}

if ($category) {
    $sql .= " AND categoria = ?";
    $params[] = $category;
}

$sql .= " ORDER BY nome";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$attractions = $stmt->fetchAll();

// Buscar categorias distintas
$categories = $db->query("SELECT DISTINCT categoria FROM atracoes WHERE categoria IS NOT NULL ORDER BY categoria")->fetchAll();

$pageTitle = $action === 'create' ? 'Nova Atração' : ($action === 'edit' ? 'Editar Atração' : 'Atrações');
include 'includes/header.php';
?>

<div class="admin-content">
    <?php if ($action === 'list'): ?>
        <div class="data-table-container">
            <div class="table-header">
                <h3><i class="fas fa-star"></i> Atrações</h3>
                <a href="attractions.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nova Atração
                </a>
            </div>

            <div style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <form method="GET" style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 15px;">
                    <input type="text" name="search" class="form-control" placeholder="Buscar..." value="<?php echo htmlspecialchars($search); ?>">
                    
                    <select name="category" class="form-control">
                        <option value="">Todas Categorias</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['categoria']); ?>" <?php echo $category === $cat['categoria'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['categoria']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </form>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Preço</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($attractions)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: #808080;">
                                Nenhuma atração encontrada
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($attractions as $attr): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($attr['nome']); ?></strong></td>
                                <td><?php echo htmlspecialchars($attr['categoria']); ?></td>
                                <td><?php echo formatCurrency($attr['preco']); ?></td>
                                <td>
                                    <?php
                                    $badges = [
                                        'disponivel' => ['Disponível', 'success'],
                                        'indisponivel' => ['Indisponível', 'danger'],
                                        'manutencao' => ['Manutenção', 'warning']
                                    ];
                                    $badge = $badges[$attr['status_atracao']] ?? ['Desconhecido', 'secondary'];
                                    ?>
                                    <span class="badge badge-<?php echo $badge[1]; ?>"><?php echo $badge[0]; ?></span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="attractions.php?action=edit&id=<?php echo $attr['id']; ?>" class="btn btn-sm btn-icon btn-edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="attractions.php?action=delete&id=<?php echo $attr['id']; ?>" 
                                           class="btn btn-sm btn-icon btn-delete"
                                           onclick="return confirmDelete('<?php echo htmlspecialchars($attr['nome']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <div class="data-table-container" style="max-width: 800px; margin: 0 auto;">
            <div class="table-header">
                <h3><i class="fas fa-star"></i> <?php echo $action === 'create' ? 'Nova Atração' : 'Editar Atração'; ?></h3>
                <a href="attractions.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
            </div>

            <form method="POST" style="padding: 30px;">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label>Nome da Atração *</label>
                        <input type="text" name="nome" class="form-control" required value="<?php echo htmlspecialchars($attraction['nome'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Categoria *</label>
                        <input type="text" name="categoria" class="form-control" required 
                               placeholder="Ex: DJ, Banda, Decoração..." 
                               value="<?php echo htmlspecialchars($attraction['categoria'] ?? ''); ?>">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label>Preço (R$) *</label>
                        <input type="number" step="0.01" name="preco" class="form-control" required value="<?php echo $attraction['preco'] ?? ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status_atracao" class="form-control" required>
                            <option value="disponivel" <?php echo ($attraction['status_atracao'] ?? '') === 'disponivel' ? 'selected' : ''; ?>>Disponível</option>
                            <option value="indisponivel" <?php echo ($attraction['status_atracao'] ?? '') === 'indisponivel' ? 'selected' : ''; ?>>Indisponível</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Descrição</label>
                    <textarea name="descricao" class="form-control" rows="5"><?php echo htmlspecialchars($attraction['descricao'] ?? ''); ?></textarea>
                </div>

                <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
                    <a href="attractions.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Atração
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>