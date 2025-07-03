<?php
session_start();
require '../../config/db_connection.php';
header('Content-Type: application/json');

if(!isset($_SESSION['id_utente'])){
    echo json_encode(['success' => false, 'message' => 'Utente non riconosciuto']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] != 'POST'){
    echo json_encode(['success' => false, 'message' => 'Richiesta non valida']);
    exit();
}

$json = file_get_contents('php://input');
$dati = json_decode($json,true);

if ($dati === null || empty($dati)) {
    echo json_encode(['success' => false, 'message' => 'Dati JSON non validi o assenti.']);
    exit;
}

$richiesta = $dati['rimozione'];
$id = $dati['idNotifica'];


$queryNotificaDocente = $conn->prepare("SELECT COUNT(*) AS notifica_docente FROM ricezioni WHERE docente = ? AND notifica = ?");
$queryNotificaDocente->bind_param("si", $_SESSION['id_utente'], $id);

$queryTotaleNotifiche = $conn->prepare('SELECT COUNT(*) AS numero_notifiche FROM ricezioni WHERE notifica = ?');
$queryTotaleNotifiche->bind_param("i", $id);

$query = $conn->prepare("DELETE FROM ricezioni WHERE notifica = ? AND docente = ?");
$query->bind_param("is",$id,$_SESSION['id_utente']);
$eliminaNotifica = $conn->prepare("DELETE FROM notifiche WHERE id = ?");
$eliminaNotifica->bind_param('i', $id);

try{
    $conn->begin_transaction();
    if(!$queryNotificaDocente->execute()){
        throw new Exception($queryNotificaDocente->error);
    }
    
    $result = $queryNotificaDocente->get_result();
    
    $notificaDocente = $result->fetch_assoc()['notifica_docente'];
    
    if($notificaDocente != 0){
        
        

        if(!$queryTotaleNotifiche->execute()){
            throw new Exception($queryTotaleNotifiche->error);
        }
        
        $resultQueryNotifiche = $queryTotaleNotifiche->get_result();
        if(!$resultQueryNotifiche){
            throw new Exception("Errore nel recupero del numero totale di notifiche");
        }
        $totaleNotifiche = $resultQueryNotifiche->fetch_assoc()['numero_notifiche'];

        if(!$query->execute()){
            throw new Exception($query->error);
        }

        if($notificaDocente == $totaleNotifiche){
            if(!$eliminaNotifica->execute()){
                throw new Exception($eliminaNotifica->error);
            }
        }
    }else{
        throw new Exception("Non ci sono notifiche associate al docente");
    }
    
    

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Notifica eliminata con successo']);
}catch(Exception $e){
    $conn->rollback();
    echo json_encode(['success' => false, 'message'=> $e->getMessage()]);
}


?>