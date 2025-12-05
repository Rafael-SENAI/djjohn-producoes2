<?php
// Suprimir TODOS os erros e warnings
error_reporting(0);
ini_set('display_errors', 0);
ob_start(); // Iniciar buffer de saída

require_once '../config/config.php';
requireAdmin();

$page_title = 'Calendário de Eventos';

// Buscar estatísticas
$db = getDB();
$stats = [
    'total_mes' => $db->query("SELECT COUNT(*) as total FROM eventos WHERE MONTH(data_evento) = MONTH(CURRENT_DATE) AND YEAR(data_evento) = YEAR(CURRENT_DATE)")->fetch()['total'] ?? 0,
    'confirmados' => $db->query("SELECT COUNT(*) as total FROM eventos WHERE status_evento = 'confirmado'")->fetch()['total'] ?? 0,
    'pendentes' => $db->query("SELECT COUNT(*) as total FROM eventos WHERE status_evento = 'pendente'")->fetch()['total'] ?? 0,
    'proximos_7dias' => $db->query("SELECT COUNT(*) as total FROM eventos WHERE data_evento BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)")->fetch()['total'] ?? 0
];

// Limpar qualquer output gerado até aqui
$output_buffer = ob_get_clean();

include 'includes/header.php';
?>

<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />

<style>
:root {
    --cor-primaria: #FF0040;
    --cor-secundaria: #cc0033;
    --cor-fundo: #0a0a0a;
    --cor-card: #1a1a1a;
}

.page-header {
    margin-bottom: 20px;
}

.page-header h1 {
    font-size: 32px;
    font-weight: 900;
    color: white;
    margin-bottom: 8px;
}

.page-header p {
    color: #999;
    font-size: 14px;
}

/* Stats Cards - COMPACTOS */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    margin-bottom: 25px;
}

.stat-card {
    background: linear-gradient(135deg, rgba(255,0,64,0.15) 0%, rgba(255,0,64,0.05) 100%);
    border: 2px solid rgba(255,0,64,0.2);
    border-radius: 12px;
    padding: 18px;
    text-align: center;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    border-color: var(--cor-primaria);
    box-shadow: 0 8px 25px rgba(255,0,64,0.3);
}

.stat-icon {
    font-size: 24px;
    color: var(--cor-primaria);
    margin-bottom: 10px;
}

.stat-number {
    font-size: 36px;
    font-weight: 900;
    color: var(--cor-primaria);
    line-height: 1;
    margin-bottom: 8px;
}

.stat-label {
    font-size: 11px;
    color: #999;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 700;
}

/* Calendar Container - COMPACTO */
.calendar-container {
    background: var(--cor-card);
    border-radius: 16px;
    padding: 20px;
    border: 1px solid rgba(255,255,255,0.08);
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.calendar-actions {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 10px 20px;
    border-radius: 10px;
    border: none;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
    color: white;
    box-shadow: 0 4px 15px rgba(255,0,64,0.4);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255,0,64,0.5);
}

.btn-secondary {
    background: rgba(255,255,255,0.05);
    color: #999;
    border: 2px solid rgba(255,255,255,0.1);
}

.btn-secondary:hover {
    background: rgba(255,255,255,0.1);
    color: white;
}

/* Filtros - COMPACTOS */
.calendar-filters {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 15px;
}

.filter-btn {
    padding: 8px 16px;
    border-radius: 20px;
    border: 2px solid rgba(255,255,255,0.1);
    background: transparent;
    color: #999;
    cursor: pointer;
    font-weight: 600;
    font-size: 12px;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.filter-btn:hover {
    border-color: var(--cor-primaria);
    color: var(--cor-primaria);
    background: rgba(255,0,64,0.05);
}

.filter-btn.active {
    background: var(--cor-primaria);
    border-color: var(--cor-primaria);
    color: white;
}

.filter-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}

/* FullCalendar - COMPACTO E FUNCIONAL */
#calendar {
    border-radius: 12px;
    overflow: hidden;
}

.fc {
    background: var(--cor-fundo);
    font-size: 13px;
}

.fc-theme-standard .fc-scrollgrid {
    border-color: rgba(255,255,255,0.08);
}

.fc-theme-standard td,
.fc-theme-standard th {
    border-color: rgba(255,255,255,0.05);
}

/* Botões do calendário */
.fc .fc-button {
    background: var(--cor-primaria);
    border-color: var(--cor-primaria);
    text-transform: uppercase;
    font-weight: 700;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 12px;
}

.fc .fc-button:hover {
    background: var(--cor-secundaria);
    border-color: var(--cor-secundaria);
}

.fc .fc-button-primary:not(:disabled).fc-button-active {
    background: #990029;
    border-color: #990029;
}

/* Header das colunas - COMPACTO */
.fc-col-header-cell {
    background: var(--cor-card) !important;
    color: var(--cor-primaria) !important;
    font-weight: 700 !important;
    text-transform: uppercase;
    padding: 12px 4px !important;
    font-size: 11px !important;
    letter-spacing: 0.5px;
}

/* Células dos dias - COMPACTAS */
.fc-daygrid-day {
    padding: 4px !important;
}

.fc-daygrid-day-frame {
    padding: 4px !important;
    min-height: 80px !important;
}

.fc-daygrid-day-number {
    color: white !important;
    font-weight: 600;
    font-size: 13px;
    padding: 4px !important;
}

.fc-daygrid-day-top {
    padding: 4px;
}

/* Dia de hoje */
.fc-daygrid-day.fc-day-today {
    background: rgba(255,0,64,0.1) !important;
}

.fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
    background: var(--cor-primaria);
    border-radius: 50%;
    width: 26px;
    height: 26px;
    display: flex !important;
    align-items: center;
    justify-content: center;
}

/* Eventos - VISÍVEIS E CLICÁVEIS */
.fc-event {
    border-radius: 6px !important;
    border: none !important;
    border-left: 3px solid rgba(255,255,255,0.3) !important;
    padding: 4px 6px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    margin: 2px 3px !important;
    transition: all 0.2s ease !important;
    font-size: 11px !important;
    line-height: 1.3 !important;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.fc-event:hover {
    transform: scale(1.05);
    box-shadow: 0 3px 10px rgba(0,0,0,0.4);
    z-index: 10;
}

.fc-event-title {
    font-weight: 700;
}

.fc-event-time {
    font-size: 10px;
    opacity: 0.9;
}

/* Cores por Status - BEM VISÍVEIS */
.event-confirmado {
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: white !important;
    border-left-color: #047857 !important;
}

.event-pendente {
    background: linear-gradient(135deg, #f59e0b, #d97706) !important;
    color: white !important;
    border-left-color: #b45309 !important;
}

.event-concluido {
    background: linear-gradient(135deg, #6366f1, #4f46e5) !important;
    color: white !important;
    border-left-color: #4338ca !important;
    opacity: 0.8;
}

.event-cancelado {
    background: linear-gradient(135deg, #ef4444, #dc2626) !important;
    color: white !important;
    border-left-color: #b91c1c !important;
    opacity: 0.6;
    text-decoration: line-through;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.92);
    backdrop-filter: blur(12px);
}

.modal-content {
    background: var(--cor-card);
    margin: 3% auto;
    padding: 0;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 20px;
    width: 90%;
    max-width: 700px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
    padding: 25px 30px;
    border-radius: 20px 20px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 22px;
    color: white;
    font-weight: 900;
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-close {
    background: rgba(255,255,255,0.15);
    border: 2px solid rgba(255,255,255,0.2);
    color: white;
    font-size: 22px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.modal-close:hover {
    background: rgba(255,255,255,0.25);
    transform: rotate(90deg);
}

.modal-body {
    padding: 30px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    display: block;
    color: var(--cor-primaria);
    font-weight: 700;
    margin-bottom: 8px;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    background: var(--cor-fundo);
    border: 2px solid rgba(255,255,255,0.1);
    border-radius: 10px;
    color: white;
    font-size: 14px;
    transition: all 0.3s ease;
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: var(--cor-primaria);
    box-shadow: 0 0 0 3px rgba(255,0,64,0.1);
}

textarea.form-control {
    min-height: 100px;
    resize: vertical;
}

.modal-footer {
    padding: 20px 30px;
    background: rgba(0,0,0,0.2);
    border-top: 1px solid rgba(255,255,255,0.05);
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    border-radius: 0 0 20px 20px;
}

.btn-cancel {
    background: transparent;
    color: #999;
    border: 2px solid rgba(255,255,255,0.1);
}

.btn-cancel:hover {
    background: rgba(255,255,255,0.05);
    color: white;
}

/* Responsivo */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .fc-daygrid-day-frame {
        min-height: 60px !important;
    }
    
    .fc-event {
        font-size: 10px !important;
        padding: 3px 5px !important;
    }
}
</style>

<div class="page-header">
    <h1><i class="fas fa-calendar-alt"></i> Calendário de Eventos</h1>
    <p>Gerencie todos os seus eventos</p>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
        <div class="stat-number"><?php echo $stats['total_mes']; ?></div>
        <div class="stat-label">Este Mês</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-number"><?php echo $stats['confirmados']; ?></div>
        <div class="stat-label">Confirmados</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-number"><?php echo $stats['pendentes']; ?></div>
        <div class="stat-label">Pendentes</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-fire"></i></div>
        <div class="stat-number"><?php echo $stats['proximos_7dias']; ?></div>
        <div class="stat-label">Próximos 7 Dias</div>
    </div>
</div>

<!-- Calendar Container -->
<div class="calendar-container">
    <div class="calendar-header">
        <div class="calendar-filters">
            <button class="filter-btn active" data-filter="all">
                <i class="fas fa-th"></i> Todos
            </button>
            <button class="filter-btn" data-filter="confirmado">
                <span class="filter-dot" style="background: #10b981;"></span> Confirmados
            </button>
            <button class="filter-btn" data-filter="pendente">
                <span class="filter-dot" style="background: #f59e0b;"></span> Pendentes
            </button>
            <button class="filter-btn" data-filter="concluido">
                <span class="filter-dot" style="background: #6366f1;"></span> Concluídos
            </button>
        </div>
        
        <div class="calendar-actions">
            <button class="btn btn-secondary" onclick="window.location.href='events.php'">
                <i class="fas fa-list"></i> Lista
            </button>
        </div>
    </div>
    
    <div id="calendar"></div>
</div>

<!-- Modal -->
<div id="eventModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-calendar-plus"></i> <span id="modalTitle">Novo Evento</span></h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="eventForm" onsubmit="saveEvent(event)">
            <div class="modal-body">
                <input type="hidden" id="event_id" name="id">
                
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label><i class="fas fa-heading"></i> Título *</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required placeholder="Ex: Casamento Maria & João">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Categoria *</label>
                        <select class="form-control" id="categoria_id" name="categoria_id" required>
                            <option value="">Selecione...</option>
                            <?php
                            $categorias = $db->query("SELECT * FROM categorias_eventos ORDER BY nome")->fetchAll();
                            foreach($categorias as $cat):
                            ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo $cat['nome']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-info-circle"></i> Status *</label>
                        <select class="form-control" id="status_evento" name="status_evento" required>
                            <option value="pendente">Pendente</option>
                            <option value="confirmado">Confirmado</option>
                            <option value="concluido">Concluído</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Data *</label>
                        <input type="date" class="form-control" id="data_evento" name="data_evento" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Horário Início *</label>
                        <input type="time" class="form-control" id="horario_evento" name="horario_evento" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Horário Fim</label>
                        <input type="time" class="form-control" id="horario_fim" name="horario_fim">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Local</label>
                        <input type="text" class="form-control" id="local" name="local" placeholder="Nome do local">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Cliente</label>
                        <input type="text" class="form-control" id="nome_cliente" name="nome_cliente" placeholder="Nome do cliente">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Telefone</label>
                        <input type="tel" class="form-control" id="telefone_cliente" name="telefone_cliente" placeholder="(00) 00000-0000">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-users"></i> Nº Convidados</label>
                        <input type="number" class="form-control" id="numero_convidados" name="numero_convidados" placeholder="100">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-dollar-sign"></i> Valor</label>
                        <input type="text" class="form-control" id="valor_orcamento" name="valor_orcamento" placeholder="R$ 0,00">
                    </div>
                    
                    <div class="form-group full-width">
                        <label><i class="fas fa-align-left"></i> Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" placeholder="Detalhes..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" onclick="closeModal()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/pt-br.global.min.js'></script>

<script>
let calendar;
let currentFilter = 'all';

function getStatusColor(status) {
    const cores = {
        'confirmado': '#10b981',
        'pendente': '#f59e0b',
        'concluido': '#6366f1',
        'cancelado': '#ef4444'
    };
    return cores[status] || '#999';
}

document.addEventListener('DOMContentLoaded', function() {
    initCalendar();
    initFilters();
});

function initCalendar() {
    const calendarEl = document.getElementById('calendar');
    
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'pt-br',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana',
            list: 'Lista'
        },
        height: 'auto',
        contentHeight: 650,
        events: function(info, successCallback, failureCallback) {
            console.log('🔄 Buscando eventos...');
            
            fetch('includes/get_eventos.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro HTTP: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('✅ Eventos recebidos:', data);
                    console.log('📊 Total:', data.length);
                    
                    if (!data || data.length === 0) {
                        console.warn('⚠️ Nenhum evento no banco de dados');
                        successCallback([]);
                        return;
                    }
                    
                    const events = data.map((evento, index) => {
                        const dataInicio = evento.data_evento + 'T' + (evento.horario_evento || '00:00:00');
                        const dataFim = evento.horario_fim ? evento.data_evento + 'T' + evento.horario_fim : null;
                        
                        console.log(`📅 Evento ${index + 1}:`, {
                            titulo: evento.titulo,
                            data: evento.data_evento,
                            horario: evento.horario_evento,
                            status: evento.status_evento
                        });
                        
                        return {
                            id: evento.id,
                            title: evento.titulo,
                            start: dataInicio,
                            end: dataFim,
                            className: 'event-' + evento.status_evento,
                            backgroundColor: getStatusColor(evento.status_evento),
                            borderColor: getStatusColor(evento.status_evento),
                            textColor: '#ffffff',
                            extendedProps: {
                                status: evento.status_evento,
                                cliente: evento.nome_cliente,
                                local: evento.local,
                                categoria: evento.categoria,
                                valor: evento.valor_orcamento
                            }
                        };
                    });
                    
                    console.log('✨ Eventos processados para calendário:', events.length);
                    successCallback(filterEvents(events));
                })
                .catch(error => {
                    console.error('❌ Erro ao carregar eventos:', error);
                    alert('Erro ao carregar eventos. Veja o console (F12).');
                    failureCallback(error);
                });
        },
        eventClick: function(info) {
            viewEvent(info.event.id);
        },
        eventDidMount: function(info) {
            console.log('🎨 Evento renderizado:', info.event.title);
        }
    });
    
    calendar.render();
    console.log('📆 Calendário inicializado');
}

function filterEvents(events) {
    if (currentFilter === 'all') {
        return events;
    }
    return events.filter(e => e.extendedProps.status === currentFilter);
}

function initFilters() {
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            calendar.refetchEvents();
        });
    });
}

function openNewEventModal(date = null) {
    document.getElementById('modalTitle').textContent = 'Novo Evento';
    document.getElementById('eventForm').reset();
    document.getElementById('event_id').value = '';
    if (date) {
        document.getElementById('data_evento').value = date;
    }
    document.getElementById('eventModal').style.display = 'block';
}

function viewEvent(eventId) {
    fetch(`includes/get_evento.php?id=${eventId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const e = data.evento;
                document.getElementById('modalTitle').textContent = 'Editar Evento';
                document.getElementById('event_id').value = e.id;
                document.getElementById('titulo').value = e.titulo || '';
                document.getElementById('categoria_id').value = e.categoria_id || '';
                document.getElementById('status_evento').value = e.status_evento || '';
                document.getElementById('data_evento').value = e.data_evento || '';
                document.getElementById('horario_evento').value = e.horario_evento || '';
                document.getElementById('horario_fim').value = e.horario_fim || '';
                document.getElementById('local').value = e.local || '';
                document.getElementById('nome_cliente').value = e.nome_cliente || '';
                document.getElementById('telefone_cliente').value = e.telefone_cliente || '';
                document.getElementById('numero_convidados').value = e.numero_convidados || '';
                document.getElementById('valor_orcamento').value = e.valor_orcamento || '';
                document.getElementById('descricao').value = e.descricao || '';
                
                document.getElementById('eventModal').style.display = 'block';
            }
        })
        .catch(error => console.error('Erro:', error));
}

function saveEvent(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    fetch('includes/save_evento.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal();
            calendar.refetchEvents();
            alert('✅ Evento salvo com sucesso!');
        } else {
            alert('❌ Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('❌ Erro ao salvar evento');
    });
}

function closeModal() {
    document.getElementById('eventModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('eventModal');
    if (event.target == modal) {
        closeModal();
    }
}

// Desabilitar confirm nativo
window.confirm = function() { return true; };
</script>

<?php include 'includes/footer.php'; ?>