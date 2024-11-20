<?php
include 'db.php';  // Zorg ervoor dat je db.php correct is ingesteld voor de databaseverbinding

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verkrijg het PO-nummer van het formulier
    $po_number = $_POST['po_number'];

    try {
        // Haal beschikbare locaties op uit de database
        $location_query = "SELECT name FROM locations";
        $location_stmt = $conn->prepare($location_query);
        $location_stmt->execute();
        $locations = $location_stmt->fetchAll(PDO::FETCH_COLUMN);

        // Controleer of er locaties beschikbaar zijn
        if (count($locations) > 0) {
            // Kies een willekeurige locatie
            $random_location = $locations[array_rand($locations)];

            // SQL-query om de PO op te slaan in de database met een willekeurige locatie
            $po_sql = "INSERT INTO purchase_orders (po_number, location, created_at, status) 
                       VALUES (:po_number, :location, NOW(), 'closed')";
            $po_stmt = $conn->prepare($po_sql);
            $po_stmt->bindParam(':po_number', $po_number);
            $po_stmt->bindParam(':location', $random_location);

            if ($po_stmt->execute()) {
                // Voeg standaardvoorraad toe aan de voorraad-tabel
                $stock_sql = "INSERT INTO stock (product_name, location, quantity) 
                              VALUES (:product_name, :location, 0)";
                $stock_stmt = $conn->prepare($stock_sql);
                $stock_stmt->bindParam(':product_name', $po_number); // PO-nummer als productnaam
                $stock_stmt->bindParam(':location', $random_location);

                if ($stock_stmt->execute()) {
                    echo "PO succesvol aangemaakt! Locatie toegewezen: " . $random_location;
                } else {
                    echo "Er is een fout opgetreden bij het toevoegen van de voorraad.";
                }
            } else {
                echo "Er is een fout opgetreden bij het aanmaken van de PO.";
            }
        } else {
            echo "Er zijn geen locaties beschikbaar in de database. Voeg locaties toe en probeer opnieuw.";
        }
    } catch (Exception $ex) {
        echo 'Er ging iets fout bij het aanmaken van de PO: ' . $ex->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nieuwe PO Aanmaken</title>
</head>
<body>
<h1>Nieuwe Purchase Order Aanmaken</h1>
<form method="POST" action="create_po.php">
    <label for="po_number">PO Nummer:</label>
    <input type="text" id="po_number" name="po_number" required>
    <button type="submit">Aanmaken</button>
</form>
</body>
</html>
