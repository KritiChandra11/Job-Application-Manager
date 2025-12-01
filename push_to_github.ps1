# Script to push entire Job Application Manager project to GitHub

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "   Job Application Manager" -ForegroundColor Cyan
Write-Host "   GitHub Upload Script" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Navigate to project directory
$projectPath = "c:\Users\KRITI CHANDRA\OneDrive\Desktop\JOB App Track\job application manager"
Set-Location $projectPath

Write-Host "Step 1: Checking git status..." -ForegroundColor Yellow
git status

Write-Host ""
Write-Host "Step 2: Adding all files to git..." -ForegroundColor Yellow
git add .

Write-Host ""
Write-Host "Step 3: Showing what will be committed..." -ForegroundColor Yellow
git status

Write-Host ""
Write-Host "Step 4: Creating commit..." -ForegroundColor Yellow
$commitMessage = "Add complete Job Application Manager project files"
git commit -m $commitMessage

Write-Host ""
Write-Host "Step 5: Pushing to GitHub (main branch)..." -ForegroundColor Yellow
git push origin main

Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
Write-Host "   Upload Complete!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Your repository: https://github.com/KritiChandra11/job-application-manager" -ForegroundColor Cyan
Write-Host ""
