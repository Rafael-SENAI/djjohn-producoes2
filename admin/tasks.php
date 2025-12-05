<?php
require_once '../config/config.php';
requireAuth();
$pageTitle = 'Tarefas';
include 'includes/header.php';
?>
<div class="admin-content">
    <div class="data-table-container">
        <div class="table-header">
            <h3><i class="fas fa-tasks"></i> Minhas Tarefas</h3>
            <button class="btn btn-primary"><i class="fas fa-plus"></i> Nova Tarefa</button>
        </div>
        <div style="padding: 40px; text-align: center; color: #808080;">
            <i class="fas fa-tasks" style="font-size: 80px; margin-bottom: 20px; display: block; color: #FF0040;"></i>
            <h3 style="color: white; margin-bottom: 15px;">Nenhuma tarefa pendente</h3>
            <p>Você está em dia com todas as suas tarefas!</p>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
