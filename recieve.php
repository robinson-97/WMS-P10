<?php
include 'db.php'; // Databaseverbinding

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['po_id']) && isset($_POST['quantity'])) {
    $po_id = $_POST['po_id']; // Verkrijg PO ID
    $quantity = (int)$_POST['quantity']; // Verkrijg ingevoerde hoeveelheid

    try {
        // Haal de PO-informatie op
        $po_query = "SELECT po_number, location FROM purchase_orders WHERE id = :po_id AND status = 'open'";
        $po_stmt = $conn->prepare($po_query);
        $po_stmt->bindParam(':po_id', $po_id);
        $po_stmt->execute();
        $po = $po_stmt->fetch(PDO::FETCH_ASSOC);

        if ($po) {
            $po_number = $po['po_number'];
            $location = $po['location'];

            // Update voorraad in de stock-tabel
            $stock_query = "INSERT INTO stock (product_name, location, quantity)
                            VALUES (:product_name, :location, :quantity)
                            ON DUPLICATE KEY UPDATE quantity = quantity + :quantity";
            $stock_stmt = $conn->prepare($stock_query);
            $stock_stmt->bindParam(':product_name', $po_number);
            $stock_stmt->bindParam(':location', $location);
            $stock_stmt->bindParam(':quantity', $quantity);
            $stock_stmt->execute();

            // Update de status van de PO naar 'received'
            $update_po_query = "UPDATE purchase_orders SET status = 'received' WHERE id = :po_id";
            $update_po_stmt = $conn->prepare($update_po_query);
            $update_po_stmt->bindParam(':po_id', $po_id);
            $update_po_stmt->execute();

            echo "Product succesvol gereplenished!";
        } else {
            echo "Geen openstaande PO gevonden voor het opgegeven ID.";
        }
    } catch (Exception $ex) {
        echo "Er ging iets fout: " . $ex->getMessage();
    }
}

// Haal alle openstaande PO's op
$sql = "SELECT id, po_number, created_at, status FROM purchase_orders WHERE status = 'open'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$poList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Replenish Openstaande PO's</title>
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
<h1 style="text-align: center;">Replenish Openstaande Purchase Orders</h1>
<table>
    <thead>
    <tr>
        <th>PO Nummer</th>
        <th>Datum Aangemaakt</th>
        <th>Status</th>
        <th>Hoeveelheid</th>
        <th>Actie</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (count($poList) > 0) {
        foreach ($poList as $row) {
            echo "<tr>";
            echo "<td>" . $row['po_number'] . "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "<td>" . $row['status'] . "</td>";
            echo "<td>
                    <form method='POST' action='recieve.php'>
                        <input type='number' name='quantity' min='1' required>
                        <input type='hidden' name='po_id' value='" . $row['id'] . "'>
                        <button type='submit'>Replenish</button>
                    </form>
                  </td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>Geen openstaande PO's gevonden.</td></tr>";
    }
    ?>
    </tbody>
</table>
</body>
</html>
