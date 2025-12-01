<?php
// Check if user is logged in
if (!isLoggedIn()) {
    redirect('index.php?page=login');
}

// Get user data
$user = User::getById($_SESSION['user_id']);

// Handle file upload
$upload_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $file = $_FILES['document'];
    $document_name = sanitize($_POST['document_name']);
    $document_type = sanitize($_POST['document_type']);
    $associated_company = isset($_POST['associated_company']) ? sanitize($_POST['associated_company']) : '';
    
    // Check for errors
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Create uploads directory if it doesn't exist
        $upload_dir = 'uploads/' . $_SESSION['user_id'];
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate a unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filepath = $upload_dir . '/' . $filename;
        
        // Move the uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Insert record into database
            $stmt = $pdo->prepare("
                INSERT INTO documents (user_id, document_name, filename, file_path, document_type, associated_company, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $_SESSION['user_id'],
                $document_name,
                $file['name'],
                $filepath,
                $document_type,
                $associated_company,
                date('Y-m-d H:i:s')
            ]);
            
            $upload_message = '<div class="alert alert-success">Document uploaded successfully!</div>';
        } else {
            $upload_message = '<div class="alert alert-danger">Failed to upload file!</div>';
        }
    } else {
        $upload_message = '<div class="alert alert-danger">Error: ' . getUploadErrorMessage($file['error']) . '</div>';
    }
}

// Delete document
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $document_id = (int)$_GET['id'];
    
    // Get document info first to delete the file
    $stmt = $pdo->prepare("SELECT file_path FROM documents WHERE id = ? AND user_id = ?");
    $stmt->execute([$document_id, $_SESSION['user_id']]);
    $document = $stmt->fetch();
    
    if ($document && file_exists($document['file_path'])) {
        // Delete the file
        unlink($document['file_path']);
        
        // Delete the database entry
        $stmt = $pdo->prepare("DELETE FROM documents WHERE id = ? AND user_id = ?");
        $stmt->execute([$document_id, $_SESSION['user_id']]);
        
        $upload_message = '<div class="alert alert-success">Document deleted successfully!</div>';
    }
}

// Get all documents
$stmt = $pdo->prepare("
    SELECT * FROM documents 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$documents = $stmt->fetchAll();

// Get companies for dropdown
$stmt = $pdo->prepare("SELECT DISTINCT company FROM events WHERE user_id = ? ORDER BY company");
$stmt->execute([$_SESSION['user_id']]);
$companies = $stmt->fetchAll();

// Function to get upload error message
function getUploadErrorMessage($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return "The file is too large.";
        case UPLOAD_ERR_PARTIAL:
            return "The file was only partially uploaded.";
        case UPLOAD_ERR_NO_FILE:
            return "No file was uploaded.";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Missing a temporary folder.";
        case UPLOAD_ERR_CANT_WRITE:
            return "Failed to write file to disk.";
        case UPLOAD_ERR_EXTENSION:
            return "A PHP extension stopped the file upload.";
        default:
            return "Unknown upload error.";
    }
}
?>

<div class="container mt-5">
    <h1>Document Manager</h1>
    
    <?= $upload_message ?>
    
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Upload New Document</h5>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="document_name">Document Name</label>
                            <input type="text" class="form-control" id="document_name" name="document_name" required>
                        </div>
                        <div class="form-group">
                            <label for="document_type">Document Type</label>
                            <select class="form-control" id="document_type" name="document_type" required>
                                <option value="resume">Resume</option>
                                <option value="cover_letter">Cover Letter</option>
                                <option value="offer_letter">Offer Letter</option>
                                <option value="rejection_letter">Rejection Letter</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="associated_company">Associated Company (Optional)</label>
                            <select class="form-control" id="associated_company" name="associated_company">
                                <option value="">None</option>
                                <?php foreach ($companies as $company): ?>
                                <option value="<?= htmlspecialchars($company['company']) ?>"><?= htmlspecialchars($company['company']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="document">Upload File</label>
                            <input type="file" class="form-control-file" id="document" name="document" required>
                            <small class="form-text text-muted">Allowed file types: PDF, DOC, DOCX, JPG, PNG</small>
                        </div>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5>My Documents</h5>
                        </div>
                        <div class="col-auto">
                            <input type="text" class="form-control" id="documentSearch" placeholder="Search documents...">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($documents)): ?>
                    <p class="text-center">No documents uploaded yet.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="documentsTable">
                            <thead>
                                <tr>
                                    <th>Document Name</th>
                                    <th>Type</th>
                                    <th>Company</th>
                                    <th>Date Uploaded</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($documents as $document): ?>
                                <tr>
                                    <td><?= htmlspecialchars($document['document_name']) ?></td>
                                    <td>
                                        <?php 
                                        $types = [
                                            'resume' => '<span class="badge badge-primary">Resume</span>',
                                            'cover_letter' => '<span class="badge badge-success">Cover Letter</span>',
                                            'offer_letter' => '<span class="badge badge-info">Offer Letter</span>',
                                            'rejection_letter' => '<span class="badge badge-danger">Rejection Letter</span>',
                                            'other' => '<span class="badge badge-secondary">Other</span>'
                                        ];
                                        echo $types[$document['document_type']] ?? $types['other'];
                                        ?>
                                    </td>
                                    <td><?= htmlspecialchars($document['associated_company'] ?: 'N/A') ?></td>
                                    <td><?= date('M d, Y', strtotime($document['created_at'])) ?></td>
                                    <td>
                                        <a href="<?= htmlspecialchars($document['file_path']) ?>" class="btn btn-sm btn-outline-primary" download>
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="?page=documents&action=delete&id=<?= $document['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this document?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
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
    </div>
</div>

<script>
    // Simple document search functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('documentSearch');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const table = document.getElementById('documentsTable');
                if (table) {
                    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
                    for (let i = 0; i < rows.length; i++) {
                        const rowText = rows[i].textContent.toLowerCase();
                        rows[i].style.display = rowText.includes(searchTerm) ? '' : 'none';
                    }
                }
            });
        }
    });
</script>
