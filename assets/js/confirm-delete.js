// Interceptar todos os cliques de delete
document.addEventListener('DOMContentLoaded', function() {
    document.body.addEventListener('click', function(e) {
        const deleteLink = e.target.closest('a.btn-delete, a[href*="action=delete"]');
        
        if (deleteLink && deleteLink.hasAttribute('onclick')) {
            e.preventDefault();
            
            // Extrair nome do item
            const onclickAttr = deleteLink.getAttribute('onclick');
            const match = onclickAttr.match(/confirmDelete\(['"](.+?)['"]\)/);
            const itemName = match ? match[1] : 'este item';
            
            showConfirm(
                `Tem certeza que deseja excluir "<strong style="color: #FF0040;">${itemName}</strong>"?<br><br><small>Esta ação não pode ser desfeita.</small>`,
                () => {
                    window.location.href = deleteLink.href;
                },
                'Confirmar Exclusão'
            );
        }
    });
});
