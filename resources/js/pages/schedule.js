// Schedule Page JavaScript
document.addEventListener('DOMContentLoaded', () => {
    console.log('Schedule page loaded');

    // State Management
    let currentView = 'month'; // 'month' or 'week'
    let currentDate = new Date();
    let currentWeekStart = null;

    // Sample events data (you can replace this with API calls)
    const events = {
        '2026-02-10': [{ title: 'Maintenance', desc: 'Machine A Maintenance', color: 'bg-yellow-100 text-yellow-800' }],
        '2026-02-14': [{ title: 'Valentine', desc: 'Special Production', color: 'bg-pink-100 text-pink-800' }],
        '2026-03-15': [{ title: 'Meeting', desc: 'Team Review', color: 'bg-blue-100 text-blue-800' }],
    };

    // Get elements
    const calendarGrid = document.getElementById('calendarGrid');
    const dayHeaders = document.getElementById('dayHeaders');
    const currentPeriodSpan = document.getElementById('currentPeriod');
    const prevPeriodBtn = document.getElementById('prevPeriod');
    const nextPeriodBtn = document.getElementById('nextPeriod');
    const todayBtn = document.getElementById('todayBtn');
    const viewBtns = document.querySelectorAll('.schedule-view-btn');

    // Helper Functions
    function getDaysInMonth(date) {
        return new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
    }

    function getFirstDayOfMonth(date) {
        return new Date(date.getFullYear(), date.getMonth(), 1).getDay();
    }

    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function getMonthName(date) {
        return date.toLocaleString('default', { month: 'long', year: 'numeric' });
    }

    function getWeekRange(date) {
        const start = new Date(date);
        start.setDate(date.getDate() - date.getDay()); // Start from Sunday
        const end = new Date(start);
        end.setDate(start.getDate() + 6);
        return { start, end };
    }

    function formatWeekRange(start, end) {
        const startMonth = start.toLocaleString('default', { month: 'short' });
        const endMonth = end.toLocaleString('default', { month: 'short' });
        const year = end.getFullYear();
        
        if (startMonth === endMonth) {
            return `${startMonth} ${start.getDate()}-${end.getDate()}, ${year}`;
        } else {
            return `${startMonth} ${start.getDate()} - ${endMonth} ${end.getDate()}, ${year}`;
        }
    }

    function isToday(date) {
        const today = new Date();
        return date.getDate() === today.getDate() &&
               date.getMonth() === today.getMonth() &&
               date.getFullYear() === today.getFullYear();
    }

    function getEventsForDate(date) {
        const dateStr = formatDate(date);
        return events[dateStr] || [];
    }

    // Render Month View
    function renderMonthView() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        const daysInMonth = getDaysInMonth(currentDate);
        const firstDay = getFirstDayOfMonth(currentDate);
        
        let html = '';
        
        // Empty cells before first day
        for (let i = 0; i < firstDay; i++) {
            html += '<div class="calendar-cell empty-cell"></div>';
        }
        
        // Day cells
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const dateStr = formatDate(date);
            const dayEvents = getEventsForDate(date);
            const isTodayClass = isToday(date) ? 'is-today' : '';
            
            html += `
                <div class="calendar-cell ${isTodayClass}">
                    <span class="day-number ${isToday(date) ? 'today-marker' : ''}">
                        ${day}
                    </span>
                    ${dayEvents.map(event => `
                        <div class="event-item ${event.color}">
                            <p class="event-title">${event.title}</p>
                            <p class="event-desc">${event.desc}</p>
                        </div>
                    `).join('')}
                </div>
            `;
        }
        
        // Empty cells after last day
        const totalCells = firstDay + daysInMonth;
        const remaining = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);
        for (let i = 0; i < remaining; i++) {
            html += '<div class="calendar-cell empty-cell"></div>';
        }
        
        calendarGrid.innerHTML = html;
        currentPeriodSpan.textContent = getMonthName(currentDate);
    }

    // Render Week View
    function renderWeekView() {
        if (!currentWeekStart) {
            const weekRange = getWeekRange(currentDate);
            currentWeekStart = weekRange.start;
        }
        
        let html = '';
        
        for (let i = 0; i < 7; i++) {
            const date = new Date(currentWeekStart);
            date.setDate(currentWeekStart.getDate() + i);
            const dayEvents = getEventsForDate(date);
            const isTodayClass = isToday(date) ? 'is-today' : '';
            
            html += `
                <div class="calendar-cell week-cell ${isTodayClass}">
                    <div class="week-day-header">
                        <span class="week-day-name">${date.toLocaleString('default', { weekday: 'short' }).toUpperCase()}</span>
                        <span class="day-number ${isToday(date) ? 'today-marker' : ''}">
                            ${date.getDate()}
                        </span>
                    </div>
                    <div class="week-events">
                        ${dayEvents.map(event => `
                            <div class="event-item ${event.color}">
                                <p class="event-title">${event.title}</p>
                                <p class="event-desc">${event.desc}</p>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }
        
        calendarGrid.innerHTML = html;
        
        const weekEnd = new Date(currentWeekStart);
        weekEnd.setDate(currentWeekStart.getDate() + 6);
        currentPeriodSpan.textContent = formatWeekRange(currentWeekStart, weekEnd);
    }

    // Navigation Functions
    function goToNextPeriod() {
        if (currentView === 'month') {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderMonthView();
        } else {
            currentWeekStart.setDate(currentWeekStart.getDate() + 7);
            renderWeekView();
        }
    }

    function goToPrevPeriod() {
        if (currentView === 'month') {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderMonthView();
        } else {
            currentWeekStart.setDate(currentWeekStart.getDate() - 7);
            renderWeekView();
        }
    }

    function goToToday() {
        currentDate = new Date();
        currentWeekStart = null;
        if (currentView === 'month') {
            renderMonthView();
        } else {
            renderWeekView();
        }
    }

    function switchView(view) {
        currentView = view;
        
        // Update button styles
        viewBtns.forEach(btn => {
            if (btn.dataset.view === view) {
                btn.classList.remove('bg-white', 'text-gray-600');
                btn.classList.add('bg-emerald-600', 'text-white');
            } else {
                btn.classList.remove('bg-emerald-600', 'text-white');
                btn.classList.add('bg-white', 'text-gray-600');
            }
        });
        
        // Update day headers visibility for week view
        if (view === 'week') {
            dayHeaders.style.display = 'none';
            renderWeekView();
        } else {
            dayHeaders.style.display = 'grid';
            renderMonthView();
        }
    }

    // Event Listeners
    prevPeriodBtn.addEventListener('click', goToPrevPeriod);
    nextPeriodBtn.addEventListener('click', goToNextPeriod);
    todayBtn.addEventListener('click', goToToday);
    
    viewBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            switchView(btn.dataset.view);
        });
    });

    // Initial render
    renderMonthView();
});
