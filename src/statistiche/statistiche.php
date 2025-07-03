<?php
session_start();
require '../../config/db_connection.php';
require 'statisticheController.php';

//Verifica se la sessione è ancora attiva
if (!isset($_SESSION['id_utente'])) {
    die("Utente non autenticato.");
}


$errorMessage = '';

$courses = getCorsi($conn, $_SESSION['id_utente'], $errorMessage);
if($courses == null){
    die($errorMessage);
}
$activeCourses = $courses->num_rows;

// esami programmati
$resultsExam = getEsamiProgrammati($conn, $_SESSION['id_utente'], $errorMessage);
if($resultsExam == null){
    die($errorMessage);
}
$examScheduled = $resultsExam->num_rows;

$promossi = getNumeroPromossi($conn, $_SESSION['id_utente'], $errorMessage);
if($promossi == null){
    die($errorMessage);
}
$totalPromossi = $promossi->fetch_assoc()['totale_promossi'];



$studenti = getTotaleStudenti($conn, $_SESSION['id_utente'], $errorMessage);
$totalStudents = $studenti->fetch_assoc()['totale_studenti'];


$totalBocciati = $totalStudents - $totalPromossi;
$mediaGenerale = 0;


$resultDistribuzione = getDistribuzioneVoti($conn, $_SESSION['id_utente'], $errorMessage);
if($resultDistribuzione == null){
    die($errorMessage);
}

$voti_labels = [];
$voti_values = [];
while($row = $resultDistribuzione->fetch_assoc()){
    $voti_labels[] = $row['voto'];
    $voti_values[] = $row['frequenza'];
}




if($totalPromossi > 0) {
    // media generale dai voti
    $resultMediaGen = getMediaGenerale($conn, $_SESSION['id_utente'], $errorMessage);
    if($resultMediaGen == null){
        die($errorMessage);
    }
    $media = $resultMediaGen->fetch_assoc()['media_generale'];
    $mediaGenerale = $media ? round($media, 2) : 0;
}

$percentualeSuccesso = $totalStudents > 0 ? round(($totalPromossi / $totalStudents) * 100, 1) : 0;


?>

<!DOCTYPE html>
<html>
<head>
    <title>Portale universitario - Statistiche</title>
    <link rel="stylesheet" href="../common/common.css">
    <link rel="stylesheet" href="statistiche.css">
    <style>

    </style>
</head>

<body>
    <?php include '../common/header.php'; ?>
    
    <div class="dashboard">
        <?php include '../common/sidebar.php'; ?>
    
    <main class="main-content">
        <h1>Statistiche</h1>
        <p>Analisi dettagliata delle performance degli esami</p>

        <div class="statsGrid">
            <div class="statCard">
                <div class="statNumber"><?php echo $totalStudents; ?></div>
                <div class="statLabel">Studenti Totali</div>
            </div>
            <div class="statCard success-rate">
                <div class="statNumber"><?php echo $totalPromossi; ?></div>
                <div class="statLabel">Promossi</div>
            </div>
            <div class="statCard fail-rate">
                <div class="statNumber"><?php echo $totalBocciati; ?></div>
                <div class="statLabel">Non Promossi</div>
            </div>
            <div class="statCard">
                <div class="statNumber"><?php echo $mediaGenerale ?: 'N/A'; ?></div>
                <div class="statLabel">Media Generale</div>
            </div>
        </div>

        <div class="stats-row">
            <div class="spaceInGrid">
                <div class="spaceHeader">
                    <h3 class="spaceTitle">Distribuzione Voti</h3>
                </div>
                <div class="spaceBody">
                    <?php if(count($voti_labels) != 0):?>
                    <table class="table">
                        <tr>
                            <th>Voto</th>
                            <th>Distribuzione</th>
                        </tr>
                        <?php for($i = 0; $i < count($voti_labels); $i++):?>
                            <tr>
                                <?php if($voti_labels[$i] != 0):?>
                                    <td><?php echo $voti_labels[$i];?></td>
                                    <td><?php echo round(100*$voti_values[$i]/$totalPromossi,2) . " %";?></td>
                                <?php endif; ?>
                            </tr>
                            <?php endfor;?>
                    </table> 
                    <br>
                    <strong><?php echo $percentualeSuccesso; ?>%</strong> di promozione <br>
                    <?php else: echo "<h3> Nessun dato su cui calcolare le statistiche </h3>";
                    endif;?> 
                </div>
            </div>
        </div>
    </main>
    </div>
    <?php include '../common/footer.php'; ?>
    <script src="../common/common.js"></script>
</body>
</html>