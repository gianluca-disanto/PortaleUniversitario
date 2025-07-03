<?php

require '../../config/db_connection.php';

$queryDipartimenti = $conn->prepare("SELECT * FROM dipartimenti");
$queryDipartimenti->execute();
$dipartimenti = $queryDipartimenti->get_result();

$queryRuoli = $conn->prepare("SELECT * FROM ruoli");
$queryRuoli->execute();
$ruoli = $queryRuoli->get_result();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione Docente - Portale Universitario</title>
    
    <link rel="stylesheet" href="registrazione.css">
</head>
<body>
    <div class="login-container">
        <div class="login-form-section">
            <div class="form-header">
                <h1 class="form-title">Registrazione Docente</h1>
                <p class="form-subtitle">Crea il tuo account nel Portale Universitario</p>
            </div>

            <div class="error-message" id="errorMessage"></div>

            <form id="registrationForm" method="POST" action="">
                <div class="user-type-selector">
                    <div class="user-type-option active" data-type="docente">
                        Docente
                    </div>
                </div>

                <div class="form-group">
                    <label for="nome" class="form-label">Nome</label>
                    <input 
                        id="nome" 
                        name="nome" 
                        type="text"
                        class="form-input" 
                        placeholder="Inserisci il tuo nome"
                        
                    >
                </div>

                <div class="form-group">
                    <label for="cognome" class="form-label">Cognome</label>
                    <input id="cognome" name="cognome" type="text"class="form-input" placeholder="Inserisci il tuo cognome">
                </div>

                <div class="form-group">
                    <label for="codice_fiscale" class="form-label">Codice Fiscale</label>
                    <input 
                        id="codice_fiscale" 
                        name="codice_fiscale" 
                        type="text"
                        class="form-input" 
                        placeholder="RSSMRA80A01H501Z"
                        maxlength="16"
                        
                    >
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Istituzionale</label>
                    <input 
                        id="email" 
                        name="email" 
                        type="email"
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
                            autocomplete="new-password"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="indirizzo" class="form-label">Indirizzo</label>
                    <input id="indirizzo" name="indirizzo" type="text"class="form-input" placeholder="Via Roma 123, Milano">
                </div>

                <div class="form-group">
                    <label for="dipartimento" class="form-label">Dipartimento</label>
                    <select id="dipartimento" name="dipartimento" class="form-input">
                        <option value="">Seleziona il dipartimento</option>
                        <?php foreach($dipartimenti as $dip):?>
                        <option value="<?= $dip['id'];?>"><?= $dip['nome'];?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ruolo" class="form-label">Ruolo</label>
                    <select id="ruolo" name="ruolo" class="form-input">
                        <option value="">Seleziona il ruolo</option>
                        <?php foreach($ruoli as $ruolo): ?>
                            <option value="<?= $ruolo['id'];?>"><?= $ruolo['nome_ruolo']; ?></option>    
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="login-button" id="registrationButton">
                    Registra Account
                </button>

                <div class="form-options">
                    <a href="../login/login.php" class="forgot-password">Hai già un account? Accedi</a>
                </div>
            </form>
        </div>

        <div class="login-visual-section">
            <div class="visual-content">
                <div class="logo">🎓 Portale Universitario</div>
                <h2 class="visual-title">Gestione Accademica Digitale</h2>
            </div>
        </div>
    </div>

    <script src="registrazione.js"></script>
</body>
</html>