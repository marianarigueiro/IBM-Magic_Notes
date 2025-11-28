/**
 * Magic Notes - Authentication Manager
 * Gerencia autenticação, tokens e sessões do usuário
 */

const AuthManager = {
  API_URL: 'http://localhost/MagicNotesApi/api',
  
  // Verificar se o usuário está logado
  isLoggedIn() {
    const token = this.getToken();
    return token !== null;
  },
  
  // Obter token do localStorage
  getToken() {
    return localStorage.getItem('magic_notes_token');
  },
  
  // Obter dados do usuário
  getUser() {
    const userData = localStorage.getItem('magic_notes_user');
    return userData ? JSON.parse(userData) : null;
  },
  
  // Salvar token e dados do usuário
  saveSession(token, user) {
    localStorage.setItem('magic_notes_token', token);
    localStorage.setItem('magic_notes_user', JSON.stringify(user));
  },
  
  // Limpar sessão
  clearSession() {
    localStorage.removeItem('magic_notes_token');
    localStorage.removeItem('magic_notes_user');
  },
  
  // Fazer login
  async login(email, senha) {
    try {
      const response = await fetch(`${this.API_URL}/user/login.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ email, senha })
      });
      
      const data = await response.json();
      
      if (response.ok && data.success) {
        this.saveSession(data.token, data.user);
        return { success: true, data };
      } else {
        return { success: false, message: data.message || 'Erro ao fazer login' };
      }
    } catch (error) {
      console.error('Erro ao fazer login:', error);
      return { success: false, message: 'Erro ao conectar com o servidor' };
    }
  },
  
  // Fazer logout
  async logout() {
    const token = this.getToken();
    
    if (token) {
      try {
        await fetch(`${this.API_URL}/user/logout.php`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });
      } catch (error) {
        console.error('Erro ao fazer logout:', error);
      }
    }
    
    this.clearSession();
    window.location.href = 'Login.html';
  },
  
  // Validar token
  async validateToken() {
    const token = this.getToken();
    
    if (!token) {
      return false;
    }
    
    try {
      const response = await fetch(`${this.API_URL}/user/validate.php`, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      });
      
      const data = await response.json();
      
      if (response.ok && data.success) {
        // Atualizar dados do usuário
        this.saveSession(data.token, data.user);
        return true;
      } else {
        // Token inválido
        this.clearSession();
        return false;
      }
    } catch (error) {
      console.error('Erro ao validar token:', error);
      return false;
    }
  },
  
  // Proteger página (requer autenticação)
  async requireAuth() {
    const isValid = await this.validateToken();
    
    if (!isValid) {
      // Redirecionar para login
      window.location.href = 'Login.html';
      return false;
    }
    
    return true;
  },
  
  // Atualizar dados do usuário
  async updateUser(userData) {
    const token = this.getToken();
    
    if (!token) {
      return { success: false, message: 'Usuário não autenticado' };
    }
    
    try {
      const response = await fetch(`${this.API_URL}/user/update.php`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(userData)
      });
      
      const data = await response.json();
      
      if (response.ok) {
        // Atualizar dados locais
        const currentUser = this.getUser();
        const updatedUser = { ...currentUser, ...data };
        localStorage.setItem('magic_notes_user', JSON.stringify(updatedUser));
        
        return { success: true, data };
      } else {
        return { success: false, message: data.message || 'Erro ao atualizar dados' };
      }
    } catch (error) {
      console.error('Erro ao atualizar usuário:', error);
      return { success: false, message: 'Erro ao conectar com o servidor' };
    }
  },
  
  // Fazer requisição autenticada
  async authenticatedRequest(endpoint, options = {}) {
    const token = this.getToken();
    
    if (!token) {
      throw new Error('Usuário não autenticado');
    }
    
    const defaultHeaders = {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    };
    
    const config = {
      ...options,
      headers: {
        ...defaultHeaders,
        ...options.headers
      }
    };
    
    try {
      const response = await fetch(`${this.API_URL}${endpoint}`, config);
      
      // Se token inválido, fazer logout
      if (response.status === 401) {
        this.clearSession();
        window.location.href = 'Login.html';
        throw new Error('Sessão expirada');
      }
      
      return response;
    } catch (error) {
      console.error('Erro na requisição autenticada:', error);
      throw error;
    }
  }
};

// Exportar para uso global
window.AuthManager = AuthManager;

// Adicionar evento de logout em todos os botões de logout
document.addEventListener('DOMContentLoaded', () => {
  const logoutButtons = document.querySelectorAll('[data-logout], .btn-logout, #logoutButton');
  
  logoutButtons.forEach(button => {
    button.addEventListener('click', (e) => {
      e.preventDefault();
      
      if (confirm('Deseja realmente sair?')) {
        AuthManager.logout();
      }
    });
  });
});