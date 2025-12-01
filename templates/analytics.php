<?php
// Check if user is logged in
if (!isLoggedIn()) {
    redirect('index.php?page=login');
}

// Get user data
$user = User::getById($_SESSION['user_id']);

// Get upcoming calendar events count
$today = date('Y-m-d');
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_upcoming,
        SUM(CASE WHEN type = 'interview' THEN 1 ELSE 0 END) as upcoming_interviews,
        SUM(CASE WHEN type = 'deadline' THEN 1 ELSE 0 END) as upcoming_deadlines,
        SUM(CASE WHEN type = 'followup' THEN 1 ELSE 0 END) as upcoming_followups,
        SUM(CASE WHEN status = 'Interview' THEN 1 ELSE 0 END) as status_interviews,
        SUM(CASE WHEN status = 'Offer' THEN 1 ELSE 0 END) as status_offers,
        SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as status_rejected,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as status_pending
    FROM calendar_events
    WHERE user_id = ? AND start >= ?
");
$stmt->execute([$_SESSION['user_id'], $today]);
$upcoming = $stmt->fetch();

// Get past calendar events count
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_past,
        SUM(CASE WHEN type = 'interview' THEN 1 ELSE 0 END) as past_interviews,
        SUM(CASE WHEN status = 'Interview' THEN 1 ELSE 0 END) as status_interviews,
        SUM(CASE WHEN status = 'Offer' THEN 1 ELSE 0 END) as status_offers,
        SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as status_rejected,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as status_pending
    FROM calendar_events
    WHERE user_id = ? AND start < ?
");
$stmt->execute([$_SESSION['user_id'], $today]);
?>

<div class="container mt-5">
    <h1>Calendar Analytics</h1>
    
    <div class="row mt-4 justify-content-center">
        <div class="col-md-10">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Calendar Events Summary</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Get upcoming calendar events count
                    $today = date('Y-m-d');
                    $stmt = $pdo->prepare("
                        SELECT 
                            COUNT(*) as total_upcoming,
                            SUM(CASE WHEN type = 'interview' THEN 1 ELSE 0 END) as upcoming_interviews,
                            SUM(CASE WHEN type = 'deadline' THEN 1 ELSE 0 END) as upcoming_deadlines,
                            SUM(CASE WHEN type = 'followup' THEN 1 ELSE 0 END) as upcoming_followups,
                            SUM(CASE WHEN status = 'Interview' THEN 1 ELSE 0 END) as status_interviews,
                            SUM(CASE WHEN status = 'Offer' THEN 1 ELSE 0 END) as status_offers,
                            SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as status_rejected,
                            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as status_pending
                        FROM calendar_events
                        WHERE user_id = ? AND start >= ?
                    ");
                    $stmt->execute([$_SESSION['user_id'], $today]);
                    $upcoming = $stmt->fetch();
                    
                    // Get past calendar events count
                    $stmt = $pdo->prepare("
                        SELECT 
                            COUNT(*) as total_past,
                            SUM(CASE WHEN type = 'interview' THEN 1 ELSE 0 END) as past_interviews,
                            SUM(CASE WHEN status = 'Interview' THEN 1 ELSE 0 END) as status_interviews,
                            SUM(CASE WHEN status = 'Offer' THEN 1 ELSE 0 END) as status_offers,
                            SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as status_rejected,
                            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as status_pending
                        FROM calendar_events
                        WHERE user_id = ? AND start < ?
                    ");
                    $stmt->execute([$_SESSION['user_id'], $today]);
                    $past = $stmt->fetch();
                    ?>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <h3><?= $upcoming['upcoming_interviews'] ?></h3>
                            <p>Upcoming Interviews</p>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $upcoming['total_upcoming'] > 0 ? ($upcoming['upcoming_interviews'] / $upcoming['total_upcoming'] * 100) : 0 ?>%"></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <h3><?= $past['past_interviews'] ?></h3>
                            <p>Completed Interviews</p>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $past['total_past'] > 0 ? ($past['past_interviews'] / $past['total_past'] * 100) : 0 ?>%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row text-center">
                        <div class="col-4">
                            <h4><?= $upcoming['upcoming_interviews'] ?></h4>
                            <p class="small">Interviews</p>
                        </div>
                        <div class="col-4">
                            <h4><?= $upcoming['upcoming_deadlines'] ?></h4>
                            <p class="small">Deadlines</p>
                        </div>
                        <div class="col-4">
                            <h4><?= $upcoming['upcoming_followups'] ?></h4>
                            <p class="small">Follow-ups</p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6 class="text-center mb-2">Calendar Events by Status</h6>
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="bg-warning text-white rounded p-1">
                                <h5><?= $upcoming['status_pending'] + $past['status_pending'] ?></h5>
                                <p class="small mb-0">Pending</p>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bg-primary text-white rounded p-1">
                                <h5><?= $upcoming['status_interviews'] + $past['status_interviews'] ?></h5>
                                <p class="small mb-0">Interview</p>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bg-success text-white rounded p-1">
                                <h5><?= $upcoming['status_offers'] + $past['status_offers'] ?></h5>
                                <p class="small mb-0">Offer</p>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bg-danger text-white rounded p-1">
                                <h5><?= $upcoming['status_rejected'] + $past['status_rejected'] ?></h5>
                                <p class="small mb-0">Rejected</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="index.php?page=calendar" class="btn btn-sm btn-outline-primary">View Calendar</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-10">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Calendar Events by Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="calendarStatusChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Calendar Events Status Chart
        var calendarStatusCtx = document.getElementById('calendarStatusChart').getContext('2d');
        var calendarStatusChart = new Chart(calendarStatusCtx, {
            type: 'pie',
            data: {
                labels: ['Pending', 'Interview', 'Offer', 'Rejected'],
                datasets: [{
                    data: [
                        <?= $upcoming['status_pending'] + $past['status_pending'] ?>,
                        <?= $upcoming['status_interviews'] + $past['status_interviews'] ?>,
                        <?= $upcoming['status_offers'] + $past['status_offers'] ?>,
                        <?= $upcoming['status_rejected'] + $past['status_rejected'] ?>
                    ],
                    backgroundColor: ['#f6c23e', '#4e73df', '#1cc88a', '#e74a3b']
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true
            }
        });
    });
</script>
