<?php
session_start();
require '../../config/db_connection.php';
require('../../vendor/setasign/fpdf/fpdf.php');

$id_esame = isset($_GET['id']) ? intval($_GET['id']) : null;

if(!$id_esame){
    die("ID Esame non comunicato");
}

$queryEsame = $conn->prepare("SELECT e.id, e.data, e.ora, e.sessione, e.luogo, e.tipologia,
                                    c.nome AS nome_corso, c.cfu, d.nome AS nome_docente, d.cognome  FROM esami e
                                JOIN corsi c ON e.corso = c.id
                                JOIN docenti d ON c.docente = d.matricola
                                WHERE d.matricola = ? AND e.id = ?");
$queryEsame->bind_param("si",$_SESSION['id_utente'], $id_esame);
$queryEsame->execute();
$esame = $queryEsame->get_result()->fetch_assoc();

$queryValutazioni = $conn->prepare("SELECT s.cognome, s.nome, s.matricola, v.voto, v.stato, v.lode, v.ammesso
                                    FROM valutazioni v
                                    JOIN studenti s ON v.studente = s.matricola
                                    WHERE v.esame = ?");
$queryValutazioni->bind_param("i", $id_esame);
$queryValutazioni->execute();
$valutazioni = $queryValutazioni->get_result();






// Crea PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Report esame', 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Corso: '.$esame['nome_corso'], 0, 0, 'L');
$pdf->Cell(0, 10, 'Docente: '.$esame['nome_docente'].' '.$esame['cognome'], 0, 0, 'R');
$pdf->Ln();
$pdf->Cell(0,0, 'CFU: '.$esame['cfu'], 0, 0,'L');
$pdf->Ln(20);

$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Informazioni dell\'esame', 0, 0, 'C');
$pdf->Ln();
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Data: '.$esame['data'], 0, 0, 'L');
$pdf->Cell(0, 10, 'Luogo: '.$esame['luogo'], 0, 0, 'R');
$pdf->Ln();
$pdf->Cell(0, 10, 'Ora: '.$esame['ora'], 0, 0, 'L');
$pdf->Cell(0, 10, 'Tipologia: '.$esame['tipologia'], 0, 0, 'R');
$pdf->Ln();
$pdf->Cell(0, 10, 'Sessione: '.$esame['sessione'], 0, 0, 'L');
$pdf->Ln();
$pdf->Cell(0,10, 'Numero studenti: '.$valutazioni->num_rows, 0,0,'L');
$pdf->Ln(20);


$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0,10, 'Lista studenti', 0,0,'L');
$pdf->Ln();

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(47, 10, 'Cognome', 1);
$pdf->Cell(47, 10, 'Nome', 1);
$pdf->Cell(47, 10, 'Matricola', 1);
$pdf->Cell(47, 10, 'Voto', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
while($row = $valutazioni->fetch_assoc()){
    
    $pdf->Cell(47,10,$row['cognome'],1,0,'L');
    $pdf->Cell(47,10,$row['nome'],1,0,'L');
    $pdf->Cell(47,10,$row['matricola'],1,0,'L');
    if($row['stato'] == 'Assente'){
        $pdf->Cell(47,10,"Assente",1,0,'L');
    } else if($row['lode'] and $row['voto'] == '30'){
        $pdf->Cell(47,10,'30 e Lode', 1,0,'L');
    } else if($row['voto'] == '0' and $row['stato'] == 'Presente' and $row['ammesso'] == 0){
        $pdf->Cell(47,10,'Non Ammesso',1,0,'L');
    } else if($row['voto'] == '0' and $row['stato'] == 'Assente' and $row['ammesso'] == 0){
        $pdf->Cell(47,10,'Assente',1,0,'L');
    }
    else {
        $pdf->Cell(47,10,$row['voto'],1,0,'L');
    }
    $pdf->Ln();
    
}

$pdf->Output('report_esame-'.$esame['nome_corso'].'pdf', 'I');
?>
