<?php
require '../../config/db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Metodo non consentito. Usa POST.'
    ]);
    exit;
}

$json = file_get_contents('php://input');
$dati = json_decode($json, true);

if ($dati === null && json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'success' => false,
        'message' => 'Errore nella decodifica del JSON ricevuto.'
    ]);
    exit;
}

if(empty($json)){
    //echo json_encode(['success' => false, 'message' => 'Mancanza di dati. Registrazione non possibile']);
    echo json_encode(['success' => false, 'message' => "Niente dati:"]);
    exit;
}

if($dati === null){
    echo json_encode(['success' => false, 'message' => 'Dati JSON non validi']);
    exit;
}

$nome = $dati['nome'];
$cognome = $dati['cognome'];
$cf = $dati['codice_fiscale'];
$email = $dati['email'];
$password =  $dati['password'];
$indirizzo =  $dati['indirizzo'];
$dipartimento = intval($dati['dipartimento']);
$ruolo = intval($dati['ruolo']);


$errore = '';

if(!isset($nome, $cognome, $cf, $email, $password, $indirizzo, $dipartimento, $ruolo)){
    $errore = 'Tutti i campi sono obbligatori';
}

$nomeRegex = "/^[a-zA-Zàèéìòù' ]{2,}$/u";

if (!preg_match($nomeRegex, $nome)) {
    $errore = "Nome non valido.";
}

if (!preg_match($nomeRegex, $cognome)) {
    $errore = "Cognome non valido.";
}

$cfRegex = "/^[a-z]{6}[0-9]{2}[a-z][0-9]{2}[a-z][0-9]{3}[a-z]$/i";
if (!preg_match($cfRegex, $cf)) {
    $errore = "Codice fiscale non valido.";
}

$emailRegex = "/^[^\\s@]+@university\\.[^\\s@]+$/";
if (!preg_match($emailRegex, $email)) {
    $errore = "Email non valida. Il dominio deve essere @university.it.";
}


$passwordRegex = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[?!@%]).{8,}$/";
if (!preg_match($passwordRegex, $password)) {
    $errore = "Password non valida. Deve contenere almeno 8 caratteri, una maiuscola, una minuscola, una cifra e un simbolo tra @?!%";
}


if ($dipartimento === '') {
    $errore = "Seleziona un dipartimento.";
}


if ($ruolo === '') {
    $errore = "Seleziona un ruolo.";
}



if ($errore != '') {
   echo json_encode(['success' => false, 'message' => $errore]);
   exit;
} else {
    
    //Da notare il vincolo di accesso concorrente per visualizzare l'ultima matricola, nel caso di registrazioni contemporanee
    $queryUltimaMatricola = $conn->prepare("SELECT matricola FROM docenti ORDER BY matricola DESC LIMIT 1 FOR UPDATE");
    if(!$queryUltimaMatricola->execute()){
        echo json_encode(['success' => false, 'message' => 'Errore. Non è possibile accedere al database']);
        exit;
    }
    
    $result = $queryUltimaMatricola->get_result();
    $ultimaMatricola = '';
    $nuovaMatricola = '';
    if($result-> num_rows === 0){
        $ultimaMatricola = 'D000';
    }else {
        $ultimaMatricola = $result->fetch_assoc()['matricola'];
    }
    
    $nuovaMatricola = assegnareMatricola($ultimaMatricola);
    if($nuovaMatricola == null || $ultimaMatricola == '' || $nuovaMatricola == '' || strstr($nuovaMatricola, 'D') == null){
        echo json_encode(['success' => false, 'message' => 'Errore di comunicazione con il server']);
        exit;
    }
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    try{
        
        $conn->begin_transaction();
        
        $queryRegistrazione = $conn->prepare("INSERT INTO docenti (matricola,nome,cognome,cf, email, password, indirizzo, ruolo, dipartimento)
                                            VALUES (?,?,?,?,?,?,?,?,?)");
        if(!$queryRegistrazione){
            echo json_encode(['success' => false, 'message' => 'Errore nella preparazione della query. La registrazione non è andata a buon fine.']);
            exit;
        }        
        $queryRegistrazione->bind_param('sssssssii',$nuovaMatricola, $nome, $cognome, $cf, $email, $passwordHash, $indirizzo, $ruolo, $dipartimento);


        if(!$queryRegistrazione->execute()){
            echo json_encode(['success' => false, 'message' => "Errore nell'esecuzione della query. La registrazione non è andata a buon fine."]);
            exit;
        }

        $queryNotifica = $conn->prepare("INSERT INTO notifiche (titolo, descrizione) VALUES (?, ?)");
        $titolo = 'Primo accesso';
        $descrizione = 'Gentile prof. '.$nome.' '.$cognome.', benvenuto!';
        $queryNotifica->bind_param("ss", $titolo, $descrizione);
        $queryNotifica->execute();

        $idNotifica = $conn->insert_id;

        $queryRicezione = $conn->prepare("INSERT INTO ricezioni (docente, notifica) VALUES (?,?)");
        $queryRicezione->bind_param("si", $nuovaMatricola, $idNotifica);
        $queryRicezione->execute();

        $conn->commit();     
        echo json_encode(['success' => true, 'message' => 'La registrazione è andata a buon fine']);
    }catch(Exception $error){
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $error->getMessage()]);
    }
}


function assegnareMatricola($lastMatricola){
    $temp = trim($lastMatricola);
    $len = strlen($temp) -1;
    
    if($temp == ''){
        return null;
    }
    $temp = str_replace('D', '' , $temp);
    $temp = intval($temp) + 1;
    $temp = (string) $temp;

    while(strlen($temp) < $len){
        $temp = '0'.$temp;
    }
    
    return 'D'.$temp;
}



?>