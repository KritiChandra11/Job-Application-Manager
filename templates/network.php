<?php
// Check if user is logged in
if (!isLoggedIn()) {
    redirect('index.php?page=login');
}

// Get user data
$user = User::getById($_SESSION['user_id']);

// Handle form submission for contacts
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_contact') {
        // Add/Edit Contact
        $contact_id = isset($_POST['contact_id']) ? (int)$_POST['contact_id'] : null;
        $name = sanitize($_POST['name']);
        $company = sanitize($_POST['company']);
        $position = sanitize($_POST['position']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        $linkedin = sanitize($_POST['linkedin']);
        $notes = sanitize($_POST['notes']);
        $relationship = sanitize($_POST['relationship']);
        $last_contact_date = !empty($_POST['last_contact_date']) ? sanitize($_POST['last_contact_date']) : null;
        
        if ($contact_id) {
            // Update existing contact
            $stmt = $pdo->prepare("
                UPDATE network_contacts 
                SET name = ?, company = ?, position = ?, email = ?, phone = ?, linkedin = ?, 
                    notes = ?, relationship = ?, last_contact_date = ?, updated_at = ?
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([
                $name, $company, $position, $email, $phone, $linkedin, 
                $notes, $relationship, $last_contact_date, date('Y-m-d H:i:s'),
                $contact_id, $_SESSION['user_id']
            ]);
            $message = '<div class="alert alert-success">Contact updated successfully!</div>';
        } else {
            // Insert new contact
            $stmt = $pdo->prepare("
                INSERT INTO network_contacts 
                (user_id, name, company, position, email, phone, linkedin, notes, relationship, last_contact_date, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $now = date('Y-m-d H:i:s');
            $stmt->execute([
                $_SESSION['user_id'], $name, $company, $position, $email, $phone, $linkedin,
                $notes, $relationship, $last_contact_date, $now, $now
            ]);
            $message = '<div class="alert alert-success">Contact added successfully!</div>';
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete_contact' && isset($_POST['contact_id'])) {
        // Delete contact
        $contact_id = (int)$_POST['contact_id'];
        $stmt = $pdo->prepare("DELETE FROM network_contacts WHERE id = ? AND user_id = ?");
        $stmt->execute([$contact_id, $_SESSION['user_id']]);
        $message = '<div class="alert alert-success">Contact deleted successfully!</div>';
    } elseif (isset($_POST['action']) && $_POST['action'] === 'record_interaction' && isset($_POST['contact_id'])) {
        // Record interaction
        $contact_id = (int)$_POST['contact_id'];
        $interaction_date = sanitize($_POST['interaction_date']);
        $interaction_type = sanitize($_POST['interaction_type']);
        $interaction_notes = sanitize($_POST['interaction_notes']);
        
        // Insert interaction
        $stmt = $pdo->prepare("
            INSERT INTO contact_interactions 
            (contact_id, user_id, interaction_date, interaction_type, notes, created_at)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $now = date('Y-m-d H:i:s');
        $stmt->execute([
            $contact_id, $_SESSION['user_id'], $interaction_date, $interaction_type, $interaction_notes, $now
        ]);
        
        // Update last contact date for the contact
        $stmt = $pdo->prepare("
            UPDATE network_contacts 
            SET last_contact_date = ?, updated_at = ?
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([
            $interaction_date, $now, $contact_id, $_SESSION['user_id']
        ]);
        
        $message = '<div class="alert alert-success">Interaction recorded successfully!</div>';
    }
}

// Get all contacts
$stmt = $pdo->prepare("
    SELECT * FROM network_contacts 
    WHERE user_id = ? 
    ORDER BY name ASC
");
$stmt->execute([$_SESSION['user_id']]);
$contacts = $stmt->fetchAll();

// Check if tables need to be created
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS network_contacts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            company VARCHAR(100) NULL,
            position VARCHAR(100) NULL,
            email VARCHAR(100) NULL,
            phone VARCHAR(30) NULL,
            linkedin VARCHAR(200) NULL,
            notes TEXT NULL,
            relationship VARCHAR(50) NULL,
            last_contact_date DATE NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS contact_interactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            contact_id INT NOT NULL,
            user_id INT NOT NULL,
            interaction_date DATE NOT NULL,
            interaction_type VARCHAR(50) NOT NULL,
            notes TEXT NULL,
            created_at DATETIME NOT NULL,
            FOREIGN KEY (contact_id) REFERENCES network_contacts(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
} catch(PDOException $e) {
    $message = '<div class="alert alert-danger">Error setting up contacts: ' . $e->getMessage() . '</div>';
}

// Calculate last interaction dates
$contactLastInteraction = [];
foreach ($contacts as $contact) {
    // Get last interaction for each contact
    $stmt = $pdo->prepare("
        SELECT interaction_date, interaction_type, notes
        FROM contact_interactions
        WHERE contact_id = ? AND user_id = ?
        ORDER BY interaction_date DESC
        LIMIT 1
    ");
    $stmt->execute([$contact['id'], $_SESSION['user_id']]);
    $lastInteraction = $stmt->fetch();
    
    if ($lastInteraction) {
        $contactLastInteraction[$contact['id']] = $lastInteraction;
    }
}

// Get contacts that need follow-up (no contact in over 30 days)
$stmt = $pdo->prepare("
    SELECT nc.id, nc.name, nc.company, nc.last_contact_date, 
           DATEDIFF(CURRENT_DATE(), nc.last_contact_date) as days_since_contact
    FROM network_contacts nc
    WHERE nc.user_id = ? AND 
          (nc.last_contact_date IS NULL OR 
           DATEDIFF(CURRENT_DATE(), nc.last_contact_date) > 30)
    ORDER BY CASE WHEN nc.last_contact_date IS NULL THEN 0 ELSE 1 END, nc.last_contact_date ASC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$follow_ups = $stmt->fetchAll();
?>

<div class="container mt-5">
    <h1>Networking Contacts</h1>
    
    <?= $message ?>
    
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">My Contacts</h5>
                        <div>
                            <input type="text" id="contactSearch" class="form-control form-control-sm mr-2" placeholder="Search contacts...">
                            <button class="btn btn-primary" data-toggle="modal" data-target="#addContactModal">
                                <i class="fas fa-plus"></i> Add Contact
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($contacts)): ?>
                        <p class="text-center">No contacts added yet. Start building your professional network!</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="contactsTable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Company</th>
                                        <th>Position</th>
                                        <th>Last Contact</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($contacts as $contact): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($contact['name']) ?></td>
                                            <td><?= htmlspecialchars($contact['company'] ?: 'N/A') ?></td>
                                            <td><?= htmlspecialchars($contact['position'] ?: 'N/A') ?></td>
                                            <td>
                                                <?php if ($contact['last_contact_date']): ?>
                                                    <?= date('M d, Y', strtotime($contact['last_contact_date'])) ?>
                                                    <span class="badge badge-secondary"><?= round((time() - strtotime($contact['last_contact_date'])) / (60*60*24)) ?> days ago</span>
                                                <?php else: ?>
                                                    <span class="text-muted">No record</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-info view-contact" data-id="<?= $contact['id'] ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-primary edit-contact" 
                                                            data-id="<?= $contact['id'] ?>"
                                                            data-name="<?= htmlspecialchars($contact['name']) ?>"
                                                            data-company="<?= htmlspecialchars($contact['company']) ?>"
                                                            data-position="<?= htmlspecialchars($contact['position']) ?>"
                                                            data-email="<?= htmlspecialchars($contact['email']) ?>"
                                                            data-phone="<?= htmlspecialchars($contact['phone']) ?>"
                                                            data-linkedin="<?= htmlspecialchars($contact['linkedin']) ?>"
                                                            data-notes="<?= htmlspecialchars($contact['notes']) ?>"
                                                            data-relationship="<?= htmlspecialchars($contact['relationship']) ?>"
                                                            data-lastcontact="<?= $contact['last_contact_date'] ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-success add-interaction" data-id="<?= $contact['id'] ?>" data-name="<?= htmlspecialchars($contact['name']) ?>">
                                                        <i class="fas fa-comment"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger delete-contact" data-id="<?= $contact['id'] ?>" data-name="<?= htmlspecialchars($contact['name']) ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Networking Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item">Follow up with new connections within 48 hours</li>
                        <li class="list-group-item">Reach out to your network every 3-6 months to stay in touch</li>
                        <li class="list-group-item">Always personalize connection requests on LinkedIn</li>
                        <li class="list-group-item">Offer value before asking for favors</li>
                        <li class="list-group-item">Send thank you notes after informational interviews</li>
                    </ul>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5>Follow-up Reminders</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($follow_ups)): ?>
                        <p class="text-center">No follow-ups needed at this time.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($follow_ups as $follow_up): ?>
                                <a href="#" class="list-group-item list-group-item-action add-interaction" 
                                   data-id="<?= $follow_up['id'] ?>" 
                                   data-name="<?= htmlspecialchars($follow_up['name']) ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($follow_up['name']) ?></h6>
                                        <?php if ($follow_up['last_contact_date']): ?>
                                            <small class="text-danger"><?= $follow_up['days_since_contact'] ?> days</small>
                                        <?php else: ?>
                                            <small class="text-danger">Never contacted</small>
                                        <?php endif; ?>
                                    </div>
                                    <small><?= htmlspecialchars($follow_up['company'] ?: 'No company listed') ?></small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Contact Modal -->
<div class="modal fade" id="addContactModal" tabindex="-1" role="dialog" aria-labelledby="addContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addContactModalLabel">Add Contact</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="contactForm" method="post" action="">
                    <input type="hidden" name="action" value="add_contact">
                    <input type="hidden" name="contact_id" id="contact_id">
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="name">Full Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="relationship">Relationship</label>
                            <select class="form-control" id="relationship" name="relationship">
                                <option value="Colleague">Colleague</option>
                                <option value="Former Colleague">Former Colleague</option>
                                <option value="Manager">Manager</option>
                                <option value="Recruiter">Recruiter</option>
                                <option value="Friend">Friend</option>
                                <option value="School Connection">School Connection</option>
                                <option value="Industry Contact">Industry Contact</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="company">Company</label>
                            <input type="text" class="form-control" id="company" name="company">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="position">Position</label>
                            <input type="text" class="form-control" id="position" name="position">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="phone">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="linkedin">LinkedIn Profile</label>
                        <input type="text" class="form-control" id="linkedin" name="linkedin" placeholder="https://linkedin.com/in/username">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="last_contact_date">Last Contact Date</label>
                            <input type="date" class="form-control" id="last_contact_date" name="last_contact_date">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveContact">Save Contact</button>
            </div>
        </div>
    </div>
</div>

<!-- View Contact Details Modal -->
<div class="modal fade" id="viewContactModal" tabindex="-1" role="dialog" aria-labelledby="viewContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewContactModalLabel">Contact Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4 id="viewName"></h4>
                        <p><strong>Company:</strong> <span id="viewCompany"></span></p>
                        <p><strong>Position:</strong> <span id="viewPosition"></span></p>
                        <p><strong>Email:</strong> <span id="viewEmail"></span></p>
                        <p><strong>Phone:</strong> <span id="viewPhone"></span></p>
                        <p><strong>LinkedIn:</strong> <a id="viewLinkedIn" href="#" target="_blank"></a></p>
                        <p><strong>Relationship:</strong> <span id="viewRelationship"></span></p>
                        <p><strong>Last Contact:</strong> <span id="viewLastContact"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h5>Notes</h5>
                        <p id="viewNotes" class="border p-2" style="min-height: 100px;"></p>
                    </div>
                </div>
                
                <div class="mt-4">
                    <h5>Interaction History</h5>
                    <div id="interactionHistory">
                        <p class="text-center">Loading interaction history...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary view-edit-contact">Edit Contact</button>
                <button type="button" class="btn btn-success view-add-interaction">Record Interaction</button>
            </div>
        </div>
    </div>
</div>

<!-- Record Interaction Modal -->
<div class="modal fade" id="addInteractionModal" tabindex="-1" role="dialog" aria-labelledby="addInteractionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addInteractionModalLabel">Record Interaction</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="interactionForm" method="post" action="">
                    <input type="hidden" name="action" value="record_interaction">
                    <input type="hidden" name="contact_id" id="interaction_contact_id">
                    <p>Recording interaction with <strong id="interaction_contact_name"></strong></p>
                    
                    <div class="form-group">
                        <label for="interaction_date">Date of Interaction</label>
                        <input type="date" class="form-control" id="interaction_date" name="interaction_date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="interaction_type">Type of Interaction</label>
                        <select class="form-control" id="interaction_type" name="interaction_type" required>
                            <option value="Email">Email</option>
                            <option value="Phone Call">Phone Call</option>
                            <option value="Video Call">Video Call</option>
                            <option value="In-Person Meeting">In-Person Meeting</option>
                            <option value="LinkedIn Message">LinkedIn Message</option>
                            <option value="Social Event">Social Event</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="interaction_notes">Notes</label>
                        <textarea class="form-control" id="interaction_notes" name="interaction_notes" rows="3" placeholder="What did you discuss? Any follow-ups needed?" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveInteraction">Save Interaction</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Contact Confirmation Modal -->
<div class="modal fade" id="deleteContactModal" tabindex="-1" role="dialog" aria-labelledby="deleteContactModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteContactModalLabel">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <span id="deleteContactName"></span> from your contacts?</p>
                <p class="text-danger">This action cannot be undone and will also delete all interaction history.</p>
                <form id="deleteContactForm" method="post" action="">
                    <input type="hidden" name="action" value="delete_contact">
                    <input type="hidden" name="contact_id" id="delete_contact_id">
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
        // Contact search functionality
        $('#contactSearch').on('keyup', function() {
            const searchTerm = $(this).val().toLowerCase();
            $('#contactsTable tbody tr').each(function() {
                const rowText = $(this).text().toLowerCase();
                $(this).toggle(rowText.includes(searchTerm));
            });
        });
        
        // Save contact
        $('#saveContact').click(function() {
            $('#contactForm').submit();
        });
        
        // Edit contact
        $('.edit-contact').click(function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const company = $(this).data('company');
            const position = $(this).data('position');
            const email = $(this).data('email');
            const phone = $(this).data('phone');
            const linkedin = $(this).data('linkedin');
            const notes = $(this).data('notes');
            const relationship = $(this).data('relationship');
            const lastcontact = $(this).data('lastcontact');
            
            $('#contact_id').val(id);
            $('#name').val(name);
            $('#company').val(company);
            $('#position').val(position);
            $('#email').val(email);
            $('#phone').val(phone);
            $('#linkedin').val(linkedin);
            $('#notes').val(notes);
            $('#relationship').val(relationship);
            $('#last_contact_date').val(lastcontact);
            
            $('#addContactModalLabel').text('Edit Contact');
            $('#addContactModal').modal('show');
        });
        
        // View contact
        $('.view-contact').click(function() {
            const id = $(this).data('id');
            
            // Find the contact in the edit buttons (they contain all the data)
            const editButton = $('.edit-contact[data-id="' + id + '"]');
            
            const name = editButton.data('name');
            const company = editButton.data('company') || 'Not specified';
            const position = editButton.data('position') || 'Not specified';
            const email = editButton.data('email') || 'Not specified';
            const phone = editButton.data('phone') || 'Not specified';
            const linkedin = editButton.data('linkedin');
            const notes = editButton.data('notes') || 'No notes';
            const relationship = editButton.data('relationship') || 'Not specified';
            const lastcontact = editButton.data('lastcontact');
            
            $('#viewName').text(name);
            $('#viewCompany').text(company);
            $('#viewPosition').text(position);
            $('#viewEmail').text(email);
            $('#viewPhone').text(phone);
            
            if (linkedin) {
                $('#viewLinkedIn').text(linkedin).attr('href', linkedin);
            } else {
                $('#viewLinkedIn').text('Not specified').removeAttr('href');
            }
            
            $('#viewRelationship').text(relationship);
            
            if (lastcontact) {
                const contactDate = new Date(lastcontact);
                const daysAgo = Math.round((new Date() - contactDate) / (1000 * 60 * 60 * 24));
                $('#viewLastContact').text(contactDate.toLocaleDateString() + ' (' + daysAgo + ' days ago)');
            } else {
                $('#viewLastContact').text('No record');
            }
            
            $('#viewNotes').text(notes);
            
            // Store contact ID for edit and interaction buttons
            $('.view-edit-contact').data('id', id);
            $('.view-add-interaction').data('id', id).data('name', name);
            
            // Load interaction history
            $('#interactionHistory').html('<p class="text-center">Loading interaction history...</p>');
            
            // This would be replaced with an actual AJAX call in a real implementation
            setTimeout(function() {
                $('#interactionHistory').html('<p class="text-center">No interaction history recorded.</p>');
            }, 500);
            
            $('#viewContactModal').modal('show');
        });
        
        // From view modal to edit modal
        $('.view-edit-contact').click(function() {
            const id = $(this).data('id');
            $('#viewContactModal').modal('hide');
            $('.edit-contact[data-id="' + id + '"]').click();
        });
        
        // From view modal to add interaction modal
        $('.view-add-interaction').click(function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            $('#viewContactModal').modal('hide');
            
            // Set up the interaction modal
            $('#interaction_contact_id').val(id);
            $('#interaction_contact_name').text(name);
            $('#interaction_date').val(new Date().toISOString().substr(0, 10));
            $('#interaction_type').val('Email'); // Default
            $('#interaction_notes').val('');
            
            $('#addInteractionModal').modal('show');
        });
        
        // Add interaction button
        $('.add-interaction').click(function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            
            $('#interaction_contact_id').val(id);
            $('#interaction_contact_name').text(name);
            $('#interaction_date').val(new Date().toISOString().substr(0, 10));
            
            $('#addInteractionModal').modal('show');
        });
        
        // Save interaction
        $('#saveInteraction').click(function() {
            $('#interactionForm').submit();
        });
        
        // Delete contact
        $('.delete-contact').click(function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            
            $('#delete_contact_id').val(id);
            $('#deleteContactName').text(name);
            
            $('#deleteContactModal').modal('show');
        });
        
        // Confirm delete
        $('#confirmDelete').click(function() {
            $('#deleteContactForm').submit();
        });
    });
</script>
