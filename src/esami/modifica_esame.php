<?php
session_start();
require '../../config/db_connection.php';
require 'esamiController.php';

// Verifica se la sessione è ancora attiva
if (!isset($_SESSION['id_utente'])) {
    die("Utente non autenticato.");
}

// Verifica se è stato passato l'ID dell'esame
if (!isset($_GET['id'])) {
    die("ID esame non specificato.");
}

$id_esame = $_GET['id'];
$errorMessage = '';

// Query per ottenere info sull'esame
$queryEsame = $conn->prepare("SELECT e.*, c.nome as nome_corso, c.id as id_corso
                              FROM esami e
                              JOIN corsi c ON e.corso = c.id
                              WHERE e.id = ? AND c.docente = ?");
$queryEsame->bind_param("is", $id_esame, $_SESSION['id_utente']);
$queryEsame->execute();
$resultEsame = $queryEsame->get_result();

if ($resultEsame->num_rows === 0) {
    die("Esame non trovato o non autorizzato.");
}

$esame = $resultEsame->fetch_assoc();

// Gestione del form di modifica
$messaggio = '';
$tipo_messaggio = '';

$queryVerificaValutazioni = $conn->prepare("SELECT 
                                            (SELECT COUNT(*) FROM valutazioni WHERE esame=?) AS count_valutazioni,
                                            (SELECT COUNT(*) FROM prenotazioni WHERE esame=?) AS count_prenotazioni");
$queryVerificaValutazioni->bind_param("ii", $id_esame, $id_esame);
$queryVerificaValutazioni->execute();
$verifica = $queryVerificaValutazioni->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST['data'];
    $ora = $_POST['ora'];
    $sessione = $_POST['sessione'];
    $luogo = $_POST['luogo'];
    $inizioPrenotazione = $_POST['inizioPrenotazione'];
    $finePrenotazione = $_POST['finePrenotazione'];
    $completato = $_POST['completato'] ?? null;
    $tipologia = $_POST['tipologia'];
    
    // Validazione dei dati
    $errori = [];
    if(($verifica['count_valutazioni']!=$verifica['count_prenotazioni']) && ($completato)){
        $errori[] = "Non è possibile considerare un esame COMPLETATO senza aver valutato tutti gli studenti";
    }
    if (empty($data)) {
        $errori[] = "La data è obbligatoria";
    }
    
    if (empty($ora)) {
        $errori[] = "L'ora è obbligatoria";
    }
    
    if (empty($sessione)) {
        $errori[] = "La sessione è obbligatoria";
    }
    
    if (!empty($inizioPrenotazione) && !empty($finePrenotazione)) {
        if (strtotime($inizioPrenotazione) >= strtotime($finePrenotazione)) {
            $errori[] = "La data di inizio prenotazioni deve essere precedente a quella di fine";
        }
    }
    
    //Query al DB
    if (empty($errori)) {
        $queryUpdateBase = "UPDATE esami SET 
                                        data = ?, 
                                        ora = ?, 
                                        sessione = ?, 
                                        luogo = ?, 
                                        inizioPrenotazione = ?, 
                                        finePrenotazione = ?,
                                        completato = ?,
                                        tipologia = ?";
        $parametri = [$data, $ora, $sessione, $luogo, $inizioPrenotazione, $finePrenotazione, $completato, $tipologia];
        $tipi = "ssssssis";
        
        if($completato){
            $queryUpdateBase .= ",prenotabile = ?";
            $parametri[] = 0;
            $tipi .= "i";
        }
                
        $queryUpdateBase .= " WHERE id = ?";
        $parametri[] = $id_esame;
        $tipi .= "i";
        
        $queryUpdate = $conn->prepare($queryUpdateBase);

        $queryUpdate->bind_param($tipi, ...$parametri); 
        /* L'utilizzo di ...$parametri fa si che l'array si espande in lista
        diventando bind_param($tipi, $data, $ora, $sessione, .....) e via discorrendo */
        
        if ($queryUpdate->execute()) {
            $messaggio = "Esame aggiornato con successo!";
            $tipo_messaggio = "success";
            
            $queryEsame->execute();
            $resultEsame = $queryEsame->get_result();
            $esame = $resultEsame->fetch_assoc();
        } else {
            $messaggio = "Errore durante l'aggiornamento dell'esame.";
            $tipo_messaggio = "error";
        }
    } else {
        $messaggio = implode("<br>", $errori);
        $tipo_messaggio = "error";
    }
}

$corsi = getCorsiDocente($conn, $_SESSION['id_utente'], $errorMessage);
if($corsi == null){
    die($errorMessage);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modifica Esame - Portale Universitario</title>
    <link rel="stylesheet" href="../common/common.css">
    <link rel="stylesheet" href="esami.css">

</head>

<body>
    <?php include '../common/header.php'; ?>
    
    <div class="dashboard">
        <?php include '../common/sidebar.php'; ?>

        <main class="main-content">
        
            <div class="page-header">
                <h1 class="page-title">Modifica Esame</h1>
                <p class="page-subtitle">Modifica i dettagli dell'esame selezionato</p>
            </div>

        
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Dettagli Esame</h3>
                    <a href="esami.php" class="btn btn-outline">< Torna agli Esami</a>
                </div>
                <div class="card-body">
        
                    <div class="exam-info">
                        <h4>Stai modificando l'esame di: <?php echo htmlspecialchars($esame['nome_corso']); ?></h4>
                        <p>ID Esame: #<?php echo $esame['id']; ?></p>
                    </div>

                    <!-- Messaggio in caso di errori -->
                    <?php if (!empty($messaggio)): ?>
                        <div class="alert <?php echo $tipo_messaggio; ?>">
                            <?php echo $messaggio; ?>
                        </div>
                    <?php endif; ?>

                    
                    <form method="POST" action="">
                        <div class="form-grid">
                    
                            <div class="form-group">
                                <label class="form-label" for="data">Data Esame *</label>
                                <input type="date" id="data" name="data" class="form-input" 
                                       value="<?php echo htmlspecialchars($esame['data']); ?>" required>
                                <small class="form-help">Seleziona la data in cui si svolgerà l'esame</small>
                            </div>

                            
                            <div class="form-group">
                                <label class="form-label" for="ora">Ora Esame *</label>
                                <input type="time" id="ora" name="ora" class="form-input" 
                                       value="<?php echo htmlspecialchars($esame['ora']); ?>" required>
                                <small class="form-help">Orario di inizio dell'esame</small>
                            </div>

                            
                            <div class="form-group">
                                <label class="form-label" for="sessione">Sessione *</label>
                                <select id="sessione" name="sessione" class="form-select" required>
                                    <option value="">Seleziona sessione</option>
                                    <option value="Invernale" <?php echo ($esame['sessione'] == 'Invernale') ? 'selected' : ''; ?>>Invernale</option>
                                    <option value="Estiva" <?php echo ($esame['sessione'] == 'Estiva') ? 'selected' : ''; ?>>Estiva</option>
                                    <option value="Straordinaria" <?php echo ($esame['sessione'] == 'Straordinaria') ? 'selected' : ''; ?>>Straordinaria</option>
                                </select>
                                <small class="form-help">Sessione d'esame</small>
                            </div>

                            
                            <div class="form-group">
                                <label class="form-label" for="luogo">Luogo</label>
                                <input type="text" id="luogo" name="luogo" class="form-input" 
                                       value="<?php echo htmlspecialchars($esame['luogo']); ?>" 
                                       placeholder="Es. Aula Magna, Lab. Informatica...">
                                <small class="form-help">Aula dove si svolgerà l'esame</small>
                            </div>

                            
                            <div class="form-group">
                                <label class="form-label" for="inizioPrenotazione">Inizio Prenotazioni</label>
                                <input type="date" id="inizioPrenotazione" name="inizioPrenotazione" class="form-input" 
                                       value="<?php echo htmlspecialchars($esame['inizioPrenotazione']); ?>">
                                <small class="form-help">Data di apertura delle prenotazioni</small>
                            </div>


                            <div class="form-group">
                                <label class="form-label" for="finePrenotazione">Fine Prenotazioni</label>
                                <input type="date" id="finePrenotazione" name="finePrenotazione" class="form-input" 
                                       value="<?php echo htmlspecialchars($esame['finePrenotazione']); ?>">
                                <small class="form-help">Data di chiusura delle prenotazioni</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="sessione">Tipologia *</label>
                                <select id="tipologia" name="tipologia" class="form-select" required>
                                    <option value="">Seleziona tipologia</option>
                                    <option value="scritto" <?php echo ($esame['tipologia'] == 'scritto') ? 'selected' : ''; ?>>Scritto</option>
                                    <option value="orale" <?php echo ($esame['tipologia'] == 'orale') ? 'selected' : ''; ?>>Orale</option>
                                    <option value="scritto/orale" <?php echo ($esame['tipologia'] == 'scritto/orale') ? 'selected' : ''; ?>>Scritto e Orale</option>
                                </select>
                                <small class="form-help">Tipologia d'esame</small>
                            </div>
                        </div>

                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Stato Esame</label>
                                <div class="form-checkbox">
                                    <input type="checkbox" id="completato" name="completato" value="1" 
                                           <?php echo ($esame['completato'] == 1) ? 'checked' : ''; ?>>
                                    <label for="completato">Esame completato</label>
                                </div>
                                <small class="form-help">Segna come completato se l'esame si è già svolto</small>
                            </div>
                        </div>

                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Salva Modifiche</button>
                            <a href="#" class="btn btn-secondary" onclick="window.history.back()">Annulla</a>
                            <a href="visualizzaEsami.php?id=<?php echo $esame['id']; ?>" class="btn btn-outline">Visualizza Dettagli</a>
                            <a class="btn btn-primary" style="background-color: red;" onclick="eliminaEsame(<?php echo $id_esame; ?>)">Elimina esame</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <?php include '../common/footer.php'; ?>                   
    <script src="../common/common.js"></script>
    <script src="modificaEsami.js"></script>
</body>
</html>