// =====================================================
// DESABILITAR CONFIRMAÇÕES NATIVAS DO NAVEGADOR
// Adicione este código no seu arquivo JS principal ou no footer
// =====================================================

// Sobrescrever a função confirm() nativa
window.confirm = function(message) {
    // Sempre retorna true (confirma automaticamente)
    // Assim suas notificações customizadas aparecem sem o popup do Google
    return true;
};

// Opcional: Sobrescrever alert() também se quiser
window.alert = function(message) {
    // Não faz nada, bloqueia completamente os alerts nativos
    return true;
};

// =====================================================
// OU, se preferir manter os confirm mas só em casos específicos:
// =====================================================

// Função auxiliar para confirmar ações sem popup nativo
function confirmAction(callback, message = 'Tem certeza?') {
    // Executa direto sem confirmar
    if (typeof callback === 'function') {
        callback();
    }
    return true;
}

// Exemplo de uso:
// Ao invés de: if(confirm('Deseja excluir?')) { excluir(); }
// Use: confirmAction(() => { excluir(); }, 'Deseja excluir?');