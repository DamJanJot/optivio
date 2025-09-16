<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: ../login.php');
    exit();
}
?>

<!doctype html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Mój Dysk – Mobilny UI</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/ef9d577567.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
      integrity="sha512-p3Yp+1lYf1KFZQ6l+Ma6R9oIL3G4o5leWcEojfVqrn7/oDCFzo1CtkRDVcxKfX8TU5t2StH3Q0ax8+YnvY3+qw=="
      crossorigin="anonymous" referrerpolicy="no-referrer" />


</head>
<body>

<h5 class="mb-4"><a href="../apki/apki.php" style="color: #aaa; text-decoration:none;">← Powrót</a></h5>
    <!--    <h4 class="text-center mb-3">📁 Mój Dysk</h4>-->
        

<div class="container py-1">
  <div class="card text-white mx-auto" style="max-width:800px;">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
       <h4 class="card-title mb-0"><img width="32" height="32" src="https://img.icons8.com/arcade/32/folder-tree.png" alt="folder-tree"/> Dysk</h4>   
        <div class="dropdown">
          <button
            class="btn btn-secondary btn-sm dropdown-toggle"
            type="button"
            id="actionsMenu"
            data-bs-toggle="dropdown"
            aria-expanded="false">
               
               <i class="fa-solid fa-plus "></i>
          </button>
          
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsMenu">
            <li><a class="dropdown-item" href="#" id="show-upload-form">Prześlij plik</a></li>
            <li><a class="dropdown-item" href="#" id="show-create-form">Nowy folder/plik</a></li>
          </ul>
        </div>
      </div>

        <!-- Lista plików i folderów -->
        <!-- Breadcrumb navigation -->
        <div id="path" class="d-flex align-items-center mb-3">
            <button id="go-back" class="btn-sm  btn-secondary d-none" type="button">
                <i class="fa-solid fa-arrow-left"></i>
            </button>
            <img width="24" height="24" src="https://img.icons8.com/arcade/32/root-server.png" alt="root-server"/> <span id="path-text"> root</span>
        </div>
      <!-- End breadcrumb -->
<hr>
      <div id="folder-content" class="row g-3"></div>

      <!-- Formularz upload (domyślnie ukryty) -->
      <form id="upload-form" class="mt-4 d-none" enctype="multipart/form-data">
        <input type="file" name="file" class="form-control mb-2" />
        <button type="submit" class="btn ">Wyślij</button>
      </form>

      <!-- Formularz tworzenia folderu/pliku (domyślnie ukryty) -->
      <form id="create-form" class="mt-4 d-none">
        <div class="input-group btn-sm">
          <input type="text" id="new-name" class="form-control" placeholder="Podaj nazwę..." />
          <select id="type-select" class="form-select">
            <option value="file">Plik</option>
            <option value="folder">Folder</option>
          </select>
          <button class="btn-lg p-2" type="submit"><i class="fa-solid fa-circle-plus"></i></button>
        </div>
      </form>

      <!-- Edytor pliku (domyślnie ukryty) -->
      <div id="file-editor" class="mt-4 d-none">
        <textarea id="editor" class="form-control mb-2" rows="8" placeholder="Edytuj plik..."></textarea>
        <button id="save-file" class="btn btn-success w-100">Zapisz</button>
      </div>
    </div>
  </div>
</div>

      
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>    
    
    <script src="script.js"></script>

</body>
</html>
	