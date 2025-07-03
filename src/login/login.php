<?php
session_start();
require '../../config/db_connection.php';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Portale Universitario</title>
    
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-form-section">
            <div class="form-header">
                <h1 class="form-title">Accedi</h1>
                <p class="form-subtitle">Benvenuto nel Portale Universitario</p>
            </div>

           
            <div class="error-message" id="errorMessage"></div>
    

            <form id="loginForm" method="POST" action="">
                <div class="user-type-selector">
                    <div class="user-type-option active" data-type="docente" onclick="selectUserType('docente')">
                        Docente
                    </div>
                </div>
            <!-- Potrei implementare login diverso per lo studente-->

                <div class="form-group">
                    <label for="email" class="form-label">Email Istituzionale</label>
                    <input 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="mario.rossi@university.it"
                        autocomplete="username"
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-container">
                        <input 
                            id="password" 
                            name="password" 
                            type="password"
                            class="form-input" 
                            placeholder="Inserisci la tua password"
                            autocomplete="current-password"
                        >
                    </div>
                </div>

                <div class="form-options">
                    <a href="#" class="forgot-password" onclick="forgotPassword()">Password dimenticata?</a>
                </div>
                <div class="form-options">
                    <a href="../registrazione/registrazione.php" class="forgot-password">Registrati ora</a>
                </div>
                <button type="submit" class="login-button" id="loginButton">
                    Accedi al Sistema
                </button>
            </form>
        </div>

        <div class="login-visual-section">
            <div class="visual-content">
                <div class="logo">🎓 Portale Universitario</div>
                <h2 class="visual-title">Gestione Accademica Digitale</h2>
            </div>
        </div>
    </div>

    <script src="login.js"></script>
</body>
</html>