<?php
// Check if user is logged in
if (!isLoggedIn()) {
    redirect('index.php?page=login');
}

// Get user data
$user = User::getById($_SESSION['user_id']);

// Handle form submission for notes
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_note') {
        // Add/Edit Note
        $note_id = isset($_POST['note_id']) ? (int)$_POST['note_id'] : null;
        $company = sanitize($_POST['company']);
        $position = sanitize($_POST['position']);
        $interview_date = sanitize($_POST['interview_date']);
        $content = sanitize($_POST['content']);
        
        if ($note_id) {
            // Update existing note
            $stmt = $pdo->prepare("
                UPDATE interview_notes 
                SET company = ?, position = ?, interview_date = ?, content = ?, updated_at = ?
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$company, $position, $interview_date, $content, date('Y-m-d H:i:s'), $note_id, $_SESSION['user_id']]);
            $message = '<div class="alert alert-success">Note updated successfully!</div>';
        } else {
            // Insert new note
            $stmt = $pdo->prepare("
                INSERT INTO interview_notes (user_id, company, position, interview_date, content, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $now = date('Y-m-d H:i:s');
            $stmt->execute([$_SESSION['user_id'], $company, $position, $interview_date, $content, $now, $now]);
            $message = '<div class="alert alert-success">Note added successfully!</div>';
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete_note' && isset($_POST['note_id'])) {
        // Delete note
        $note_id = (int)$_POST['note_id'];
        $stmt = $pdo->prepare("DELETE FROM interview_notes WHERE id = ? AND user_id = ?");
        $stmt->execute([$note_id, $_SESSION['user_id']]);
        $message = '<div class="alert alert-success">Note deleted successfully!</div>';
    }
}

// Get all notes
$stmt = $pdo->prepare("
    SELECT * FROM interview_notes 
    WHERE user_id = ? 
    ORDER BY interview_date DESC, created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$notes = $stmt->fetchAll();

// Get companies for dropdown
$stmt = $pdo->prepare("SELECT DISTINCT company FROM events WHERE user_id = ? ORDER BY company");
$stmt->execute([$_SESSION['user_id']]);
$companies = $stmt->fetchAll();

// Get upcoming interviews
$today = date('Y-m-d');
$stmt = $pdo->prepare("
    SELECT e.id, e.title, e.company, e.date
    FROM events e
    WHERE e.user_id = ? AND e.status = 'Interview' AND e.date >= ?
    ORDER BY e.date ASC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id'], $today]);
$upcoming_interviews = $stmt->fetchAll();

// Check if we need to create the table
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS interview_notes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            company VARCHAR(100) NOT NULL,
            position VARCHAR(100) NOT NULL,
            interview_date DATE NOT NULL,
            content TEXT NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
} catch(PDOException $e) {
    $message = '<div class="alert alert-danger">Error setting up notes: ' . $e->getMessage() . '</div>';
}
?>

<div class="container mt-5">
    <h1>Interview Preparation Notes</h1>
    
    <?= $message ?>
    
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">My Interview Notes</h5>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addNoteModal">
                            <i class="fas fa-plus"></i> Add Note
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($notes)): ?>
                        <p class="text-center">No interview notes yet. Create your first note to prepare for interviews!</p>
                    <?php else: ?>
                        <div class="accordion" id="notesAccordion">
                            <?php foreach ($notes as $index => $note): ?>
                                <div class="card">
                                    <div class="card-header" id="heading<?= $note['id'] ?>">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h2 class="mb-0">
                                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse<?= $note['id'] ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $note['id'] ?>">
                                                    <strong><?= htmlspecialchars($note['company']) ?></strong> - <?= htmlspecialchars($note['position']) ?>
                                                    <small class="text-muted ml-2"><?= date('M d, Y', strtotime($note['interview_date'])) ?></small>
                                                </button>
                                            </h2>
                                            <div>
                                                <button class="btn btn-sm btn-outline-primary edit-note" 
                                                        data-id="<?= $note['id'] ?>"
                                                        data-company="<?= htmlspecialchars($note['company']) ?>"
                                                        data-position="<?= htmlspecialchars($note['position']) ?>"
                                                        data-date="<?= $note['interview_date'] ?>"
                                                        data-content="<?= htmlspecialchars($note['content']) ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger delete-note" data-id="<?= $note['id'] ?>" data-company="<?= htmlspecialchars($note['company']) ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="collapse<?= $note['id'] ?>" class="collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="heading<?= $note['id'] ?>" data-parent="#notesAccordion">
                                        <div class="card-body">
                                            <p class="text-muted">Last updated: <?= date('M d, Y H:i', strtotime($note['updated_at'])) ?></p>
                                            <div class="note-content">
                                                <?= nl2br(htmlspecialchars($note['content'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Upcoming Interviews</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($upcoming_interviews)): ?>
                        <p class="text-center">No upcoming interviews scheduled.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($upcoming_interviews as $interview): ?>
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($interview['title']) ?></h6>
                                        <small><?= date('M d', strtotime($interview['date'])) ?></small>
                                    </div>
                                    <small class="text-muted"><?= htmlspecialchars($interview['company']) ?></small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5>Interview Prep Resources</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="https://www.glassdoor.com/blog/common-interview-questions/" target="_blank" class="list-group-item list-group-item-action">
                            <i class="fas fa-external-link-alt mr-2"></i> 50 Most Common Interview Questions
                        </a>
                        <a href="https://www.themuse.com/advice/interview-questions-and-answers" target="_blank" class="list-group-item list-group-item-action">
                            <i class="fas fa-external-link-alt mr-2"></i> How to Answer the 31 Most Common Interview Questions
                        </a>
                        <a href="https://leetcode.com/problemset/all/" target="_blank" class="list-group-item list-group-item-action">
                            <i class="fas fa-external-link-alt mr-2"></i> LeetCode - Technical Interview Prep
                        </a>
                        <a href="https://www.youtube.com/playlist?list=PLAwxTw4SYaPmjFQ1Qp5EiViKwSuYWf9-8" target="_blank" class="list-group-item list-group-item-action">
                            <i class="fas fa-external-link-alt mr-2"></i> Mock Interview Videos
                        </a>
                        <a href="https://www.amazon.com/Cracking-Coding-Interview-Programming-Questions/dp/0984782850" target="_blank" class="list-group-item list-group-item-action">
                            <i class="fas fa-external-link-alt mr-2"></i> Cracking the Coding Interview Book
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1" role="dialog" aria-labelledby="addNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addNoteModalLabel">Add Interview Note</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="noteForm" method="post" action="">
                    <input type="hidden" name="action" value="add_note">
                    <input type="hidden" name="note_id" id="note_id">
                    <div class="form-group">
                        <label for="company">Company</label>
                        <select class="form-control" id="company" name="company" required>
                            <option value="">Select a company</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?= htmlspecialchars($company['company']) ?>"><?= htmlspecialchars($company['company']) ?></option>
                            <?php endforeach; ?>
                            <option value="new_company">+ Add New Company</option>
                        </select>
                        <input type="text" class="form-control mt-2" id="new_company" placeholder="Enter company name" style="display:none;">
                    </div>
                    <div class="form-group">
                        <label for="position">Position</label>
                        <input type="text" class="form-control" id="position" name="position" required>
                    </div>
                    <div class="form-group">
                        <label for="interview_date">Interview Date</label>
                        <input type="date" class="form-control" id="interview_date" name="interview_date" required>
                    </div>
                    <div class="form-group">
                        <label for="content">Notes</label>
                        <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
                        <small class="form-text text-muted">Tips: Include research about the company, prepared answers to common questions, your own questions to ask, and key points about your experience that relate to the role.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveNote">Save Note</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteNoteModal" tabindex="-1" role="dialog" aria-labelledby="deleteNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteNoteModalLabel">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete your notes for <span id="deleteCompanyName"></span>?</p>
                <p class="text-danger">This action cannot be undone.</p>
                <form id="deleteNoteForm" method="post" action="">
                    <input type="hidden" name="action" value="delete_note">
                    <input type="hidden" name="note_id" id="delete_note_id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle company dropdown
        $('#company').change(function() {
            if ($(this).val() === 'new_company') {
                $('#new_company').show();
            } else {
                $('#new_company').hide();
            }
        });
        
        // Handle new company input
        $('#new_company').change(function() {
            if ($(this).val().trim() !== '') {
                const newOption = new Option($(this).val(), $(this).val(), true, true);
                $('#company').append(newOption).val($(this).val());
                $('#new_company').hide();
            }
        });
        
        // Handle save button
        $('#saveNote').click(function() {
            // Handle new company if needed
            if ($('#company').val() === 'new_company' && $('#new_company').val().trim() !== '') {
                const newOption = new Option($('#new_company').val(), $('#new_company').val(), true, true);
                $('#company').append(newOption).val($('#new_company').val());
            }
            
            // Submit the form
            $('#noteForm').submit();
        });
        
        // Handle edit button
        $('.edit-note').click(function() {
            const id = $(this).data('id');
            const company = $(this).data('company');
            const position = $(this).data('position');
            const date = $(this).data('date');
            const content = $(this).data('content');
            
            $('#note_id').val(id);
            
            // Check if company exists in dropdown, add if it doesn't
            if ($('#company option[value="' + company + '"]').length === 0) {
                const newOption = new Option(company, company, true, true);
                $('#company').append(newOption);
            }
            
            $('#company').val(company);
            $('#position').val(position);
            $('#interview_date').val(date);
            $('#content').val(content);
            $('#addNoteModalLabel').text('Edit Interview Note');
            $('#addNoteModal').modal('show');
        });
        
        // Handle delete button
        $('.delete-note').click(function() {
            const id = $(this).data('id');
            const company = $(this).data('company');
            
            $('#delete_note_id').val(id);
            $('#deleteCompanyName').text(company);
            $('#deleteNoteModal').modal('show');
        });
        
        // Handle confirm delete
        $('#confirmDelete').click(function() {
            $('#deleteNoteForm').submit();
        });
    });
</script>
