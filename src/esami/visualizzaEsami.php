<?php
session_start();
require '../../config/db_connection.php';

//Verifica se la sessione è ancora attiva
if (!isset($_SESSION['id_utente'])) {
    die("Utente non autenticato.");
}

// Verifica se è stato passato l'ID dell'esame
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID esame non specificato.");
}

$id_esame = $_GET['id'];

// Query per ottenere i dettagli dell'esame
$queryEsame = $conn->prepare("SELECT *
                              FROM corsi c
                              JOIN esami e ON c.id = e.corso
                              WHERE c.docente = ? AND e.id = ?");

$queryEsame->bind_param("si", $_SESSION['id_utente'], $id_esame);
$queryEsame->execute();
$risultatoEsame = $queryEsame->get_result();

if ($risultatoEsame->num_rows == 0) {
    die("Esame non trovato o non autorizzato.");
}

$esame = $risultatoEsame->fetch_assoc();
$completato = $esame['completato'];

// Query per ottenere le prenotazioni degli studenti
$queryPrenotazioni = $conn->prepare("SELECT pr.studente, pr.esame ,s.nome, s.cognome, s.matricola,
                                    pr.data, pr.ora
                                    FROM prenotazioni pr
                                    JOIN studenti s ON pr.studente = s.matricola
                                
                                    WHERE pr.esame = ?
                                    GROUP BY pr.studente, pr.esame
                                    ORDER BY s.cognome, s.nome
                                    ");
$queryPrenotazioni->bind_param("i", $id_esame);
$queryPrenotazioni->execute();
$prenotazioni = $queryPrenotazioni->get_result();

$queryValutazioni = $conn->prepare("SELECT stato, voto, lode, studente, ammesso FROM valutazioni v
                                    WHERE esame = ?");
$queryValutazioni->bind_param("i",$id_esame);
$queryValutazioni->execute();
$valutazioni = $queryValutazioni->get_result();

$valutazioniPerStudente = [];
while ($row = $valutazioni->fetch_assoc()){
    $valutazioniPerStudente[$row['studente']] = $row;
}

// Query per ottenere gli indicatori per ogni studente
$queryIndicatori = $conn->prepare("SELECT ind.id, ind.descrizione, s.matricola, sup.valore FROM studenti s
                                  JOIN valutazioni v ON s.matricola = v.studente
                                  JOIN supporto sup ON sup.valutazione = v.id
                                  JOIN indicatori ind ON ind.id = sup.indicatore
                                  WHERE v.esame = ?");

$queryIndicatori->bind_param("i", $id_esame);
$queryIndicatori->execute();
$indicatori = $queryIndicatori->get_result();

// Praticamente sto creando un array associativo. Cioé valori raggruppati per matricola
$indicatoriPerPrenotazione = [];
while ($indicatore = $indicatori->fetch_assoc()) {
    $indicatoriPerPrenotazione[$indicatore['matricola']][] = $indicatore;
}

$queryArgomenti = $conn->prepare("SELECT dv.id, v.studente, dv.tipologia, dv.descrizione FROM dettaglivalutazione dv
                                    JOIN valutazioni v ON v.id = dv.valutazione
                                    WHERE v.esame = ? ");
$queryArgomenti->bind_param("i",$id_esame);
$queryArgomenti->execute();
$argomenti = $queryArgomenti->get_result();

$argomentiPerPrenotazione = [];
while($argomento = $argomenti->fetch_assoc()){
    $argomentiPerPrenotazione[$argomento['studente']][] = $argomento;
}

$queryStats = $conn->prepare("SELECT AVG(v.voto) AS media, COUNT(*) AS promossi 
                            FROM valutazioni v
                            WHERE v.esame = ? AND v.ammesso = 1");
    $queryStats->bind_param("i",$id_esame);
if($completato){
   // echo $id_esame;

    $queryStats->execute();
    $stat = $queryStats->get_result()->fetch_assoc();
    $media = round($stat['media'],2);
    $promossi = $stat['promossi'];
}

$queryIndicatori = $conn->prepare("SELECT * FROM indicatori");
$queryIndicatori->execute();
$risultatoIndicatori = $queryIndicatori->get_result();


function formatData($data) {
    if (!$data) return 'Non specificato';
    $date = new DateTime($data);
    return $date->format('d/m/Y');
}

function formatDataOra($data, $ora) {
    if (!$data) return 'Non specificato';
    $date = new DateTime($data . ' ' . $ora);
    return $date->format('d/m/Y H:i');
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Visualizza Esame - Portale Universitario</title>
    <link rel="stylesheet" href="../common/common.css">
    <link rel="stylesheet" href="esami.css">
</head>

<body>
    <?php include '../common/header.php'; ?>
    
    <div class="dashboard">
        <?php include '../common/sidebar.php'; ?>

        <main class="main-content">
            <h1>Visualizza Esame</h1>
            <p><?php echo htmlspecialchars($esame['nome']); ?> - <?php echo formatData($esame['data']); ?></p>

            <!-- Riquadro informazioni esami -->
            <div class="exam-info-grid">
                <div class="info-card">
                    <h3>📅 Informazioni Esame</h3>
                    <div class="info-item">
                        <span class="info-label">Data:</span>
                        <span class="info-value"><?php echo formatData($esame['data']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Ora:</span>
                        <span class="info-value"><?php echo substr($esame['ora'], 0, 5); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Luogo:</span>
                        <span class="info-value"><?php echo htmlspecialchars($esame['luogo'] ?? 'Non specificato'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Sessione:</span>
                        <span class="info-value"><?php echo htmlspecialchars($esame['sessione'] ?? 'Non specificata'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tipologia:</span>
                        <span class="info-value"><?php echo htmlspecialchars($esame['tipologia'] ?? 'Non specificato'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Stato:</span>
                        <span class="info-value">
                            <?php 
                            if ($esame['completato']) {
                                echo '<span class="badge success">Completato</span>';
                            } else if(!$esame['completato']){
                                echo '<span class="badge wait">Programmato</span>';
                            }
                            ?>
                        </span>
                    </div>
                </div>

                <div class="info-card">
                    <h3>📝 Prenotazioni</h3>
                    <div class="info-item">
                        <span class="info-label">Inizio:</span>
                        <span class="info-value"><?php echo formatData($esame['inizioPrenotazione']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fine:</span>
                        <span class="info-value"><?php echo formatData($esame['finePrenotazione']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Stato:</span>
                        <span class="info-value">
                            <?php 
                            if ($esame['prenotabile']) {
                                echo '<span class="badge success">Aperte</span>';
                            } else if(!$esame['prenotabile']){
                                echo '<span class="badge danger">Chiuse</span>';
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="spaceBody">
                <a href="modifica_esame.php?id=<?php echo $id_esame; ?>" class="btn btn-success">Modifica esame</a>
            </div>
            <?php 
            
            
            $totalStudents = $prenotazioni->num_rows;/*
            $prenotazioni->data_seek(0);
            $valutati = 0;
            $promossi = 0;
            $bocciati = 0;
            $assenti = 0;
            
           
            $prenotazioni->data_seek(0);*/
            ?>

            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">📊 Statistiche Esame</h3>
                </div>
                <div class="card-body">
                    <div class="stats-summary">
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $totalStudents; ?></div>
                            <div class="stat-label">Iscritti</div>
                        </div>
                        <?php if($completato): ?>
                            <div class="stat-box">
                                <div class="stat-number"><?php echo $promossi; ?></div>
                                <div class="stat-label">Promossi</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-number"><?php echo $media; ?></div>
                                <div class="stat-label">Media</div>
                            </div>
                            
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- lista studenti -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">👥 Studenti Prenotati</h3>
                    <?php if(!$completato):?>
                            <button class="btn btn-success" onclick="salvaVoti('<?php echo $id_esame;?>')">💾 Salva Tutte le Valutazioni</button>
                    <?php endif;?>
                    
                </div>
                <div class="card-body">
                    <?php if ($prenotazioni->num_rows == 0): ?>
                        <div class="no-data">
                            <h4>Nessuna prenotazione</h4>
                            <p>Non ci sono studenti prenotati per questo esame.</p>
                        </div>
                    <?php else: ?>
                        <?php while($prenotazione = $prenotazioni->fetch_assoc()): ?>
                            <?php $id_form = $prenotazione['studente'];?>
                            <div class="student-row">
                                <div class="student-header" onclick="toggleStudent('<?php echo $id_form; ?>')">
                                    <div class="student-info">
                                        <div>
                                            <div class="student-name"><?php echo htmlspecialchars($prenotazione['cognome'] . ' ' . $prenotazione['nome']); ?></div>
                                            <div class="student-matricola">Matricola: <?php echo htmlspecialchars($prenotazione['matricola']); ?></div>
                                        </div>
                                    </div>
                                    <div class="student-actions">
                                    
                                        
                                        <span class="expand-icon" id="icon-<?php echo $id_form; ?>">▼</span>
                                    </div>
                                </div>
                                
                                <div class="grade-section" id="section-<?php echo $id_form; ?>">
                                        <form class="grade-form" id="form-<?php echo $id_form; ?>" data-completato="<?php echo $completato ? 'true' : 'false'; ?>">
                                            <input type="hidden" name="id" value="<?php echo $id_form; ?>">

                                            <!-- Stato Esame -->
                                            <div class="form-group">
                                                <label class="form-label">Stato Esame</label>
                                                
                                                
                                                
                                                <select class="form-select" name="stato" onchange="toggleVotoField('<?php echo $id_form; ?>')" <?php echo $completato ? 'disabled' : ''; ?> id="<?php echo $id_form;?>">
                                                    
                                                    <option value="">Seleziona...</option>
                                                    <option value="Presente" <?php if(isset($valutazioniPerStudente[$id_form])){ if($valutazioniPerStudente[$id_form]['stato'] == 'Presente') echo 'selected';} ?>>Presente</option>
                                                    <option value="Assente" <?php if(isset($valutazioniPerStudente[$id_form])){ if($valutazioniPerStudente[$id_form]['stato'] == 'Assente') echo 'selected';} ?>>Assente</option>
                                                </select>
                                            </div>

                                            <!-- Voto -->
                                            <div class="form-group">
                                                <label class="form-label">Voto</label>
                                                

                                                    <select class="form-select" name="voto" id="voto-<?php echo $id_form; ?>" onchange="toggleVotoField('<?php echo $id_form; ?>')" <?php echo $completato ? 'disabled' : '';?>>
                                                    
                                                    <option value="">Seleziona...</option>
                                                    <?php if($valutazioniPerStudente[$id_form]['stato'] ==='Presente'): ?>
                                                            
                                                        <option value="non_superato" <?php if(isset($valutazioniPerStudente[$id_form])){echo $valutazioniPerStudente[$id_form]['voto'] == 0 ? 'selected' : ''; }?>>Non Superato</option>
                                                        <?php for($i = 18; $i <= 30; $i++): ?>
                                                            <option value="<?php echo $i; ?>" <?php if(isset($valutazioniPerStudente[$id_form])){echo $valutazioniPerStudente[$id_form]['voto'] == $i ? 'selected' : '';} ?>><?php echo $i; ?></option>
                                                        <?php endfor; ?>
                                                    <?php elseif(!$completato): ?>
                                                        <option value="non_superato">Non Superato</option>
                                                        <?php for($i = 18; $i <= 30; $i++): ?>
                                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                        <?php endfor; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </div>

                                            <!-- Lode -->
                                            <div class="form-group">
                                                <label class="form-label">Lode</label>
                                                <label class="lode-checkbox">
                                                <?php 
                                                        $checked = '';
                                                        if(isset($valutazioniPerStudente[$id_form])){if($valutazioniPerStudente[$id_form]['lode'] == 1 and $valutazioniPerStudente[$id_form]['voto'] == '30'){ 
                                                                $checked = 'checked';
                                                            }
                                                        }?>
                                                    <input type="checkbox" name="lode" value="1"  <?=$checked;?>>
                                                    <span>Con Lode</span>
                                                </label>
                                            </div>
                                        </form>

                                
                                
                                
                                
                                
                                
                                
                                
                                
                                
                               
                                    <!-- sezione indicatori -->
                                    <div class="indicators-section">
                                        <div class="indicators-header">
                                            <h4>📈 Indicatori Performance</h4>
                                            
                                            <?php if (!$completato): ?>
                                            <button type="button" class="btn btn-outline btn-sm" onclick="aggiungiIndicatore('<?php echo $id_form; ?>')">
                                                ➕ Aggiungi Indicatore
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                            
                                        <div id="indicators-<?php echo $id_form; ?>">
                                            <?php 
                                            if ($completato) {
                                                
                                                // Visualizzo dati in sola lettura se l'esame è copletato
                                                if (!empty($indicatoriPerPrenotazione[$id_form])) {
                                                    foreach ($indicatoriPerPrenotazione[$id_form] as $indicatore) {
                                                        ?>
                                                        
                                                        <div class="indicator-item" data-id="<?php echo $indicatore['id']; ?>">
                                                        <span class="indicator-desc"><?php echo htmlspecialchars($indicatore['descrizione']); ?></span>
                                                        <span class="indicator-value"><?php echo htmlspecialchars($indicatore['valore']); ?></span>
                                                        </div>
                                                        <?php
                                                    }
                                                } else {
                                                    echo "<p>Nessun indicatore disponibile.</p>";
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <template id="template-indicatore">
                                        <div class="indicator-item">
                                            <select name="indicatori[]" class="form-select">
                                                    <option value="">Seleziona...</option>
                                                <?php foreach($risultatoIndicatori as $elemento): ?>
                                                    <option value="<?= $elemento['id'] ?>"><?= htmlspecialchars($elemento['descrizione']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="number" class="indicator-value" name="valori[]" placeholder="1-10" min="1" max="10" onchange="valoreIndicatore(this)">
                                            <button type="button" class="btn btn-success btn-sm" onclick="inserimentoManuale.call(this)">Inserimento manuale</button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">🗑️</button>
                                        </div>
                                    </template>


                                        <!-- Argomenti Valutazione -->
                                        <div class="arguments-section" style="margin-top: 30px;">
                                            <div class="arguments-header">
                                                <h4>📝 Argomenti Valutazione</h4>
                                                <?php if (!$completato): ?>

                                            
                                                <button type="button" class="btn btn-outline btn-sm" onclick="aggiungiArgomento('<?php echo $id_form; ?>')">
                                                    ➕ Aggiungi Argomento
                                                </button>
                                                <?php endif; ?>
                                            </div>

                                            <div id="arguments-<?php echo $id_form; ?>">
                                                <?php 
                                                if ($completato):
                                                    // Mostro gli argomenti esistenti dal DB se l'esame è segnato come completato
                                                    if (isset($argomentiPerPrenotazione[$id_form]) && count($argomentiPerPrenotazione[$id_form]) > 0):
                                                        foreach ($argomentiPerPrenotazione[$id_form] as $argomento):
                                                ?>
                                                            <div class="argument-item" data-id="<?php echo $argomento['id']; ?>">
                                                                <select class="argument-tipologia" disabled>
                                                                    <option value="scritto" <?php echo ($argomento['tipologia'] === 'scritto') ? 'selected' : ''; ?>>Scritto</option>
                                                                    <option value="orale" <?php echo ($argomento['tipologia'] === 'orale') ? 'selected' : ''; ?>>Orale</option>
                                                                </select>
                                                                <span class="indicator-desc"><?php echo htmlspecialchars($argomento['descrizione']); ?></span>
                                                                
                                                            </div>
                                                <?php endforeach;?>
                                                <?php else: ?>
                                                        <div class="no-arguments-message">
                                                            <em>Nessun argomento presente.</em>
                                                        </div>
                                                <?php endif; ?>
                                                
                                                
                                                <?php endif; ?>
                                            </div>
                                        </div>


                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <?php include '../common/footer.php'; ?>
    <script src="../common/common.js"></script>
    <script src="visualizzaEsami.js"></script>
</body>
</html>
