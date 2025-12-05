/* ========================================
   SISTEMA DE NOTIFICACOES JS
   ======================================== */

// Criar container de notificações se não existir
function createNotificationContainer() {
    let container = document.getElementById('notification-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notification-container';
        container.className = 'notification-container';
        document.body.appendChild(container);
    }
    return container;
}

// Função principal para mostrar notificação
function showNotification(message, type = 'success', duration = 5000) {
    const container = createNotificationContainer();
    
    // Configurações por tipo
    const config = {
        success: {
            icon: 'fa-check-circle',
            title: 'Sucesso!'
        },
        error: {
            icon: 'fa-times-circle',
            title: 'Erro!'
        },
        warning: {
            icon: 'fa-exclamation-triangle',
            title: 'Atenção!'
        },
        info: {
            icon: 'fa-info-circle',
            title: 'Informação'
        }
    };
    
    const settings = config[type] || config.success;
    
    // Criar notificação
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-icon">
            <i class="fas ${settings.icon}"></i>
        </div>
        <div class="notification-content">
            <div class="notification-title">${settings.title}</div>
            <div class="notification-message">${message}</div>
        </div>
        <button class="notification-close" onclick="closeNotification(this)">
            <i class="fas fa-times"></i>
        </button>
        <div class="notification-progress"></div>
    `;
    
    container.appendChild(notification);
    
    // Auto-fechar após duração
    if (duration > 0) {
        setTimeout(() => {
            closeNotification(notification.querySelector('.notification-close'));
        }, duration);
    }
}

// Fechar notificação
function closeNotification(button) {
    const notification = button.closest('.notification');
    notification.classList.add('closing');
    setTimeout(() => {
        notification.remove();
    }, 300);
}

// Funções de atalho
function showSuccess(message) {
    showNotification(message, 'success');
}

function showError(message) {
    showNotification(message, 'error');
}

function showWarning(message) {
    showNotification(message, 'warning');
}

function showInfo(message) {
    showNotification(message, 'info');
}

// Detectar mensagens da URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('success')) {
        showSuccess(urlParams.get('success'));
        // Limpar URL
        const newUrl = window.location.pathname;
        window.history.replaceState({}, '', newUrl);
    }
    
    if (urlParams.has('error')) {
        showError(urlParams.get('error'));
        const newUrl = window.location.pathname;
        window.history.replaceState({}, '', newUrl);
    }
});

// Substituir confirmações padrão
function confirmDelete(name) {
    return confirm(`Tem certeza que deseja excluir "${name}"?\n\nEsta ação não pode ser desfeita.`);
}

/* ========================================
   MODAL DE CONFIRMACAO CUSTOMIZADO
   ======================================== */

function showConfirm(message, onConfirm, title = 'Confirmar ação') {
    // Remover modal existente se houver
    const existingModal = document.getElementById('custom-confirm-modal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Criar overlay
    const overlay = document.createElement('div');
    overlay.id = 'custom-confirm-modal';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.8);
        backdrop-filter: blur(10px);
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.2s ease-out;
    `;
    
    // Criar modal
    const modal = document.createElement('div');
    modal.style.cssText = `
        background: rgba(26,26,26,0.98);
        border: 1px solid rgba(255,0,64,0.3);
        border-radius: 20px;
        padding: 35px;
        max-width: 450px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(255,0,64,0.3);
        animation: scaleIn 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    `;
    
    modal.innerHTML = `
        <div style="text-align: center; margin-bottom: 25px;">
            <div style="width: 70px; height: 70px; margin: 0 auto 20px; border-radius: 50%; background: rgba(255,0,64,0.15); display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-exclamation-triangle" style="font-size: 32px; color: #FF0040;"></i>
            </div>
            <h3 style="color: white; font-size: 22px; font-weight: 700; margin-bottom: 12px;">${title}</h3>
            <p style="color: #B0B0B0; font-size: 15px; line-height: 1.6;">${message}</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <button id="confirm-cancel" style="flex: 1; padding: 14px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.05); color: white; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button id="confirm-ok" style="flex: 1; padding: 14px; border-radius: 12px; border: none; background: linear-gradient(135deg, #FF0040 0%, #CC0033 100%); color: white; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 15px rgba(255,0,64,0.4);">
                <i class="fas fa-check"></i> Confirmar
            </button>
        </div>
    `;
    
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    // Adicionar estilos de animação
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes scaleIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        #confirm-cancel:hover {
            background: rgba(255,255,255,0.1) !important;
        }
        #confirm-ok:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255,0,64,0.6) !important;
        }
    `;
    document.head.appendChild(style);
    
    // Event listeners
    document.getElementById('confirm-cancel').onclick = () => {
        overlay.style.animation = 'fadeOut 0.2s ease-out';
        setTimeout(() => overlay.remove(), 200);
    };
    
    document.getElementById('confirm-ok').onclick = () => {
        overlay.remove();
        onConfirm();
    };
    
    // Fechar com ESC
    document.addEventListener('keydown', function escHandler(e) {
        if (e.key === 'Escape') {
            overlay.remove();
            document.removeEventListener('keydown', escHandler);
        }
    });
    
    // Fechar clicando fora
    overlay.onclick = (e) => {
        if (e.target === overlay) {
            overlay.remove();
        }
    };
}

// Substituir confirmDelete
function confirmDelete(name) {
    return new Promise((resolve) => {
        showConfirm(
            `Tem certeza que deseja excluir "<strong style="color: #FF0040;">${name}</strong>"?<br><br><small>Esta ação não pode ser desfeita.</small>`,
            () => resolve(true),
            'Confirmar Exclusão'
        );
    });
}
