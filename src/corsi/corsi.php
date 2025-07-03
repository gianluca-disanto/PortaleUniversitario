<?php
session_start();
require '../../config/db_connection.php';

//Verifica se la sessione è ancora attiva
if (!isset($_SESSION['id_utente'])) {
    die("Utente non autenticato.");
}

// Query per ottenere i corsi del docente con informazioni aggiuntive
$query = $conn->prepare("SELECT * FROM Corsi
                        WHERE docente = ?");
if(!$query){
    die("Errore nella preparazione della query: ". $conn->error);
}

$query->bind_param("s", $_SESSION['id_utente']);
$query->execute();
$results = $query->get_result();

// Per ogni corso, contiamo gli esami programmati
$corsiConEsami = [];
while($row = $results->fetch_assoc()) {
    $queryEsami = $conn->prepare("SELECT COUNT(*) as num_esami 
                                 FROM esami e
                                 
                                 WHERE e.corso = ? AND completato != 1");
    $queryEsami->bind_param("i", $row['id']);
    $queryEsami->execute();
    $resultEsami = $queryEsami->get_result();
    $numEsami = $resultEsami->fetch_assoc()['num_esami'];
    
    $row['num_esami'] = $numEsami;
    $corsiConEsami[] = $row;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>I miei corsi - Portale Universitario</title>
    <link rel="stylesheet" href="../common/common.css">
    <link rel="stylesheet" href="corsi.css">
</head>
  
<body>
    <?php include '../common/header.php'; ?>
    
    <div class="dashboard">
        <?php include '../common/sidebar.php'; ?>
        
        <main class="main-content">
            <h1>I miei corsi</h1>
            <p>Gestisci i tuoi corsi e programma gli esami</p>

            <!-- Alcune statistiche rapide -->
            <div class="statsGrid">
                <div class="statCard">
                    <div class="statNumber"><?php echo count($corsiConEsami); ?></div>
                    <div class="statLabel">Corsi Totali</div>
                </div>
                <div class="statCard">
                    <div class="statNumber"><?php echo array_sum(array_column($corsiConEsami, 'num_esami')); ?></div>
                    <div class="statLabel">Esami Programmati</div>
                </div>
            </div>

            <!-- Lista corsi -->
            <div class="courses-container">
                <?php if(empty($corsiConEsami)): ?>
                    <div class="no-courses">
                        <h3>Nessun corso assegnato</h3>
                        <p>Non hai ancora corsi assegnati in questo momento.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($corsiConEsami as $corso): ?>
                        <div class="course-card">
                            <div class="course-header">
                                <h3 class="course-title"><?php echo htmlspecialchars($corso['nome']); ?></h3>
                                <span class="course-cfu"><?php echo $corso['cfu']; ?> CFU</span>
                            </div>
                            
                            <div class="course-info">
                                <div class="info-item">
                                    <span>📝</span>
                                    <span><?php echo $corso['num_esami']; ?> esami programmati</span>
                                </div>
                            </div>
                            
                            <div class="course-actions">
                                <a href="../esami/esami.php?corso=<?php echo $corso['id']; ?>" class="btn btn-primary">
                                    Visualizza Esami
                                </a>
                                <a href="../esami/nuovo_esame.php?corso=<?php echo $corso['id']; ?>" class="btn btn-outline">
                                    <span>+</span>
                                    Programma Esame
                                </a>
                                <a href="./report_corso.php?corso=<?php echo $corso['id']; ?>" class="btn btn-success">
                                    Report corso
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <?php include '../common/footer.php'; ?>
    <script src="../common/common.js"></script>
</body>
</html>