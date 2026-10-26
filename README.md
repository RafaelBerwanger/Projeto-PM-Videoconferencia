# Projeto PM Videoconferencia
 Projeto de programa auxiliar de videoconferências para PMPR

## Instalação local (XAMPP)

1. Copie o projeto para `C:\xampp\htdocs\Projeto-PM-Videoconferencia`.
2. Crie o banco importando `database.sql` no phpMyAdmin.
3. Copie `config.example.php` para `config.php` e preencha os dados do banco e a `GROQ_API_KEY`.
   O `config.php` está no `.gitignore` e nunca deve ser versionado.
4. Crie o primeiro administrador: rode `php gerar_senha.php "SuaSenha"` no terminal e use o hash
   no `INSERT` comentado ao final do `database.sql`.
5. Acesse `http://localhost/Projeto-PM-Videoconferencia/login.php`.

## Estrutura

| Arquivo | Função |
|---|---|
| `sessao.php` | Sessão segura, conexão, `exigirLogin()`, `exigirAdmin()` e proteção CSRF |
| `listas.php` | Listas padronizadas de postos/graduações e unidades (OPM) |
| `db.php` | Conexão PDO (lê credenciais do `config.php`) |
| `audiovisual.php` + `functionsJS.js` | Oitivas audiovisuais: HASH, captura de quadro e PDF |
| `presencial.php` + `functionsPresencial.js` | Oitivas escritas, ditado por voz e refinamento por IA |
| `procedimentos.php`, `ver_procedimento.php` | Cadastro e dossiê de procedimentos |
| `admin_usuarios.php` | Aprovação e gestão de acessos (somente admin) |
