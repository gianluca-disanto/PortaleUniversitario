<?php
session_start();
require '../../config/db_connection.php';

header('Content-Type: application/json');

//Verifica se la sessione è ancora attiva
if (!isset($_SESSION['id_utente'])) {
    die("Utente non autenticato.");
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("richiesta non pervenuta");
}

// Lettura dei dati JSON dalla richiesta
$json = file_get_contents('php://input');
$dati = json_decode($json, true);


if(!isset($_GET['esame'])){
    die("Richiesta non risolvibile");
}
$id_esame = $_GET['esame'];

if(empty($dati)){
    echo json_encode(['success' => false, 'message' => 'Dati assenti. Probabilmente nessuno studente valutato']);
    exit;
}

if ($dati === null) {
    echo json_encode(['success' => false, 'message' => 'Dati JSON non validi o assenti.']);
    exit;
}

$queryInserimentoIndicatori = $conn->prepare("INSERT INTO indicatori (descrizione) VALUES (?)");
$queryInserimentoValutazioni = $conn->prepare("INSERT INTO valutazioni (data, voto, lode, stato, ammesso, esame, studente)
                                                 VALUES (?,?,?,?,?,?,?)");
$queryInserimentoSupporto = $conn->prepare("INSERT INTO supporto (valutazione, indicatore, valore)
                                            VALUES (?,?,?)");
$queryInserimentoDettagli = $conn->prepare("INSERT INTO dettaglivalutazione (tipologia, descrizione, valutazione)
                                            VALUES (?,?,?)");
$queryEsameCompletato = $conn->prepare("UPDATE esami
                                        SET completato = 1, prenotabile = 0
                                        WHERE id = ?");
$queryEsameCompletato->bind_param("i", $id_esame);

$dataCorrente = date('Y-m-d');
$ammesso = 0;

try{
    $conn->begin_transaction();

    foreach($dati as $row){
        $voto = null;
        $lode = 0;
        $stato = null;
        $ammesso = 0;

        if(!isset($row['stato']) || $row['stato'] === ''){
            echo json_encode(['success' => false, 'message' => 'Campo "stato" mancante']);
            exit;
        }

        if ($row['stato'] == 'Presente' && (!isset($row['voto']) || $row['voto'] === '')) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Campo "voto" mancante']);
            exit;
        }
        if($row['id'] != ''){
            $id = $row['id'];
        }
        if($row['stato'] == 'Presente' || $row['stato'] == 'Assente'){
            $stato = $row['stato'];
        }
        if($row['voto'] == 'non_superato'){
            $voto = 0;
            $ammesso = 0;
        }else if($row['voto'] >= 18 && $row['voto'] <= 30){
            $voto = $row['voto'];
            $ammesso = 1;
        } else {
            $voto = 0;
        }

        if(isset($row['lode']) || $row['lode'] == '0' || $row['lode'] == '1'){
            $lode = $row['lode'];
        }
        
        
        
        $numeroIndicatori = $row['numeroIndicatori'];
        $numeroArgomenti = $row['numeroArgomenti'];
        $indicatori = [];
        $argomenti = [];
        for($i = 0 ; $i < $numeroIndicatori; $i++){
            $indicatori[$i]['idIndicatore'] = $row['idInd'.$i] ?? null;
            $indicatori[$i]['descrizione'] = $row['indDesc'.$i];
            $indicatori[$i]['valore'] = $row['indValore'.$i];
        }

        
        for($i = 0 ; $i < $numeroArgomenti; $i++){
            $argomenti[$i]['tipologia'] = $row['tipoArgomento'.$i];
            $argomenti[$i]['descrizione'] = $row['descArgomento'.$i];
        }

        
        $queryInserimentoValutazioni->bind_param("siisiis",$dataCorrente,$voto,$lode,$stato,$ammesso,$id_esame,$id);
        if (!$queryInserimentoValutazioni->execute()) {
            die(json_encode([
                'success' => false,
                'query' => 'valutazioni',
                'message' => $queryInserimentoValutazioni->error
            ]));
        }

        $id_valutazione = $conn->insert_id;


        $idIndicatori = [];
        for ($i = 0; $i < $numeroIndicatori; $i++) {
            $idIndicatore = $indicatori[$i]['idIndicatore'];
            $descrizione = $indicatori[$i]['descrizione'];
            $valore = $indicatori[$i]['valore'];
        
            if($idIndicatore == null && $descrizione != ''){
                $queryInserimentoIndicatori->bind_param("s", $descrizione);
                if (!$queryInserimentoIndicatori->execute()) {
                    die(json_encode([
                        'success' => false,
                        'query' => 'indicatori',
                        'message' => $queryInserimentoIndicatori->error
                    ]));
                }
                $idIndicatore = $conn->insert_id;
            }
            
            if($idIndicatore != null && $valore != ''){ //Sono sicuro che sia o un valore o null perché quando li copio dal JSON faccio il confronto
                $queryInserimentoSupporto->bind_param("iii",$id_valutazione, $idIndicatore, $valore);
                if (!$queryInserimentoSupporto->execute()) {
                    die(json_encode([
                        'success' => false,
                        'query' => 'supporto',
                        'valutazione' => $id_valutazione,
                        'indicatore' => $id_indicatore,
                        'message' => $queryInserimentoSupporto->error
                    ]));
                }    
            }

            
            
        }

        for ($i = 0; $i < $numeroArgomenti; $i++) {
            $tipologia = $argomenti[$i]['tipologia'];
            $descrizione = $argomenti[$i]['descrizione'];
            $queryInserimentoDettagli->bind_param("ssi", $tipologia, $descrizione, $id_valutazione);
            if (!$queryInserimentoDettagli->execute()) {
                die(json_encode([
                    'success' => false,
                    'query' => 'dettagliValutazione',
                    'message' => $queryInserimentoDettagli->error
                ]));
            }
        }

    }
    
    if(!$queryEsameCompletato->execute()){
        die(json_encode([
            'success' => false,
            'query' => 'esameCompletato',
            'message' => $queryEsameCompletato->error
        ]));
    }
    $conn->commit();
    echo json_encode(['success' =>true, 'message' => "Inserimento avvenuto"]);
}catch(Exception $e){
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
