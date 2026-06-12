<?php
/**
 * Header / Navigation Layout
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --color-primary: #08326d;
            --color-primary-dark: #05214a;
            --color-accent: #e67e22; /* Dark IziTech Orange */
            --color-accent-hover: #d97706;
            --color-bg-light: #f8fbff;
            --color-white: #ffffff;
            --shadow-premium: 0 10px 30px -5px rgba(8, 50, 109, 0.15);
            --sidebar-width: 280px;
            --color-text-main: #ffffff; /* White Text */
            --color-text-muted: #64748b;
            --color-border-soft: #e2e8f0;
        }

        * { box-sizing: border-box; }

        /* Dashboard Layout Styles */
        body {
            display: flex;
            min-height: 100vh;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--color-bg-light);
            color: var(--color-text-main);
            margin: 0;
            overflow-x: hidden;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(195deg, #0b4d8c 0%, #061b30 100%);
            color: white;
            padding: var(--spacing-lg);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.15);
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: var(--spacing-2xl);
            padding-bottom: var(--spacing-lg);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar-brand h1 {
            font-size: 1.25rem;
            margin: 0;
            color: white;
        }

        .sidebar-brand p {
            margin: 0;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.75);
        }

        .brand-logo {
            width: 45px;
            height: auto;
            border-radius: 8px;
            background: white;
            padding: 4px;
        }

        .sidebar-nav {
            list-style: none;
        }

        .sidebar-nav-item {
            margin-bottom: var(--spacing-sm);
        }

        .sidebar-nav-link {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 18px;
            color: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            transition: all 0.3s ease;
            text-decoration: none;
            font-weight: 500;
        }

        .sidebar-nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .sidebar-nav-link.active {
            background: var(--color-accent);
            color: white;
            box-shadow: 0 4px 15px rgba(247, 148, 29, 0.3);
        }

        .sidebar-nav-link i {
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }

        .sidebar-nav-link.active i {
            color: white !important;
        }

        .main-content {
            /* Shifting content left: reduced margin and adjusted width calculation */
            margin-left: calc(var(--sidebar-width) - 15px);
            width: calc(100% - (var(--sidebar-width) - 15px));
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            background: var(--color-primary);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: var(--spacing-lg);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-sm);
        }

        .topbar-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
        }

        .topbar-actions {
            display: flex;
            gap: var(--spacing-lg);
            align-items: center;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: background-color var(--transition-fast);
        }

        .user-menu:hover {
            background-color: var(--color-bg);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--color-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            background-color: var(--color-primary); /* Use IziTech Blue inside the pages */
            color: white;
        }

        .container {
            /* Ensure the container doesn't push elements off-screen */
            width: 98%;
            max-width: 1380px;
            margin: 0;
        }

        /* Glassmorphism Cards for Blue Background */
        .card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }

        /* Enhanced Table Visibility */
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 1rem;
            color: white;
        }

        .table thead th {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--color-accent); /* Orange headers for contrast */
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 1.25rem 1rem;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            text-align: left;
        }

        .table tbody td {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 0.95rem;
            vertical-align: middle;
            color: white;
        }

        .table tbody tr:hover {
            background-color: rgba(247, 148, 29, 0.03); /* Subtle Orange tint on hover */
        }

        /* Modern Badges with Better Text Contrast */
        .badge {
            padding: 0.5em 0.9em;
            font-weight: 700;
            font-size: 0.7rem;
            border-radius: 8px;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .badge-primary { 
            background: var(--color-accent); /* Small boxes in Dark Orange */
            color: white; 
            box-shadow: 0 2px 4px rgba(230, 126, 34, 0.2);
        }

        .badge-success { 
            background: #dcfce7; 
            color: #15803d; 
            border: 1px solid #bbf7d0; 
        }

        .badge-inactive { 
            background: #fff7ed; 
            color: #c2410c; 
            border: 1px solid #ffedd5; 
        }

        .badge-warning, .badge-medium { 
            background: #fef9c3; 
            color: #854d0e; 
            border: 1px solid #fef08a; 
        }

        .badge-danger, .badge-critical, .badge-high { 
            background: #fef2f2; 
            color: #b91c1c; 
            border: 1px solid #fee2e2; 
        }

        .text-muted {
            color: var(--color-text-muted) !important;
            font-weight: 500;
        }

        /* Sidebar Icon Colors */
        .nav-icon-dashboard { color: #60a5fa !important; } /* Sky Blue */
        .nav-icon-guards { color: #818cf8 !important; }    /* Indigo */
        .nav-icon-sites { color: #22d3ee !important; }     /* Cyan */
        .nav-icon-tags { color: #fbbf24 !important; }      /* Amber */
        .nav-icon-logs { color: #34d399 !important; }      /* Emerald */
        .nav-icon-incidents { color: #facc15 !important; } /* Yellow */
        .nav-icon-map { color: #fb7185 !important; }       /* Rose */
        .nav-icon-admin { color: #94a3b8 !important; }     /* Slate */

        .text-warning {
            color: #facc15 !important;
        }

        /* Global Button Styles - IziTech Orange */
        .btn-primary {
            background-color: var(--color-accent) !important;
            border-color: var(--color-accent) !important;
            color: #ffffff !important;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-secondary { background: #475569; color: white; border: none; }
        .btn-secondary:hover { background: #334155; }
        
        .btn-danger { background: #ef4444; color: white; border: none; }
        .btn-danger:hover { background: #dc2626; }

        .btn-warning { background: #f59e0b; color: white; border: none; }
        .btn-warning:hover { background: #d97706; }
        
        .btn-info { background: #0ea5e9; color: white; border: none; }
        .btn-info:hover { background: #0284c7; }

        .btn-sm { padding: 5px 10px; font-size: 0.8rem; border-radius: 6px; cursor: pointer; }

        .btn-primary:hover {
            background-color: #d35400 !important;
            border-color: #d35400 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(230, 126, 34, 0.3);
        }

        /* Fix for dark blue text inside inputs/selects */
        input, select, textarea {
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
        }

        option {
            background-color: var(--color-primary-dark);
            color: white;
        }

        /* Improved table responsiveness */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            display: block;
            -webkit-overflow-scrolling: touch;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: var(--spacing-md);
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .sidebar-nav {
                display: flex;
                flex-wrap: wrap;
                gap: var(--spacing-md);
            }

            .sidebar-nav-item {
                margin-bottom: 0;
                flex: 1;
                min-width: 150px;
            }

            .sidebar-nav-link {
                flex-direction: column;
                text-align: center;
                padding: var(--spacing-md);
            }

            .content {
                padding: var(--spacing-lg);
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-brand" style="display: flex; align-items: center; gap: var(--spacing-md); margin-bottom: var(--spacing-2xl); padding-bottom: var(--spacing-lg); border-bottom: 1px solid rgba(255, 255, 255, 0.2);">
            <img src="<?php echo APP_URL; ?>/izitech.jpg" alt="IziTech logo" class="brand-logo">
            <div>
                <h1>iZi GP Admin</h1>
            </div>
        </div>
        
        <nav>
            <ul class="sidebar-nav">
                <li class="sidebar-nav-item">
                    <a href="<?php echo APP_URL; ?>/dashboard.php" class="sidebar-nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'dashboard.php') ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line nav-icon-dashboard"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo APP_URL; ?>/guards.php" class="sidebar-nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'guards.php') ? 'active' : ''; ?>">
                        <i class="fas fa-users nav-icon-guards"></i>
                        <span>Guards</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo APP_URL; ?>/sites.php" class="sidebar-nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'sites.php') ? 'active' : ''; ?>">
                        <i class="fas fa-map-marker-alt nav-icon-sites"></i>
                        <span>Sites</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo APP_URL; ?>/nfc-tags.php" class="sidebar-nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'nfc-tags.php') ? 'active' : ''; ?>">
                        <i class="fas fa-tag nav-icon-tags"></i>
                        <span>NFC Tags</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo APP_URL; ?>/patrol-charts.php" class="sidebar-nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'patrol-charts.php') ? 'active' : ''; ?>">
                        <i class="fas fa-chart-pie nav-icon-tags" style="color: #f472b6 !important;"></i>
                        <span>Analytics</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo APP_URL; ?>/patrol-tracking.php" class="sidebar-nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'patrol-tracking.php') ? 'active' : ''; ?>">
                        <i class="fas fa-history nav-icon-logs"></i>
                        <span>Patrol Logs</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo APP_URL; ?>/incidents.php" class="sidebar-nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'incidents.php') ? 'active' : ''; ?>">
                        <i class="fas fa-exclamation-triangle nav-icon-incidents"></i>
                        <span>Incidents</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo APP_URL; ?>/map.php" class="sidebar-nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'map.php') ? 'active' : ''; ?>">
                        <i class="fas fa-map nav-icon-map"></i>
                        <span>Map</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo APP_URL; ?>/admin-users.php" class="sidebar-nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'admin-users.php') ? 'active' : ''; ?>">
                        <i class="fas fa-user-shield nav-icon-admin"></i>
                        <span>Admin Users</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="topbar">
            <h2 class="topbar-title"><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard'; ?></h2>
            <div class="topbar-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="user-menu">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                    </div>
                    <a href="<?php echo APP_URL; ?>/logout.php" class="btn btn-sm btn-primary">Logout</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content">
