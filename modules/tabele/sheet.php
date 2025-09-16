<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['id'];
$db = new SQLite3('tabele.db');

$id = (int)$_GET['id'];
$sheet = $db->querySingle("SELECT * FROM sheets WHERE id = $id AND user_id = $user_id", true);
if (!$sheet) die("Arkusz nie istnieje.");

$columns = json_decode($sheet['columns'], true);
$content = json_decode($sheet['content'], true) ?? [];

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Arkusz</title>";
echo "<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css'>";
echo "<style>
body { background: #0e0e12; color: #ccc; font-family: Arial; margin: 20px; }
h1 { font-size: 18px; color: #888; margin-bottom: 15px; }
.sheet-container { overflow-x: auto; }
#sheet { border-collapse: collapse; background-color: #12131a; margin-bottom: 20px; }
#sheet th, #sheet td { border: 1px solid #2c2c38; padding: 12px; color: #ccc; width: 150px; text-align: left; font-size: 16px; }
#sheet th { background-color: #2a2b3a; color: #aaa; }
#sheet td:focus { background-color: #22222e; outline: none; }
.actions { margin-top: 15px; }
button, .icon-btn { padding: 10px 15px; background-color: #444; color: white; border: none; cursor: pointer; border-radius: 6px; margin-right: 10px; text-decoration: none; }
button:hover, .icon-btn:hover { background-color: #666; }
.back-btn { display: inline-block; margin-bottom: 20px; color: #aaa; text-decoration: none; background-color: #2a2c3a; padding: 8px 12px; border-radius: 6px; }
.back-btn:hover { background-color: #3a3c4a; color: white; }
.add-row { text-align: center; cursor: pointer; background: #1f202a; color: #aaa; }
.add-row:hover { background: #333; color: white; }
</style>
</head><body>";

echo "<a class='back-btn' href='tabele.php'>← Powrót do listy</a>";
echo "<h1>" . htmlspecialchars($sheet['name']) . "</h1>";
echo "<div class='sheet-container'><table id='sheet'>";

// Nazwy kolumn
echo "<tr>";
foreach ($columns as $col) {
    echo "<th>" . htmlspecialchars($col) . "</th>";
}
echo "</tr>";

// Dane w tabeli
foreach ($content as $r => $row) {
    echo "<tr>";
    foreach ($columns as $c => $name) {
        $val = isset($row[$c]) ? htmlspecialchars($row[$c]) : '';
        echo "<td contenteditable='true' data-row='$r' data-col='$c'>$val</td>";
    }
    echo "</tr>";
}

echo "</table></div>";

echo "<div class='actions'>
<button onclick='save()'>Zapisz</button>
<button onclick='addRow()'>Dodaj wiersz</button>
<a class='icon-btn' href='export.php?id=$id&type=csv'><i class='fa-solid fa-file-csv'></i> CSV</a>
<a class='icon-btn' href='export.php?id=$id&type=xlsx'><i class='fa-solid fa-file-excel'></i> XLSX</a>
</div>";

echo "</body></html>";
?>

<script>
function save() {
    let rows = document.querySelectorAll("#sheet tr");
    let data = [];
    rows.forEach((r, i) => {
        if (i === 0) return;
        let cols = r.querySelectorAll("td");
        let rowData = [];
        cols.forEach(c => rowData.push(c.innerText));
        data.push(rowData);
    });

    fetch("save.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({id: <?php echo $id; ?>, content: data})
    }).then(() => alert("Zapisano!"));
}

function addRow() {
    let table = document.getElementById('sheet');
    let row = table.insertRow(-1);
    let cols = table.rows[0].cells.length;

    for (let i = 0; i < cols; i++) {
        let cell = row.insertCell();
        cell.contentEditable = true;
    }
}
</script>
