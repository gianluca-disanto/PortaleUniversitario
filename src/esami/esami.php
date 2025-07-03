<?php
session_start();
require '../../config/db_connection.php';
require 'esamiController.php';

//Verifica se la sessione è ancora attiva
if (!isset($_SESSION['id_utente'])) {
    die("Utente non autenticato.");
}

$errorMessage = '';

$resultsExam = getEsami($conn, $_SESSION['id_utente'], $errorMessage);
if($resultsExam == null){
    die($errorMessage);
}


// Separazione degli esami in categorie per fare una tabella filtrata
$esami = getCategorieEsami($conn, $resultsExam, $errorMessage);
$tuttiEsami = $esami['tuttiEsami'];
$prossimi = $esami['prossimi'];
$completati = $esami['completati'];


$totaleEsami = count($tuttiEsami);
$inAttesa = count(array_filter($tuttiEsami, function($e) { return $e['completato'] == 0; }));
$daProgrammare = $totaleEsami - $inAttesa - count($completati);

$corsi = getCorsiDocente($conn, $_SESSION['id_utente'], $errorMessage);
if($corsi == null){
    die($errorMessage);
}


?>

<!DOCTYPE html>
<html>
<head>
    <title>Esami - Portale Universitario</title>
    <link rel="stylesheet" href="../common/common.css">
    <link rel="stylesheet" href="esami.css">

</head>

<body>
<?php include '../common/header.php'; ?>
    
    <div class="dashboard">
        <?php include '../common/sidebar.php'; ?>

        <main class="main-content">
                <h1>Gestione Esami</h1>
                <p>Visualizza e gestisci tutti gli esami programmati per i tuoi corsi</p>


           
            <div class="statsGrid">
                <div class="statCard">
                    <div class="statNumber"><?php echo $totaleEsami; ?></div>
                    <div class="statLabel">Esami Totali</div>
                </div>
                <div class="statCard">
                    <div class="statNumber"><?php echo $inAttesa; ?></div>
                    <div class="statLabel">In Attesa</div>
                </div>
                <div class="statCard">
                    <div class="statNumber"><?php echo count($completati); ?></div>
                    <div class="statLabel">Completati</div>
                </div>
            </div>

            <?php if (isset($_GET['prenotazione'])): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars('Programmazione effettuata con successo');
                              ?>
                              
                    </div>
                <?php endif; ?>

            
            <div class="mainGrid">
                <div class="spaceInGrid">
                    <div class="spaceHeader">
                    <h3 class="spaceTitle" data-icon="📝">Lista Esami</h3>
                    <a href="nuovo_esame.php" class="btn btn-primary">➕ Nuovo Esame</a>
                    </div>
                
                <div class="spaceBody">
                    
                    <div class="tabs">
                        <button class="tab active" onclick="showTab('tutti')">Tutti gli Esami</button>
                        <button class="tab" onclick="showTab('prossimi')">Prossimi</button>
                        <button class="tab" onclick="showTab('passati')">Completati</button>
                    </div>

                
                    <div class="filters">
                        <select class="filter-select" id="corsoFilter" onchange="filterTable()">
                            <option value="">Tutti i Corsi</option>
                            <?php 
                            $corsi->data_seek(0);
                            while($corso = $corsi->fetch_assoc()): ?>
                                <option value="<?php echo $corso['id']; ?>"><?php echo htmlspecialchars($corso['nome']); ?></option>
                            <?php endwhile; ?>
                        </select>
                        <select class="filter-select" id="statoFilter" onchange="filterTable()">
                            <option value="">Tutti gli Stati</option>
                            <option value="completato">Completato</option>
                            <option value="prenotabile">Prenotabile</option>
                            <option value="non prenotabile">Non Prenotabile</option>
                        </select>
                        <input type="date" class="filter-input" id="dataFilter" onchange="filterTable()">
                    </div>

                    <!-- Tab: Tutti gli Esami -->
                    <div id="tutti" class="tab-content active">
                        <?php if(empty($tuttiEsami)): ?>
                            <div class="no-data">
                                <h4>Nessun esame programmato</h4>
                                <p>Non hai ancora esami programmati per i tuoi corsi.</p>
                            </div>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Corso</th>
                                        <th>Data</th>
                                        <th>Ora</th>
                                        <th>Iscritti</th>
                                        <th>Stato</th>
                                        <th>Azioni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($tuttiEsami as $esame): ?>
                                        <tr data-corso="<?php echo $esame['corso']; ?>" data-data="<?php echo $esame['data_esame']; ?>">
                                            <td><strong><?php echo htmlspecialchars($esame['nome_esame']); ?></strong></td>
                                            <td><?php echo formatData($esame['data_esame']); ?></td>
                                            <td><?php echo substr($esame['ora_esame'], 0, 5); ?></td>
                                            <td><?php echo $esame['num_iscritti']; ?></td>
                                            <td><?php echo getStatoBadge($esame); ?></td>
                                            <td>
                                            <?php if(!$esame['completato']): ?>
                                                <a href="modifica_esame.php?id=<?php echo $esame['id']; ?>" class="btn btn-outline btn-sm">Modifica</a>
                                            <?php else: ?>
                                                <a href="report_esame.php?id=<?php echo $esame['id']; ?>" class="btn btn-outline btn-sm">Report</a>
                                            <?php endif;?>
                                                <a href="visualizzaEsami.php?id=<?php echo $esame['id']; ?>" class="btn btn-outline btn-sm">Visualizza </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                    <!-- Tab: Prossimi -->
                    <div id="prossimi" class="tab-content">
                        <?php if(empty($prossimi)): ?>
                            <div class="no-data">
                                <h4>Nessun esame in programma</h4>
                                <p>Non ci sono esami programmati per le prossime date.</p>
                            </div>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Corso</th>
                                        <th>Data</th>
                                        <th>Ora</th>
                                        <th>Iscritti</th>
                                        <th>Stato</th>
                                        <th>Azioni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($prossimi as $esame): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($esame['nome_esame']); ?></strong></td>
                                            <td><?php echo formatData($esame['data_esame']); ?></td>
                                            <td><?php echo substr($esame['ora_esame'], 0, 5); ?></td>
                                            <td><?php echo $esame['num_iscritti']; ?></td>
                                            <td><?php echo getStatoBadge($esame); ?></td>
                                            <td>
                                                <a href="modifica_esame.php?id=<?php echo $esame['id']; ?>" class="btn btn-outline btn-sm">Modifica</a>
                                                <a href="visualizzaEsami.php?id=<?php echo $esame['id']; ?>" class="btn btn-outline btn-sm">Visualizza </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                    <!-- Tab: Completati -->
                    <div id="passati" class="tab-content">
                        <?php if(empty($completati)): ?>
                            <div class="no-data">
                                <h4>Nessun esame completato</h4>
                                <p>Non ci sono ancora esami completati da visualizzare.</p>
                            </div>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Corso</th>
                                        <th>Data</th>
                                        <th>Ora</th>
                                        <th>Iscritti</th>
                                        <th>Media Voti</th>
                                        <th>Azioni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($completati as $esame): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($esame['nome_esame']); ?></strong></td>
                                            <td><?php echo formatData($esame['data_esame']); ?></td>
                                            <td><?php echo substr($esame['ora_esame'], 0, 5); ?></td>
                                            <td><?php echo $esame['partecipanti']."/".$esame['num_iscritti']; ?></td>
                                            <td><?php echo $esame['media_voti']; ?></td>
                                            <td>
                                                <a href="report_esame.php?id=<?php echo $esame['id']; ?>" class="btn btn-outline btn-sm">Report</a>
                                                <a href="visualizzaEsami.php?id=<?php echo $esame['id']; ?>" class="btn btn-outline btn-sm">Visualizza</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php include '../common/footer.php'; ?>                                  
    <script src="../common/common.js"></script>
    <script src="esami.js"></script>
</body>
</html>