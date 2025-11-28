/**
 * Script da Área do Aluno - Magic Notes
 * Integrado com sistema de autenticação
 */

document.addEventListener('DOMContentLoaded', () => {
  const menuToggle = document.querySelector('.menu-toggle');
  const sidebar = document.querySelector('.sidebar');
  const mobileOverlay = document.createElement('div');
  mobileOverlay.className = 'mobile-overlay';
  document.body.appendChild(mobileOverlay);

  // Verificar se usuário está logado
  checkAuthentication();

  // Menu toggle com overlay
  if (menuToggle) {
    menuToggle.addEventListener('click', (e) => {
      e.preventDefault();
      toggleSidebar();
    });
  }

  // Fechar menu ao clicar no overlay
  mobileOverlay.addEventListener('click', () => {
    closeSidebar();
  });

  // Botões dos cards - navegação com feedback visual
  const cardButtons = document.querySelectorAll('.card-btn');
  cardButtons.forEach(button => {
    button.addEventListener('click', function() {
      // Efeito visual ao clicar
      this.style.transform = 'scale(0.95)';
      setTimeout(() => {
        this.style.transform = '';
      }, 150);
    });
  });

  // Fechar menu com ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeSidebar();
    }
  });

  // Ajustar layout ao redimensionar
  window.addEventListener('resize', handleResize);

  // Adicionar listeners de navegação
  setupNavigationListeners();

  // Funções auxiliares
  function toggleSidebar() {
    sidebar.classList.toggle('active');
    mobileOverlay.classList.toggle('active');
    document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
  }

  function closeSidebar() {
    sidebar.classList.remove('active');
    mobileOverlay.classList.remove('active');
    document.body.style.overflow = '';
  }

  function handleResize() {
    if (window.innerWidth > 768 && sidebar.classList.contains('active')) {
      closeSidebar();
    }
  }

  function checkAuthentication() {
    // Verificar se AuthManager está disponível
    if (typeof AuthManager === 'undefined') {
      console.warn('AuthManager não encontrado. Redirecionando para login...');
      setTimeout(() => {
        window.location.href = 'Login.html';
      }, 2000);
      return false;
    }

    // Verificar se está logado
    if (!AuthManager.isLoggedIn()) {
      console.log('Usuário não está logado. Redirecionando...');
      window.location.href = 'Login.html';
      return false;
    }

    return true;
  }

  function setupNavigationListeners() {
    // Links do sidebar
    const sidebarLinks = document.querySelectorAll('.sidebar-nav a:not(#logoutBtn)');
    
    sidebarLinks.forEach(link => {
      link.addEventListener('click', (e) => {
        // Fechar sidebar no mobile ao navegar
        if (window.innerWidth <= 768) {
          closeSidebar();
        }
        
        // Marcar link ativo
        sidebarLinks.forEach(l => l.parentElement.classList.remove('active'));
        link.parentElement.classList.add('active');
      });
    });
  }

  // Função para atualizar informações do usuário na interface
  function updateUserInterface() {
    if (typeof AuthManager === 'undefined') return;
    
    const user = AuthManager.getUser();
    
    if (user) {
      // Atualizar elementos da UI com dados do usuário
      updateUserInfo(user);
      
      // Personalizar experiência baseado no curso
      personalizeByCourse(user.curso);
    }
  }

  function updateUserInfo(user) {
    // Nome completo na sidebar
    const userNameElement = document.getElementById('userName');
    if (userNameElement) {
      userNameElement.textContent = user.nome || 'Usuário';
    }

    // Primeiro nome no welcome
    const welcomeNameElement = document.getElementById('welcomeName');
    if (welcomeNameElement) {
      const firstName = user.nome ? user.nome.split(' ')[0] : 'Student';
      welcomeNameElement.textContent = firstName;
    }

    // Curso
    const userCourseElement = document.getElementById('userCourse');
    if (userCourseElement && user.curso) {
      userCourseElement.textContent = user.curso.instrumento || 'Not enrolled';
    }

    // Avatar
    const userAvatarElement = document.getElementById('userAvatar');
    if (userAvatarElement && user.foto_perfil) {
      userAvatarElement.src = user.foto_perfil;
    }
  }

  function personalizeByCourse(curso) {
    if (!curso) return;

    // Adicionar classe personalizada baseada no instrumento
    const instrumento = curso.instrumento ? curso.instrumento.toLowerCase() : '';
    document.body.classList.add(`course-${instrumento.replace(/\s+/g, '-')}`);

    // Log para debug
    console.log(`Curso personalizado: ${curso.nome} - ${curso.instrumento}`);
  }

  // Atualizar interface ao carregar
  setTimeout(updateUserInterface, 100);
});

// Função global para logout rápido
function quickLogout() {
  if (typeof AuthManager !== 'undefined') {
    if (confirm('Deseja realmente sair?')) {
      AuthManager.logout();
    }
  } else {
    window.location.href = 'Login.html';
  }
}

// Função global para verificar status da autenticação
function checkAuthStatus() {
  if (typeof AuthManager !== 'undefined') {
    const user = AuthManager.getUser();
    console.log('Status da autenticação:', {
      logado: AuthManager.isLoggedIn(),
      usuario: user ? user.nome : 'Nenhum',
      token: AuthManager.getToken() ? 'Presente' : 'Ausente'
    });
  } else {
    console.log('AuthManager não está disponível');
  }
}

// Função para atualizar dados do usuário
async function refreshUserData() {
  if (typeof AuthManager === 'undefined') return;
  
  try {
    const isValid = await AuthManager.validateToken();
    
    if (isValid) {
      console.log('✅ Token validado. Dados atualizados.');
      location.reload(); // Recarregar página para mostrar dados atualizados
    } else {
      console.log('❌ Token inválido. Redirecionando para login...');
      AuthManager.logout();
    }
  } catch (error) {
    console.error('Erro ao atualizar dados:', error);
  }
}

// Expostar funções globalmente para debug
window.quickLogout = quickLogout;
window.checkAuthStatus = checkAuthStatus;
window.refreshUserData = refreshUserData;