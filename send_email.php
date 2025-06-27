<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pokea data kutoka kwa fomu
    $name = htmlspecialchars($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $message = htmlspecialchars($_POST['message']);

    // Angalia kama barua pepe ni sahihi
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Barua pepe ya kupokea
        $to = "markmartinmwaitolola@gmail.com";  // Badilisha na barua pepe yako
        $subject = "Ujumbe kutoka MarkTechHub";   // Kichwa cha barua pepe

        // Maudhui ya ujumbe
        $message_body = "
        <html>
        <head>
        <title>Ujumbe kutoka MarkTechHub</title>
        </head>
        <body>
        <h2>Ujumbe kutoka kwa: $name</h2>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Ujumbe:</strong><br>$message</p>
        </body>
        </html>
        ";

        // Headings kwa barua pepe
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: $email" . "\r\n";
        $headers .= "Reply-To: $email" . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        // Tuma barua pepe
        if (mail($to, $subject, $message_body, $headers)) {
            // Ujumbe umeenda
            header("Location: thank_you.html");  // Redirect baada ya kutuma
            exit;
        } else {
            // Hitilafu ya kutuma
            echo "Imeshindikana kutuma ujumbe. Tafadhali jaribu tena baadaye.";
        }
    } else {
        // Barua pepe si sahihi
        echo "Barua pepe yako si sahihi.";
    }
}
?>
