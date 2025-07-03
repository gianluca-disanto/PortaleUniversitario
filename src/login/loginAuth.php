<?php
session_start();
require '../../config/db_connection.php';

header('Content-Type: application/json');

$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT matricola, nome, cognome, email, password FROM docenti WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $docente = $result->fetch_assoc();
        $id_utente = $docente['matricola'];

        if (password_verify($password, $docente['password'])) {
            $_SESSION['id_utente'] = $id_utente;
            $_SESSION['nome'] = $docente['nome'];
            $_SESSION['cognome'] = $docente['cognome'];
            $_SESSION['email'] = $docente['email'];

            echo json_encode(['success' => true]);
            exit();
        } else {
            echo json_encode(['success' => false, 'message' => "Password errata. Riprovare"]);
            $login_error = "Password errata. Riprovare";
        }
    }else {
        $login_error = "Utente non registrato. Riprovare, oppure riferirsi all'amministratore";
        echo json_encode(['success' => false, 'message' => "Utente non registrato. Riprovare, oppure riferirsi all'amministratore"]);
    }
}
?>