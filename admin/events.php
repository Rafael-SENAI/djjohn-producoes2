<?php
require_once '../config/config.php';
requireAdmin();

$db = getDB();
$action = $_GET['action'] ?? 'list';
$eventId = $_GET['id'] ?? null;

// Criar/Editar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['create', 'edit'])) {
    $titulo = sanitize($_POST['titulo']);
    $categoria_id = (int)$_POST['categoria_id'];
    $descricao = sanitize($_POST['descricao'] ?? '');
    $data_evento = $_POST['data_evento'];
    $horario_evento = $_POST['horario_evento'];
    $local = sanitize($_POST['local']);
    $nome_cliente = sanitize($_POST['nome_cliente']);
    $email_cliente = sanitize($_POST['email_cliente'] ?? '');
    $telefone_cliente = sanitize($_POST['telefone_cliente'] ?? '');
    $numero_convidados = (int)($_POST['numero_convidados'] ?? 0);
    
    // Limpar valor do orçamento
    $valor_str = $_POST['valor_orcamento'] ?? '0';
    $valor_str = str_replace(['R$', '.', ' '], '', $valor_str);
    $valor_str = str_replace(',', '.', $valor_str);
    $valor_orcamento = floatval($valor_str);
    
    $status_evento = sanitize($_POST['status_evento']);
    
    try {
        if ($action === 'create') {
            $stmt = $db->prepare("INSERT INTO eventos (titulo, categoria_id, descricao, data_evento, horario_evento, local, nome_cliente, email_cliente, telefone_cliente, numero_convidados, valor_orcamento, status_evento, criado_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$titulo, $categoria_id, $descricao, $data_evento, $horario_evento, $local, $nome_cliente, $email_cliente, $telefone_cliente, $numero_convidados, $valor_orcamento, $status_evento, $_SESSION['user_id']]);
            redirect('admin/events.php?success=' . urlencode('Evento criado com sucesso!'));
        } else {
            $stmt = $db->prepare("UPDATE eventos SET titulo=?, categoria_id=?, descricao=?, data_evento=?, horario_evento=?, local=?, nome_cliente=?, email_cliente=?, telefone_cliente=?, numero_convidados=?, valor_orcamento=?, status_evento=? WHERE id=?");
            $stmt->execute([$titulo, $categoria_id, $descricao, $data_evento, $horario_evento, $local, $nome_cliente, $email_cliente, $telefone_cliente, $numero_convidados, $valor_orcamento, $status_evento, $eventId]);
            redirect('admin/events.php?success=' . urlencode('Evento atualizado com sucesso!'));
        }
    } catch (PDOException $e) {
        $error = 'Erro ao salvar: ' . $e->getMessage();
    }
}

// Deletar
if ($action === 'delete' && $eventId) {
    try {
        $stmt = $db->prepare("DELETE FROM eventos WHERE id = ?");
        $stmt->execute([$eventId]);
        redirect('admin/events.php?success=' . urlencode('Evento excluído com sucesso!'));
    } catch (PDOException $e) {
        $error = 'Erro ao excluir: ' . $e->getMessage();
    }
}

// Buscar evento para editar
$event = null;
if ($action === 'edit' && $eventId) {
    $stmt = $db->prepare("SELECT * FROM eventos WHERE id = ?");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch();
    
    if (!$event) {
        redirect('admin/events.php?error=' . urlencode('Evento não encontrado!'));
    }
}

// Buscar categorias
$categorias = $db->query("SELECT * FROM categorias_eventos ORDER BY nome")->fetchAll();

// Listar eventos com filtros
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';

$sql = "SELECT e.*, c.nome as categoria_nome, c.cor 
        FROM eventos e 
        LEFT JOIN categorias_eventos c ON e.categoria_id = c.id 
        WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (e.titulo LIKE ? OR e.nome_cliente LIKE ? OR e.local LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if ($category) {
    $sql .= " AND e.categoria_id = ?";
    $params[] = $category;
}

if ($status) {
    $sql .= " AND e.status_evento = ?";
    $params[] = $status;
}

$sql .= " ORDER BY e.data_evento DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll();

$pageTitle = $action === 'create' ? 'Novo Evento' : ($action === 'edit' ? 'Editar Evento' : 'Eventos');
include 'includes/header.php';
?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success" style="margin: 20px; padding: 15px; background: rgba(16, 185, 129, 0.1); border: 2px solid #10b981; border-radius: 8px; color: #10b981;">
    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?>
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="alert alert-danger" style="margin: 20px; padding: 15px; background: rgba(239, 68, 68, 0.1); border: 2px solid #ef4444; border-radius: 8px; color: #ef4444;">
    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<div class="admin-content">
    <?php if ($action === 'list'): ?>
        <div class="data-table-container">
            <div class="table-header">
                <h3><i class="fas fa-calendar-alt"></i> Eventos</h3>
                <a href="events.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Novo Evento
                </a>
            </div>

            <div style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <form method="GET" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 15px;">
                    <input type="text" name="search" class="form-control" placeholder="Buscar por título, cliente ou local..." value="<?php echo htmlspecialchars($search); ?>">
                    
                    <select name="category" class="form-control">
                        <option value="">Todas Categorias</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="status" class="form-control">
                        <option value="">Todos Status</option>
                        <option value="pendente" <?php echo $status === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                        <option value="confirmado" <?php echo $status === 'confirmado' ? 'selected' : ''; ?>>Confirmado</option>
                        <option value="concluido" <?php echo $status === 'concluido' ? 'selected' : ''; ?>>Concluído</option>
                        <option value="cancelado" <?php echo $status === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </form>
            </div>

            <?php if (!empty($events)): ?>
                <div style="padding: 15px; background: rgba(255,255,255,0.02); border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <span style="color: #999; font-size: 14px;">
                        <i class="fas fa-info-circle"></i> 
                        Mostrando <?php echo count($events); ?> evento(s)
                    </span>
                </div>
            <?php endif; ?>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Evento</th>
                        <th>Cliente</th>
                        <th>Data</th>
                        <th>Horário</th>
                        <th>Local</th>
                        <th>Status</th>
                        <th style="text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($events)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 60px; color: #666;">
                                <i class="fas fa-calendar-times" style="font-size: 48px; color: #333; margin-bottom: 15px; display: block;"></i>
                                <strong style="font-size: 18px; display: block; margin-bottom: 8px;">Nenhum evento encontrado</strong>
                                <span style="font-size: 14px;">Crie seu primeiro evento clicando no botão "Novo Evento"</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($events as $evt): ?>
                            <tr>
                                <td>
                                    <strong style="color: white; font-size: 15px;"><?php echo htmlspecialchars($evt['titulo']); ?></strong><br>
                                    <small style="color: #808080;">
                                        <span style="background: <?php echo $evt['cor'] ?? '#FF0040'; ?>20; color: <?php echo $evt['cor'] ?? '#FF0040'; ?>; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                            <?php echo htmlspecialchars($evt['categoria_nome'] ?? 'Sem categoria'); ?>
                                        </span>
                                    </small>
                                </td>
                                <td><?php echo htmlspecialchars($evt['nome_cliente']); ?></td>
                                <td><?php echo formatDate($evt['data_evento']); ?></td>
                                <td><?php echo date('H:i', strtotime($evt['horario_evento'])); ?></td>
                                <td><?php echo htmlspecialchars($evt['local']); ?></td>
                                <td>
                                    <?php
                                    $badges = [
                                        'pendente' => ['Pendente', 'warning', '#f59e0b'],
                                        'confirmado' => ['Confirmado', 'success', '#10b981'],
                                        'concluido' => ['Concluído', 'secondary', '#6366f1'],
                                        'cancelado' => ['Cancelado', 'danger', '#ef4444']
                                    ];
                                    $badge = $badges[$evt['status_evento']] ?? ['Desconhecido', 'secondary', '#666'];
                                    ?>
                                    <span class="badge badge-<?php echo $badge[1]; ?>" style="background: <?php echo $badge[2]; ?>20; color: <?php echo $badge[2]; ?>; border: 1px solid <?php echo $badge[2]; ?>40;">
                                        <?php echo $badge[0]; ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <div class="btn-group" style="display: inline-flex; gap: 5px;">
                                        <a href="events.php?action=edit&id=<?php echo $evt['id']; ?>" 
                                           class="btn btn-sm btn-icon btn-edit"
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="events.php?action=delete&id=<?php echo $evt['id']; ?>" 
                                           class="btn btn-sm btn-icon btn-delete"
                                           title="Excluir"
                                           onclick="return confirm('⚠️ Tem certeza que deseja excluir o evento:\n\n<?php echo htmlspecialchars($evt['titulo']); ?>\n\nEsta ação não pode ser desfeita!');">
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
                <h3><i class="fas fa-calendar-<?php echo $action === 'create' ? 'plus' : 'edit'; ?>"></i> 
                    <?php echo $action === 'create' ? 'Novo Evento' : 'Editar Evento'; ?>
                </h3>
                <a href="events.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>

            <form method="POST" style="padding: 30px;">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label><i class="fas fa-heading"></i> Título do Evento *</label>
                        <input type="text" name="titulo" class="form-control" required 
                               value="<?php echo htmlspecialchars($event['titulo'] ?? ''); ?>"
                               placeholder="Ex: Casamento Maria & João">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-tags"></i> Categoria *</label>
                        <select name="categoria_id" class="form-control" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo ($event && $event['categoria_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Data do Evento *</label>
                        <input type="date" name="data_evento" class="form-control" required 
                               value="<?php echo $event['data_evento'] ?? ''; ?>">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Horário *</label>
                        <input type="time" name="horario_evento" class="form-control" required 
                               value="<?php echo $event['horario_evento'] ?? ''; ?>">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-info-circle"></i> Status *</label>
                        <select name="status_evento" class="form-control" required>
                            <option value="pendente" <?php echo ($event['status_evento'] ?? '') === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                            <option value="confirmado" <?php echo ($event['status_evento'] ?? '') === 'confirmado' ? 'selected' : ''; ?>>Confirmado</option>
                            <option value="concluido" <?php echo ($event['status_evento'] ?? '') === 'concluido' ? 'selected' : ''; ?>>Concluído</option>
                            <option value="cancelado" <?php echo ($event['status_evento'] ?? '') === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Local *</label>
                    <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                        <select id="venue_select" class="form-control" style="flex: 1;">
                            <option value="">Selecione um salão cadastrado...</option>
                            <?php
                            $saloes = $db->query("SELECT id, nome, cidade FROM saloes WHERE status_salao='ativo' ORDER BY nome")->fetchAll();
                            foreach ($saloes as $salao) {
                                $salaoNome = htmlspecialchars($salao['nome']);
                                $selected = ($event && $event['local'] === $salao['nome']) ? 'selected' : '';
                                echo '<option value="' . $salaoNome . '" ' . $selected . '>' . $salaoNome . ' - ' . htmlspecialchars($salao['cidade']) . '</option>';
                            }
                            ?>
                        </select>
                        <a href="venues.php?action=create" target="_blank" class="btn btn-secondary btn-sm" style="white-space: nowrap;">
                            <i class="fas fa-plus"></i> Novo Salão
                        </a>
                    </div>
                    <input type="text" name="local" id="location_input" class="form-control" 
                           placeholder="Ou digite manualmente o local do evento..." 
                           value="<?php echo htmlspecialchars($event['local'] ?? ''); ?>" required>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Nome do Cliente *</label>
                        <input type="text" name="nome_cliente" class="form-control" required 
                               value="<?php echo htmlspecialchars($event['nome_cliente'] ?? ''); ?>"
                               placeholder="Nome completo do cliente">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email_cliente" class="form-control" 
                               value="<?php echo htmlspecialchars($event['email_cliente'] ?? ''); ?>"
                               placeholder="email@exemplo.com">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Telefone</label>
                        <input type="text" name="telefone_cliente" class="form-control" 
                               value="<?php echo htmlspecialchars($event['telefone_cliente'] ?? ''); ?>"
                               placeholder="(00) 00000-0000">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label><i class="fas fa-users"></i> Número de Convidados</label>
                        <input type="number" name="numero_convidados" class="form-control" 
                               value="<?php echo $event['numero_convidados'] ?? ''; ?>"
                               placeholder="0" min="0">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-dollar-sign"></i> Valor do Orçamento (R$)</label>
                        <input type="text" name="valor_orcamento" class="form-control" 
                               value="<?php echo $event['valor_orcamento'] ?? ''; ?>"
                               placeholder="0,00">
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Descrição / Observações</label>
                    <textarea name="descricao" class="form-control" rows="5" 
                              placeholder="Detalhes adicionais sobre o evento..."><?php echo htmlspecialchars($event['descricao'] ?? ''); ?></textarea>
                </div>

                <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.05);">
                    <a href="events.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Evento
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
    
    // Esperar DOM carregar
    document.addEventListener('DOMContentLoaded', function() {
        
        // Select de salão preenche input de local
        var venueSelect = document.getElementById('venue_select');
        var locationInput = document.getElementById('location_input');
        
        if (venueSelect && locationInput) {
            venueSelect.addEventListener('change', function() {
                if (this.value) {
                    locationInput.value = this.value;
                }
            });
        }
        
        // Auto-hide mensagens de sucesso após 5 segundos
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