<?php
session_start();
require '../../config/db_connection.php';

//Verifica se la sessione è acnora attiva
if (!isset($_SESSION['id_utente'])) {
    die("Utente non autenticato.");
}

$query = $conn->prepare("SELECT id, nome, cfu FROM corsi where docente = ?");
if(!$query){
    die("Errore nella preparazione della query: ". $conn->error);
}

$query->bind_param("s",$_SESSION['id_utente']);
$query->execute();
$results = $query->get_result();
$activeCourses = $results->num_rows;
$students = 0;

$queryExam = $conn->prepare("SELECT * FROM esami e
                            JOIN corsi c ON c.id = e.corso
                            WHERE c.docente = ? AND completato != 1
                            ORDER BY data ASC");
$queryExam->bind_param("s",$_SESSION['id_utente']);


if(!$queryExam){
    die("Errore nella preparazione della query: ". $conn->error);
}
$queryExam->execute();
$resultsExam = $queryExam->get_result();


$esamiDaValutare = $conn->prepare("SELECT COUNT(*) FROM esami e
                                    JOIN corsi c ON c.id = e.corso
                                    WHERE c.docente = ? AND e.completato = 0");
$esamiDaValutare->bind_param("s",$_SESSION['id_utente']);
$esamiDaValutare->execute();
$esamiDaValutare->bind_result($daValutare);
$esamiDaValutare->fetch();
$esamiDaValutare->close();




$queryNotify = $conn->prepare("SELECT * FROM notifiche n
                                JOIN ricezioni r ON r.notifica = n.id
                                WHERE r.docente=?");
$queryNotify->bind_param("s",$_SESSION['id_utente']);
$queryNotify->execute();
$resultsNotify = $queryNotify->get_result();


$mesi = [
    "01" => "Gennaio", "02" => "Febbraio", "03" => "Marzo",
    "04" => "Aprile", "05" => "Maggio", "06" => "Giugno",
    "07" => "Luglio", "08" => "Agosto", "09" => "Settembre",
    "10" => "Ottobre", "11" => "Novembre", "12" => "Dicembre"
];


$queryEliminaNotifica = $conn->prepare("DELETE FROM ricezioni WHERE notifica = ?");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Portale Universitario</title>
    <link rel="stylesheet" href="../common/common.css">
    <link rel="stylesheet" href="dashboard.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=3.0">
</head>

<body>
    <?php include '../common/header.php'; ?>
    
    <div class="dashboard">
        <?php include '../common/sidebar.php'; ?>
    
    <main class="main-content">
        <h1>Dashboard</h1>
        <p>Benvenuto nella dashboard, prof. <?php echo $_SESSION['nome'].' '.$_SESSION['cognome']; ?></p>


        <!-- statistiche generali -->
        <div class="statsGrid">
            <div class="statCard">
                <a href='../corsi/corsi.php' style="text-decoration: none;">
                    <div class="statNumber"><?php echo $activeCourses?></div>
                    <div class="statLabel">Corsi Attivi</div>
                </a>
            </div>
            <div class="statCard">
                <a href='../esami/esami.php' style="text-decoration: none;">
                    <div class="statNumber"><?php echo $daValutare?></div>
                    <div class="statLabel">Esami Da Valutare</div>
                </a>
            </div>
        </div>


        <div class="mainGrid">
            <!-- Esami programmati -->
            <div class="spaceInGrid">
                <div class="spaceHeader">
                    <h3 class="spaceTitle">Prossimi Esami</h3>
                </div>
                <div class="spaceBody">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Corso</th>
                                <th>Data</th>
                                <th>Iscritti</th>
                                <th>Tipologia</th>
                                <th>Stato</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $queryIscritti = $conn->prepare('SELECT COUNT(*) FROM prenotazioni p
                                                                JOIN esami e ON e.id = p.esame
                                                                WHERE e.id = ?');
                            for($i = 0; $i < $resultsExam->num_rows && $i < 6; $i++ ):
                                $row = $resultsExam->fetch_assoc();

                                $data = $row['data']; // yyyy-mm-dd da trasformare in dd MM(scritto per esteso) yyyy. Per esempio 5 giugno 2025
                                list($anno, $mese, $giorno) = explode("-", $data);
                                $dataFormattata = "$giorno " . $mesi[$mese] . " $anno";

                                
                                $queryIscritti->bind_param('i', $row['id']);
                                $queryIscritti->execute();
                                $queryIscritti->bind_result($numeroIscrittiPerEsame);
                                $queryIscritti->fetch();
                                ?>
                                <tr><td><strong><?= htmlspecialchars($row['nome'])?></strong></td>
                                <td><?= $dataFormattata?></td>
                                <td><?= $numeroIscrittiPerEsame?></td>
                                <td><?= $row['tipologia']?></td>
                                <td><?= $row['prenotabile'] ? 'Prenotabile' : 'Non Prenotabile'?></td></tr>
                                
                                <?php endfor; ?>

                        </tbody>
                    </table>
                </div>
            </div>

            <!-- sezione delle notifiche  -->
            <div class="spaceInGrid">
                <div class="spaceHeader">
                    <h3 class="spaceTitle">Notifiche Recenti</h3>
                </div>
                <div class="spaceBody">
                    <?php 
                    while($row = $resultsNotify->fetch_assoc()):?>
                        <div class="notification-item" id="<?= $row['notifica']?>">
                            <div class="notification-content">
                                <h4><?= $row['titolo']?></h4>
                                <p><?= $row['descrizione']?></p>
                            </div>
                            <a class="btn btn-notification" id="<?= '$row[\'notifica\']'?>" title="Rimuovi notifica" onclick="rimuoviNotifica('<?= $row['notifica']?>')">🗑️</a>
                        </div>
                    <?php endwhile;?>
                </div>
            </div>
        </div>
        <div class="spaceInGrid">
            <div class="spaceHeader">
                <h3 class="spaceTitle">Azioni Rapide</h3>
            </div>
            <div class="spaceBody">
                <a href="../esami/nuovo_esame.php" class="btn btn-success">Programma Esame</a>
            </div>
        </div>
    </main>
    </div>

                    
    <?php include '../common/footer.php'; ?>
    <script src='../common/common.js'></script>
    <script src="dashboard.js"></script>
</body>
    
</html>