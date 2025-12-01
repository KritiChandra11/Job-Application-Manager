<?php
// Check if user is logged in
if (!isLoggedIn()) {
    redirect('index.php?page=login');
}
?>

<div class="container mt-5">
    <h1>Job Portals</h1>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Click on any job portal to open it in a new tab. These are direct links to popular job search websites.
    </div>
    
    <div class="row mt-4">
        <!-- Popular Job Portals -->
        <div class="col-12">
            <h3 class="mb-3">Popular Job Portals</h3>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fab fa-linkedin text-primary"></i> LinkedIn</h5>
                    <p class="card-text">Find jobs through your professional network. Connect with recruiters and professionals.</p>
                    <a href="https://www.linkedin.com/jobs/" target="_blank" class="btn btn-primary btn-block">Visit LinkedIn</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-search text-primary"></i> Indeed</h5>
                    <p class="card-text">Search millions of jobs from thousands of job boards, newspapers, and company websites.</p>
                    <a href="https://www.indeed.com/" target="_blank" class="btn btn-primary btn-block">Visit Indeed</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-door-open text-success"></i> Glassdoor</h5>
                    <p class="card-text">Find jobs and company reviews from employees. Get salary insights and interview tips.</p>
                    <a href="https://www.glassdoor.com/" target="_blank" class="btn btn-primary btn-block">Visit Glassdoor</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-briefcase text-warning"></i> Naukri.com</h5>
                    <p class="card-text">India's premier job site with thousands of jobs from leading companies across industries.</p>
                    <a href="https://www.naukri.com/" target="_blank" class="btn btn-primary btn-block">Visit Naukri</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-dragon text-danger"></i> Monster</h5>
                    <p class="card-text">Find jobs, career advice and recruitment solutions. Access resume writing services.</p>
                    <a href="https://www.monster.com/" target="_blank" class="btn btn-primary btn-block">Visit Monster</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-file-archive text-info"></i> ZipRecruiter</h5>
                    <p class="card-text">Find jobs with one search across multiple job sites. Get job alerts and recommendations.</p>
                    <a href="https://www.ziprecruiter.com/" target="_blank" class="btn btn-primary btn-block">Visit ZipRecruiter</a>
                </div>
            </div>
        </div>
        
        <!-- Industry Specific Job Portals -->
        <div class="col-12 mt-4">
            <h3 class="mb-3">Industry-Specific Job Portals</h3>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-dice text-secondary"></i> Dice</h5>
                    <p class="card-text">Specialized in tech and IT jobs. Find positions for developers, analysts, and IT professionals.</p>
                    <a href="https://www.dice.com/" target="_blank" class="btn btn-primary btn-block">Visit Dice</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-money-bill-wave text-success"></i> eFinancialCareers</h5>
                    <p class="card-text">Specialized in financial services jobs in banking, finance, and accounting.</p>
                    <a href="https://www.efinancialcareers.com/" target="_blank" class="btn btn-primary btn-block">Visit eFinancialCareers</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-heartbeat text-danger"></i> Health eCareers</h5>
                    <p class="card-text">Find healthcare jobs in medical, nursing, pharmacy, and allied health fields.</p>
                    <a href="https://www.healthecareers.com/" target="_blank" class="btn btn-primary btn-block">Visit Health eCareers</a>
                </div>
            </div>
        </div>
    </div>
</div>
