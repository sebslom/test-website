<?php
// Ustaw naglowek odpowiedzi na JSON i UTF-8
header('Content-Type: application/json; charset=utf-8');

// Obsluga JSON w ciele zapytania
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if (strpos($contentType, 'application/json') !== false) {
    $content = trim(file_get_contents("php://input"));
    $decoded = json_decode($content, true);
    if (is_array($decoded)) {
        $_POST = $decoded;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Adres e-mail, na ktory zostanie wyslana wiadomosc
    $to = "yaneprincipio@gmail.com"; 
    
    // Pobranie i filtrowanie danych z formularza
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : 'Anonim';
    
    // Obsluga pola email / emailOrPhone
    $emailOrPhone = '';
    if (isset($_POST['emailOrPhone'])) {
        $emailOrPhone = htmlspecialchars(trim($_POST['emailOrPhone']));
    } elseif (isset($_POST['email'])) {
        $emailOrPhone = htmlspecialchars(trim($_POST['email']));
    }
    
    $message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';
    
    // Walidacja pol
    if (empty($name) || empty($emailOrPhone) || empty($message)) {
        echo json_encode([
            "ok" => false,
            "message" => "Wszystkie pola są wymagane."
        ]);
        exit;
    }
    
    $subject = "Nowa wiadomosc z formularza portfolio od: " . $name;
    
    // Budowanie tresci maila
    $body = "Nowa wiadomosc z formularza kontaktowego portfolio:\n\n";
    $body .= "Imie i nazwisko: " . $name . "\n";
    $body .= "Kontakt (E-mail/Telefon): " . $emailOrPhone . "\n\n";
    $body .= "Tresc wiadomosci:\n" . $message . "\n\n";
    $body .= "--------------------------------------------------\n";
    $body .= "Wiadomosc wyslana automatycznie z Twojego portfolio.\n";

    // Naglowki maila
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8" . "\r\n";
    
    // Jesli podany zostal poprawny e-mail, uzyj go jako Reply-To / From
    if (filter_var($emailOrPhone, FILTER_VALIDATE_EMAIL)) {
        $headers .= "From: " . $emailOrPhone . "\r\n";
        $headers .= "Reply-To: " . $emailOrPhone . "\r\n";
    } else {
        $headers .= "From: noreply@slomportfolio.pl" . "\r\n";
    }

    // Wysylka wiadomosci
    if (mail($to, $subject, $body, $headers)) {
        echo json_encode([
            "ok" => true,
            "message" => "Wiadomość została wysłana!"
        ]);
    } else {
        echo json_encode([
            "ok" => false,
            "message" => "Wystąpił błąd podczas wysyłania."
        ]);
    }
} else {
    echo json_encode([
        "ok" => false,
        "message" => "Nieprawidłowa metoda żądania."
    ]);
}
?>
