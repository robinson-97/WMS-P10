<?php
include 'db.php';  // Zorg ervoor dat je db.php correct is ingesteld voor de databaseverbinding

// Controleer of het formulier is ingediend en de PO_id aanwezig is
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['po_id'])) {
    $po_id = $_POST['po_id']; // Verkrijg de PO ID

    // Update de status van de PO naar 'open' wanneer het product geunload is
    $sql = "UPDATE purchase_orders SET status = 'open' WHERE id = :po_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':po_id', $po_id);
    $stmt->execute();
}

// Query om openstaande PO's op te halen
$sql = "SELECT id, po_number, created_at, status FROM purchase_orders WHERE status = 'closed'"; // Alleen gesloten PO's ophalen
$stmt = $conn->prepare($sql);
$stmt->execute();

// Haal alle resultaten op
$poList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Openstaande PO's</title>
    <style>
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<h1 style="text-align: center;">Openstaande Purchase Orders</h1>
<table>
    <thead>
    <tr>
        <th>PO Nummer</th>
        <th>Datum Aangemaakt</th>
        <th>Status</th>
        <th>Actie</th>
    </tr>
    </thead>
    <tbody>
    <?php
    // Controleer of er resultaten zijn
    if (count($poList) > 0) {
        // Resultaten doorlopen
        foreach ($poList as $row) {
            echo "<tr>";
            echo "<td>" . $row['po_number'] . "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "<td>" . $row['status'] . "</td>";
            // Voeg de "Markeer als Geunload" knop toe
            echo "<td>
                    <form method='POST' action='po_list.php'>
                        <input type='hidden' name='po_id' value='" . $row['id'] . "'>
                        <button type='submit'>Markeer als Geunload</button>
                    </form>
                  </td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='4'>Geen openstaande PO's gevonden.</td></tr>";
    }
    ?>
    </tbody>
</table>
</body>
</html>
