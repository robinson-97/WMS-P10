<?php
include 'db.php'; // Zorg ervoor dat je databaseverbinding correct is ingesteld

// Haal alle locaties en hun voorraadinformatie op
$sql = "SELECT * FROM stock";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Info</title>
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
<h1 style="text-align: center;">Stock Information</h1>
<table>
    <thead>
    <tr>
        <th>Product</th>
        <th>Locatie</th>
        <th>Hoeveelheid</th>
        <th>Laatst Bijgewerkt</th>
        <th>Actie</th>
    </tr>
    </thead>
    <tbody>
    <?php
    // Controleer of er resultaten zijn
    if (count($stocks) > 0) {
        foreach ($stocks as $stock) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($stock['product_name']) . "</td>";
            echo "<td>" . htmlspecialchars($stock['location']) . "</td>";
            echo "<td>" . htmlspecialchars($stock['quantity']) . "</td>";
            echo "<td>" . htmlspecialchars($stock['updated_at']) . "</td>";
            echo "<td>
                    <form method='POST' action='update_stock.php'>
                        <input type='hidden' name='id' value='" . $stock['id'] . "'>
                        <input type='number' name='quantity' min='1' required>
                        <button type='submit'>Update</button>
                    </form>
                  </td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>Geen voorraadinformatie gevonden.</td></tr>";
    }
    ?>
    </tbody>
</table>
</body>
</html>
