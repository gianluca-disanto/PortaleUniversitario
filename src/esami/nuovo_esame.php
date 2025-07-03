<?php
session_start();
require '../../config/db_connection.php';

// Verifica se la sessione è ancora attiva
if (!isset($_SESSION['id_utente'])) {
    die("Utente non autenticato.");
}

$corso_id = isset($_GET['corso']) ? intval($_GET['corso']) : null;
$corsi = [];
// se il corso non è del docente, riporto la lista di corsi
if ($corso_id) {
    $query = $conn->prepare("SELECT * FROM Corsi WHERE id = ? AND docente = ?");
    $query->bind_param("is", $corso_id, $_SESSION['id_utente']);
    $query->execute();
    $corso = $query->get_result()->fetch_assoc();
    
    if (!$corso) {
        die("Corso non trovato o non autorizzato.");
    }
} else {
    $query = $conn->prepare("SELECT * FROM Corsi WHERE docente = ?");
    $query->bind_param("s", $_SESSION['id_utente']);
    $query->execute();
    $corsi = $query->get_result()->fetch_all(MYSQLI_ASSOC);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data_esame = $_POST['data_esame'];
    $ora_esame = $_POST['ora_esame'];
    $sessione = $_POST['sessione'];
    $data_inizio_prenotazione = $_POST['data_inizio_prenotazione'];
    $data_fine_prenotazione = $_POST['data_fine_prenotazione'];
    $corso_selezionato = $_POST['corso'];
    $luogo = $_POST['luogo'];
    $tipologia = $_POST['tipologia'];
    
    // Validazioni
    $errori = [];
    
    if (empty($data_esame)) {
        $errori[] = "La data dell'esame è obbligatoria.";
    }
    
    if (empty($ora_esame)) {
        $errori[] = "L'ora dell'esame è obbligatoria.";
    }
    
    if (empty($sessione)) {
        $errori[] = "La sessione è obbligatoria.";
    }
    
    if (empty($data_inizio_prenotazione)) {
        $errori[] = "La data di inizio prenotazione è obbligatoria.";
    }
    
    if (empty($data_fine_prenotazione)) {
        $errori[] = "La data di fine prenotazione è obbligatoria.";
    }
    
    if (empty($corso_selezionato)) {
        $errori[] = "Il corso è obbligatorio.";
    }

    if (empty($tipologia)){
        $errori[] = "La tipologia è obbligatoria";
    }
    
    if (!empty($data_fine_prenotazione) && !empty($data_esame) && $data_fine_prenotazione >= $data_esame) {
        $errori[] = "La data di fine prenotazione deve essere precedente alla data dell'esame.";
    }
    
    if (!empty($data_inizio_prenotazione) && !empty($data_fine_prenotazione) && $data_inizio_prenotazione >= $data_fine_prenotazione) {
        $errori[] = "La data di inizio prenotazione deve essere precedente alla data di fine prenotazione.";
    }
    
    // Verifico che il corso appartenga al docente
    $query = $conn->prepare("SELECT * FROM Corsi WHERE id = ? AND docente = ?");
    $query->bind_param("is", $corso_selezionato, $_SESSION['id_utente']);
    $query->execute();
    if (!$query->get_result()->fetch_assoc()) {
        $errori[] = "Corso non valido o non autorizzato.";
    }
    
    if (empty($errori)) {
        // Inserimento nel db
        $query = $conn->prepare("INSERT INTO esami (data, ora, sessione, inizioPrenotazione, finePrenotazione, corso, luogo, tipologia) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $query->bind_param("sssssiss", $data_esame, $ora_esame, $sessione, $data_inizio_prenotazione, $data_fine_prenotazione, $corso_selezionato, $luogo, $tipologia);
        
        if ($query->execute()) {
            $successo = "Esame programmato con successo!";
            $data_esame = $ora_esame = $sessione = $data_inizio_prenotazione = $data_fine_prenotazione = '';
            if (!$corso_id) $corso_selezionato = '';
            header("Location: esami.php?prenotazione=1");
            
        } else {
            $errori[] = "Errore durante l'inserimento dell'esame: " . $conn->error;
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Nuovo Esame - Portale Universitario</title>
    <link rel="stylesheet" href="../common/common.css">
    <link rel="stylesheet" href="esami.css">
</head>

<body>
<?php include '../common/header.php'; ?>
    
    <div class="dashboard">
        <?php include '../common/sidebar.php'; ?>
        
    
        <main class="main-content">
            <div class="form-container">
                <div class="form-header">
                    <h2>Programma Nuovo Esame</h2>
                    <p>Inserisci i dettagli per programmare un nuovo esame</p>
                </div>
                
                
                <?php if (!empty($errori)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errori as $errore): ?>
                                <li><?php echo htmlspecialchars($errore); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if ($corso_id && isset($corso)): ?>
                    <div class="course-info">
                        <h3><?php echo htmlspecialchars($corso['nome']); ?></h3>
                        <p><?php echo $corso['cfu']; ?> CFU</p>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <?php if (!$corso_id): ?>
                        <div class="form-group">
                            <label class="form-label" for="corso">Corso *</label>
                            <select class="form-select" id="corso" name="corso" required>
                                <option value="">Seleziona un corso...</option>
                                <?php foreach ($corsi as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php echo (isset($corso_selezionato) && $corso_selezionato == $c['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['nome']) . ' (' . $c['cfu'] . ' CFU)'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="corso" value="<?php echo $corso_id; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="data_esame">Data Esame *</label>
                            <input type="date" class="form-control" id="data_esame" name="data_esame" 
                                   value="<?php echo isset($data_esame) ? htmlspecialchars($data_esame) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="ora_esame">Ora Esame *</label>
                            <input type="time" class="form-control" id="ora_esame" name="ora_esame" 
                                   value="<?php echo isset($ora_esame) ? htmlspecialchars($ora_esame) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="sessione">Sessione *</label>
                        <select class="form-select" id="sessione" name="sessione" required>
                            <option value="">Seleziona una sessione...</option>
                            <option value="straordinaria" <?php echo (isset($sessione) && $sessione == 'straordinaria') ? 'selected' : ''; ?>>Straordinaria</option>
                            <option value="invernale" <?php echo (isset($sessione) && $sessione == 'invernale') ? 'selected' : ''; ?>>Invernale</option>
                            <option value="estiva" <?php echo (isset($sessione) && $sessione == 'estiva') ? 'selected' : ''; ?>>Estiva</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="tipologia">Tipologia *</label>
                        <select class="form-select" id="tipologia" name="tipologia" required>
                            <option value="">Seleziona una tipologia...</option>
                            <option value="scritto" <?php echo (isset($tipologia) && $tipologia == 'scritto') ? 'selected' : ''; ?>>Scritto</option>
                            <option value="orale" <?php echo (isset($tipologia) && $tipologia == 'orale') ? 'selected' : ''; ?>>Orale</option>
                            <option value="scritto/orale" <?php echo (isset($tipologia) && $tipologia == 'scritto/orale') ? 'selected' : ''; ?>>Scritto e Orale</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="luogo">Luogo *</label>
                        <input type="text" class="form-input" id="luogo" name="luogo"
                                required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="data_inizio_prenotazione">Inizio Prenotazioni *</label>
                            <input type="date" class="form-control" id="data_inizio_prenotazione" name="data_inizio_prenotazione" 
                                   value="<?php echo isset($data_inizio_prenotazione) ? htmlspecialchars($data_inizio_prenotazione) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="data_fine_prenotazione">Fine Prenotazioni *</label>
                            <input type="date" class="form-control" id="data_fine_prenotazione" name="data_fine_prenotazione" 
                                   value="<?php echo isset($data_fine_prenotazione) ? htmlspecialchars($data_fine_prenotazione) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span>✓</span>
                            Programma Esame
                        </button>
                        <a href="<?php echo $corso_id ? '../esami/esami.php?corso=' . $corso_id : '../corsi/corsi.php'; ?>" class="btn btn-secondary">
                            <span>✕</span>
                            Annulla
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <?php include '../common/footer.php'; ?>                    
    <script src="../common/common.js"></script>
    <script src="nuovoEsame.js"></script>
</body>
</html>