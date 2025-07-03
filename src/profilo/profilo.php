<?php
session_start();
require '../../config/db_connection.php';
require 'profiloController.php';

//Verifica se la sessione è ancora attiva
if (!isset($_SESSION['id_utente'])) {
    die("Utente non autenticato.");
}

$message = "";
$messageType = "";

$datiDocente = getDatiDocente($conn, $_SESSION['id_utente'], $message);
$datiStatistiche = getStatsDocente($conn, $_SESSION['id_utente'], $message);
if($datiDocente == null || $datiStatistiche == null){
    die($message);
}
$profilo = $datiDocente->fetch_assoc();
$stats = $datiStatistiche->fetch_assoc();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Profilo - Portale Universitario</title>
    <link rel="stylesheet" href="../common/common.css">
    <link rel="stylesheet" href="profilo.css">

</head>

<body>
    <?php include '../common/header.php'; ?>
    
    <div class="dashboard">
        <?php include '../common/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="profile-container">
                <h1>Il mio Profilo</h1>
                
                <?php if (!empty($message)): ?>
                    <div class="message <?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <div class="profile-header">
                    <div class="profile-photo">
                        <div class="default-avatar">
                            <?php echo strtoupper(substr($profilo['nome'], 0, 1) . substr($profilo['cognome'], 0, 1)); ?>
                        </div>
                    </div>
                    
                    <div class="profile-info">
                        <h2>Prof. <?php echo htmlspecialchars($profilo['nome'] . ' ' . $profilo['cognome']); ?></h2>
                        <p><strong>Matricola Docente:</strong> <?php echo htmlspecialchars($profilo['matricola']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($profilo['email']); ?></p>
                        <p><strong>Ruolo:</strong> <?php echo htmlspecialchars($profilo['ruolo']); ?></p>
                        <p><strong>Dipartimento:</strong> <?php echo htmlspecialchars($profilo['dipartimento']); ?></p>
                        
                        <div class="stats-mini">
                            <div class="stat-mini">
                                <div class="stat-mini-number"><?php echo $stats['corsi_totali']; ?></div>
                                <div class="stat-mini-label">Corsi</div>
                            </div>
                            <div class="stat-mini">
                                <div class="stat-mini-number"><?php echo $stats['esami_totali']; ?></div>
                                <div class="stat-mini-label">Esami</div>
                            </div>
                            <div class="stat-mini">
                                <div class="stat-mini-number"><?php echo $stats['esami_completati']; ?></div>
                                <div class="stat-mini-label">Completati</div>
                            </div>
                        </div>
                    </div>
                </div>
                
            
                <div class="profile-tabs">
                    <button class="tab-button active" onclick="showTab('informazioni')">Informazioni</button>
                </div>
                
                <div class="tab-content">
            
                    <div id="informazioni" class="tab-pane active">
                        <h3>Informazioni Personali</h3>
                        <form method="POST" action="">
                            <div class="info-grid">
                                <div class="form-group">
                                    <label for="nome">Nome</label>
                                    <input type="text" id="nome" value="<?php echo htmlspecialchars($profilo['nome']); ?>" disabled>
                                </div>
                                
                                <div class="form-group">
                                    <label for="cognome">Cognome</label>
                                    <input type="text" id="cognome" value="<?php echo htmlspecialchars($profilo['cognome']); ?>" disabled>
                                </div>
                                
                                <div class="form-group">
                                    <label for="id_docente">Matricola Docente</label>
                                    <input type="text" id="id_docente" value="<?php echo htmlspecialchars($profilo['matricola']); ?>" disabled>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($profilo['email']); ?>" disabled>
                                </div>
                                <div class="form-group">
                                    <label for="indirizzo">Indirizzo</label>
                                    <input type="indirizzo" id="indirizzo" name="indirizzo" value="<?php echo htmlspecialchars($profilo['indirizzo']); ?>" disabled>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php include '../common/footer.php'; ?>
    <script src="../common/common.js"></script>
</body>
</html>