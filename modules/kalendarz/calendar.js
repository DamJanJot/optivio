document.addEventListener('DOMContentLoaded', function() {
  const calendarContainer = document.getElementById('calendar-container');
  const eventList = document.getElementById('event-list');
  const selectedDay = document.getElementById('selected-day');
  const addEventForm = document.getElementById('add-event-form');
  const eventDateInput = document.getElementById('event-date');
  const addToggle = document.getElementById('add-toggle');
  const addFormContainer = document.getElementById('add-form-container');

  const today = new Date();
  let currentYear = today.getFullYear();
  let currentMonth = today.getMonth();
  let selectedDate = '';

  function escapeAttr(value) {
    return String(value ?? '').replaceAll('&', '&amp;').replaceAll('"', '&quot;').replaceAll("'", '&#039;').replaceAll('<', '&lt;').replaceAll('>', '&gt;');
  }

  function generateCalendar(year, month, events = []) {
    const monthNames = ['Styczeń', 'Luty', 'Marzec', 'Kwiecień', 'Maj', 'Czerwiec', 'Lipiec', 'Sierpień', 'Wrzesień', 'Październik', 'Listopad', 'Grudzień'];
    const weekdays = ['Pon', 'Wt', 'Śr', 'Czw', 'Pt', 'Sob', 'Nd'];
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const startDay = new Date(year, month, 1).getDay();

    let html = `<div class="month-nav"><button type="button" onclick="changeMonth(-1)">&lt;</button><span>${monthNames[month]} ${year}</span><button type="button" onclick="changeMonth(1)">&gt;</button></div><div class="days">`;
    weekdays.forEach(day => html += `<div class="weekday">${day}</div>`);

    for (let i = 0; i < (startDay === 0 ? 6 : startDay - 1); i++) html += '<div class="empty"></div>';
    for (let d = 1; d <= daysInMonth; d++) {
      const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
      const event = events.find(e => e.data === dateStr);
      const isToday = year === today.getFullYear() && month === today.getMonth() && d === today.getDate();
      const isSelected = selectedDate === dateStr;
      let style = event ? `background:${event.kolor};color:#fff;` : '';
      if (isToday) style += 'border-color:#facc15;';
      html += `<button type="button" class="day ${isSelected ? 'selected' : ''}" style="${style}" onclick="selectDay(${year}, ${month + 1}, ${d})">${d}</button>`;
    }
    html += '</div>';
    calendarContainer.innerHTML = html;
  }

  window.changeMonth = function(offset) {
    currentMonth += offset;
    if (currentMonth < 0) { currentMonth = 11; currentYear--; }
    else if (currentMonth > 11) { currentMonth = 0; currentYear++; }
    fetchEvents();
  };

  window.selectDay = function(year, month, day) {
    selectedDate = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    selectedDay.innerText = `Wydarzenia na ${selectedDate}`;
    eventDateInput.value = selectedDate;
    loadEvents();
    fetchEvents();
  };

  function fetchEvents() {
    fetch(`get-all-events.php?month=${currentMonth + 1}&year=${currentYear}`)
      .then(res => res.json())
      .then(data => generateCalendar(currentYear, currentMonth, data))
      .catch(() => {
        eventList.innerHTML = '<p>Nie udało się wczytać kalendarza.</p>';
      });
  }

  function loadEvents() {
    if (!selectedDate) {
      return;
    }

    fetch(`get-events.php?date=${selectedDate}`)
      .then(res => res.json())
      .then(data => {
        eventList.innerHTML = '';
        if (data.length === 0) {
          eventList.innerHTML = '<p>Brak wydarzeń.</p>';
          return;
        }

        data.forEach(ev => {
          const el = document.createElement('div');
          el.className = 'event';
          el.innerHTML = `<strong>${escapeAttr(ev.tytul)}</strong> ${ev.typ ? `(${escapeAttr(ev.typ)})` : ''}<br>${ev.opis ? `Opis: ${escapeAttr(ev.opis)}<br>` : ''}${ev.godzina ? `Godzina: ${escapeAttr(ev.godzina)}<br>` : ''}<button type="button" onclick="editEvent(${ev.id}, '${escapeAttr(ev.tytul)}', '${escapeAttr(ev.opis)}', '${escapeAttr(ev.typ)}', '${escapeAttr(ev.godzina)}', '${escapeAttr(ev.kolor)}')">Edytuj</button> <button type="button" onclick="deleteEvent(${ev.id})">Usuń</button>`;
          eventList.appendChild(el);
        });
      });
  }

  window.deleteEvent = function(id) {
    if (confirm('Na pewno chcesz usunąć?')) {
      fetch(`delete-event.php?id=${id}`).then(() => { loadEvents(); fetchEvents(); });
    }
  };

  window.editEvent = function(id, title, desc, type, hour, color) {
    const newTitle = prompt('Nowy tytuł:', title);
    if (newTitle === null) return;
    const newDesc = prompt('Nowy opis:', desc) ?? '';
    const newType = prompt('Nowy typ:', type) ?? '';
    const newHour = prompt('Nowa godzina:', hour) ?? '';
    const newColor = prompt('Nowy kolor:', color) ?? '#3478f6';

    fetch('edit-event.php', {
      method: 'POST',
      body: new URLSearchParams({ id, title: newTitle, description: newDesc, type: newType, hour: newHour, color: newColor })
    }).then(() => { loadEvents(); fetchEvents(); });
  };

  addToggle.addEventListener('click', () => {
    addFormContainer.style.display = addFormContainer.style.display === 'none' ? 'block' : 'none';
    if (!selectedDate) {
      window.selectDay(today.getFullYear(), today.getMonth() + 1, today.getDate());
    }
  });

  addEventForm.addEventListener('submit', function(e) {
    e.preventDefault();
    if (!eventDateInput.value) {
      window.selectDay(today.getFullYear(), today.getMonth() + 1, today.getDate());
    }
    fetch('save-event.php', { method: 'POST', body: new FormData(addEventForm) })
      .then(() => { addEventForm.reset(); eventDateInput.value = selectedDate; loadEvents(); fetchEvents(); });
  });

  fetchEvents();
});
