<?php

$baseDir = __DIR__ . '/uploads/';

function listFiles($dir) {
    $files = array_diff(scandir($dir), ['.', '..']);
    $result = [];
    foreach ($files as $file) {
        $filePath = $dir . '/' . $file;
        $result[] = [
            'name' => $file,
            'type' => is_dir($filePath) ? 'folder' : 'file',
            'path' => str_replace($GLOBALS['baseDir'], '', $filePath)
        ];
    }
    return $result;
}

if ($_GET['action'] === 'list') {
    $path = $baseDir . ($_GET['path'] ?? '');
    echo json_encode(listFiles($path));
}

if ($_GET['action'] === 'read') {
    $filePath = $baseDir . $_GET['path'];
    if (file_exists($filePath)) {
        echo file_get_contents($filePath);
    }
}

if ($_GET['action'] === 'save') {
    $data = json_decode(file_get_contents('php://input'), true);
    $filePath = $baseDir . $data['path'];
    file_put_contents($filePath, $data['content']);
    echo json_encode(['message' => 'Zapisano plik!']);
}

if ($_GET['action'] === 'upload') {
    $path = $baseDir . ($_POST['path'] ?? '');
    $targetFile = $path . '/' . $_FILES['file']['name'];
    move_uploaded_file($_FILES['file']['tmp_name'], $targetFile);
    echo json_encode(['message' => 'Przesłano plik!']);
}

if ($_GET['action'] === 'create') {
    $data = json_decode(file_get_contents('php://input'), true);
    $path = $baseDir . $data['path'] . '/' . $data['name'];
    if ($data['type'] === 'folder') {
        mkdir($path);
    } else {
        file_put_contents($path, '');
    }
    echo json_encode(['message' => 'Utworzono element!']);
}
?>
