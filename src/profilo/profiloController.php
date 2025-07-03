<?php

function getDatiDocente(mysqli $conn, string $matricola, string &$message){
    // Recupera informazioni complete del docente
    $queryProfilo = $conn->prepare("SELECT d.nome, d.cognome, d.matricola, d.email, dip.nome AS dipartimento, r.nome_ruolo AS ruolo,
                                    d.indirizzo FROM docenti d
                                    JOIN ruoli r ON r.id = d.ruolo
                                    JOIN dipartimenti dip ON d.dipartimento = dip.id
                                    WHERE matricola = ?");
    $queryProfilo->bind_param("s", $matricola);
    if(!$queryProfilo->execute()){
        if(isset($conn->error) && is_string($conn->error)){
            $message = $conn->error;
        } else{
            $message = "Errore nella fase di esecuzione della query";
        }
        return null;
    }
    return $queryProfilo->get_result();
    
}

function getStatsDocente(mysqli $conn, string $matricola, string &$message){
     // Recupera statistiche del docente
    $queryStats = $conn->prepare("SELECT 
        COUNT(DISTINCT c.id) as corsi_totali,
        COUNT(DISTINCT e.id) as esami_totali,
        COUNT(DISTINCT CASE WHEN e.completato = 1 THEN e.id END) as esami_completati
        FROM corsi c 
        LEFT JOIN esami e ON c.id = e.corso 
        WHERE c.docente = ?");
    $queryStats->bind_param("s", $matricola);
    if(!$queryStats->execute()){
        if(isset($conn->error) && is_string($conn->error)){
            $message = $conn->error;
        } else{
            $message = "Errore nella fase di esecuzione della query";
        }
        return null;
    }
    
    return $queryStats->get_result();
}

?>