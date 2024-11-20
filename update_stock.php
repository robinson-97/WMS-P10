<?php
include 'db.php'; // Zorg ervoor dat je databaseverbinding correct is ingesteld

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id']; // Verkrijg het ID van de voorraad
    $quantity = $_POST['quantity']; // Nieuwe hoeveelheid

    try {
        // SQL-query om de voorraad te updaten
        $sql = "UPDATE stock SET quantity = quantity + :quantity, updated_at = NOW() WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            echo "Voorraad succesvol geüpdatet!";
        } else {
            echo "Er is een fout opgetreden bij het updaten van de voorraad.";
        }
    } catch (Exception $ex) {
        echo 'Er ging iets fout bij het updaten: ' . $ex->getMessage();
    }

    // Redirect terug naar de stock info pagina
    header("Location: stock_info.php");
    exit();
}
?>
