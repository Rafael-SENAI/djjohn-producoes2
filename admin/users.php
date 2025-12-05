<?php
require_once '../config/config.php';
requireAdmin();

$db = getDB();
$action = $_GET['action'] ?? 'list';
$userId = $_GET['id'] ?? null;

// Criar/Editar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['create', 'edit'])) {
    $nome = sanitize($_POST['nome']);
    $email = sanitize($_POST['email']);
    $funcao = sanitize($_POST['funcao']);
    $status = sanitize($_POST['status']);
    
    try {
        if ($action === 'create') {
            $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO usuarios (nome, email, senha, funcao, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $senha, $funcao, $status]);
            redirect('admin/users.php?success=' . urlencode('Usuário criado!'));
        } else {
            if (!empty($_POST['senha'])) {
                $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE usuarios SET nome=?, email=?, senha=?, funcao=?, status=? WHERE id=?");
                $stmt->execute([$nome, $email, $senha, $funcao, $status, $userId]);
            } else {
                $stmt = $db->prepare("UPDATE usuarios SET nome=?, email=?, funcao=?, status=? WHERE id=?");
                $stmt->execute([$nome, $email, $funcao, $status, $userId]);
            }
            redirect('admin/users.php?success=' . urlencode('Usuário atualizado!'));
        }
    } catch (PDOException $e) {
        $error = 'Erro: ' . $e->getMessage();
    }
}

// Deletar
if ($action === 'delete' && $userId) {
    if ($userId != $_SESSION['user_id']) {
        try {
            $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$userId]);
            redirect('admin/users.php?success=' . urlencode('Usuário excluído!'));
        } catch (PDOException $e) {
            $error = 'Erro: ' . $e->getMessage();
        }
    } else {
        $error = 'Você não pode excluir seu próprio usuário!';
    }
}

// Buscar usuário
$user = null;
if ($action === 'edit' && $userId) {
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
}

// Listar
$users = $db->query("SELECT * FROM usuarios ORDER BY nome")->fetchAll();

$pageTitle = $action === 'create' ? 'Novo Usuário' : ($action === 'edit' ? 'Editar Usuário' : 'Usuários');
include 'includes/header.php';
?>

<div class="admin-content">
    <?php if ($action === 'list'): ?>
        <div class="data-table-container">
            <div class="table-header">
                <h3><i class="fas fa-users"></i> Usuários</h3>
                <a href="users.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Novo Usuário
                </a>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Função</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['nome']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $u['funcao'] === 'admin' ? 'danger' : 'info'; ?>">
                                    <?php echo $u['funcao'] === 'admin' ? 'Administrador' : 'Funcionário'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $u['status'] === 'ativo' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($u['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="users.php?action=edit&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-icon btn-edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <a href="users.php?action=delete&id=<?php echo $u['id']; ?>" 
                                           class="btn btn-sm btn-icon btn-delete"
                                           onclick="return confirmDelete('<?php echo htmlspecialchars($u['nome']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <div class="data-table-container" style="max-width: 600px; margin: 0 auto;">
            <div class="table-header">
                <h3><i class="fas fa-user"></i> <?php echo $action === 'create' ? 'Novo Usuário' : 'Editar Usuário'; ?></h3>
                <a href="users.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
            </div>

            <form method="POST" style="padding: 30px;">
                <div class="form-group">
                    <label>Nome Completo *</label>
                    <input type="text" name="nome" class="form-control" required value="<?php echo htmlspecialchars($user['nome'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Senha <?php echo $action === 'create' ? '*' : '(deixe em branco para manter)'; ?></label>
                    <input type="password" name="senha" class="form-control" <?php echo $action === 'create' ? 'required' : ''; ?>>
                </div>

                <div class="form-group">
                    <label>Função *</label>
                    <select name="funcao" class="form-control" required>
                        <option value="funcionario" <?php echo ($user['funcao'] ?? '') === 'funcionario' ? 'selected' : ''; ?>>Funcionário</option>
                        <option value="admin" <?php echo ($user['funcao'] ?? '') === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" class="form-control" required>
                        <option value="ativo" <?php echo ($user['status'] ?? 'ativo') === 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                        <option value="inativo" <?php echo ($user['status'] ?? '') === 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                    </select>
                </div>

                <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
                    <a href="users.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
