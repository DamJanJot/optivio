<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Nauka angielskiego</title>
  <style>
    body { font-family: Arial; text-align: center; margin-top: 50px; background-color: #333; color: #fff; }
    select, input[type="text"], button {
      font-size: 1.2em; padding: 10px; margin: 5px;
    }
  </style>
</head>
<body>
  <h1>Nauka słówek</h1>

  <label for="category">Wybierz kategorię:</label>
  <select id="category">
    <option value="general">Ogólne</option>
    <option value="food">Jedzenie</option>
    <option value="travel">Podróże</option>
    <option value="work">Praca</option>
    <option value="home">Dom</option>
    <option value="body">Ciało</option>
    <option value="feelings">Uczucia</option>
    <option value="verbs">Czasowniki</option>
    <option value="school">Szkoła</option>
    <option value="time">Czas</option>
    <option value="colors">Kolory</option>
  </select>

<hr>
<div style="height: 10vh;"></div>
  <h2 id="word">Wczytywanie słowa...</h2>

  <form id="wordForm">
    <input type="hidden" id="word_id" name="word_id">
    <input type="text" id="translation" placeholder="Podaj tłumaczenie" required>
    <br>
    <button type="submit">Sprawdź</button>
  </form>

  <p id="result"></p>

  <script>
    const form = document.getElementById("wordForm");
    const categorySelect = document.getElementById("category");

    function fetchWord() {
      const category = categorySelect.value;
      fetch('get_word.php?category=' + encodeURIComponent(category))
        .then(response => response.json())
        .then(data => {
          document.getElementById("word").textContent = data.word_en || "Brak słówek!";
          document.getElementById("word_id").value = data.id || "";
          document.getElementById("translation").value = "";
          document.getElementById("result").textContent = "";
        });
    }

    form.addEventListener("submit", function(e) {
      e.preventDefault();
      const word_id = document.getElementById("word_id").value;
      const translation = document.getElementById("translation").value;

      fetch('check_word.php', {
        method: 'POST',
        body: new URLSearchParams({word_id, translation})
      })
      .then(response => response.text())
      .then(text => {
        document.getElementById("result").textContent = text;
        setTimeout(fetchWord, 1500);
      });
    });

    categorySelect.addEventListener("change", fetchWord);
    window.onload = fetchWord;
  </script>
</body>
</html>
