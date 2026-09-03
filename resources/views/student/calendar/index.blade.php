<x-student-layout>
    <x-slot name="title">My Calendar</x-slot>

    <style>
        .calendar-card { background:#fff; border-radius:12px; border:1px solid var(--border-color, #e2e8f0); box-shadow:0 1px 3px rgba(0,0,0,0.05); padding:20px; }
        .calendar-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px; }
        .calendar-controls { display:flex; align-items:center; gap:8px; }
        .calendar-grid { display:grid; grid-template-columns:repeat(7, 1fr); border-top:1px solid #e2e8f0; border-left:1px solid #e2e8f0; }
        .calendar-day-header { padding:10px; background:#f8fafc; text-align:center; font-weight:600; font-size:13px; color:#64748b; border-right:1px solid #e2e8f0; border-bottom:1px solid #e2e8f0; }
        .calendar-day-cell { min-height:100px; padding:6px; background:#fff; border-right:1px solid #e2e8f0; border-bottom:1px solid #e2e8f0; position:relative; }
        .calendar-day-cell.other-month { background:#f8fafc; opacity:0.5; }
        .calendar-day-cell.today { background:rgba(139,92,246,0.05); }
        .day-number { font-size:12px; font-weight:600; color:#334155; margin-bottom:4px; display:inline-block; width:22px; height:22px; line-height:22px; text-align:center; border-radius:50%; }
        .calendar-day-cell.today .day-number { background:#8b5cf6; color:#fff; }
        .event-pill { margin-bottom:4px; padding:4px 6px; border-radius:4px; font-size:11px; font-weight:600; cursor:pointer; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; transition:all 0.15s ease; display:block; }
        .event-pill:hover { transform:translateY(-1px); filter:brightness(0.95); }
        .event-scheduled { background:linear-gradient(135deg, #3b82f6, #2563eb); color:#fff; }
        .event-completed { background:#e2e8f0; color:#475569; }
        .event-upcoming { background:#fef3c7; color:#92400e; border:1px dashed #f59e0b; }
    </style>

    <div class="page-header">
        <div class="page-header-left">
            <h1>My Interactive Class Calendar</h1>
            <p>Google Calendar-style schedule viewer for live classes and modules</p>
        </div>
    </div>

    <div class="calendar-card">
        <div class="calendar-header">
            <div>
                <h2 id="calendarMonthYear" style="font-size:20px;font-weight:700;color:#1e293b;margin:0">January 2026</h2>
            </div>
            <div class="calendar-controls">
                <button class="btn btn-outline btn-sm" onclick="prevMonth()">‹ Prev</button>
                <button class="btn btn-outline btn-sm" onclick="goToToday()">Today</button>
                <button class="btn btn-outline btn-sm" onclick="nextMonth()">Next ›</button>
            </div>
        </div>

        <div class="calendar-grid">
            <div class="calendar-day-header">Sun</div>
            <div class="calendar-day-header">Mon</div>
            <div class="calendar-day-header">Tue</div>
            <div class="calendar-day-header">Wed</div>
            <div class="calendar-day-header">Thu</div>
            <div class="calendar-day-header">Fri</div>
            <div class="calendar-day-header">Sat</div>
        </div>
        <div class="calendar-grid" id="calendarDaysGrid"></div>
    </div>

    <!-- Event Detail Modal -->
    <div class="modal-overlay" id="eventDetailModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title" id="m_subject_name">Class Details</span>
                <button class="modal-close" onclick="closeModal('eventDetailModal')">&times;</button>
            </div>
            <div class="modal-body" style="font-size:13px">
                <div style="margin-bottom:12px">
                    <span class="badge badge-secondary" id="m_status_badge">SCHEDULED</span>
                </div>
                <table class="table" style="margin-bottom:0">
                    <tr><th style="color:var(--text-muted);width:120px">Module Step:</th><td id="m_module_title" style="font-weight:600"></td></tr>
                    <tr><th style="color:var(--text-muted)">Faculty Teacher:</th><td id="m_teacher_name"></td></tr>
                    <tr><th style="color:var(--text-muted)">Scheduled Date:</th><td id="m_scheduled_date" style="font-weight:600"></td></tr>
                    <tr><th style="color:var(--text-muted)">Start Time:</th><td id="m_start_time" style="color:var(--blue);font-weight:600"></td></tr>
                </table>
            </div>
            <div class="modal-footer" id="m_footer_action">
                <button type="button" class="btn btn-outline" onclick="closeModal('eventDetailModal')">Close</button>
                <a id="m_join_btn" href="#" target="_blank" class="btn btn-primary">Join Live Class</a>
            </div>
        </div>
    </div>

    <script>
        const eventsData = @json($events);
        let currentDate = new Date();

        function renderCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            
            const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            document.getElementById('calendarMonthYear').innerText = `${monthNames[month]} ${year}`;

            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const prevMonthDays = new Date(year, month, 0).getDate();

            const grid = document.getElementById('calendarDaysGrid');
            grid.innerHTML = '';

            // Previous month padding days
            for (let i = firstDay - 1; i >= 0; i--) {
                const dayNum = prevMonthDays - i;
                const cell = document.createElement('div');
                cell.className = 'calendar-day-cell other-month';
                cell.innerHTML = `<span class="day-number">${dayNum}</span>`;
                grid.appendChild(cell);
            }

            // Current month days
            const todayStr = new Date().toISOString().split('T')[0];

            for (let day = 1; day <= daysInMonth; day++) {
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const cell = document.createElement('div');
                cell.className = 'calendar-day-cell';
                if (dateStr === todayStr) cell.classList.add('today');

                let html = `<span class="day-number">${day}</span>`;

                // Find matching events
                const matchingEvents = eventsData.filter(e => e.date === dateStr);
                matchingEvents.forEach(evt => {
                    const pillClass = evt.status === 'COMPLETED' ? 'event-completed' : (evt.status === 'SCHEDULED' ? 'event-scheduled' : 'event-upcoming');
                    html += `
                        <div class="event-pill ${pillClass}" onclick='openEventModal(${JSON.stringify(evt)})'>
                            ${evt.start_time} — ${evt.subject_name}
                        </div>
                    `;
                });

                cell.innerHTML = html;
                grid.appendChild(cell);
            }

            // Next month padding days to complete grid
            const totalCells = firstDay + daysInMonth;
            const remaining = (7 - (totalCells % 7)) % 7;
            for (let i = 1; i <= remaining; i++) {
                const cell = document.createElement('div');
                cell.className = 'calendar-day-cell other-month';
                cell.innerHTML = `<span class="day-number">${i}</span>`;
                grid.appendChild(cell);
            }
        }

        function prevMonth() { currentDate.setMonth(currentDate.getMonth() - 1); renderCalendar(); }
        function nextMonth() { currentDate.setMonth(currentDate.getMonth() + 1); renderCalendar(); }
        function goToToday() { currentDate = new Date(); renderCalendar(); }

        function openEventModal(evt) {
            document.getElementById('m_subject_name').innerText = evt.subject_name;
            document.getElementById('m_module_title').innerText = evt.batch_name + (evt.slot_name ? ' · ' + evt.slot_name : '');
            document.getElementById('m_teacher_name').innerText = evt.teacher_name;
            document.getElementById('m_scheduled_date').innerText = evt.date || 'Date Pending';
            document.getElementById('m_start_time').innerText = evt.start_time || 'TBA';
            document.getElementById('m_status_badge').innerText = evt.status;

            const joinBtn = document.getElementById('m_join_btn');
            if (evt.meeting_link && (evt.status === 'SCHEDULED' || evt.status === 'RUNNING')) {
                joinBtn.href = evt.meeting_link;
                joinBtn.style.display = 'inline-flex';
            } else {
                joinBtn.style.display = 'none';
            }

            openModal('eventDetailModal');
        }

        document.addEventListener('DOMContentLoaded', renderCalendar);
    </script>
</x-student-layout>
