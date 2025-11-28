document.addEventListener('DOMContentLoaded', () => {
  const menuToggle = document.querySelector('.menu-toggle');
  const sidebar = document.querySelector('.sidebar');
  const mobileOverlay = document.createElement('div');
  mobileOverlay.className = 'mobile-overlay';
  document.body.appendChild(mobileOverlay);

  function init() {
    setupEventListeners();
  }

  function setupEventListeners() {
    // Menu mobile
    if (menuToggle) {
      menuToggle.addEventListener('click', (e) => {
        e.preventDefault();
        toggleSidebar();
      });
    }

    // Overlay
    mobileOverlay.addEventListener('click', closeSidebar);

    // Navegação
    const navLinks = document.querySelectorAll('.sidebar-nav a');
    navLinks.forEach(link => {
      link.addEventListener('click', handleNavigation);
    });

    // Teclas
    document.addEventListener('keydown', handleKeyPress);
    window.addEventListener('resize', handleResize);
  }

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

  function handleNavigation(e) {
    e.preventDefault();
    
    if (this.parentElement.classList.contains('logout')) {
      showLogoutConfirmation();
      return;
    }

    const navLinks = document.querySelectorAll('.sidebar-nav a');
    navLinks.forEach(link => link.parentElement.classList.remove('active'));
    this.parentElement.classList.add('active');

    if (window.innerWidth <= 768) {
      closeSidebar();
    }
  }

  function handleKeyPress(e) {
    if (e.key === 'Escape') {
      closeSidebar();
    }
  }

  function handleResize() {
    if (window.innerWidth > 768 && sidebar.classList.contains('active')) {
      closeSidebar();
    }
  }

  function showLogoutConfirmation() {
    if (confirm('Are you sure you want to logout?')) {
      alert('Redirecting to login...');
      // window.location.href = 'index.html';
    }
  }

  init();
});