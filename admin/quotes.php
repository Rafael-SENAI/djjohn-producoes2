<?php
require_once '../config/config.php';
requireAdmin();

$db = getDB();
$action = $_GET['action'] ?? 'list';
$quoteId = $_GET['id'] ?? null;

// Atualizar status
if ($action === 'update_status' && $quoteId && isset($_POST['status'])) {
    $status = $_POST['status'];
    $notes = sanitize($_POST['notes'] ?? '');
    
    try {
        $stmt = $db->prepare("UPDATE orcamentos SET status_evento = ?, notes = ? WHERE id = ?");
        $stmt->execute([$status, $notes, $quoteId]);
        redirect('admin/quotes.php?success=' . urlencode('Status atualizado!'));
    } catch (PDOException $e) {
        $error = 'Erro: ' . $e->getMessage();
    }
}

// Deletar orçamento
if ($action === 'delete' && $quoteId) {
    try {
        $stmt = $db->prepare("DELETE FROM orcamentos WHERE id = ?");
        $stmt->execute([$quoteId]);
        redirect('admin/quotes.php?success=' . urlencode('Orçamento excluído!'));
    } catch (PDOException $e) {
        $error = 'Erro: ' . $e->getMessage();
    }
}

// Buscar orçamento específico
$quote = null;
if ($action === 'view' && $quoteId) {
    $stmt = $db->prepare("SELECT * FROM orcamentos WHERE id = ?");
    $stmt->execute([$quoteId]);
    $quote = $stmt->fetch();
}

// Listar orçamentos
$statusFilter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM orcamentos WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (nome LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if ($statusFilter) {
    $sql .= " AND status_evento = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY criado_em DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$quotes = $stmt->fetchAll();

$pageTitle = 'Orçamentos';
include 'includes/header.php';
?>

<div class="admin-content">
    <?php if ($action === 'list'): ?>
        <div class="data-table-container">
            <div class="table-header">
                <h3><i class="fas fa-file-invoice-dollar"></i> Orçamentos Solicitados</h3>
            </div>

            <!-- Filtros -->
            <div style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.05); background: rgba(26,26,26,0.3);">
                <form method="GET" style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 15px;">
                    <input type="text" name="search" class="form-control" placeholder="Buscar por nome, email ou telefone..." value="<?php echo htmlspecialchars($search); ?>">
                    
                    <select name="status" class="form-control">
                        <option value="">Todos os Status</option>
                        <option value="new" <?php echo $statusFilter === 'new' ? 'selected' : ''; ?>>Novo</option>
                        <option value="contacted" <?php echo $statusFilter === 'contacted' ? 'selected' : ''; ?>>Contatado</option>
                        <option value="sent" <?php echo $statusFilter === 'sent' ? 'selected' : ''; ?>>Orçamento Enviado</option>
                        <option value="approved" <?php echo $statusFilter === 'approved' ? 'selected' : ''; ?>>Aprovado</option>
                        <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Rejeitado</option>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                </form>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Evento</th>
                        <th>Data</th>
                        <th>Convidados</th>
                        <th>Status</th>
                        <th>Recebido</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($quotes)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px;">
                                <i class="fas fa-inbox" style="font-size: 50px; color: #808080; margin-bottom: 15px; display: block;"></i>
                                <p style="color: #808080;">Nenhum orçamento encontrado</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($quotes as $q): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($q['name']); ?></strong><br>
                                    <small style="color: #808080;">
                                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($q['email']); ?><br>
                                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($q['phone']); ?>
                                    </small>
                                </td>
                                <td><?php echo htmlspecialchars($q['event_type']); ?></td>
                                <td><?php echo $q['event_date'] ? formatDate($q['event_date']) : '-'; ?></td>
                                <td><?php echo $q['guests_count'] ?: '-'; ?></td>
                                <td>
                                    <?php
                                    $statusMap = [
                                        'new' => ['Novo', 'info'],
                                        'contacted' => ['Contatado', 'warning'],
                                        'sent' => ['Enviado', 'secondary'],
                                        'approved' => ['Aprovado', 'success'],
                                        'rejected' => ['Rejeitado', 'danger']
                                    ];
                                    $s = $statusMap[$q['status']] ?? ['Desconhecido', 'secondary'];
                                    ?>
                                    <span class="badge badge-<?php echo $s[1]; ?>">
                                        <?php echo $s[0]; ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($q['created_at']); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="quotes.php?action=view&id=<?php echo $q['id']; ?>" class="btn btn-sm btn-icon btn-view" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="quotes.php?action=delete&id=<?php echo $q['id']; ?>" 
                                           class="btn btn-sm btn-icon btn-delete" 
                                           title="Excluir"
                                           onclick="return confirmDelete('Deseja excluir este orçamento?')">
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
        <!-- DETALHES DO ORÇAMENTO -->
        <div class="data-table-container" style="max-width: 900px; margin: 0 auto;">
            <div class="table-header">
                <h3><i class="fas fa-file-invoice-dollar"></i> Detalhes do Orçamento</h3>
                <a href="quotes.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>

            <?php if ($quote): ?>
                <div style="padding: 30px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                        <div class="info-card">
                            <h4 style="color: #FF0040; margin-bottom: 20px;"><i class="fas fa-user"></i> Dados do Cliente</h4>
                            <p><strong>Nome:</strong> <?php echo htmlspecialchars($quote['name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($quote['email']); ?></p>
                            <p><strong>Telefone:</strong> <?php echo htmlspecialchars($quote['phone']); ?></p>
                        </div>

                        <div class="info-card">
                            <h4 style="color: #FF0040; margin-bottom: 20px;"><i class="fas fa-calendar"></i> Dados do Evento</h4>
                            <p><strong>Tipo:</strong> <?php echo htmlspecialchars($quote['event_type']); ?></p>
                            <p><strong>Data:</strong> <?php echo $quote['event_date'] ? formatDate($quote['event_date']) : '-'; ?></p>
                            <p><strong>Local:</strong> <?php echo htmlspecialchars($quote['location'] ?: '-'); ?></p>
                            <p><strong>Convidados:</strong> <?php echo $quote['guests_count'] ?: '-'; ?></p>
                            <p><strong>Orçamento:</strong> <?php echo htmlspecialchars($quote['budget_range'] ?: '-'); ?></p>
                        </div>
                    </div>

                    <?php if ($quote['message']): ?>
                        <div class="info-card" style="margin-bottom: 30px;">
                            <h4 style="color: #FF0040; margin-bottom: 15px;"><i class="fas fa-comment"></i> Mensagem</h4>
                            <p><?php echo nl2br(htmlspecialchars($quote['message'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Atualizar Status -->
                    <form method="POST" action="quotes.php?action=update_status&id=<?php echo $quote['id']; ?>" class="info-card">
                        <h4 style="color: #FF0040; margin-bottom: 20px;"><i class="fas fa-edit"></i> Atualizar Status</h4>
                        
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="new" <?php echo $quote['status'] === 'new' ? 'selected' : ''; ?>>Novo</option>
                                <option value="contacted" <?php echo $quote['status'] === 'contacted' ? 'selected' : ''; ?>>Contatado</option>
                                <option value="sent" <?php echo $quote['status'] === 'sent' ? 'selected' : ''; ?>>Orçamento Enviado</option>
                                <option value="approved" <?php echo $quote['status'] === 'approved' ? 'selected' : ''; ?>>Aprovado</option>
                                <option value="rejected" <?php echo $quote['status'] === 'rejected' ? 'selected' : ''; ?>>Rejeitado</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Observações Internas</label>
                            <textarea name="notes" class="form-control" rows="4"><?php echo htmlspecialchars($quote['notes'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div style="padding: 40px; text-align: center; color: #808080;">
                    <p>Orçamento não encontrado</p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.info-card {
    background: rgba(26,26,26,0.3);
    border: 1px solid rgba(255,255,255,0.05);
    border-radius: 12px;
    padding: 25px;
}

.info-card p {
    color: #B0B0B0;
    margin-bottom: 12px;
    line-height: 1.6;
}

.info-card strong {
    color: white;
    margin-right: 8px;
}
</style>

<?php include 'includes/footer.php'; ?>
