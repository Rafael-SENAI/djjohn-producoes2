<?php
require_once '../config/config.php';
requireAuth();

try {
    $db = getDB();
    
    $totalEvents = $db->query("SELECT COUNT(*) as total FROM eventos")->fetch()['total'];
    $eventsThisMonth = $db->query("SELECT COUNT(*) as total FROM eventos WHERE MONTH(data_evento) = MONTH(CURDATE()) AND YEAR(data_evento) = YEAR(CURDATE())")->fetch()['total'];
    $totalAttractions = $db->query("SELECT COUNT(*) as total FROM atracoes")->fetch()['total'];
    $pendingQuotes = $db->query("SELECT COUNT(*) as total FROM orcamentos WHERE status_orcamento = 'novo'")->fetch()['total'];
    
    $upcomingEvents = $db->query("SELECT e.*, c.nome as categoria_nome, c.cor 
                        FROM eventos e 
                        LEFT JOIN categorias_eventos c ON e.categoria_id = c.id 
                        WHERE e.data_evento >= CURDATE() 
                        AND e.data_evento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                        AND e.status_evento != 'cancelado'
                        ORDER BY e.data_evento ASC 
                        LIMIT 5")->fetchAll();
    
    $recentQuotes = $db->query("SELECT * FROM orcamentos ORDER BY criado_em DESC LIMIT 5")->fetchAll();
    
    $categoryStats = $db->query("SELECT c.nome, c.cor, COUNT(e.id) as total 
                        FROM categorias_eventos c 
                        LEFT JOIN eventos e ON c.id = e.categoria_id 
                        GROUP BY c.id 
                        ORDER BY total DESC")->fetchAll();
    
} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}

$pageTitle = 'Dashboard';
include 'includes/header.php';
?>

<div class="admin-content">
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(255,0,64,0.1); color: #FF0040;">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-details">
                <h3><?php echo $totalEvents; ?></h3>
                <p>Total de Eventos</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(52,152,219,0.1); color: #3498db;">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-details">
                <h3><?php echo $eventsThisMonth; ?></h3>
                <p>Eventos Este Mês</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(46,204,113,0.1); color: #2ecc71;">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-details">
                <h3><?php echo $totalAttractions; ?></h3>
                <p>Atrações</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(241,196,15,0.1); color: #f1c40f;">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div class="stat-details">
                <h3><?php echo $pendingQuotes; ?></h3>
                <p>Orçamentos Pendentes</p>
            </div>
        </div>
    </div>

    <div class="data-table-container" style="margin-bottom: 30px;">
        <div class="table-header">
            <h3><i class="fas fa-chart-pie"></i> Eventos por Categoria</h3>
        </div>
        <div style="padding: 30px;">
            <?php foreach ($categoryStats as $stat): ?>
                <div style="margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="color: white;"><?php echo htmlspecialchars($stat['nome']); ?></span>
                        <span style="color: #808080;"><?php echo $stat['total']; ?></span>
                    </div>
                    <div style="background: rgba(255,255,255,0.05); height: 8px; border-radius: 4px; overflow: hidden;">
                        <div style="width: <?php echo $totalEvents > 0 ? ($stat['total'] / $totalEvents * 100) : 0; ?>%; height: 100%; background: <?php echo $stat['cor']; ?>"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        <div class="data-table-container">
            <div class="table-header">
                <h3><i class="fas fa-calendar-alt"></i> Próximos Eventos</h3>
                <a href="events.php" class="btn btn-sm btn-primary">Ver Todos</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Evento</th>
                        <th>Data</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($upcomingEvents)): ?>
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 30px; color: #808080;">
                                Nenhum evento próximo
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($upcomingEvents as $evt): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($evt['titulo']); ?></strong><br>
                                    <small style="color: <?php echo $evt['cor']; ?>;"><?php echo $evt['categoria_nome']; ?></small>
                                </td>
                                <td><?php echo formatDate($evt['data_evento']); ?></td>
                                <td>
                                    <?php
                                    $badges = [
                                        'pendente' => ['Pendente', 'warning'],
                                        'confirmado' => ['Confirmado', 'success'],
                                        'concluido' => ['Concluído', 'secondary'],
                                        'cancelado' => ['Cancelado', 'danger']
                                    ];
                                    $badge = $badges[$evt['status_evento']] ?? ['Desconhecido', 'secondary'];
                                    ?>
                                    <span class="badge badge-<?php echo $badge[1]; ?>"><?php echo $badge[0]; ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="data-table-container">
            <div class="table-header">
                <h3><i class="fas fa-file-invoice-dollar"></i> Orçamentos Recentes</h3>
                <a href="quotes.php" class="btn btn-sm btn-primary">Ver Todos</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Tipo</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentQuotes)): ?>
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 30px; color: #808080;">
                                Nenhum orçamento
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentQuotes as $q): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($q['nome']); ?></td>
                                <td><?php echo htmlspecialchars($q['tipo_evento']); ?></td>
                                <td>
                                    <?php
                                    $statusMap = [
                                        'novo' => ['Novo', 'info'],
                                        'contatado' => ['Contatado', 'warning'],
                                        'enviado' => ['Enviado', 'secondary'],
                                        'aprovado' => ['Aprovado', 'success'],
                                        'rejeitado' => ['Rejeitado', 'danger']
                                    ];
                                    $s = $statusMap[$q['status_orcamento']] ?? ['Desconhecido', 'secondary'];
                                    ?>
                                    <span class="badge badge-<?php echo $s[1]; ?>"><?php echo $s[0]; ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
