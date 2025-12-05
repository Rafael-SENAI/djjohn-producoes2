<?php
require_once '../config/config.php';
requireAdmin();

$db = getDB();
$userId = $_SESSION['user_id'];
$success = '';
$error = '';

// Atualizar Perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nome = sanitize($_POST['nome']);
    $email = sanitize($_POST['email']);
    $telefone = sanitize($_POST['telefone'] ?? '');
    $cargo = sanitize($_POST['cargo'] ?? '');
    
    // Upload de foto
    $foto_perfil = null;
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['foto_perfil']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed) && $_FILES['foto_perfil']['size'] <= 5000000) {
            $newname = 'perfil_' . $userId . '_' . time() . '.' . $ext;
            $upload_path = '../uploads/perfis/';
            
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0777, true);
            }
            
            if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $upload_path . $newname)) {
                $foto_perfil = 'uploads/perfis/' . $newname;
            }
        }
    }
    
    try {
        if ($foto_perfil) {
            $stmt = $db->prepare("UPDATE usuarios SET nome=?, email=?, telefone=?, cargo=?, foto_perfil=? WHERE id=?");
            $stmt->execute([$nome, $email, $telefone, $cargo, $foto_perfil, $userId]);
        } else {
            $stmt = $db->prepare("UPDATE usuarios SET nome=?, email=?, telefone=?, cargo=? WHERE id=?");
            $stmt->execute([$nome, $email, $telefone, $cargo, $userId]);
        }
        
        $_SESSION['user_name'] = $nome;
        $success = 'Perfil atualizado com sucesso!';
    } catch (PDOException $e) {
        $error = 'Erro ao atualizar perfil: ' . $e->getMessage();
    }
}

// Alterar Senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $senha_atual = $_POST['senha_atual'];
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    
    // Buscar senha atual
    $stmt = $db->prepare("SELECT senha FROM usuarios WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (password_verify($senha_atual, $user['senha'])) {
        if ($nova_senha === $confirmar_senha) {
            if (strlen($nova_senha) >= 6) {
                $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE usuarios SET senha=? WHERE id=?");
                $stmt->execute([$hash, $userId]);
                $success = 'Senha alterada com sucesso!';
            } else {
                $error = 'A nova senha deve ter no mínimo 6 caracteres!';
            }
        } else {
            $error = 'As senhas não conferem!';
        }
    } else {
        $error = 'Senha atual incorreta!';
    }
}

// Buscar dados do usuário
$stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Buscar estatísticas
$stats = [
    'eventos_criados' => $db->query("SELECT COUNT(*) as total FROM eventos WHERE criado_por = $userId")->fetch()['total'] ?? 0,
    'ultimo_acesso' => $user['ultimo_acesso'] ?? date('Y-m-d H:i:s')
];

$pageTitle = 'Meu Perfil';
include 'includes/header.php';
?>

<?php if ($success): ?>
<div class="alert alert-success" style="margin: 20px; padding: 15px; background: rgba(16, 185, 129, 0.1); border: 2px solid #10b981; border-radius: 8px; color: #10b981;">
    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger" style="margin: 20px; padding: 15px; background: rgba(239, 68, 68, 0.1); border: 2px solid #ef4444; border-radius: 8px; color: #ef4444;">
    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<div class="admin-content" style="max-width: 1200px; margin: 0 auto;">
    <div style="display: grid; grid-template-columns: 350px 1fr; gap: 25px;">
        
        <!-- SIDEBAR PERFIL -->
        <div class="data-table-container" style="padding: 0; height: fit-content;">
            <div style="background: linear-gradient(135deg, #FF0040, #cc0033); padding: 30px; text-align: center; border-radius: 24px 24px 0 0;">
                <div style="width: 120px; height: 120px; margin: 0 auto 20px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 4px solid rgba(255,255,255,0.3);">
                    <?php if (!empty($user['foto_perfil']) && file_exists('../' . $user['foto_perfil'])): ?>
                        <img src="../<?php echo htmlspecialchars($user['foto_perfil']); ?>" alt="Perfil" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <span style="font-size: 48px; color: #FF0040; font-weight: 900;">
                            <?php echo strtoupper(substr($user['nome'], 0, 2)); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <h2 style="margin: 0 0 5px 0; color: white; font-size: 22px; font-weight: 900;">
                    <?php echo htmlspecialchars($user['nome']); ?>
                </h2>
                <p style="margin: 0; color: rgba(255,255,255,0.8); font-size: 14px;">
                    <?php echo htmlspecialchars($user['cargo'] ?: 'Administrador'); ?>
                </p>
            </div>
            
            <div style="padding: 25px;">
                <div style="margin-bottom: 20px;">
                    <div style="color: #666; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">
                        <i class="fas fa-envelope"></i> Email
                    </div>
                    <div style="color: white; font-weight: 600;">
                        <?php echo htmlspecialchars($user['email']); ?>
                    </div>
                </div>
                
                <?php if ($user['telefone']): ?>
                <div style="margin-bottom: 20px;">
                    <div style="color: #666; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">
                        <i class="fas fa-phone"></i> Telefone
                    </div>
                    <div style="color: white; font-weight: 600;">
                        <?php echo htmlspecialchars($user['telefone']); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 25px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.05);">
                    <div style="text-align: center; padding: 15px; background: rgba(255,0,64,0.05); border-radius: 12px; border: 1px solid rgba(255,0,64,0.1);">
                        <div style="font-size: 28px; font-weight: 900; color: #FF0040;">
                            <?php echo $stats['eventos_criados']; ?>
                        </div>
                        <div style="font-size: 11px; color: #666; margin-top: 5px;">
                            Eventos Criados
                        </div>
                    </div>
                    
                    <div style="text-align: center; padding: 15px; background: rgba(255,0,64,0.05); border-radius: 12px; border: 1px solid rgba(255,0,64,0.1);">
                        <div style="font-size: 12px; font-weight: 900; color: #FF0040;">
                            <?php echo date('d/m/Y', strtotime($stats['ultimo_acesso'])); ?>
                        </div>
                        <div style="font-size: 11px; color: #666; margin-top: 5px;">
                            Último Acesso
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- FORMULÁRIOS -->
        <div style="display: flex; flex-direction: column; gap: 25px;">
            
            <!-- EDITAR PERFIL -->
            <div class="data-table-container">
                <div class="table-header">
                    <h3><i class="fas fa-user-edit"></i> Editar Perfil</h3>
                </div>
                
                <form method="POST" enctype="multipart/form-data" style="padding: 30px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Nome Completo *</label>
                            <input type="text" name="nome" class="form-control" required 
                                   value="<?php echo htmlspecialchars($user['nome']); ?>"
                                   placeholder="Seu nome completo">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> Email *</label>
                            <input type="email" name="email" class="form-control" required 
                                   value="<?php echo htmlspecialchars($user['email']); ?>"
                                   placeholder="seu@email.com">
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> Telefone/WhatsApp</label>
                            <input type="text" name="telefone" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['telefone'] ?? ''); ?>"
                                   placeholder="(00) 00000-0000">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-briefcase"></i> Cargo/Função</label>
                            <input type="text" name="cargo" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['cargo'] ?? ''); ?>"
                                   placeholder="Ex: DJ, Coordenador, Gerente">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-camera"></i> Foto de Perfil</label>
                        <input type="file" name="foto_perfil" class="form-control" accept="image/*">
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 5MB
                        </small>
                    </div>
                    
                    <div style="text-align: right; margin-top: 25px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.05);">
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- ALTERAR SENHA -->
            <div class="data-table-container">
                <div class="table-header">
                    <h3><i class="fas fa-lock"></i> Alterar Senha</h3>
                </div>
                
                <form method="POST" style="padding: 30px;">
                    <div class="form-group">
                        <label><i class="fas fa-key"></i> Senha Atual *</label>
                        <input type="password" name="senha_atual" class="form-control" required 
                               placeholder="Digite sua senha atual">
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> Nova Senha *</label>
                            <input type="password" name="nova_senha" class="form-control" required 
                                   placeholder="Mínimo 6 caracteres"
                                   minlength="6">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> Confirmar Nova Senha *</label>
                            <input type="password" name="confirmar_senha" class="form-control" required 
                                   placeholder="Digite novamente"
                                   minlength="6">
                        </div>
                    </div>
                    
                    <div style="background: rgba(255,255,0,0.05); border: 1px solid rgba(255,255,0,0.2); border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                        <div style="color: #f59e0b; font-size: 13px;">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Dica de Segurança:</strong> Use uma senha forte com letras, números e símbolos.
                        </div>
                    </div>
                    
                    <div style="text-align: right; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.05);">
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="fas fa-shield-alt"></i> Alterar Senha
                        </button>
                    </div>
                </form>
            </div>
            
        </div>
    </div>
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
        
        // Validar senhas iguais
        var form = document.querySelector('form[method="POST"]');
        if (form) {
            var novaSenha = document.querySelector('input[name="nova_senha"]');
            var confirmarSenha = document.querySelector('input[name="confirmar_senha"]');
            
            if (novaSenha && confirmarSenha) {
                confirmarSenha.addEventListener('input', function() {
                    if (novaSenha.value !== confirmarSenha.value) {
                        confirmarSenha.setCustomValidity('As senhas não conferem!');
                    } else {
                        confirmarSenha.setCustomValidity('');
                    }
                });
            }
        }
    });
})();
</script>

<?php include 'includes/footer.php'; ?>