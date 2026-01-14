</main>
</div>
</div>

<!-- Font Awesome for icons -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<!-- Template Scripts -->
<script src="../../assets/js/script-updated.js"></script>

<!-- Unified Session Management -->
<script src="../../assets/js/session-manager.js"></script>

<script>
    // Toggle calendar dropdown
    function toggleCalendarDropdown() {
        const dropdown = document.getElementById('calendarDropdown');
        dropdown.classList.toggle('hidden');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function (event) {
        const dropdown = document.getElementById('calendarDropdown');
        const button = document.getElementById('calendarButton');
        if (dropdown && button && !button.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Add to Google Calendar (opens directly in browser)
    function addToGoogleCalendar(meetingId) {
        fetch(`../../api/get-meeting-calendar.php?id=${meetingId}`)
            .then(response => response.json())
            .then(meeting => {
                const startDate = new Date(meeting.date + ' ' + meeting.time_start);
                const endDate = new Date(meeting.date + ' ' + meeting.time_end);

                // Format dates for Google Calendar
                const formatGoogleDate = (date) => {
                    return date.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
                };

                const googleUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE' +
                    '&text=' + encodeURIComponent(meeting.title) +
                    '&dates=' + formatGoogleDate(startDate) + '/' + formatGoogleDate(endDate) +
                    '&details=' + encodeURIComponent(meeting.description) +
                    '&location=' + encodeURIComponent(meeting.venue) +
                    '&sf=true&output=xml';

                window.open(googleUrl, '_blank');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to add meeting to Google Calendar');
            });
    }

    // Add to Outlook (opens directly in browser)
    function addToOutlook(meetingId) {
        fetch(`../../api/get-meeting-calendar.php?id=${meetingId}`)
            .then(response => response.json())
            .then(meeting => {
                const startDate = new Date(meeting.date + ' ' + meeting.time_start);
                const endDate = new Date(meeting.date + ' ' + meeting.time_end);

                const outlookUrl = 'https://outlook.live.com/calendar/0/deeplink/compose?path=/calendar/action/compose&rru=addevent' +
                    '&subject=' + encodeURIComponent(meeting.title) +
                    '&startdt=' + startDate.toISOString() +
                    '&enddt=' + endDate.toISOString() +
                    '&body=' + encodeURIComponent(meeting.description) +
                    '&location=' + encodeURIComponent(meeting.venue);

                window.open(outlookUrl, '_blank');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to add meeting to Outlook');
            });
    }

    // Add to Calendar function (.ics download for Apple Calendar, etc.)
    function addToCalendar(meetingId) {
        // Fetch meeting details
        fetch(`../../api/get-meeting-calendar.php?id=${meetingId}`)
            .then(response => response.json())
            .then(meeting => {
                // Create .ics file content
                const icsContent = [
                    'BEGIN:VCALENDAR',
                    'VERSION:2.0',
                    'PRODID:-//Legislative Agenda System//EN',
                    'BEGIN:VEVENT',
                    `UID:meeting-${meeting.id}@legislature.gov`,
                    `DTSTAMP:${formatDateForICS(new Date())}`,
                    `DTSTART:${formatDateForICS(new Date(meeting.date + ' ' + meeting.time_start))}`,
                    `DTEND:${formatDateForICS(new Date(meeting.date + ' ' + meeting.time_end))}`,
                    `SUMMARY:${meeting.title}`,
                    `DESCRIPTION:${meeting.description.replace(/\n/g, '\\n')}`,
                    `LOCATION:${meeting.venue}`,
                    `ORGANIZER:CN=${meeting.committee_name}`,
                    'STATUS:CONFIRMED',
                    'END:VEVENT',
                    'END:VCALENDAR'
                ].join('\r\n');

                // Create download link
                const blob = new Blob([icsContent], { type: 'text/calendar;charset=utf-8' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `meeting-${meeting.id}.ics`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to add meeting to calendar');
            });
    }

    // Format date for ICS file
    function formatDateForICS(date) {
        return date.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
    }

    // Logout function
    function logout() {
        if (confirm('Are you sure you want to logout?')) {
            // Use fetch to destroy session first, then redirect
            fetch('../../../app/controllers/AuthController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=logout'
            })
                .then(() => {
                    // Redirect to login page after logout
                    window.location.href = '../../../auth/login.php?logout=success';
                })
                .catch(() => {
                    // Even if fetch fails, redirect anyway
                    window.location.href = '../../../auth/login.php?logout=success';
                });
        }
    }
</script>
</body>

</html>