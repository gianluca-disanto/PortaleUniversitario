<?php

function getEsamiProgrammati(mysqli $conn, string $matricola, string &$message){
    $queryExam = $conn->prepare("SELECT e.id, c.Nome as nome_esame, e.data AS data_esame, e.ora AS ora_esame, e.prenotabile AS prenotabile FROM esami e
                            JOIN corsi c ON e.corso = c.id
                            WHERE c.docente = ?");
    if(!$queryExam){
        if(isset($conn->error) && is_string($conn->error)){
            $message = $conn->error;
        }else{
            $message = "Errore nella preparazione della query";
        }            
        return null;
    }

    $queryExam->bind_param("s",$matricola);

    if(!$queryExam->execute()){
        if(isset($conn->error) && is_string($conn->error)){
            $message = $conn->error;
        }else{
            $message = "Errore nell'esecuzione della query";
        }
        $queryExam->close();
        return null;
    }
    $result = $queryExam->get_result();
    $queryExam->close(); 
    return $result;
}

function getCorsi(mysqli $conn, string $matricola, string &$message){
    $query = $conn->prepare("SELECT id FROM corsi where docente = ?");
    if(!$query){
        if(isset($conn->error) && is_string($conn->error)){
            $message = $conn->error;
        }else{
            $message = "Errore nella preparazione della query";
        }
        return null;
    }

    $query->bind_param("s",$matricola);

    if(!$query->execute()){
        if(isset($conn->error) && is_string($conn->error)){
            $message = $conn->error;
        }else{
            $message = "Errore nell'esecuzione della query";
        }
        $query->close();
        return null;
    }
    $result = $query->get_result();
    $query->close();
    return $result;
}

function getNumeroPromossi(mysqli $conn, string $matricola, string &$message){
    $queryPromossi = $conn->prepare("SELECT COUNT(*) as totale_promossi FROM valutazioni v
                                JOIN esami e ON v.esame = e.id
                                JOIN corsi c ON e.corso = c.id
                                WHERE c.docente = ? AND v.voto >=18 AND v.voto <=30");
    if(!$queryPromossi){
        if(isset($conn->error) && is_string($conn->error)){
            $message = $conn->error;
        }else{
            $message = "Errore nella preparazione della query";
        }
        return null;
    }

    $queryPromossi->bind_param("s",$matricola);

    if(!$queryPromossi->execute()){
        if(isset($conn->error) && is_string($conn->error)){
            $message = $conn->error;
        }else{
            $message = "Errore nell'esecuzione della query";
        }
        $queryPromossi->close();
        return null;
    }
    $result = $queryPromossi->get_result();
    $queryPromossi->close();
    return $result;
}

function getTotaleStudenti(mysqli $conn, string $matricola, string &$message){
    $queryStudenti = $conn->prepare("SELECT COUNT(*) as totale_studenti FROM valutazioni v
                                JOIN esami e ON v.esame = e.id
                                JOIN corsi c ON e.corso = c.id
                                WHERE c.docente = ?");
    if(!$queryStudenti){
        if(isset($conn->error) && is_string($conn->error)){
            $message = $conn->error;
        }else{
            $message = "Errore nella preparazione della query";
        }
        return null;
    }

    $queryStudenti->bind_param("s",$matricola);

    if(!$queryStudenti->execute()){
        if(isset($conn->error) && is_string($conn->error)){
            $message = $conn->error;
        }else{
            $message = "Errore nell'esecuzione della query";
        }
        $queryStudenti->close();
        return null;
    }
    $result = $queryStudenti->get_result();
    $queryStudenti->close();
    return $result;
}

function getDistribuzioneVoti(mysqli $conn, string $matricola, string &$message){
    $queryDistribuzione = $conn->prepare("SELECT voto, COUNT(*) AS frequenza
                                    FROM valutazioni v
                                    JOIN esami e ON v.esame = e.id
                                    JOIN corsi c ON e.corso = c.id
                                    WHERE c.docente = ?
                                    GROUP BY voto
                                    ORDER BY voto");
    if(!$queryDistribuzione){
        if(isset($conn->error) && is_string($conn->error)){
            $message = $conn->error;
        }else{
            $message = "Errore nella preparazione della query";
        }
        return null; 
    }

    $queryDistribuzione->bind_param("s",$matricola);

    if(!$queryDistribuzione->execute()){
        if(isset($conn->error) && is_string($conn->error)){
            $message = $conn->error;
        }else{
            $message = "Errore nell'esecuzione della query";
        }
        $queryDistribuzione->close();
        return null;
    }
    $result = $queryDistribuzione->get_result();
    $queryDistribuzione->close();
    return $result;
}

function getMediaGenerale(mysqli $conn, string $matricola, string &$message){
    $queryMediaGen = $conn->prepare("SELECT AVG(v.voto) as media_generale
                                    FROM valutazioni v
                                    JOIN esami e ON e.id = v.esame
                                    JOIN corsi c ON e.corso = c.id
                                    WHERE c.docente = ? AND ammesso = 1");
    if(!$queryMediaGen){
        if(isset($conn->error) && is_string($conn->error)){
            $message = $conn->error;
        }else{
            $message = "Errore nella preparazione della query";
        }
        return null;
    }

    $queryMediaGen->bind_param("s",$matricola);

    if(!$queryMediaGen->execute()){
        if(isset($conn->error) && is_string($conn->error)){
            $message = $conn->error;
        }else{
            $message = "Errore nell'esecuzione della query";
        }
        $queryMediaGen->close();
        return null;
    }
    $result = $queryMediaGen->get_result();
    $queryMediaGen->close();
    return $result;
}

?>