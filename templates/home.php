<?php
// Redirect to dashboard if logged in
if (isLoggedIn()) {
    redirect('index.php?page=dashboard');
}
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8">
            <h1 class="display-4">Track Your Job Applications</h1>
            <p class="lead">Keep track of all your job applications in one place and never miss an opportunity again.</p>
            <hr class="my-4">
            <p>Register now to start managing your job applications effectively.</p>
            <a class="btn btn-primary btn-lg" href="index.php?page=register" role="button">Get Started</a>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Already have an account?</h5>
                    <p class="card-text">Sign in to access your dashboard.</p>
                    <a href="index.php?page=login" class="btn btn-outline-primary">Sign In</a>
                </div>
            </div>
        </div>
    </div>
</div>
