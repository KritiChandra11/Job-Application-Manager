<?php
// Check if user is logged in
if (!isLoggedIn()) {
    redirect('index.php?page=login');
}

// Get user data
$user = User::getById($_SESSION['user_id']);

// Get job application statistics
$total_apps = Event::countByUserId($_SESSION['user_id']);
$pending_apps = Event::countByUserIdAndStatus($_SESSION['user_id'], 'Pending');
$interview_apps = Event::countByUserIdAndStatus($_SESSION['user_id'], 'Interview');
$combined_interviews = Event::countCombinedInterviews($_SESSION['user_id']);
$calendar_interviews = $combined_interviews - $interview_apps;
$offer_apps = Event::countByUserIdAndStatus($_SESSION['user_id'], 'Offer');

// Get upcoming calendar events
$today = date('Y-m-d');
$stmt = $pdo->prepare("
    SELECT * FROM calendar_events 
    WHERE user_id = ? AND start >= ? 
    ORDER BY start ASC
    LIMIT 3
");
$stmt->execute([$_SESSION['user_id'], $today]);
$upcoming_events = $stmt->fetchAll();

// Get recent documents
$stmt = $pdo->prepare("
    SELECT * FROM documents 
    WHERE user_id = ? 
    ORDER BY created_at DESC
    LIMIT 3
");
$stmt->execute([$_SESSION['user_id']]);
$recent_documents = $stmt->fetchAll();

// Get network contacts who need follow-up
$stmt = $pdo->prepare("
    SELECT * FROM network_contacts
    WHERE user_id = ? AND 
          (last_contact_date IS NULL OR 
           DATEDIFF(CURRENT_DATE, last_contact_date) > 30)
    ORDER BY last_contact_date IS NULL DESC, last_contact_date ASC
    LIMIT 3
");
$stmt->execute([$_SESSION['user_id']]);
$follow_up_contacts = $stmt->fetchAll();
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Job Search Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 text-center mb-3">
                            <h3><?= $total_apps ?></h3>
                            <p class="text-muted">Applications</p>
                        </div>
                        <div class="col-6 text-center mb-3">
                            <h3><?= $pending_apps ?></h3>
                            <p class="text-muted">Pending</p>
                        </div>
                        <div class="col-6 text-center">
                            <h3><?= $combined_interviews ?></h3>
                            <p class="text-muted">Interviews</p>
                            <?php if($calendar_interviews > 0): ?>
                                <small class="text-primary">(Includes <?= $calendar_interviews ?> calendar events)</small>
                            <?php endif; ?>
                        </div>
                        <div class="col-6 text-center">
                            <h3><?= $offer_apps ?></h3>
                            <p class="text-muted">Offers</p>
                        </div>
                    </div>
                    <div class="text-center mt-2">
                        <a href="index.php?page=analytics" class="btn btn-sm btn-outline-primary">View Analytics</a>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Quick Links</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="index.php?page=documents" class="list-group-item list-group-item-action">
                            <i class="fas fa-file-alt"></i> Document Manager
                        </a>
                        <a href="index.php?page=interview_notes" class="list-group-item list-group-item-action">
                            <i class="fas fa-clipboard"></i> Interview Notes
                        </a>
                        <a href="index.php?page=email_templates" class="list-group-item list-group-item-action">
                            <i class="fas fa-envelope"></i> Email Templates
                        </a>
                        <a href="index.php?page=calendar" class="list-group-item list-group-item-action">
                            <i class="fas fa-calendar-alt"></i> Calendar
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Job Applications</h5>
                    <button type="button" class="btn btn-primary" id="add-event-btn">Add New</button>
                </div>
                <div class="card-body">
                    <div id="events-container"></div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Upcoming Events</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($upcoming_events)): ?>
                        <p class="text-center py-3">No upcoming events.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($upcoming_events as $event): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= htmlspecialchars($event['title']) ?></strong>
                                            <p class="mb-0 text-muted"><?= htmlspecialchars($event['company']) ?></p>
                                        </div>
                                        <span class="badge badge-info">
                                            <?= date('M d, g:i a', strtotime($event['start'])) ?>
                                        </span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="card-footer text-center p-2">
                            <a href="index.php?page=calendar" class="btn btn-sm btn-outline-info">View Calendar</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Follow-up Reminders</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($follow_up_contacts)): ?>
                        <p class="text-center py-3">No follow-ups needed.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($follow_up_contacts as $contact): ?>
                                <li class="list-group-item">
                                    <strong><?= htmlspecialchars($contact['name']) ?></strong>
                                    <p class="mb-0 text-muted"><?= htmlspecialchars($contact['company'] ?: 'No company') ?></p>
                                    <?php if ($contact['last_contact_date']): ?>
                                        <small class="text-danger">Last contact: <?= date('M d, Y', strtotime($contact['last_contact_date'])) ?></small>
                                    <?php else: ?>
                                        <small class="text-danger">No previous contact</small>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="card-footer text-center p-2">
                            <a href="index.php?page=email_templates" class="btn btn-sm btn-outline-warning">Email Templates</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Recent Documents</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recent_documents)): ?>
                        <p class="text-center py-3">No documents uploaded yet.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recent_documents as $doc): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= htmlspecialchars($doc['document_name']) ?></strong>
                                            <p class="mb-0 text-muted"><?= htmlspecialchars($doc['document_type']) ?></p>
                                        </div>
                                        <a href="<?= $doc['file_path'] ?>" class="btn btn-sm btn-outline-secondary" download>
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="card-footer text-center p-2">
                            <a href="index.php?page=documents" class="btn btn-sm btn-outline-secondary">View Documents</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Event Modal -->
<div class="modal fade" id="event-modal" tabindex="-1" role="dialog" aria-labelledby="event-modal-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="event-modal-label">Add Job Application</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="event-form">
                    <input type="hidden" id="event-id">
                    <div class="form-group">
                        <label for="event-title">Job Title</label>
                        <input type="text" class="form-control" id="event-title" required>
                    </div>
                    <div class="form-group">
                        <label for="event-company">Company</label>
                        <input type="text" class="form-control" id="event-company" required>
                    </div>
                    <div class="form-group">
                        <label for="event-date">Date</label>
                        <input type="datetime-local" class="form-control" id="event-date" required>
                    </div>
                    <div class="form-group">
                        <label for="event-status">Status</label>
                        <select class="form-control" id="event-status">
                            <option value="Pending">Pending</option>
                            <option value="Applied">Applied</option>
                            <option value="Interview">Interview</option>
                            <option value="Offer">Offer</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="event-description">Notes</label>
                        <textarea class="form-control" id="event-description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="save-event-btn">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="delete-modal-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="delete-modal-label">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this job application?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-btn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Load events when page loads
        loadEvents();
        
        // Add event button click
        $('#add-event-btn').click(function() {
            $('#event-modal_label').text('Add Job Application');
            $('#event-id').val('');
            $('#event-form')[0].reset();
            $('#event-modal').modal('show');
        });
        
        // Save event button click
        $('#save-event-btn').click(function() {
            const eventId = $('#event-id').val();
            const eventData = {
                title: $('#event-title').val(),
                company: $('#event-company').val(),
                date: $('#event-date').val(),
                status: $('#event-status').val(),
                description: $('#event-description').val()
            };
            
            if (eventId) {
                // Update existing event
                updateEvent(eventId, eventData);
            } else {
                // Create new event
                createEvent(eventData);
            }
        });
        
        // Handle event deletion
        $('#confirm-delete-btn').click(function() {
            const eventId = $(this).data('event-id');
            deleteEvent(eventId);
        });
    });
    
    function loadEvents() {
        $.ajax({
            url: 'api/events.php',
            method: 'GET',
            dataType: 'json',
            success: function(events) {
                displayEvents(events);
            },
            error: function(xhr) {
                alert('Error loading events: ' + xhr.responseJSON.error);
            }
        });
    }
    
    function displayEvents(events) {
        if (events.length === 0) {
            $('#events-container').html('<p class="text-center">No job applications yet. Click "Add New" to create one.</p>');
            return;
        }
        
        let html = '<div class="table-responsive"><table class="table table-striped">';
        html += '<thead><tr><th>Job Title</th><th>Company</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>';
        html += '<tbody>';
        
        events.forEach(function(event) {
            const date = new Date(event.date);
            const formattedDate = date.toLocaleString();
            
            html += '<tr>';
            html += '<td>' + event.title + '</td>';
            html += '<td>' + event.company + '</td>';
            html += '<td>' + formattedDate + '</td>';
            html += '<td><span class="badge badge-' + getStatusBadgeClass(event.status) + '">' + event.status + '</span></td>';
            html += '<td>';
            html += '<button class="btn btn-sm btn-info mr-1 edit-event-btn" data-id="' + event.id + '">Edit</button>';
            html += '<button class="btn btn-sm btn-danger delete-event-btn" data-id="' + event.id + '">Delete</button>';
            html += '</td>';
            html += '</tr>';
        });
        
        html += '</tbody></table></div>';
        
        $('#events-container').html(html);
        
        // Attach event handlers
        $('.edit-event-btn').click(function() {
            const eventId = $(this).data('id');
            editEvent(eventId);
        });
        
        $('.delete-event-btn').click(function() {
            const eventId = $(this).data('id');
            $('#confirm-delete-btn').data('event-id', eventId);
            $('#delete-modal').modal('show');
        });
    }
    
    function getStatusBadgeClass(status) {
        switch(status) {
            case 'Pending': return 'secondary';
            case 'Applied': return 'primary';
            case 'Interview': return 'info';
            case 'Offer': return 'success';
            case 'Rejected': return 'danger';
            default: return 'secondary';
        }
    }
    
    function editEvent(eventId) {
        $.ajax({
            url: 'api/events.php?id=' + eventId,
            method: 'GET',
            dataType: 'json',
            success: function(event) {
                $('#event-modal_label').text('Edit Job Application');
                $('#event-id').val(event.id);
                $('#event-title').val(event.title);
                $('#event-company').val(event.company);
                $('#event-description').val(event.description);
                
                // Format date for datetime-local input
                const date = new Date(event.date);
                const formattedDate = date.toISOString().slice(0, 16);
                $('#event-date').val(formattedDate);
                
                $('#event-status').val(event.status);
                $('#event-modal').modal('show');
            },
            error: function(xhr) {
                alert('Error loading event: ' + xhr.responseJSON.error);
            }
        });
    }
    
    function createEvent(eventData) {
        $.ajax({
            url: 'api/events.php',
            method: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify(eventData),
            success: function() {
                $('#event-modal').modal('hide');
                loadEvents();
            },
            error: function(xhr) {
                alert('Error creating event: ' + xhr.responseJSON.error);
            }
        });
    }
    
    function updateEvent(eventId, eventData) {
        $.ajax({
            url: 'api/events.php?id=' + eventId,
            method: 'PUT',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify(eventData),
            success: function() {
                $('#event-modal').modal('hide');
                loadEvents();
            },
            error: function(xhr) {
                alert('Error updating event: ' + xhr.responseJSON.error);
            }
        });
    }
    
    function deleteEvent(eventId) {
        $.ajax({
            url: 'api/events.php?id=' + eventId,
            method: 'DELETE',
            dataType: 'json',
            success: function() {
                $('#delete-modal').modal('hide');
                loadEvents();
            },
            error: function(xhr) {
                alert('Error deleting event: ' + xhr.responseJSON.error);
            }
        });
    }
</script>
