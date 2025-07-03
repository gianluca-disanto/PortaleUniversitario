<?php
session_start();
require '../../config/db_connection.php';
require('../../vendor/setasign/fpdf/fpdf.php');

$id_corso = isset($_GET['corso']) ? intval($_GET['corso']) : null;

if(!$id_corso){
    die("ID Corso non comunicato");
}

$queryDocente = $conn->prepare("SELECT c.nome AS nome_corso, c.cfu, d.nome AS nome_docente, d.cognome FROM corsi c
                                JOIN docenti d ON c.docente = d.matricola
                                WHERE d.matricola = ? AND c.id = ?");
$queryDocente->bind_param("si", $_SESSION['id_utente'], $id_corso);
$queryDocente->execute();
$docente = $queryDocente->get_result()->fetch_assoc();


$queryEsami = $conn->prepare("SELECT e.id, e.data, e.ora, e.sessione, e.luogo, e.tipologia,
                                    c.nome AS nome_corso, c.cfu, d.nome AS nome_docente, d.cognome FROM esami e
                                JOIN corsi c ON e.corso = c.id
                                JOIN docenti d ON c.docente = d.matricola
                                WHERE d.matricola = ? AND c.id = ? AND completato = 1");
$queryEsami->bind_param("si",$_SESSION['id_utente'], $id_corso);
$queryEsami->execute();
$esami = $queryEsami->get_result();

$queryIscritti = $conn->prepare("SELECT COUNT(*) AS count FROM prenotazioni
                                WHERE esame = ?");

$queryPromossi = $conn->prepare("SELECT COUNT(*) AS count FROM valutazioni v
                                WHERE esame = ? AND ammesso = 1");
    

$queryValutazioni = $conn->prepare("SELECT AVG(v.voto) AS media    
    FROM valutazioni v
    JOIN studenti s ON v.studente = s.matricola
    WHERE v.esame = ? AND ammesso = 1");



$studentiPromossi = 0;
$mediaVoti = 0;


//Inizio della struttura visiva del pdf

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Report corso', 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Corso: '.$docente['nome_corso'], 0, 0, 'L');
$pdf->Cell(0, 10, 'Docente: '.$docente['nome_docente'].' '.$docente['cognome'], 0, 0, 'R');
$pdf->Ln();
$pdf->Cell(0,0, 'CFU: '.$docente['cfu'], 0, 0,'L');
$pdf->Ln(20);



if($esami->num_rows >0 ){
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Lista esami completati', 0, 0, 'C');
    $pdf->Ln();

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(37, 10, 'Data', 1);
    $pdf->Cell(37, 10, 'Sessione', 1);
    $pdf->Cell(37, 10, 'Tipologia', 1);
    $pdf->Cell(37, 10, 'Promossi/Iscritti', 1);
    $pdf->Cell(37, 10, 'Media voti', 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 12);

    
    foreach($esami as $row){

        $queryValutazioni->bind_param("i", $row['id']);
        $queryValutazioni->execute();
        $valutazioni = $queryValutazioni->get_result()->fetch_assoc();
        $mediaVoti = round($valutazioni['media'],2);

        $queryIscritti->bind_param("i",$row['id']);
        $queryIscritti->execute();
        $numeroIscritti = $queryIscritti->get_result()->fetch_assoc()['count'];

        $queryPromossi->bind_param("i",$row['id']);
        $queryPromossi->execute();
        $studentiPromossi = $queryPromossi->get_result()->fetch_assoc()['count'];


        $pdf->Cell(37,10,$row['data'],1,0,'L');
        $pdf->Cell(37,10,$row['sessione'],1,0,'L');
        $pdf->Cell(37,10,$row['tipologia'],1,0,'L');
        $pdf->Cell(37,10, $studentiPromossi.'/'.$numeroIscritti,1,0,'L');
        $pdf->Cell(37,10, $mediaVoti,1,0,'L');
        
        
        $pdf->Ln();
        
    }
}else {
    $pdf->Cell(0,0,"Nessun esame completato");
}



$pdf->Output('report_corso'.$docente['nome_corso'].'.pdf', 'I'); 
?>
