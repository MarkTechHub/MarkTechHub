<?php
$servername = "localhost"; 
$username = "root";
$password = ""; 
$dbname = "user";

// Unda muunganisho wa database
$conn = new mysqli($servername, $username, $password, $dbname);

// Angalia kama kuna error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Pokea Data kutoka kwa Fomu
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST["name"]);
    $email = htmlspecialchars($_POST["email"]);
    $message = htmlspecialchars($_POST["message"]);

    // Andaa SQL Query
    $sql = "INSERT INTO messages (name, email, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $message);

    // Tekeleza na angalia kama imefanikiwa
    if ($stmt->execute()) {
        echo "<script>alert('Ujumbe wako umetumwa kwa mafanikio!'); window.location='index.html';</script>";
    } else {
        echo "<script>alert('Tatizo limetokea, tafadhali jaribu tena.');</script>";
    }

    // Funga muunganisho
    $stmt->close();
    $conn->close();
}
?>
