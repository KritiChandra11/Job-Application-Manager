<?php
// Check if user is logged in
if (!isLoggedIn()) {
    redirect('index.php?page=login');
}

// Get user data
$user = User::getById($_SESSION['user_id']);
?>

<div class="container mt-5">
    <h1>Calendar View</h1>
    
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Interviews & Deadlines</h5>
                    <button type="button" class="btn btn-primary" id="add-calendar-event-btn">Add New Reminder</button>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Calendar Event Modal -->
<div class="modal fade" id="calendar-event-modal" tabindex="-1" role="dialog" aria-labelledby="calendar-event-modal-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="calendar-event-modal-label">Add Interview/Reminder</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="calendar-event-form">
                    <input type="hidden" id="calendar-event-id">
                    <div class="form-group">
                        <label for="calendar-event-title">Event Title</label>
                        <input type="text" class="form-control" id="calendar-event-title" placeholder="Interview with Company X" required>
                    </div>
                    <div class="form-group">
                        <label for="calendar-event-company">Company</label>
                        <input type="text" class="form-control" id="calendar-event-company" required>
                    </div>
                    <div class="form-group">
                        <label for="calendar-event-start">Start Date & Time</label>
                        <input type="datetime-local" class="form-control" id="calendar-event-start" required>
                    </div>
                    <div class="form-group">
                        <label for="calendar-event-end">End Date & Time</label>
                        <input type="datetime-local" class="form-control" id="calendar-event-end" required>
                    </div>
                    <div class="form-group">
                        <label for="calendar-event-type">Event Type</label>
                        <select class="form-control" id="calendar-event-type">
                            <option value="interview">Interview</option>
                            <option value="deadline">Application Deadline</option>
                            <option value="followup">Follow-up Reminder</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="calendar-event-status">Status</label>
                        <select class="form-control" id="calendar-event-status">
                            <option value="pending">Pending</option>
                            <option value="Interview">Interview</option>
                            <option value="Offer">Offer</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="calendar-event-description">Notes</label>
                        <textarea class="form-control" id="calendar-event-description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="calendar-event-reminder">Set Reminder</label>
                        <select class="form-control" id="calendar-event-reminder">
                            <option value="0">No reminder</option>
                            <option value="15">15 minutes before</option>
                            <option value="30">30 minutes before</option>
                            <option value="60">1 hour before</option>
                            <option value="1440">1 day before</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="delete-calendar-event-btn" style="display:none;">Delete</button>
                <button type="button" class="btn btn-primary" id="save-calendar-event-btn">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Add FullCalendar JS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        
        // Create calendar instance and store it on the element for later access
        var calendarInstance = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            events: 'api/calendar_events.php',
            editable: true,
            selectable: true,
            select: function(info) {
                console.log('Selected date range:', info.startStr, 'to', info.endStr);
                
                // Clear form and set default dates
                $('#calendar-event-form')[0].reset();
                $('#calendar-event-id').val('');
                
                // Format dates properly for datetime-local input
                let startDate = new Date(info.start);
                let endDate = new Date(info.end);
                
                // Adjust the end date (FullCalendar uses exclusive end dates)
                endDate.setDate(endDate.getDate() - 1);
                endDate.setHours(startDate.getHours() + 1);
                
                // Format for datetime-local input (YYYY-MM-DDTHH:MM)
                let formattedStart = startDate.toISOString().slice(0, 16);
                let formattedEnd = endDate.toISOString().slice(0, 16);
                
                $('#calendar-event-start').val(formattedStart);
                $('#calendar-event-end').val(formattedEnd);
                $('#calendar-event-modal-label').text('Add Interview/Reminder');
                $('#delete-calendar-event-btn').hide();
                $('#calendar-event-modal').modal('show');
            },
            eventClick: function(info) {
                console.log('Clicked on event:', info.event);
                
                // Load event data into the form
                $('#calendar-event-id').val(info.event.id);
                $('#calendar-event-title').val(info.event.title);
                
                // Check if extendedProps exists to prevent errors
                if (info.event.extendedProps) {
                    $('#calendar-event-company').val(info.event.extendedProps.company || '');
                    $('#calendar-event-type').val(info.event.extendedProps.type || 'other');
                    $('#calendar-event-status').val(info.event.extendedProps.status || 'pending');
                    $('#calendar-event-description').val(info.event.extendedProps.description || '');
                    $('#calendar-event-reminder').val(info.event.extendedProps.reminder || '0');
                }
                
                // Format date for datetime-local input
                const startDate = new Date(info.event.start);
                const endDate = info.event.end ? new Date(info.event.end) : new Date(startDate.getTime() + 3600000);
                
                $('#calendar-event-start').val(startDate.toISOString().slice(0, 16));
                $('#calendar-event-end').val(endDate.toISOString().slice(0, 16));
                
                // Change modal title and show delete button
                $('#calendar-event-modal-label').text('Edit Interview/Reminder');
                $('#delete-calendar-event-btn').show();
                $('#calendar-event-modal').modal('show');
            }
        });
        
        // Store the calendar instance on the element
        calendarEl.calendar = calendarInstance;
        
        // Render the calendar
        calendarInstance.render();
        
        // Add event button click
        $('#add-calendar-event-btn').click(function() {
            $('#calendar-event-modal-label').text('Add Interview/Reminder');
            $('#calendar-event-id').val('');
            $('#calendar-event-form')[0].reset();
            $('#delete-calendar-event-btn').hide();
            $('#calendar-event-modal').modal('show');
        });
        
        // Prevent default form submission
        $('#calendar-event-form').on('submit', function(e) {
            e.preventDefault();
            $('#save-calendar-event-btn').click();
        });
        
        // Save event button click
        $('#save-calendar-event-btn').click(function() {
            // Get form values
            const eventId = $('#calendar-event-id').val();
            const title = $('#calendar-event-title').val();
            const company = $('#calendar-event-company').val();
            const start = $('#calendar-event-start').val();
            const end = $('#calendar-event-end').val();
            
            console.log('Form values before submission:');
            console.log('Title:', title);
            console.log('Company:', company);
            console.log('Start Date:', start);
            console.log('End Date:', end);
            
            // Check required fields
            if (!title || !company || !start || !end) {
                console.error('Validation failed: Missing required fields');
                alert('Please fill in all required fields: Title, Company, Start Time, and End Time');
                return;
            }
            
            const eventData = {
                title: title,
                company: company,
                start: start,
                end: end,
                type: $('#calendar-event-type').val(),
                status: $('#calendar-event-status').val(),
                description: $('#calendar-event-description').val(),
                reminder: $('#calendar-event-reminder').val()
            };
            
            if (eventId) {
                // Update existing event
                updateCalendarEvent(eventId, eventData);
            } else {
                // Create new event
                createCalendarEvent(eventData);
            }
        });
        
        // Delete event button click
        $('#delete-calendar-event-btn').click(function() {
            const eventId = $('#calendar-event-id').val();
            if (confirm('Are you sure you want to delete this event?')) {
                deleteCalendarEvent(eventId);
            }
        });
    });
    
    function createCalendarEvent(eventData) {
        // Add validation to check required fields before sending
        if (!eventData.title || !eventData.company || !eventData.start || !eventData.end) {
            alert('Please fill in all required fields: Title, Company, Start Time, and End Time');
            return;
        }
        
        // Format dates properly for MySQL (YYYY-MM-DD HH:MM:SS)
        if (eventData.start) {
            // Make sure we have a proper ISO string with correct timezone handling
            const startDate = new Date(eventData.start);
            eventData.start = startDate.toISOString().slice(0, 19).replace('T', ' ');
        }
        
        if (eventData.end) {
            const endDate = new Date(eventData.end);
            eventData.end = endDate.toISOString().slice(0, 19).replace('T', ' ');
        }
        
        console.log('Sending event data:', eventData);
        
        $.ajax({
            url: 'api/calendar_events.php',
            method: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify(eventData),
            success: function(response) {
                console.log('Event created successfully:', response);
                $('#calendar-event-modal').modal('hide');
                // Using proper FullCalendar v5 refresh method
                var calendar = document.querySelector('#calendar');
                if (calendar && calendar.calendar) {
                    calendar.calendar.refetchEvents();
                } else {
                    location.reload(); // Fallback
                }
            },
            error: function(xhr) {
                console.error('Error response:', xhr);
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    alert('Error creating event: ' + xhr.responseJSON.error);
                } else {
                    alert('Error creating event. Please check the console for details.');
                }
            }
        });
    }
    
    function updateCalendarEvent(eventId, eventData) {
        // Add validation
        if (!eventData.title || !eventData.company || !eventData.start || !eventData.end) {
            alert('Please fill in all required fields: Title, Company, Start Time, and End Time');
            return;
        }
        
        // Format dates properly for MySQL (YYYY-MM-DD HH:MM:SS)
        if (eventData.start) {
            const startDate = new Date(eventData.start);
            eventData.start = startDate.toISOString().slice(0, 19).replace('T', ' ');
        }
        
        if (eventData.end) {
            const endDate = new Date(eventData.end);
            eventData.end = endDate.toISOString().slice(0, 19).replace('T', ' ');
        }
        
        console.log('Updating event data:', eventData);
        
        $.ajax({
            url: 'api/calendar_events.php?id=' + eventId,
            method: 'PUT',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify(eventData),
            success: function(response) {
                console.log('Event updated successfully:', response);
                $('#calendar-event-modal').modal('hide');
                // Using proper FullCalendar v5 refresh method
                var calendar = document.querySelector('#calendar');
                if (calendar && calendar.calendar) {
                    calendar.calendar.refetchEvents();
                } else {
                    location.reload(); // Fallback
                }
            },
            error: function(xhr) {
                console.error('Error response:', xhr);
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    alert('Error updating event: ' + xhr.responseJSON.error);
                } else {
                    alert('Error updating event. Please check the console for details.');
                }
            }
        });
    }
    
    function deleteCalendarEvent(eventId) {
        $.ajax({
            url: 'api/calendar_events.php?id=' + eventId,
            method: 'DELETE',
            dataType: 'json',
            success: function() {
                $('#calendar-event-modal').modal('hide');
                location.reload(); // Simple reload is more reliable
            },
            error: function(xhr) {
                console.error('Error response:', xhr);
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    alert('Error deleting event: ' + xhr.responseJSON.error);
                } else {
                    alert('Error deleting event. Please check the console for details.');
                }
            }
        });
    }
</script>

<style>
    #calendar {
        max-width: 100%;
        margin: 0 auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 5px;
    }
    
    .fc-event {
        cursor: pointer;
    }
    
    .fc-event.interview {
        background-color: #007bff;
        border-color: #007bff;
    }
    
    .fc-event.deadline {
        background-color: #dc3545;
        border-color: #dc3545;
    }
    
    .fc-event.followup {
        background-color: #17a2b8;
        border-color: #17a2b8;
    }
    
    .fc-event.other {
        background-color: #6c757d;
        border-color: #6c757d;
    }
</style>
