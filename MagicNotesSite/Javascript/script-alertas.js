document.addEventListener('DOMContentLoaded', function() {
  // Toggle entre Alertas e Disciplinas
  const toggleButtons = document.querySelectorAll('.toggle-btn');
  const AlertasContent = document.getElementById('Alertas-content');
  const AlertasList = document.getElementById('Alertas-list');
  const disciplinesList = document.getElementById('disciplines-list');

  toggleButtons.forEach(button => {
    button.addEventListener('click', function() {
      const targetTab = this.getAttribute('data-tab');
      
      // Atualizar botões ativos
      toggleButtons.forEach(btn => btn.classList.remove('active'));
      this.classList.add('active');
      
      // Mostrar/ocultar conteúdo
      if (targetTab === 'Alertas') {
        AlertasContent.style.display = 'grid';
        AlertasList.style.display = 'block';
        disciplinesList.style.display = 'none';
      } else {
        AlertasContent.style.display = 'none';
        AlertasList.style.display = 'none';
        disciplinesList.style.display = 'block';
      }
    });
  });

  // Efeitos visuais nos cards de status
  const statusCards = document.querySelectorAll('.status-card');
  statusCards.forEach(card => {
    card.addEventListener('click', function() {
      this.style.transform = 'scale(0.98)';
      setTimeout(() => {
        this.style.transform = '';
      }, 150);
    });
  });

  // Efeitos nas disciplinas
  const disciplinaItems = document.querySelectorAll('.disciplina-item');
  disciplinaItems.forEach(item => {
    item.addEventListener('click', function() {
      this.style.backgroundColor = '#f8f9fa';
      setTimeout(() => {
        this.style.backgroundColor = '';
      }, 300);
    });
  });

  // Contadores dinâmicos (pode ser expandido com dados reais)
  function updateCounters() {
    const pendingItems = document.querySelectorAll('.status-pending').length;
    const overdueItems = document.querySelectorAll('.status-overdue').length;
    
    // Atualizar contadores nos cards de status
    const statusCounts = document.querySelectorAll('.status-count');
    if (statusCounts.length >= 4) {
      statusCounts[1].textContent = pendingItems; // Pending
      statusCounts[3].textContent = overdueItems; // Overdue
    }
  }

  // Inicializar contadores
  updateCounters();
});