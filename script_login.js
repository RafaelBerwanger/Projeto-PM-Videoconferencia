// Alternar visibilidade da senha (mostrar/ocultar)
function togglePasswordVisibility() {
  const passwordInput = document.getElementById('password');
  const toggleIcon = document.getElementById('togglePassword');

  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    toggleIcon.classList.remove('fa-eye');
    toggleIcon.classList.add('fa-eye-slash');
  } else {
    passwordInput.type = 'password';
    toggleIcon.classList.remove('fa-eye-slash');
    toggleIcon.classList.add('fa-eye');
  }
}

function handleLogin(event) {
  event.preventDefault();

  const usernameInput = document.getElementById('username').value.trim();
  const passwordInput = document.getElementById('password').value.trim();
  const messageBox = document.getElementById('message');

  messageBox.className = 'message-box';
  messageBox.innerText = '';

  // Defina a senha/usuário padrão do sistema (ex: admin / pmpr1854 ou chave do operador)
  if (usernameInput === 'admin' && passwordInput === 'admin') {
    messageBox.classList.add('success');
    messageBox.innerText = 'Autenticado com sucesso! Redirecionando...';

    // Salva a sessão no navegador para saber que o usuário está logado
    sessionStorage.setItem('usuarioLogado', 'true');

    // Redireciona para o sistema de oitivas após 1 segundo
    setTimeout(() => {
      window.location.href = 'index.html'; 
    }, 1000);

  } else {
    messageBox.classList.add('error');
    messageBox.innerText = 'Usuário ou senha incorretos.';
  }
}


