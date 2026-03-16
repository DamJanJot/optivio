<?php
function show_gallery($dir, $rel = "") {
    if (!is_dir($dir)) return;

    $items = scandir($dir);
    echo "<div class='gallery'>";
    foreach ($items as $item) {
        if ($item == "." || $item == "..") continue;
        $path = "$dir/$item";
        $rel_path = $rel ? "$rel/$item" : $item;

        if (is_dir($path)) {
            echo "<div class='folder'><a href='?folder=" . urlencode($rel_path) . "'>" . htmlspecialchars($item) . "</a></div>";
        } else {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) continue;

            $date_file = $path . ".txt";
            $date_added = file_exists($date_file) ? file_get_contents($date_file) : "brak daty";

            echo "<div class='item'>
        <img src=\"$path\" onclick=\"showPreview(this.src, '".htmlspecialchars($date_added)."')\">
        <button onclick='toggleInfo(this)'>Pokaż info</button>
        <div class='info' style='display:none;'>Dodano: $date_added</div>
        <form action='delete.php' method='post'>
            <input type='hidden' name='file' value='" . htmlspecialchars($path) . "'>
            <input type='submit' value='Usuń'>
        </form>
      </div>";

        }
    }
    echo "</div>";
}

show_gallery($current_dir, $folder);
?>
