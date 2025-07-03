<?php
session_start();
require '../../config/db_connection.php';

header('Content-Type: application/json');


//Verifica se la sessione è ancora attiva
if (!isset($_SESSION['id_utente'])) {
    die("Utente non autenticato.");
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    
    echo json_encode(['success' => false, 'message' => 'Richiesta non valida']);
    exit();
}

$json = file_get_contents('php://input');
$dati = json_decode($json, true);


$queryVerificaEsame = $conn->prepare("SELECT completato FROM esami WHERE id = ?");

$queryEliminazionePrenotazioni = $conn->prepare("DELETE FROM prenotazioni
                                                WHERE esame = ?");

$queryEliminazioneEsame = $conn->prepare("DELETE FROM esami
                                        WHERE id = ?");

if(isset($dati['idesame']) && $dati['eliminare']){
    $id_esame = $dati['idesame'];
    $queryEliminazionePrenotazioni->bind_param("i", $id_esame);
    $queryEliminazioneEsame->bind_param("i", $id_esame);
    $queryVerificaEsame->bind_param("i",$id_esame);


    try{
        

        $queryVerificaEsame->execute();
        $esameCompletato = $queryVerificaEsame->get_result()->fetch_assoc();
        if($esameCompletato['completato']){
            echo json_encode(['success' => false, 'message' => "Esame già completato. Effettuare una comunicazione all'amministratore"]);
            exit();
        }
        $conn->begin_transaction();
        if(!$queryEliminazionePrenotazioni->execute()){
            throw new Exception("Errore nella query per la cancellazione delle prenotazioni: ".$queryEliminazionePrenotazioni->error);
        }
        if(!$queryEliminazioneEsame->execute()){
            throw new Exception("Errore nella query per la cancellazione degli esami: ".$queryEliminazioneEsame->error);
            
        }
        $conn->commit();
        echo json_encode(['success' => true, 'message' => "Eliminazione esame avvenuta"]);

    }catch(Exception $e){
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => "Errore: ".$e->getMessage()]);
    }
}else {
    echo json_encode(['success' => false, 'message'=> "ID Esame mancante e/o condizioni non soddisfatte"]);
}
?>