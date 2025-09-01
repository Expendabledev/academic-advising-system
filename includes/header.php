<?php
require_once __DIR__ . '/../includes/auth.php';
$auth = new Auth();
$auth->requireAuth();

$currentUser = [
    'name' => $_SESSION['full_name'] ?? 'Supervisor',
    'role' => $_SESSION['role'] ?? 'supervisor'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Advising System | <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></title>
    <link rel="stylesheet" href="/swepgroup17/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <img src="/GROUP17/LAUTECH_LAUTECH-LAUTECH.png" alt="LAUTECH Logo" width="80" height="80">
                <div>
                    <h1>Academic Advising System</h1>
                    <p class="university-name">LADOKE AKINTOLA UNIVERSITY OF TECHNOLOGY, OGBOMOSO</p>
                </div>
            </div>
           
            <div class="supervisor-info">
                <div class="user-profile">
                    <i class="fas fa-user-circle"></i>
                    <span><?= htmlspecialchars($currentUser['name']) ?></span>
                </div>
                <div>
                    <h3>Group Supervisor</h3>
                    <p>Dr. Oyelakun T.A.</p>
                    <small>Department of Information System Science</small>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="nav-tabs">
            <a href="/GROUP17/index.php?page=dashboard" class="nav-tab <?= ($activeTab ?? '') === 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i> Dashboard
            </a>
            <a href="/GROUP17/index.php?page=students" class="nav-tab <?= ($activeTab ?? '') === 'students' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Students
            </a>
            <a href="/GROUP17/index.php?page=courses" class="nav-tab <?= ($activeTab ?? '') === 'courses' ? 'active' : '' ?>">
                <i class="fas fa-book"></i> Courses
            </a>
            <a href="/GROUP17/index.php?page=meetings" class="nav-tab <?= ($activeTab ?? '') === 'meetings' ? 'active' : '' ?>">
                <i class="fas fa-comments"></i> Advising
            </a>
            <a href="/GROUP17/index.php?page=reports" class="nav-tab <?= ($activeTab ?? '') === 'reports' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i> Reports
            </a>
            <a href="/GROUP17/index.php?page=profile" class="nav-tab <?= ($activeTab ?? '') === 'profile' ? 'active' : '' ?>">
                <i class="fas fa-cog"></i> Profile
            </a>
            <a href="/GROUP17/includes/logout.php" class="nav-tab" style="margin-left: auto;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <!-- Main Content -->
        <main class="main-content">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
           
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash'])): ?>
                <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['type']) ?>">
                    <i class="fas fa-info-circle"></i>
                    <?= htmlspecialchars($_SESSION['flash']['message']) ?>
                    <?php unset($_SESSION['flash']); ?>
                </div>
            <?php endif; ?>