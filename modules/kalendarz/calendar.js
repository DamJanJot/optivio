
document.addEventListener('DOMContentLoaded', function() {
  const calendarContainer = document.getElementById('calendar-container');
  const eventList = document.getElementById('event-list');
  const selectedDay = document.getElementById('selected-day');
  const addEventForm = document.getElementById('add-event-form');
  const eventDateInput = document.getElementById('event-date');
  const titleInput = document.getElementById('event-title');
  const descInput = document.getElementById('event-description');
  const typeInput = document.getElementById('event-type');
  const hourInput = document.getElementById('event-hour');
  const colorInput = document.getElementById('event-color');
  const addToggle = document.getElementById('add-toggle');
  const addFormContainer = document.getElementById('add-form-container');

  const today = new Date();
  let currentYear = today.getFullYear();
  let currentMonth = today.getMonth();
  let selectedDate = '';

  function generateCalendar(year, month, events = []) {
    const monthNames = ['Styczeń', 'Luty', 'Marzec', 'Kwiecień', 'Maj', 'Czerwiec', 'Lipiec', 'Sierpień', 'Wrzesień', 'Październik', 'Listopad', 'Grudzień'];
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const startDay = new Date(year, month, 1).getDay();

    let html = `<div class='month-nav'><button onclick='changeMonth(-1)'>&lt;</button> ${monthNames[month]} ${year} <button onclick='changeMonth(1)'>&gt;</button></div><div class='days'>`;

    for (let i = 0; i < (startDay === 0 ? 6 : startDay - 1); i++) html += "<div class='empty'></div>";
    for (let d = 1; d <= daysInMonth; d++) {
      const dateStr = `${year}-${(month+1).toString().padStart(2,'0')}-${d.toString().padStart(2,'0')}`;
      const event = events.find(e => e.data === dateStr);
      const isToday = (year === today.getFullYear() && month === today.getMonth() && d === today.getDate());
      let style = event ? `background:${event.kolor};color:#fff;` : '';
      if(isToday) style += "border:2px solid yellow;";
      html += `<div class='day' style="${style}" onclick='selectDay(${year}, ${month + 1}, ${d})'>${d}</div>`;
    }
    html += "</div>";
    calendarContainer.innerHTML = html;
  }

  window.changeMonth = function(offset) {
    currentMonth += offset;
    if (currentMonth < 0) { currentMonth = 11; currentYear--; }
    else if (currentMonth > 11) { currentMonth = 0; currentYear++; }
    fetchEvents();
  };

  window.selectDay = function(year, month, day) {
    selectedDate = `${year}-${month.toString().padStart(2, "0")}-${day.toString().padStart(2, "0")}`;
    selectedDay.innerText = `Wydarzenia na ${selectedDate}`;
    eventDateInput.value = selectedDate;
    loadEvents();
  };

  function fetchEvents() {
    fetch('get-all-events.php?month=' + (currentMonth + 1) + '&year=' + currentYear)
    .then(res => res.json())
    .then(data => generateCalendar(currentYear, currentMonth, data));
  }

  function loadEvents() {
    fetch('get-events.php?date=' + selectedDate).then(res => res.json()).then(data => {
      eventList.innerHTML = '';
      if (data.length === 0) eventList.innerHTML = '<p>Brak wydarzeń.</p>';
      else data.forEach(ev => {
        const el = document.createElement('div');
        el.innerHTML = `<strong>${ev.tytul}</strong> (${ev.typ}) <br> Opis: ${ev.opis} <br> Godzina: ${ev.godzina} <br> <button onclick="editEvent(${ev.id}, '${ev.tytul}', '${ev.opis}', '${ev.typ}', '${ev.godzina}', '${ev.kolor}')">Edytuj</button> <button onclick="deleteEvent(${ev.id})">Usuń</button>`;
        eventList.appendChild(el);
      });
    });
  }

  window.deleteEvent = function(id) {
    if(confirm("Na pewno chcesz usunąć?"))
      fetch('delete-event.php?id=' + id).then(() => loadEvents());
  };

  window.editEvent = function(id, title, desc, type, hour, color) {
    const newTitle = prompt("Nowy tytuł:", title);
    const newDesc = prompt("Nowy opis:", desc);
    const newType = prompt("Nowy typ:", type);
    const newHour = prompt("Nowa godzina:", hour);
    const newColor = prompt("Nowy kolor:", color);
    if (newTitle !== null) {
      fetch('edit-event.php', {
        method: 'POST',
        body: new URLSearchParams({ id, title: newTitle, description: newDesc, type: newType, hour: newHour, color: newColor })
      }).then(() => loadEvents());
    }
  };

  addToggle.addEventListener('click', () => {
    addFormContainer.style.display = addFormContainer.style.display === 'none' ? 'block' : 'none';
  });

  addEventForm.addEventListener('submit', function(e) {
    e.preventDefault();
    fetch('save-event.php', { method: 'POST', body: new FormData(addEventForm) })
      .then(() => { addEventForm.reset(); loadEvents(); fetchEvents(); });
  });

  fetchEvents();
});
