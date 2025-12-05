<?php
require_once '../config/config.php';
requireAdmin();

$db = getDB();
$action = $_GET['action'] ?? 'list';
$venueId = $_GET['id'] ?? null;

// Criar/Editar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['create', 'edit'])) {
    $nome = sanitize($_POST['nome']);
    $endereco = sanitize($_POST['endereco'] ?? '');
    $cidade = sanitize($_POST['cidade'] ?? '');
    $estado = sanitize($_POST['estado'] ?? '');
    $telefone = sanitize($_POST['telefone'] ?? '');
    $capacidade = (int)($_POST['capacidade'] ?? 0);
    $descricao = sanitize($_POST['descricao'] ?? '');
    $status_salao = sanitize($_POST['status_salao']);
    
    try {
        if ($action === 'create') {
            $stmt = $db->prepare("INSERT INTO saloes (nome, endereco, cidade, estado, telefone, capacidade, descricao, status_salao) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $endereco, $cidade, $estado, $telefone, $capacidade, $descricao, $status_salao]);
            redirect('admin/venues.php?success=' . urlencode('Salão cadastrado com sucesso!'));
        } else {
            $stmt = $db->prepare("UPDATE saloes SET nome=?, endereco=?, cidade=?, estado=?, telefone=?, capacidade=?, descricao=?, status_salao=? WHERE id=?");
            $stmt->execute([$nome, $endereco, $cidade, $estado, $telefone, $capacidade, $descricao, $status_salao, $venueId]);
            redirect('admin/venues.php?success=' . urlencode('Salão atualizado com sucesso!'));
        }
    } catch (PDOException $e) {
        $error = 'Erro ao salvar: ' . $e->getMessage();
    }
}

// Deletar
if ($action === 'delete' && $venueId) {
    try {
        $stmt = $db->prepare("DELETE FROM saloes WHERE id = ?");
        $stmt->execute([$venueId]);
        redirect('admin/venues.php?success=' . urlencode('Salão excluído com sucesso!'));
    } catch (PDOException $e) {
        $error = 'Erro ao excluir: ' . $e->getMessage();
    }
}

// Buscar salão para editar
$venue = null;
if ($action === 'edit' && $venueId) {
    $stmt = $db->prepare("SELECT * FROM saloes WHERE id = ?");
    $stmt->execute([$venueId]);
    $venue = $stmt->fetch();
    
    if (!$venue) {
        redirect('admin/venues.php?error=' . urlencode('Salão não encontrado!'));
    }
}

// Listar salões
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM saloes WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (nome LIKE ? OR cidade LIKE ? OR endereco LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$sql .= " ORDER BY nome ASC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$venues = $stmt->fetchAll();

$pageTitle = $action === 'create' ? 'Novo Salão' : ($action === 'edit' ? 'Editar Salão' : 'Salões');
include 'includes/header.php';
?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success" style="margin: 20px; padding: 15px; background: rgba(16, 185, 129, 0.1); border: 2px solid #10b981; border-radius: 8px; color: #10b981;">
    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?>
</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<div class="alert alert-danger" style="margin: 20px; padding: 15px; background: rgba(239, 68, 68, 0.1); border: 2px solid #ef4444; border-radius: 8px; color: #ef4444;">
    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="alert alert-danger" style="margin: 20px; padding: 15px; background: rgba(239, 68, 68, 0.1); border: 2px solid #ef4444; border-radius: 8px; color: #ef4444;">
    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<div class="admin-content">
    <?php if ($action === 'list'): ?>
        <!-- LISTA DE SALÕES -->
        <div class="data-table-container">
            <div class="table-header">
                <h3><i class="fas fa-building"></i> Salões Cadastrados</h3>
                <a href="venues.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Novo Salão
                </a>
            </div>

            <div style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <form method="GET" style="display: flex; gap: 15px;">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Buscar por nome, cidade ou endereço..." 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           style="max-width: 500px; flex: 1;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <?php if ($search): ?>
                        <a href="venues.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Limpar
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (!empty($venues)): ?>
                <div style="padding: 15px; background: rgba(255,255,255,0.02); border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <span style="color: #999; font-size: 14px;">
                        <i class="fas fa-info-circle"></i> 
                        Mostrando <?php echo count($venues); ?> salão(ões)
                    </span>
                </div>
            <?php endif; ?>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Cidade</th>
                        <th>Capacidade</th>
                        <th>Telefone</th>
                        <th>Status</th>
                        <th style="text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($venues)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 60px; color: #666;">
                                <i class="fas fa-building" style="font-size: 48px; color: #333; margin-bottom: 15px; display: block;"></i>
                                <strong style="font-size: 18px; display: block; margin-bottom: 8px;">Nenhum salão encontrado</strong>
                                <span style="font-size: 14px;">
                                    <?php echo $search ? 'Tente buscar com outros termos' : 'Cadastre seu primeiro salão clicando em "Novo Salão"'; ?>
                                </span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($venues as $v): ?>
                            <tr>
                                <td>
                                    <strong style="color: white; font-size: 15px;">
                                        <?php echo htmlspecialchars($v['nome']); ?>
                                    </strong>
                                    <?php if ($v['endereco']): ?>
                                        <br>
                                        <small style="color: #666;">
                                            <i class="fas fa-map-marker-alt"></i> 
                                            <?php echo htmlspecialchars($v['endereco']); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $location = [];
                                    if ($v['cidade']) $location[] = $v['cidade'];
                                    if ($v['estado']) $location[] = $v['estado'];
                                    echo $location ? htmlspecialchars(implode(', ', $location)) : '-';
                                    ?>
                                </td>
                                <td>
                                    <?php if ($v['capacidade']): ?>
                                        <i class="fas fa-users" style="color: #FF0040; margin-right: 5px;"></i>
                                        <?php echo number_format($v['capacidade'], 0, ',', '.'); ?> pessoas
                                    <?php else: ?>
                                        <span style="color: #666;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($v['telefone']): ?>
                                        <i class="fas fa-phone" style="color: #FF0040; margin-right: 5px;"></i>
                                        <?php echo htmlspecialchars($v['telefone']); ?>
                                    <?php else: ?>
                                        <span style="color: #666;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $v['status_salao'] === 'ativo' ? 'success' : 'secondary'; ?>"
                                          style="background: <?php echo $v['status_salao'] === 'ativo' ? '#10b98120' : '#66666620'; ?>; 
                                                 color: <?php echo $v['status_salao'] === 'ativo' ? '#10b981' : '#666'; ?>; 
                                                 border: 1px solid <?php echo $v['status_salao'] === 'ativo' ? '#10b98140' : '#66666640'; ?>;">
                                        <?php echo $v['status_salao'] === 'ativo' ? 'Ativo' : 'Inativo'; ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <div class="btn-group" style="display: inline-flex; gap: 5px;">
                                        <a href="venues.php?action=edit&id=<?php echo $v['id']; ?>" 
                                           class="btn btn-sm btn-icon btn-edit"
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="venues.php?action=delete&id=<?php echo $v['id']; ?>" 
                                           class="btn btn-sm btn-icon btn-delete"
                                           title="Excluir"
                                           onclick="return confirm('⚠️ Tem certeza que deseja excluir o salão:\n\n<?php echo htmlspecialchars($v['nome']); ?>\n\nEsta ação não pode ser desfeita!');">
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
        <!-- FORMULÁRIO CRIAR/EDITAR -->
        <div class="data-table-container" style="max-width: 900px; margin: 0 auto;">
            <div class="table-header">
                <h3>
                    <i class="fas fa-building"></i> 
                    <?php echo $action === 'create' ? 'Novo Salão' : 'Editar Salão'; ?>
                </h3>
                <a href="venues.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>

            <form method="POST" style="padding: 30px;">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label><i class="fas fa-building"></i> Nome do Salão *</label>
                        <input type="text" name="nome" class="form-control" required 
                               value="<?php echo htmlspecialchars($venue['nome'] ?? ''); ?>"
                               placeholder="Ex: Salão de Festas Premium">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-toggle-on"></i> Status *</label>
                        <select name="status_salao" class="form-control" required>
                            <option value="ativo" <?php echo ($venue['status_salao'] ?? 'ativo') === 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                            <option value="inativo" <?php echo ($venue['status_salao'] ?? '') === 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Endereço Completo</label>
                    <input type="text" name="endereco" class="form-control" 
                           value="<?php echo htmlspecialchars($venue['endereco'] ?? ''); ?>"
                           placeholder="Rua, número, bairro">
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label><i class="fas fa-city"></i> Cidade</label>
                        <input type="text" name="cidade" class="form-control" 
                               value="<?php echo htmlspecialchars($venue['cidade'] ?? ''); ?>"
                               placeholder="Nome da cidade">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-map"></i> Estado</label>
                        <input type="text" name="estado" class="form-control" maxlength="2" 
                               value="<?php echo htmlspecialchars($venue['estado'] ?? ''); ?>"
                               placeholder="SP" style="text-transform: uppercase;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Telefone</label>
                        <input type="text" name="telefone" class="form-control" 
                               value="<?php echo htmlspecialchars($venue['telefone'] ?? ''); ?>"
                               placeholder="(00) 0000-0000">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-users"></i> Capacidade</label>
                        <input type="number" name="capacidade" class="form-control" 
                               value="<?php echo $venue['capacidade'] ?? ''; ?>"
                               placeholder="Ex: 500" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Descrição</label>
                    <textarea name="descricao" class="form-control" rows="4"
                              placeholder="Descrição detalhada do salão..."><?php echo htmlspecialchars($venue['descricao'] ?? ''); ?></textarea>
                </div>

                <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.05);">
                    <a href="venues.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Salão
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
// JavaScript PURO - SEM JQUERY - SEM ERROS
(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-hide mensagens após 5 segundos
        var alerts = document.querySelectorAll('.alert');
        if (alerts.length > 0) {
            setTimeout(function() {
                alerts.forEach(function(alert) {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                });
            }, 5000);
        }
    });
})();
</script>

<?php include 'includes/footer.php'; ?>