<?php
require_once '../config/config.php';
requireAdmin();

$db = getDB();
$action = $_GET['action'] ?? 'list';
$comunicadoId = $_GET['id'] ?? null;

// Criar/Editar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['create', 'edit'])) {
    $titulo = sanitize($_POST['titulo']);
    $mensagem = sanitize($_POST['mensagem']);
    $tipo = sanitize($_POST['tipo'] ?? 'informacao');
    $destinatarios = sanitize($_POST['destinatarios'] ?? 'todos');
    $prioridade = sanitize($_POST['prioridade'] ?? 'normal');
    
    try {
        if ($action === 'create') {
            $stmt = $db->prepare("INSERT INTO comunicados (titulo, mensagem, tipo, destinatarios, prioridade, criado_por, criado_em) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$titulo, $mensagem, $tipo, $destinatarios, $prioridade, $_SESSION['user_id']]);
            redirect('admin/announcements.php?success=' . urlencode('Comunicado criado com sucesso!'));
        } else {
            $stmt = $db->prepare("UPDATE comunicados SET titulo=?, mensagem=?, tipo=?, destinatarios=?, prioridade=? WHERE id=?");
            $stmt->execute([$titulo, $mensagem, $tipo, $destinatarios, $prioridade, $comunicadoId]);
            redirect('admin/announcements.php?success=' . urlencode('Comunicado atualizado com sucesso!'));
        }
    } catch (PDOException $e) {
        $error = 'Erro ao salvar: ' . $e->getMessage();
    }
}

// Deletar
if ($action === 'delete' && $comunicadoId) {
    try {
        $stmt = $db->prepare("DELETE FROM comunicados WHERE id = ?");
        $stmt->execute([$comunicadoId]);
        redirect('admin/announcements.php?success=' . urlencode('Comunicado excluído com sucesso!'));
    } catch (PDOException $e) {
        $error = 'Erro ao excluir: ' . $e->getMessage();
    }
}

// Buscar comunicado para editar
$comunicado = null;
if ($action === 'edit' && $comunicadoId) {
    $stmt = $db->prepare("SELECT * FROM comunicados WHERE id = ?");
    $stmt->execute([$comunicadoId]);
    $comunicado = $stmt->fetch();
    
    if (!$comunicado) {
        redirect('admin/announcements.php?error=' . urlencode('Comunicado não encontrado!'));
    }
}

// Listar comunicados
$search = $_GET['search'] ?? '';
$tipo_filter = $_GET['tipo'] ?? '';

$sql = "SELECT c.*, u.nome as autor_nome 
        FROM comunicados c 
        LEFT JOIN usuarios u ON c.criado_por = u.id 
        WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (c.titulo LIKE ? OR c.mensagem LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if ($tipo_filter) {
    $sql .= " AND c.tipo = ?";
    $params[] = $tipo_filter;
}

$sql .= " ORDER BY c.criado_em DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$comunicados = $stmt->fetchAll();

$pageTitle = $action === 'create' ? 'Novo Comunicado' : ($action === 'edit' ? 'Editar Comunicado' : 'Comunicados');
include 'includes/header.php';
?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success" style="margin: 20px; padding: 15px; background: rgba(16, 185, 129, 0.1); border: 2px solid #10b981; border-radius: 8px; color: #10b981;">
    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?>
</div>
<?php endif; ?>

<?php if (isset($_GET['error']) || isset($error)): ?>
<div class="alert alert-danger" style="margin: 20px; padding: 15px; background: rgba(239, 68, 68, 0.1); border: 2px solid #ef4444; border-radius: 8px; color: #ef4444;">
    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error'] ?? $error); ?>
</div>
<?php endif; ?>

<div class="admin-content">
    <?php if ($action === 'list'): ?>
        <!-- LISTA DE COMUNICADOS -->
        <div class="data-table-container">
            <div class="table-header">
                <h3><i class="fas fa-bullhorn"></i> Comunicados</h3>
                <a href="announcements.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Novo Comunicado
                </a>
            </div>

            <div style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <form method="GET" style="display: flex; gap: 15px;">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Buscar por título ou mensagem..." 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           style="flex: 1; max-width: 400px;">
                    
                    <select name="tipo" class="form-control" style="max-width: 200px;">
                        <option value="">Todos os Tipos</option>
                        <option value="informacao" <?php echo $tipo_filter === 'informacao' ? 'selected' : ''; ?>>Informação</option>
                        <option value="alerta" <?php echo $tipo_filter === 'alerta' ? 'selected' : ''; ?>>Alerta</option>
                        <option value="urgente" <?php echo $tipo_filter === 'urgente' ? 'selected' : ''; ?>>Urgente</option>
                        <option value="lembrete" <?php echo $tipo_filter === 'lembrete' ? 'selected' : ''; ?>>Lembrete</option>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    
                    <?php if ($search || $tipo_filter): ?>
                        <a href="announcements.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Limpar
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (!empty($comunicados)): ?>
                <div style="padding: 15px; background: rgba(255,255,255,0.02); border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <span style="color: #999; font-size: 14px;">
                        <i class="fas fa-info-circle"></i> 
                        Mostrando <?php echo count($comunicados); ?> comunicado(s)
                    </span>
                </div>
            <?php endif; ?>

            <div style="padding: 20px;">
                <?php if (empty($comunicados)): ?>
                    <div style="text-align: center; padding: 80px 20px; color: #666;">
                        <i class="fas fa-bullhorn" style="font-size: 64px; color: #333; margin-bottom: 20px; display: block;"></i>
                        <h3 style="font-size: 24px; margin-bottom: 10px; color: white;">Nenhum comunicado</h3>
                        <p style="font-size: 16px; color: #999;">
                            <?php echo $search || $tipo_filter ? 'Nenhum comunicado encontrado com os filtros aplicados.' : 'Não há comunicados no momento.'; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div style="display: grid; gap: 20px;">
                        <?php foreach ($comunicados as $com): ?>
                            <?php
                            $tipos = [
                                'informacao' => ['Info', 'info', '#3b82f6', 'info-circle'],
                                'alerta' => ['Alerta', 'warning', '#f59e0b', 'exclamation-triangle'],
                                'urgente' => ['Urgente', 'danger', '#ef4444', 'exclamation-circle'],
                                'lembrete' => ['Lembrete', 'secondary', '#8b5cf6', 'bell']
                            ];
                            $tipo = $tipos[$com['tipo']] ?? ['Info', 'info', '#3b82f6', 'info-circle'];
                            
                            $prioridades = [
                                'baixa' => '🔵',
                                'normal' => '🟢',
                                'alta' => '🟡',
                                'urgente' => '🔴'
                            ];
                            $prioridade_icon = $prioridades[$com['prioridade']] ?? '🟢';
                            ?>
                            
                            <div style="background: rgba(255,255,255,0.03); border: 2px solid rgba(255,255,255,0.05); border-left: 4px solid <?php echo $tipo[2]; ?>; border-radius: 12px; padding: 25px; transition: all 0.3s ease;" 
                                 onmouseover="this.style.background='rgba(255,255,255,0.05)'; this.style.borderColor='rgba(255,255,255,0.1)';" 
                                 onmouseout="this.style.background='rgba(255,255,255,0.03)'; this.style.borderColor='rgba(255,255,255,0.05)';">
                                
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                                    <div style="flex: 1;">
                                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                                            <span class="badge badge-<?php echo $tipo[1]; ?>" 
                                                  style="background: <?php echo $tipo[2]; ?>20; color: <?php echo $tipo[2]; ?>; border: 1px solid <?php echo $tipo[2]; ?>40; font-size: 11px; padding: 4px 10px;">
                                                <i class="fas fa-<?php echo $tipo[3]; ?>"></i> <?php echo $tipo[0]; ?>
                                            </span>
                                            
                                            <span style="font-size: 18px;" title="Prioridade: <?php echo ucfirst($com['prioridade']); ?>">
                                                <?php echo $prioridade_icon; ?>
                                            </span>
                                        </div>
                                        
                                        <h4 style="margin: 0 0 10px 0; color: white; font-size: 20px; font-weight: 700;">
                                            <?php echo htmlspecialchars($com['titulo']); ?>
                                        </h4>
                                        
                                        <p style="margin: 0 0 15px 0; color: #ccc; font-size: 15px; line-height: 1.6;">
                                            <?php echo nl2br(htmlspecialchars($com['mensagem'])); ?>
                                        </p>
                                        
                                        <div style="display: flex; align-items: center; gap: 20px; font-size: 13px; color: #666;">
                                            <span>
                                                <i class="fas fa-user"></i> 
                                                <?php echo htmlspecialchars($com['autor_nome'] ?? 'Sistema'); ?>
                                            </span>
                                            <span>
                                                <i class="fas fa-clock"></i> 
                                                <?php echo date('d/m/Y H:i', strtotime($com['criado_em'])); ?>
                                            </span>
                                            <span>
                                                <i class="fas fa-users"></i> 
                                                <?php echo ucfirst($com['destinatarios']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="btn-group" style="display: flex; gap: 8px;">
                                        <a href="announcements.php?action=edit&id=<?php echo $com['id']; ?>" 
                                           class="btn btn-sm btn-icon btn-edit"
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="announcements.php?action=delete&id=<?php echo $com['id']; ?>" 
                                           class="btn btn-sm btn-icon btn-delete"
                                           title="Excluir"
                                           onclick="return confirm('⚠️ Tem certeza que deseja excluir este comunicado?\n\n<?php echo htmlspecialchars($com['titulo']); ?>');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php else: ?>
        <!-- FORMULÁRIO CRIAR/EDITAR -->
        <div class="data-table-container" style="max-width: 900px; margin: 0 auto;">
            <div class="table-header">
                <h3>
                    <i class="fas fa-bullhorn"></i> 
                    <?php echo $action === 'create' ? 'Novo Comunicado' : 'Editar Comunicado'; ?>
                </h3>
                <a href="announcements.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>

            <form method="POST" style="padding: 30px;">
                <div class="form-group">
                    <label><i class="fas fa-heading"></i> Título do Comunicado *</label>
                    <input type="text" name="titulo" class="form-control" required 
                           value="<?php echo htmlspecialchars($comunicado['titulo'] ?? ''); ?>"
                           placeholder="Ex: Reunião de equipe amanhã">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Mensagem *</label>
                    <textarea name="mensagem" class="form-control" rows="6" required 
                              placeholder="Digite a mensagem do comunicado..."><?php echo htmlspecialchars($comunicado['mensagem'] ?? ''); ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Tipo *</label>
                        <select name="tipo" class="form-control" required>
                            <option value="informacao" <?php echo ($comunicado['tipo'] ?? '') === 'informacao' ? 'selected' : ''; ?>>📘 Informação</option>
                            <option value="alerta" <?php echo ($comunicado['tipo'] ?? '') === 'alerta' ? 'selected' : ''; ?>>⚠️ Alerta</option>
                            <option value="urgente" <?php echo ($comunicado['tipo'] ?? '') === 'urgente' ? 'selected' : ''; ?>>🚨 Urgente</option>
                            <option value="lembrete" <?php echo ($comunicado['tipo'] ?? '') === 'lembrete' ? 'selected' : ''; ?>>🔔 Lembrete</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-users"></i> Destinatários *</label>
                        <select name="destinatarios" class="form-control" required>
                            <option value="todos" <?php echo ($comunicado['destinatarios'] ?? '') === 'todos' ? 'selected' : ''; ?>>👥 Todos</option>
                            <option value="equipe" <?php echo ($comunicado['destinatarios'] ?? '') === 'equipe' ? 'selected' : ''; ?>>👔 Apenas Equipe</option>
                            <option value="admins" <?php echo ($comunicado['destinatarios'] ?? '') === 'admins' ? 'selected' : ''; ?>>⭐ Apenas Admins</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-flag"></i> Prioridade *</label>
                        <select name="prioridade" class="form-control" required>
                            <option value="baixa" <?php echo ($comunicado['prioridade'] ?? '') === 'baixa' ? 'selected' : ''; ?>>🔵 Baixa</option>
                            <option value="normal" <?php echo ($comunicado['prioridade'] ?? 'normal') === 'normal' ? 'selected' : ''; ?>>🟢 Normal</option>
                            <option value="alta" <?php echo ($comunicado['prioridade'] ?? '') === 'alta' ? 'selected' : ''; ?>>🟡 Alta</option>
                            <option value="urgente" <?php echo ($comunicado['prioridade'] ?? '') === 'urgente' ? 'selected' : ''; ?>>🔴 Urgente</option>
                        </select>
                    </div>
                </div>

                <div style="background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 8px; padding: 15px; margin-bottom: 25px;">
                    <div style="color: #3b82f6; font-size: 13px;">
                        <i class="fas fa-lightbulb"></i> 
                        <strong>Dica:</strong> Comunicados urgentes aparecem em destaque para todos os usuários.
                    </div>
                </div>

                <div style="display: flex; gap: 15px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.05);">
                    <a href="announcements.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> 
                        <?php echo $action === 'create' ? 'Publicar Comunicado' : 'Salvar Alterações'; ?>
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-hide alertas
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