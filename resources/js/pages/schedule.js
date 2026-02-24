// Schedule Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Schedule page loaded');

    // State Management
    var currentView = 'month';
    var currentDate = new Date();
    var currentWeekStart = null;

    // Build events from backend schedule notes
    var events = {};
    var priorityColors = {
        'high': 'bg-red-100 text-red-800',
        'medium': 'bg-yellow-100 text-yellow-800',
        'low': 'bg-blue-100 text-blue-800'
    };
    if (window.scheduleNotes) {
        window.scheduleNotes.forEach(function(note) {
            if (!events[note.schedule_date]) events[note.schedule_date] = [];
            events[note.schedule_date].push({
                title: note.title,
                desc: note.description || '',
                color: priorityColors[note.priority] || 'bg-blue-100 text-blue-800'
            });
        });
    }

    // Build order bars from backend orders
    var orderBars = [];
    var orderPriorityColors = {
        'Normal':  { bg: '#abcbf4', text: '#1e40af', border: '#93c5fd' },
        'High':    { bg: '#ffea95', text: '#92400e', border: '#fcd34d' },
        'Urgent':  { bg: '#fba8a8', text: '#991b1b', border: '#fca5a5' }
    };
    if (window.scheduleOrders) {
        window.scheduleOrders.forEach(function(order) {
            orderBars.push({
                id: order.id,
                orderId: order.order_id,
                title: order.customer_name,
                startDate: order.start_date,
                endDate: order.end_date,
                priority: order.priority,
                status: order.status,
                colors: orderPriorityColors[order.priority] || orderPriorityColors['Normal']
            });
        });
    }

    // Get elements
    var calendarGrid = document.getElementById('calendarGrid');
    var dayHeaders = document.getElementById('dayHeaders');
    var currentPeriodSpan = document.getElementById('currentPeriod');
    var prevPeriodBtn = document.getElementById('prevPeriod');
    var nextPeriodBtn = document.getElementById('nextPeriod');
    var todayBtn = document.getElementById('todayBtn');
    var viewBtns = document.querySelectorAll('.schedule-view-btn');

    // ========== Helper Functions ==========
    function getDaysInMonth(date) {
        return new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
    }

    function getFirstDayOfMonth(date) {
        return new Date(date.getFullYear(), date.getMonth(), 1).getDay();
    }

    function formatDate(date) {
        var year = date.getFullYear();
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var day = String(date.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
    }

    function getMonthName(date) {
        return date.toLocaleString('default', { month: 'long', year: 'numeric' });
    }

    function getWeekRange(date) {
        var start = new Date(date);
        start.setDate(date.getDate() - date.getDay());
        var end = new Date(start);
        end.setDate(start.getDate() + 6);
        return { start: start, end: end };
    }

    function formatWeekRange(start, end) {
        var startMonth = start.toLocaleString('default', { month: 'short' });
        var endMonth = end.toLocaleString('default', { month: 'short' });
        var year = end.getFullYear();
        if (startMonth === endMonth) {
            return startMonth + ' ' + start.getDate() + '-' + end.getDate() + ', ' + year;
        } else {
            return startMonth + ' ' + start.getDate() + ' - ' + endMonth + ' ' + end.getDate() + ', ' + year;
        }
    }

    function isToday(date) {
        var today = new Date();
        return date.getDate() === today.getDate() &&
               date.getMonth() === today.getMonth() &&
               date.getFullYear() === today.getFullYear();
    }

    function getEventsForDate(date) {
        var dateStr = formatDate(date);
        return events[dateStr] || [];
    }

    // ========== Order Bar Helpers ==========
    function getBarPosition(order, dateStr, weekStartStr, weekEndStr) {
        var effectiveStart = order.startDate < weekStartStr ? weekStartStr : order.startDate;
        var effectiveEnd = order.endDate > weekEndStr ? weekEndStr : order.endDate;
        if (dateStr === effectiveStart && dateStr === effectiveEnd) return 'single';
        if (dateStr === effectiveStart) return 'start';
        if (dateStr === effectiveEnd) return 'end';
        if (dateStr > effectiveStart && dateStr < effectiveEnd) return 'middle';
        return 'single';
    }

    function assignBarRows(ordersForWeek) {
        ordersForWeek.forEach(function(o) { o._row = 0; });
        var rows = [];
        ordersForWeek.forEach(function(order) {
            var placed = false;
            for (var r = 0; r < rows.length; r++) {
                var canPlace = rows[r].every(function(existing) {
                    return order.endDate < existing.startDate || order.startDate > existing.endDate;
                });
                if (canPlace) {
                    rows[r].push(order);
                    order._row = r;
                    placed = true;
                    break;
                }
            }
            if (!placed) {
                order._row = rows.length;
                rows.push([order]);
            }
        });
        return rows.length;
    }

    function getOrdersForWeekRow(weekStartDate) {
        var weekStartStr = formatDate(weekStartDate);
        var weekEndDate = new Date(weekStartDate);
        weekEndDate.setDate(weekEndDate.getDate() + 6);
        var weekEndStr = formatDate(weekEndDate);
        return orderBars.filter(function(order) {
            return order.startDate <= weekEndStr && order.endDate >= weekStartStr;
        });
    }

    // ========== Render Month View ==========
    function renderMonthView() {
        var year = currentDate.getFullYear();
        var month = currentDate.getMonth();
        var daysInMonth = getDaysInMonth(currentDate);
        var firstDay = getFirstDayOfMonth(currentDate);

        // Build a grid of weeks
        var weeks = [];
        var dayCounter = 1 - firstDay;

        while (dayCounter <= daysInMonth) {
            var week = [];
            for (var col = 0; col < 7; col++) {
                var d = dayCounter + col;
                if (d >= 1 && d <= daysInMonth) {
                    week.push({ day: d, date: new Date(year, month, d) });
                } else {
                    week.push(null);
                }
            }
            weeks.push({ cells: week });
            dayCounter += 7;
        }

        var html = '';

        weeks.forEach(function(weekObj) {
            var firstCell = null;
            for (var i = 0; i < weekObj.cells.length; i++) {
                if (weekObj.cells[i] !== null) { firstCell = weekObj.cells[i]; break; }
            }
            if (!firstCell) return;

            var sundayDate = new Date(firstCell.date);
            sundayDate.setDate(firstCell.date.getDate() - firstCell.date.getDay());
            var saturdayDate = new Date(sundayDate);
            saturdayDate.setDate(sundayDate.getDate() + 6);
            var weekStartStr = formatDate(sundayDate);
            var weekEndStr = formatDate(saturdayDate);

            var weekOrders = getOrdersForWeekRow(sundayDate);
            var totalBarRows = assignBarRows(weekOrders);

            weekObj.cells.forEach(function(cell) {
                if (!cell) {
                    html += '<div class="calendar-cell empty-cell"></div>';
                    return;
                }

                var date = cell.date;
                var dateStr = formatDate(date);
                var dayEvents = getEventsForDate(date);
                var isTodayClass = isToday(date) ? 'is-today' : '';

                html += '<div class="calendar-cell ' + isTodayClass + '" style="position:relative;padding-top:2rem;">';

                // Day number + add button
                html += '<div style="position:absolute;top:0.25rem;left:0.25rem;right:0.25rem;display:flex;align-items:center;justify-content:space-between;z-index:5;">';
                html += '<span class="day-number ' + (isToday(date) ? 'today-marker' : '') + '">' + cell.day + '</span>';
                html += '<button onclick="openScheduleModal(\'' + dateStr + '\')" class="add-date-btn" title="Add note" style="width:1.25rem;height:1.25rem;border-radius:50%;display:none;align-items:center;justify-content:center;font-size:0.875rem;color:#9ca3af;border:none;background:transparent;cursor:pointer;line-height:1;">+</button>';
                html += '</div>';

                // Order bars area
                if (totalBarRows > 0) {
                    html += '<div class="order-bars-area" style="margin-top:0.25rem;">';
                    for (var r = 0; r < totalBarRows; r++) {
                        var orderInRow = null;
                        for (var oi = 0; oi < weekOrders.length; oi++) {
                            var o = weekOrders[oi];
                            if (o._row === r && dateStr >= o.startDate && dateStr <= o.endDate) {
                                orderInRow = o;
                                break;
                            }
                        }
                        if (orderInRow) {
                            var pos = getBarPosition(orderInRow, dateStr, weekStartStr, weekEndStr);
                            var colors = orderInRow.colors;
                            var isStart = (pos === 'start' || pos === 'single') || (dateStr === weekStartStr && orderInRow.startDate < weekStartStr);
                            var isEnd = (pos === 'end' || pos === 'single') || (dateStr === weekEndStr && orderInRow.endDate > weekEndStr);
                            var isDeadline = dateStr === orderInRow.endDate;

                            var borderRadius = '0';
                            if (isStart && isEnd) borderRadius = '0.375rem';
                            else if (isStart) borderRadius = '0.375rem 0 0 0.375rem';
                            else if (isEnd) borderRadius = '0 0.375rem 0.375rem 0';

                            var barStyle = 'background-color:' + colors.bg + ';color:' + colors.text + ';height:1.375rem;display:flex;align-items:center;font-size:0.6875rem;font-weight:600;border-radius:' + borderRadius + ';overflow:hidden;white-space:nowrap;position:relative;';
                            if (isStart) {
                                barStyle += 'padding-left:0.375rem;';
                            }

                            html += '<div class="order-bar" style="' + barStyle + '" title="' + orderInRow.title + ' (' + orderInRow.priority + ') - Deadline: ' + orderInRow.endDate + '">';
                            if (isStart) {
                                html += '<span style="overflow:hidden;text-overflow:ellipsis;">' + orderInRow.title + '</span>';
                            }
                            if (isDeadline) {
                                html += '<span style="position:absolute;right:0.25rem;display:flex;align-items:center;">';
                                html += '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="' + colors.text + '" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';
                                html += '</span>';
                            }
                            if (isEnd && dateStr === orderInRow.endDate) {
                                html += '<span style="position:absolute;right:-0.25rem;top:50%;transform:translateY(-50%);width:0.5rem;height:0.5rem;border-radius:50%;background-color:#ef4444;border:1.5px solid white;z-index:2;"></span>';
                            }
                            html += '</div>';
                        } else {
                            html += '<div style="height:1.375rem;"></div>';
                        }
                    }
                    html += '</div>';
                }

                // Schedule note events
                for (var ei = 0; ei < dayEvents.length; ei++) {
                    var safeTitle = dayEvents[ei].title.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                    var safeDesc = (dayEvents[ei].desc || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                    html += '<div class="event-item ' + dayEvents[ei].color + '" style="cursor:pointer;" onclick="openViewNoteModal(\'' + safeTitle + '\', \'' + safeDesc + '\')"><p class="event-title">' + dayEvents[ei].title + '</p></div>';
                }

                html += '</div>';
            });
        });

        calendarGrid.innerHTML = html;
        currentPeriodSpan.textContent = getMonthName(currentDate);

        // Add hover effect to show + buttons
        var cells = calendarGrid.querySelectorAll('.calendar-cell:not(.empty-cell)');
        cells.forEach(function(cell) {
            var btn = cell.querySelector('.add-date-btn');
            if (btn) {
                cell.addEventListener('mouseenter', function() { btn.style.display = 'flex'; });
                cell.addEventListener('mouseleave', function() { btn.style.display = 'none'; });
            }
        });
    }

    // ========== Render Week View ==========
    function renderWeekView() {
        if (!currentWeekStart) {
            var weekRange = getWeekRange(currentDate);
            currentWeekStart = weekRange.start;
        }

        var weekStartStr = formatDate(currentWeekStart);
        var weekEndDate = new Date(currentWeekStart);
        weekEndDate.setDate(currentWeekStart.getDate() + 6);
        var weekEndStr = formatDate(weekEndDate);

        var weekOrders = getOrdersForWeekRow(currentWeekStart);
        var totalBarRows = assignBarRows(weekOrders);

        var html = '';

        for (var i = 0; i < 7; i++) {
            var date = new Date(currentWeekStart);
            date.setDate(currentWeekStart.getDate() + i);
            var dateStr = formatDate(date);
            var dayEvents = getEventsForDate(date);
            var isTodayClass = isToday(date) ? 'is-today' : '';

            html += '<div class="calendar-cell week-cell ' + isTodayClass + '">';
            html += '<div class="week-day-header">';
            html += '<span class="week-day-name">' + date.toLocaleString('default', { weekday: 'short' }).toUpperCase() + '</span>';
            html += '<span class="day-number ' + (isToday(date) ? 'today-marker' : '') + '">' + date.getDate() + '</span>';
            html += '</div>';
            html += '<div class="week-events">';

            // Order bars in week view
            if (totalBarRows > 0) {
                for (var r = 0; r < totalBarRows; r++) {
                    var orderInRow = null;
                    for (var oi = 0; oi < weekOrders.length; oi++) {
                        var o = weekOrders[oi];
                        if (o._row === r && dateStr >= o.startDate && dateStr <= o.endDate) {
                            orderInRow = o;
                            break;
                        }
                    }
                    if (orderInRow) {
                        var colors = orderInRow.colors;
                        var isStart = dateStr === orderInRow.startDate || (dateStr === weekStartStr && orderInRow.startDate < weekStartStr);
                        var isEnd = dateStr === orderInRow.endDate || (dateStr === weekEndStr && orderInRow.endDate > weekEndStr);
                        var isDeadline = dateStr === orderInRow.endDate;

                        var borderRadius = '0';
                        if (isStart && isEnd) borderRadius = '0.375rem';
                        else if (isStart) borderRadius = '0.375rem 0 0 0.375rem';
                        else if (isEnd) borderRadius = '0 0.375rem 0.375rem 0';

                        html += '<div class="order-bar" style="background-color:' + colors.bg + ';color:' + colors.text + ';padding:0.25rem 0.375rem;border-radius:' + borderRadius + ';font-size:0.75rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;position:relative;min-height:1.5rem;display:flex;align-items:center;" title="' + orderInRow.title + ' (' + orderInRow.priority + ')">';
                        if (isStart) {
                            html += orderInRow.title;
                        }
                        if (isDeadline) {
                            html += '<span style="margin-left:auto;display:flex;align-items:center;"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="' + colors.text + '" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></span>';
                        }
                        if (isEnd && dateStr === orderInRow.endDate) {
                            html += '<span style="position:absolute;right:-0.25rem;top:50%;transform:translateY(-50%);width:0.5rem;height:0.5rem;border-radius:50%;background-color:#ef4444;border:1.5px solid white;z-index:2;"></span>';
                        }
                        html += '</div>';
                    } else {
                        html += '<div style="min-height:1.5rem;"></div>';
                    }
                }
            }

            // Schedule note events
            for (var ei = 0; ei < dayEvents.length; ei++) {
                var safeTitle = dayEvents[ei].title.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                var safeDesc = (dayEvents[ei].desc || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                html += '<div class="event-item ' + dayEvents[ei].color + '" style="cursor:pointer;" onclick="openViewNoteModal(\'' + safeTitle + '\', \'' + safeDesc + '\')"><p class="event-title">' + dayEvents[ei].title + '</p><p class="event-desc">' + dayEvents[ei].desc + '</p></div>';
            }

            html += '</div></div>';
        }

        calendarGrid.innerHTML = html;

        var weekEnd = new Date(currentWeekStart);
        weekEnd.setDate(currentWeekStart.getDate() + 6);
        currentPeriodSpan.textContent = formatWeekRange(currentWeekStart, weekEnd);
    }

    // ========== Navigation ==========
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

        viewBtns.forEach(function(btn) {
            if (btn.dataset.view === view) {
                btn.classList.remove('bg-white', 'text-gray-600');
                btn.classList.add('bg-emerald-600', 'text-white');
            } else {
                btn.classList.remove('bg-emerald-600', 'text-white');
                btn.classList.add('bg-white', 'text-gray-600');
            }
        });

        if (view === 'week') {
            dayHeaders.style.display = 'none';
            renderWeekView();
        } else {
            dayHeaders.style.display = 'grid';
            renderMonthView();
        }
    }

    // ========== Event Listeners ==========
    prevPeriodBtn.addEventListener('click', goToPrevPeriod);
    nextPeriodBtn.addEventListener('click', goToNextPeriod);
    todayBtn.addEventListener('click', goToToday);

    viewBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            switchView(btn.dataset.view);
        });
    });

    // Initial render
    renderMonthView();
});
