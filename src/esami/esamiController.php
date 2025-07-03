<?php

function getEsami(mysqli $conn, string $docente, string &$message){
    $queryExam = $conn->prepare("SELECT e.id, c.nome as nome_esame, e.data AS data_esame, 
                            e.ora AS ora_esame, e.prenotabile AS prenotabile,
                            e.completato AS completato, e.tipologia AS tipologia, e.corso AS corso
                            FROM esami e
                            JOIN corsi c ON e.corso = c.id
                            WHERE c.docente = ?
                            ORDER BY e.completato ASC, e.data ASC, e.ora ASC");
    if(!$queryExam){
        if(isset($conn->error) && is_string($conn->error)){
            $message = $conn->error;
        }else{
            $message = "Errore nella preparazione della query";
        }
        return null;
    }
    $queryExam->bind_param("s", $_SESSION['id_utente']);
    if(!$queryExam->execute()){
        if(isset($conn->error) && is_string($conn->error)){
            $message = $conn->error;
        } else{
            $message = "Errore nell'esecuzione della query";
        }
        return null;
    }
    $result = $queryExam->get_result();
    $queryExam->close();
    return $result;
}

function getCorsiDocente(mysqli $conn, string $docente, string &$message){
    $queryCorsi = $conn->prepare("SELECT DISTINCT id, nome 
                             FROM corsi
                             WHERE docente = ?");
    if(!$queryCorsi){
        if(isset($conn->error) && is_string($conn->error)){
            $message = $conn->error;
        } else {
            $message = "Errore durante la preparazione della query";
        }
        return null;
    }
    $queryCorsi->bind_param("s", $docente);
    if(!$queryCorsi->execute()){
        if(isset($conn->error) && is_string($conn->error)){
            $message = $conn->error;
        } else {
            $message = "Errore durante l'esecuzione della query";
        }
        return null;
    }
    $corsi = $queryCorsi->get_result();
    $queryCorsi->close();
    return $corsi;
}

function getStatsPerEsame(mysqli $conn, int $id, string &$message){
    $queryMedia = $conn->prepare("SELECT AVG(v.voto) AS media_voti, COUNT(*) AS ammessi FROM valutazioni v
                                WHERE v.esame = ? AND v.ammesso = 1
                                ");
    if(!$queryMedia){
        if(isset($conn->error) && is_string($conn->error)){
            $message = $conn->error;
        } else {
            $message = "Errore durante la preparazione della query";
        }
        return null;
    }
    $queryMedia->bind_param("i", $id);
    if(!$queryMedia->execute()){
        if(isset($conn->error) && is_string($conn->error)){
            $message = $conn->error;
        } else {
            $message = "Errore durante l'esecuzione della query";
        }
        return null;
    }

    $stats = $queryMedia->get_result()->fetch_assoc(); //Per come è strutturata la query dovrebbe ritornare una sola riga, non un array
    $queryMedia->close();
    return $stats;
}

function getNumeroIscritti(mysqli $conn, int $id, string &$message){
    $queryIscritti = $conn->prepare('SELECT COUNT(*) AS numero_iscritti FROM prenotazioni p
                                WHERE p.esame = ? ');
    if(!$queryIscritti){
        if(isset($conn->error) && is_string($conn->error)){
            $message = $conn->error;
        } else {
            $message = "Errore durante la preparazione della query";
        }
        return null;
    }
    $queryIscritti->bind_param("i", $id);
    if(!$queryIscritti->execute()){
        if(isset($conn->error) && is_string($conn->error)){
            $message = $conn->error;
        } else {
            $message = "Errore durante l'esecuzione della query";
        }
        return null;
    }
    $iscritti = $queryIscritti->get_result()->fetch_assoc();
    $queryIscritti->close();
    return $iscritti;
}

function getStatoBadge($esame) {
    if($esame['completato'] == 1) return '<span class="badge success">Completato</span>';
    if($esame['prenotabile'] == 1) return '<span class="badge warning">prenotabile</span>';
    if($esame['prenotabile'] == 0) return '<span class="badge primary">non prenotabile</span>';
}

function formatData($data) {
    $date = new DateTime($data);
    return $date->format('d/m/Y');
}

function getCategorieEsami(mysqli $conn, mysqli_result $esami, string &$message){
    $tuttiEsami = [];
    $prossimi = [];
    $completati = [];
    $oggi = new DateTime();



    while($row = $esami->fetch_assoc() ) {
        $dataEsame = DateTime::createFromFormat('d/m/Y', $row['data_esame']);

        $iscrittiPerEsame = getNumeroIscritti($conn, $row['id'], $message);
        if($iscrittiPerEsame == null){
            die($message);
        }
        

        $row['num_iscritti'] = $iscrittiPerEsame['numero_iscritti'];
        
        $resultMedia = getStatsPerEsame($conn, $row['id'], $message);
        if($resultMedia == null){
            die($message);
        }
        $mediaVoti = $resultMedia['media_voti'];
        $ammessi = $resultMedia['ammessi'];
        $tuttiEsami[] = $row;
        
        if($row['completato'] == 1) {
            $row['partecipanti'] = $ammessi;
            $row['media_voti'] = round($mediaVoti,1);
            $completati[] = $row;
        } elseif($dataEsame >= $oggi) {
            $prossimi[] = $row;
        }
    } 

    return [
        'tuttiEsami' => $tuttiEsami,
        'prossimi' => $prossimi,
        'completati' => $completati
    ];

}
?>