<?php
// Check if user is logged in
if (!isLoggedIn()) {
    redirect('index.php?page=login');
}

// Load templates from data/email_templates.json
$templates_file = __DIR__ . '/../data/email_templates.json';
$templates = [];
if (file_exists($templates_file)) {
    $json = file_get_contents($templates_file);
    $templates = json_decode($json, true);
}

// Group by category
$categories = [];
foreach ($templates as $t) {
    $cat = $t['category'] ?? 'Uncategorized';
    if (!isset($categories[$cat])) $categories[$cat] = [];
    $categories[$cat][] = $t;
}

?>

<div class="container mt-5">
    <h1>Email Templates</h1>
    <p class="text-muted">Quickly use and personalize common email templates for outreach, follow-ups, thank-yous and salary negotiations.</p>

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="list-group">
                <a href="#" class="list-group-item list-group-item-action active">All</a>
                <?php foreach ($categories as $cat => $list): ?>
                    <a href="#" class="list-group-item list-group-item-action category-link" data-category="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?> <span class="badge badge-secondary float-right"><?= count($list) ?></span></a>
                <?php endforeach; ?>
            </div>
            <div class="mt-3">
                <input type="text" id="templateSearch" class="form-control" placeholder="Search templates...">
            </div>
        </div>

        <div class="col-md-6">
            <div id="templatesList">
                <?php if (empty($templates)): ?>
                    <div class="alert alert-info">No templates found. Upload `data/email_templates.json` to add templates.</div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($templates as $index => $t): ?>
                            <a href="#" class="list-group-item list-group-item-action template-item" data-index="<?= $index ?>" data-category="<?= htmlspecialchars($t['category'] ?? '') ?>">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><?= htmlspecialchars($t['subject']) ?></h5>
                                    <small class="text-muted"><?= htmlspecialchars($t['category']) ?></small>
                                </div>
                                <p class="mb-1 text-truncate"><?= htmlspecialchars($t['body']) ?></p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-header bg-primary text-white">Preview & Personalize</div>
                <div class="card-body">
                    <div id="templatePreview">
                        <p class="text-muted">Select a template to preview and personalize variables like <code>{Name}</code>, <code>{Company}</code>.</p>
                    </div>

                    <div id="templateForm" style="display:none;">
                        <div class="form-group">
                            <label for="toName">To (Name)</label>
                            <input type="text" id="toName" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="toCompany">Company</label>
                            <input type="text" id="toCompany" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="extraVars">Extra variables (JSON)</label>
                            <textarea id="extraVars" class="form-control" rows="3" placeholder='{"Skill":"Python"}'></textarea>
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-success" id="copyBtn">Copy to Clipboard</button>
                            <button class="btn btn-outline-secondary" id="insertEditorBtn">Insert into Notes</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const templates = <?= json_encode($templates) ?>;

    function renderPreview(index) {
        const t = templates[index];
        const preview = document.getElementById('templatePreview');
        const form = document.getElementById('templateForm');
        preview.innerHTML = `
            <h5>${escapeHtml(t.subject)}</h5>
            <pre style="white-space:pre-wrap">${escapeHtml(t.body)}</pre>
        `;
        form.style.display = 'block';
        form.dataset.index = index;
    }

    document.querySelectorAll('.template-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const index = this.dataset.index;
            renderPreview(index);
        });
    });

    document.querySelectorAll('.category-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const cat = this.dataset.category;
            document.querySelectorAll('.template-item').forEach(item => {
                item.style.display = (item.dataset.category === cat) ? '' : 'none';
            });
        });
    });

    document.getElementById('templateSearch').addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.template-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(q) ? '' : 'none';
        });
    });

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Copy button
    document.getElementById('copyBtn').addEventListener('click', function() {
        const form = document.getElementById('templateForm');
        const index = form.dataset.index;
        const tpl = templates[index];
        let body = tpl.body;

        // Replace simple placeholders
        const name = document.getElementById('toName').value || '';
        const company = document.getElementById('toCompany').value || '';
        body = body.replace(/{Name}/g, name).replace(/{Company}/g, company);

        // Apply extra vars
        try {
            const extras = JSON.parse(document.getElementById('extraVars').value || '{}');
            for (const k in extras) {
                const re = new RegExp('{' + k + '}', 'g');
                body = body.replace(re, extras[k]);
            }
        } catch (e) {
            // ignore parse errors
        }

        // Copy to clipboard
        navigator.clipboard.writeText(body).then(() => {
            alert('Template copied to clipboard');
        }).catch(err => {
            alert('Copy failed: ' + err);
        });
    });

    // Insert into interview notes editor if open
    document.getElementById('insertEditorBtn').addEventListener('click', function() {
        const form = document.getElementById('templateForm');
        const index = form.dataset.index;
        const tpl = templates[index];
        let body = tpl.body;
        const name = document.getElementById('toName').value || '';
        const company = document.getElementById('toCompany').value || '';
        body = body.replace(/{Name}/g, name).replace(/{Company}/g, company);

        try {
            const extras = JSON.parse(document.getElementById('extraVars').value || '{}');
            for (const k in extras) {
                const re = new RegExp('{' + k + '}', 'g');
                body = body.replace(re, extras[k]);
            }
        } catch (e) {}

        // If interview notes editor exists, insert there
        const textarea = document.querySelector('#content');
        if (textarea) {
            textarea.value = body + '\n\n' + textarea.value;
            alert('Inserted into notes editor');
        } else {
            alert('Notes editor not found. Template copied to clipboard instead.');
            navigator.clipboard.writeText(body);
        }
    });
</script>
