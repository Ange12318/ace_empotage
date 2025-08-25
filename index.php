<?php include 'datas.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des BL - ACE Empotage</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.3/jspdf.plugin.autotable.min.js"></script>
    <style>
        /* Reset et variables */
        :root {
            --primary-blue: #1e40af; /* Bleu profond */
            --light-blue: #3b82f6; /* Bleu clair */
            --sky-blue: #93c5fd; /* Bleu ciel */
            --white: #ffffff; /* Blanc */
            --light-gray: #f8fafc; /* Gris très clair */
            --medium-gray: #e2e8f0; /* Gris moyen */
            --dark-gray: #94a3b8; /* Gris foncé */
            --light-green: #34d399; /* Vert clair */
            --soft-green: #a7f3d0; /* Vert pastel */
            --red: #ef4444; /* Rouge */
            --orange: #f97316; /* Orange */
            --purple: #8b5cf6; /* Violet */
            --border-radius: 12px;
            --border-radius-sm: 8px;
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            line-height: 1.6;
            color: #334155;
            background: linear-gradient(135deg, var(--light-gray) 0%, var(--white) 100%);
            min-height: 100vh;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .app-container { display: flex; flex-direction: column; min-height: 100vh; }

        /* Header redesign */
        .app-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--light-blue) 100%);
            position: sticky; top: 0; z-index: 100; 
            box-shadow: var(--shadow-lg);
        }

        .header-content {
            display: flex; justify-content: space-between; align-items: center;
            padding: 1rem 2rem; max-width: 1600px; margin: 0 auto; width: 100%;
        }

        .header-logo-container {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-logo {
            max-width: 200px; height: auto; transition: transform 0.3s ease;
            filter: brightness(0) invert(1);
        }
        .header-logo:hover { transform: scale(1.05); }

        .header-title {
            color: var(--white);
        }
        .header-title h1 {
            font-size: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header-title h1 i {
            color: var(--sky-blue); font-size: 1.8rem;
        }

        .header-right {
            display: flex; align-items: center; gap: 1rem;
        }
        .datetime {
            color: var(--white); font-size: 0.9rem; font-weight: 500; padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.15); border-radius: var(--border-radius-sm);
            border: 1px solid rgba(255, 255, 255, 0.2); text-align: right;
            backdrop-filter: blur(10px);
        }

        /* Navigation redesign */
        .nav-menu {
            background: var(--white); padding: 0.5rem 2rem;
            display: flex; justify-content: center; gap: 1rem; max-width: 1600px; margin: 0 auto;
            border-bottom: 1px solid var(--medium-gray);
        }
        .nav-item {
            display: inline-flex; align-items: center; padding: 0.75rem 1.5rem; border-radius: var(--border-radius-sm);
            cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); font-weight: 500;
            color: var(--primary-blue); text-transform: uppercase; letter-spacing: 0.05em; position: relative; overflow: hidden;
        }
        .nav-item::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent); transition: left 0.5s; }
        .nav-item:hover::before { left: 100%; }
        .nav-item:hover { background: var(--light-gray); color: var(--primary-blue); transform: translateY(-2px); }
        .nav-item.active { background: var(--light-blue); color: var(--white); box-shadow: var(--shadow); }
        .nav-item i { margin-right: 0.5rem; font-size: 1.1rem; }

        /* Main content redesign */
        .app-main {
            flex: 1; max-width: 1600px; margin: 0 auto; width: 100%; padding: 2rem;
            display: grid; grid-template-columns: 1fr; gap: 2rem;
        }
        .view-content { display: none; animation: fadeIn 0.5s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px);} to { opacity: 1; transform: translateY(0);} }

        /* Card redesign */
        .search-card, .form-card, .table-card {
            background: var(--white); border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg); overflow: hidden; border: 1px solid var(--medium-gray);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .search-card:hover, .form-card:hover, .table-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-xl); }

        .card-header {
            padding: 1.25rem 2rem; background: linear-gradient(135deg, var(--light-blue) 0%, var(--primary-blue) 100%);
            border-bottom: 1px solid var(--medium-gray); display: flex; justify-content: space-between; align-items: center;
        }
        .card-header h2 {
            font-size: 1.25rem; font-weight: 700; color: var(--white); display: flex; align-items: center; gap: 0.75rem;
        }

        /* Form redesign */
        .filter-form, .bl-form, .login-form { padding: 2rem; }
        .filter-grid, .form-grid, .login-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;
        }
        .filter-group, .form-group, .login-group { position: relative; }

        label { display: block; margin-bottom: 0.5rem; font-size: 0.875rem; font-weight: 600; color: var(--primary-blue);
            text-transform: uppercase; letter-spacing: 0.025em; }

        select, input[type="text"], input[type="number"], input[type="date"], input[type="password"] {
            width: 100%; padding: 0.875rem 1rem 0.875rem 2.5rem; border: 2px solid var(--medium-gray);
            border-radius: var(--border-radius-sm); font-size: 0.95rem; transition: all 0.3s ease; background: var(--white); color: #334155;
        }
        select:focus, input:focus { outline: none; border-color: var(--light-blue); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); transform: translateY(-1px); }

        .input-icon {
            position: absolute; left: 0.75rem; top: 2.5rem; color: var(--dark-gray); font-size: 1rem; pointer-events: none;
        }

        /* Button redesign */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; padding: 0.875rem 1.5rem;
            border-radius: var(--border-radius-sm); font-size: 0.925rem; font-weight: 600; cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: none; gap: 0.5rem; text-transform: uppercase; letter-spacing: 0.025em;
            position: relative; overflow: hidden;
        }
        .btn::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent); transition: left 0.5s; }
        .btn:hover::before { left: 100%; }
        .primary-btn { background: var(--light-blue); color: var(--white); box-shadow: var(--shadow); }
        .primary-btn:hover { background: var(--primary-blue); transform: translateY(-2px); box-shadow: var(--shadow-lg); }
        .secondary-btn { background: var(--white); color: var(--primary-blue); border: 2px solid var(--light-blue); }
        .secondary-btn:hover { background: var(--light-blue); color: var(--white); transform: translateY(-2px); box-shadow: var(--shadow); }
        .danger-btn { background: var(--red); color: var(--white); }
        .danger-btn:hover { background: #dc2626; transform: translateY(-2px); box-shadow: var(--shadow-lg); }
        .export-btn { background: var(--light-green); color: var(--white); }
        .export-btn:hover { background: #059669; transform: translateY(-2px); box-shadow: var(--shadow-lg); }
        .filter-actions, .form-actions, .login-actions { display: flex; gap: 1rem; justify-content: flex-end; flex-wrap: wrap; }

        /* Table controls redesign */
        .table-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            background: var(--light-gray);
            padding: 1rem;
            border-radius: var(--border-radius-sm);
            margin-bottom: 1rem;
        }
        .group-option { display: flex; align-items: center; gap: 0.5rem; }
        .group-option input[type="checkbox"] { width: 1.25rem; height: 1.25rem; accent-color: var(--light-blue); }
        .group-option label { font-size: 0.9rem; font-weight: 500; margin: 0; color: var(--primary-blue); text-transform: none; letter-spacing: normal; }

        /* Table redesign */
        .table-container {
            padding: 0 1rem 1rem;
        }
        
        .table-responsive { 
            overflow-x: auto; 
            border-radius: var(--border-radius-sm); 
            border: 1px solid var(--medium-gray);
            max-height: 60vh;
            position: relative;
        }
        table { width: 100%; border-collapse: collapse; font-size: 0.85rem; background: var(--white); }
        th, td { padding: 0.75rem 0.5rem; text-align: left; border-bottom: 1px solid var(--medium-gray); }
        th {
            background: var(--light-gray); font-weight: 700; color: var(--primary-blue);
            text-transform: uppercase; letter-spacing: 0.025em; font-size: 0.75rem; position: sticky; top: 0; z-index: 10;
            border-bottom: 2px solid var(--light-blue);
        }
        th:nth-child(1), td:nth-child(1) { width: 8%; }
        th:nth-child(2), td:nth-child(2) { width: 8%; }
        th:nth-child(3), td:nth-child(3) { width: 8%; }
        th:nth-child(4), td:nth-child(4) { width: 10%; }
        th:nth-child(5), td:nth-child(5) { width: 8%; }
        th:nth-child(6), td:nth-child(6) { width: 6%; }
        th:nth-child(7), td:nth-child(7) { width: 8%; }
        th:nth-child(8), td:nth-child(8) { width: 6%; }
        th:nth-child(9), td:nth-child(9) { width: 6%; }
        th:nth-child(10), td:nth-child(10) { width: 6%; }
        th:nth-child(11), td:nth-child(11) { width: 6%; }
        th:nth-child(12), td:nth-child(12) { width: 8%; }
        th:nth-child(13), td:nth-child(13) { width: 6%; }
        th:nth-child(14), td:nth-child(14) { width: 10%; }
        tbody tr { transition: all 0.2s ease; }
        tbody tr:hover { background: #f1f5f9; transform: scale(1.001); }
        tbody tr.completed { background: linear-gradient(135deg, #ecfdf5, #f0fdf4); }
        tbody tr.group-summary { background: var(--light-blue); color: var(--white); font-weight: 600; }

        .relance-cell { font-size: 0.8rem; font-weight: 600; color: var(--primary-blue); }
        .alert-cell { font-size: 0.8rem; font-weight: 600; color: var(--red); }

        .status-btn, .edit-btn, .delete-btn { padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; border: none; cursor: pointer; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 0.025em; margin: 0.2rem; }
        .status-pending { background: linear-gradient(135deg, var(--orange), #ea580c); color: var(--white); }
        .status-completed { background: var(--light-green); color: var(--white); }
        .edit-btn { background: var(--light-blue); color: var(--white); }
        .delete-btn { background: var(--red); color: var(--white); }
        .status-btn:hover, .edit-btn:hover, .delete-btn:hover { transform: translateY(-1px); box-shadow: var(--shadow); }

        .date-input { width: 100%; padding: 0.5rem; border: 2px solid var(--medium-gray); border-radius: var(--border-radius-sm); font-size: 0.85rem; }
        .date-input:focus { outline: none; border-color: var(--light-blue); box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1); }

        .icon-btn {
            width: 2.5rem; height: 2.5rem; padding: 0; border-radius: 50%;
            background: rgba(255, 255, 255, 0.2); color: var(--white); border: 1px solid rgba(255, 255, 255, 0.3);
            cursor: pointer; transition: all 0.3s ease; backdrop-filter: blur(10px);
        }
        .icon-btn:hover { background: rgba(255, 255, 255, 0.3); color: var(--white); transform: scale(1.1); }

        /* Login screen redesign */
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem;
            background: linear-gradient(135deg, var(--light-blue) 0%, var(--primary-blue) 100%);
        }
        .login-card {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-xl);
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            border: 1px solid var(--medium-gray);
            transform: translateY(20px);
            animation: slideIn 0.5s ease forwards;
        }
        @keyframes slideIn { to { transform: translateY(0); } }
        .login-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-xl);
        }
        .login-logo {
            max-width: 200px;
            margin: 0 auto 2rem;
            display: block;
            transition: transform 0.3s ease;
        }
        .login-logo:hover { transform: scale(1.05); }
        .login-error {
            color: var(--red);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            text-align: center;
            display: none;
        }

        /* Profile panel redesign */
        .profile-panel {
            position: absolute; top: 70px; right: 2rem; width: 250px;
            background: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow-lg);
            border: 1px solid var(--medium-gray); padding: 1.5rem;
            display: none; z-index: 100; transition: all 0.3s ease;
        }
        .profile-panel.active { display: block; }
        .profile-info {
            display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;
        }
        .profile-info i { font-size: 2rem; color: var(--light-blue); }
        .profile-info span { font-weight: 600; color: var(--primary-blue); }
        .profile-actions { display: flex; justify-content: flex-end; }
        .logout-btn {
            background: var(--red); color: var(--white);
            padding: 0.5rem 1rem; border-radius: var(--border-radius-sm); font-size: 0.9rem;
            font-weight: 600; cursor: pointer; transition: all 0.3s ease;
        }
        .logout-btn:hover { transform: translateY(-1px); box-shadow: var(--shadow); }

        /* Notifications redesign */
        .notification {
            position: fixed; top: 20px; right: 20px; padding: 1rem 1.5rem; border-radius: 8px;
            color: var(--white); font-weight: 600; z-index: 1000; transform: translateX(400px);
            transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex; align-items: center; gap: 1rem; max-width: 400px;
        }
        .notification .close-btn {
            cursor: pointer; font-size: 0.9rem; opacity: 0.7; transition: opacity 0.3s ease;
        }
        .notification .close-btn:hover { opacity: 1; }
        .notification-history {
            position: fixed; top: 80px; right: 2rem; width: 300px; max-height: 400px; overflow-y: auto;
            background: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow-lg);
            border: 1px solid var(--medium-gray); padding: 1rem; display: none; z-index: 100;
        }
        .notification-history.active { display: block; }
        .notification-history-item {
            padding: 0.5rem; border-bottom: 1px solid var(--medium-gray); font-size: 0.85rem;
            color: var(--primary-blue); display: flex; align-items: center; gap: 0.5rem;
        }
        .notification-history-item:last-child { border-bottom: none; }

        /* Action buttons container */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        /* Stats cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            border-left: 4px solid var(--light-blue);
        }
        
        .stat-card.pending {
            border-left-color: var(--orange);
        }
        
        .stat-card.completed {
            border-left-color: var(--light-green);
        }
        
        .stat-card.total {
            border-left-color: var(--purple);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: var(--dark-gray);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* User management view */
        #user-management-view {
            display: none;
        }

        @media (max-width: 768px) {
            .app-main { padding: 1rem; grid-template-columns: 1fr; }
            .header-content { padding: 0 1rem; flex-wrap: wrap; gap: 1rem; }
            .header-logo { max-width: 40px; }
            .header-title h1 { font-size: 1.2rem; }
            .nav-menu { flex-wrap: wrap; gap: 0.5rem; justify-content: flex-start; }
            .profile-panel, .notification-history { right: 1rem; width: 90%; max-width: 300px; }
            .filter-grid, .form-grid, .login-grid { grid-template-columns: 1fr; gap: 1rem; }
            .filter-actions, .form-actions, .login-actions { flex-direction: column; }
            .btn { width: 100%; }
            .card-header { padding: 1rem; flex-direction: column; gap: 1rem; align-items: flex-start; }
            .table-responsive { font-size: 0.75rem; }
            th, td { padding: 0.5rem 0.25rem; }
            th:nth-child(1), td:nth-child(1) { width: auto; min-width: 60px; }
            th:nth-child(2), td:nth-child(2) { width: auto; min-width: 60px; }
            th:nth-child(3), td:nth-child(3) { width: auto; min-width: 60px; }
            th:nth-child(4), td:nth-child(4) { width: auto; min-width: 80px; }
            th:nth-child(5), td:nth-child(5) { width: auto; min-width: 60px; }
            th:nth-child(6), td:nth-child(6) { width: auto; min-width: 50px; }
            th:nth-child(7), td:nth-child(7) { width: auto; min-width: 60px; }
            th:nth-child(8), td:nth-child(8) { width: auto; min-width: 50px; }
            th:nth-child(9), td:nth-child(9) { width: auto; min-width: 50px; }
            th:nth-child(10), td:nth-child(10) { width: auto; min-width: 50px; }
            th:nth-child(11), td:nth-child(11) { width: auto; min-width: 50px; }
            th:nth-child(12), td:nth-child(12) { width: auto; min-width: 60px; }
            th:nth-child(13), td:nth-child(13) { width: auto; min-width: 50px; }
            th:nth-child(14), td:nth-child(14) { width: auto; min-width: 100px; }
            .login-card { padding: 2rem; max-width: 350px; }
            .login-logo { max-width: 150px; }
            .datetime { font-size: 0.8rem; padding: 0.4rem 0.8rem; }
            .action-buttons {
                flex-direction: column;
            }
            .stats-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .header-logo { max-width: 35px; }
            .header-title h1 { font-size: 1rem; }
            .header-title h1 i { font-size: 1.2rem; }
            .app-main { padding: 0.5rem; }
            .nav-item { padding: 0.5rem 1rem; font-size: 0.9rem; }
            .login-card { padding: 1.5rem; }
            .datetime { font-size: 0.75rem; }
        }
    </style>
</head>
<body>
    <div id="login-container" class="login-container">
        <section class="login-card">
            <img src="ACE_LOG.jpg" alt="Logo de l'entreprise" class="login-logo">
            <div class="card-header">
                <h2><i class="fas fa-sign-in-alt"></i> Connexion</h2>
            </div>
            <form id="loginForm" class="login-form">
                <div class="login-error" id="loginError"></div>
                <div class="login-grid">
                    <div class="login-group">
                        <label for="login_id">Identifiant</label>
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" id="login_id" name="username" required placeholder="Identifiant">
                    </div>
                    <div class="login-group">
                        <label for="login_password">Mot de passe</label>
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="login_password" name="password" required placeholder="Mot de passe">
                    </div>
                </div>
                <div class="login-actions">
                    <button type="submit" class="btn primary-btn"><i class="fas fa-sign-in-alt"></i> Se connecter</button>
                </div>
            </form>
        </section>
    </div>

    <div id="app-container" class="app-container" style="display: none;">
        <header class="app-header">
            <div class="header-content">
                <div class="header-logo-container">
                    <img src="ace.png" alt="Logo de l'entreprise" class="header-logo">
                    <div class="header-title">
                        <h1><i class="fas fa-file-invoice"></i> Gestion des BL</h1>
                    </div>
                </div>
                <div class="header-right">
                    <div class="datetime" id="datetime"></div>
                    <button id="toggleNotifications" class="icon-btn"><i class="fas fa-bell"></i></button>
                    <button id="toggleProfile" class="icon-btn"><i class="fas fa-user"></i></button>
                </div>
            </div>
            <nav class="nav-menu">
                <span class="nav-item active" data-view="dashboard"><i class="fas fa-chart-line"></i> Tableau de bord</span>
                <span class="nav-item" data-view="new-bl"><i class="fas fa-plus-circle"></i> Nouveau BL</span>
                <span class="nav-item" data-view="user-management" style="display: none;" id="adminNavItem"><i class="fas fa-users-cog"></i> Gestion Utilisateurs</span>
            </nav>
            <div id="profile-panel" class="profile-panel">
                <div class="profile-info">
                    <i class="fas fa-user-circle"></i>
                    <span id="user-id">Utilisateur</span>
                </div>
                <div class="profile-actions">
                    <button id="logout-btn" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Déconnexion</button>
                </div>
            </div>
            <div id="notification-history" class="notification-history"></div>
        </header>

        <main class="app-main">
            <div id="dashboard-view" class="view-content" style="display: block;">
                <section class="search-card">
                    <div class="card-header">
                        <h2><i class="fas fa-search"></i> Recherche Avancée</h2>
                    </div>
                    <form id="searchForm" class="filter-form">
                        <div class="filter-grid">
                            <div class="filter-group">
                                <label for="search_banque">Banque</label>
                                <select id="search_banque" name="banque">
                                    <option value="">Toutes les banques</option>
                                      <?php foreach ($banques as $b): ?>
                <option value="<?= $b['nom'] ?>"><?= $b['nom'] ?></option>
            <?php endforeach; ?>
                                </select>
                            </div>
                             <div class="form-group">
                                <label for="transitaire">Transitaire</label>
                                <select id="transitaire" name="transitaire">
                                    <option value="">Sélectionnez un transitaire</option>
                                    <?php foreach ($transitaires as $t): ?>
                                        <option value="<?= $t['nom'] ?>"><?= $t['nom'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label for="search_numero_das">N° DAS</label>
                                <i class="fas fa-hashtag input-icon"></i>
                                <input type="text" id="search_numero_das" name="numero_das" placeholder="Rechercher par N° DAS">
                            </div>
                        </div>
                        <div class="filter-actions">
                            <button type="submit" class="btn primary-btn"><i class="fas fa-search"></i> Rechercher</button>
                            <button type="reset" class="btn secondary-btn"><i class="fas fa-undo"></i> Réinitialiser</button>
                        </div>
                    </form>
                </section>

                <div class="stats-container">
                    <div class="stat-card total">
                        <div class="stat-value" id="total-bl">0</div>
                        <div class="stat-label">Total des BL</div>
                    </div>
                    <div class="stat-card pending">
                        <div class="stat-value" id="pending-bl">0</div>
                        <div class="stat-label">BL en cours</div>
                    </div>
                    <div class="stat-card completed">
                        <div class="stat-value" id="completed-bl">0</div>
                        <div class="stat-label">BL terminés</div>
                    </div>
                </div>

                <section class="table-card">
                    <div class="card-header">
                        <h2><i class="fas fa-list"></i> Liste des BL</h2>
                    </div>
                    
                    <div class="table-container">
                        <div class="table-controls">
                            <div class="group-option">
                                <input type="checkbox" id="groupSimilar" checked>
                                <label for="groupSimilar">Regrouper les similaires</label>
                            </div>
                            <button id="exportExcel" class="btn export-btn"><i class="fas fa-file-excel"></i> Exporter en Excel</button>
                            <button id="exportPDF" class="btn export-btn"><i class="fas fa-file-pdf"></i> Exporter en PDF</button>
                        </div>

                        <div class="table-responsive">
                            <table id="blTable">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-university"></i> Banque</th>
                                        <th><i class="fas fa-user"></i> Client</th>
                                        <th><i class="fas fa-truck"></i> Transitaire</th>
                                        <th><i class="fas fa-box"></i> Produit</th>
                                        <th><i class="fas fa-hashtag"></i> N°DAS</th>
                                        <th><i class="fas fa-weight"></i> Poids</th>
                                        <th><i class="fas fa-calendar"></i> Date Empotage</th>
                                        <th><i class="fas fa-bell"></i> R1</th>
                                        <th><i class="fas fa-bell"></i> R2</th>
                                        <th><i class="fas fa-bell"></i> R3</th>
                                        <th><i class="fas fa-bell"></i> R4</th>
                                        <th><i class="fas fa-exclamation-triangle"></i> Alerte Banque</th>
                                        <th><i class="fas fa-tasks"></i> Statut</th>
                                        <th><i class="fas fa-cogs"></i> Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Rempli dynamiquement -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>

            <div id="new-bl-view" class="view-content">
                <section class="form-card">
                    <div class="card-header">
                        <h2 id="form-title"><i class="fas fa-plus-circle"></i> Créer un nouveau BL</h2>
                    </div>
                    <form id="blForm" class="bl-form">
                        <input type="hidden" id="bl_id" name="id">
                        <div class="form-grid">
                             <div class="form-group">
        <label for="banque">Banque</label>
        <select id="banque" name="banque" required>
            <option value="">Sélectionnez une banque</option>
            <?php foreach ($banques as $b): ?>
                <option value="<?= $b['nom'] ?>"><?= $b['nom'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
                            <div class="form-group">
                                <label for="client">Client</label>
                                <select id="client" name="client" required>
                                    <option value="">Sélectionnez un client</option>
                                    <?php foreach ($clients as $c): ?>
                                        <option value="<?= $c['nom'] ?>"><?= $c['nom'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="transitaire">Transitaire</label>
                                <select id="transitaire" name="transitaire" required>
                                    <option value="">Sélectionnez un transitaire</option>
                                    <?php foreach ($transitaires as $t): ?>
                                        <option value="<?= $t['nom'] ?>"><?= $t['nom'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="produit">Produit</label>
                                <input type="text" id="produit" name="produit" required placeholder="Nom du produit">
                            </div>
                            <div class="form-group">
                                <label for="numero_das">N° DAS</label>
                                <input type="text" id="numero_das" name="numero_das" required placeholder="Numéro DAS">
                            </div>
                            <div class="form-group">
                                <label for="poids">Poids (kg)</label>
                                <input type="number" id="poids" name="poids" step="0.01" min="0" required placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label for="date_accord_banque">Date accord banque</label>
                                <input type="date" id="date_accord_banque" name="date_accord_banque" max="">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn primary-btn"><i class="fas fa-save"></i> Enregistrer</button>
                            <button type="reset" class="btn secondary-btn"><i class="fas fa-times"></i> Annuler</button>
                        </div>
                    </form>
                </section>
            </div>

            <div id="user-management-view" class="view-content">
                <section class="form-card">
                    <div class="card-header">
                        <h2><i class="fas fa-user-plus"></i> Créer un nouvel utilisateur</h2>
                    </div>
                    <form id="userForm" class="bl-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="new_username">Nom d'utilisateur</label>
                                <input type="text" id="new_username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">Mot de passe</label>
                                <input type="password" id="new_password" name="password" required>
                            </div>
                            <div class="form-group">
                                <label for="user_role">Rôle</label>
                                <select id="user_role" name="role">
                                    <option value="standard">Utilisateur Standard</option>
                                    <option value="admin">Administrateur</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn primary-btn">Créer l'utilisateur</button>
                        </div>
                    </form>
                </section>

                <section class="table-card">
                    <div class="card-header">
                        <h2><i class="fas fa-users"></i> Liste des utilisateurs</h2>
                    </div>
                    <div class="table-container">
                        <table id="usersTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom d'utilisateur</th>
                                    <th>Rôle</th>
                                    <th>Créé le</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Rempli dynamiquement -->
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const API_URL = 'bl_api.php';
            const LOGIN_API_URL = 'login_api.php';
            const USER_API_URL = 'user_management_api.php';
            const { jsPDF } = window.jspdf;

            // Éléments DOM
            const loginContainer = document.getElementById('login-container');
            const appContainer = document.getElementById('app-container');
            const loginForm = document.getElementById('loginForm');
            const loginError = document.getElementById('loginError');
            const blForm = document.getElementById('blForm');
            const userForm = document.getElementById('userForm');
            const formTitle = document.getElementById('form-title');
            const blIdInput = document.getElementById('bl_id');
            const searchForm = document.getElementById('searchForm');
            const tableBody = document.querySelector('#blTable tbody');
            const usersTableBody = document.querySelector('#usersTable tbody');
            const groupSimilarCheckbox = document.getElementById('groupSimilar');
            const exportExcelBtn = document.getElementById('exportExcel');
            const exportPDFBtn = document.getElementById('exportPDF');
            const toggleProfileBtn = document.getElementById('toggleProfile');
            const toggleNotificationsBtn = document.getElementById('toggleNotifications');
            const profilePanel = document.getElementById('profile-panel');
            const userIdSpan = document.getElementById('user-id');
            const logoutBtn = document.getElementById('logout-btn');
            const notificationHistory = document.getElementById('notification-history');
            const navItems = document.querySelectorAll('.nav-item');
            const datetimeDisplay = document.getElementById('datetime');
            const dateAccordBanqueInput = document.getElementById('date_accord_banque');
            const totalBlElement = document.getElementById('total-bl');
            const pendingBlElement = document.getElementById('pending-bl');
            const completedBlElement = document.getElementById('completed-bl');
            const adminNavItem = document.getElementById('adminNavItem');
            const views = {
                dashboard: document.getElementById('dashboard-view'),
                'new-bl': document.getElementById('new-bl-view'),
                'user-management': document.getElementById('user-management-view')
            };

            // Données en mémoire
            let bls = [];
            let users = [];
            let notificationHistoryItems = [];
            let editMode = false;
            let currentUserId = sessionStorage.getItem('userId') || 'Utilisateur';
            let currentUserRole = sessionStorage.getItem('userRole') || 'standard';

            // Définir la date maximale pour les champs de date (aujourd'hui)
            function setMaxDateForInputs() {
                const today = new Date().toISOString().split('T')[0];
                dateAccordBanqueInput.setAttribute('max', today);
            }

            // Mettre à jour les statistiques
            function updateStats() {
                const total = bls.length;
                const pending = bls.filter(bl => bl.statut === 'pending').length;
                const completed = bls.filter(bl => bl.statut === 'completed').length;
                
                totalBlElement.textContent = total;
                pendingBlElement.textContent = pending;
                completedBlElement.textContent = completed;
            }

            // Affichage de la date et de l'heure en temps réel
            function updateDateTime() {
                const now = new Date();
                const date = now.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
                const time = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                datetimeDisplay.textContent = `${date} ${time}`;
            }
            updateDateTime();
            setInterval(updateDateTime, 1000);

            // Vérifier l'état de connexion
            if (sessionStorage.getItem('isLoggedIn') === 'true') {
                loginContainer.style.display = 'none';
                appContainer.style.display = 'flex';
                userIdSpan.textContent = currentUserId;
                if (currentUserRole === 'admin') {
                    adminNavItem.style.display = 'inline-flex';
                }
                setMaxDateForInputs();
                loadBLs();
                updateUIForRole(currentUserRole);
            }

            // Gestion de la connexion
            loginForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                loginError.style.display = 'none';
                
                const loginData = {
                    username: document.getElementById('login_id').value,
                    password: document.getElementById('login_password').value
                };

                try {
                    const res = await fetch(LOGIN_API_URL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(loginData)
                    });
                    const data = await res.json();
                    if (data.success) {
                        sessionStorage.setItem('isLoggedIn', 'true');
                        sessionStorage.setItem('userId', data.username);
                        sessionStorage.setItem('userRole', data.role);
                        
                        currentUserId = data.username;
                        currentUserRole = data.role;
                        
                        userIdSpan.textContent = data.username;
                        loginContainer.style.display = 'none';
                        appContainer.style.display = 'flex';
                        setMaxDateForInputs();
                        showNotification('Connexion réussie', 'success', 5000);
                        loadBLs();
                        
                        // Afficher menu admin si nécessaire
                        if (data.role === 'admin') {
                            adminNavItem.style.display = 'inline-flex';
                            loadUsers();
                        }
                        
                        updateUIForRole(data.role);
                    } else {
                        loginError.textContent = data.error || 'Identifiant ou mot de passe incorrect';
                        loginError.style.display = 'block';
                    }
                } catch (e) {
                    loginError.textContent = 'Erreur de connexion au serveur';
                    loginError.style.display = 'block';
                    console.error(e);
                }
            });

            // Cacher les boutons suppression si pas admin
            function updateUIForRole(role) {
                if (role !== 'admin') {
                    document.querySelectorAll('.delete-btn').forEach(btn => {
                        btn.style.display = 'none';
                    });
                } else {
                    document.querySelectorAll('.delete-btn').forEach(btn => {
                        btn.style.display = 'inline-block';
                    });
                }
            }

            // Gestion du profil et déconnexion
            toggleProfileBtn.addEventListener('click', () => {
                profilePanel.classList.toggle('active');
                notificationHistory.classList.remove('active');
            });
            logoutBtn.addEventListener('click', () => {
                sessionStorage.removeItem('isLoggedIn');
                sessionStorage.removeItem('userId');
                sessionStorage.removeItem('userRole');
                loginContainer.style.display = 'flex';
                appContainer.style.display = 'none';
                profilePanel.classList.remove('active');
                adminNavItem.style.display = 'none';
                showNotification('Déconnexion réussie', 'info');
            });
            toggleNotificationsBtn.addEventListener('click', () => {
                notificationHistory.classList.toggle('active');
                profilePanel.classList.remove('active');
            });

            // Initialisation
            initNavigation();

            // Événements
            blForm.addEventListener('submit', handleFormSubmit);
            userForm.addEventListener('submit', handleUserFormSubmit);
            searchForm.addEventListener('submit', (e) => { e.preventDefault(); updateTable(); });
            searchForm.addEventListener('reset', () => setTimeout(updateTable, 50));
            groupSimilarCheckbox.addEventListener('change', updateTable);
            exportExcelBtn.addEventListener('click', exportToExcel);
            exportPDFBtn.addEventListener('click', exportToPDF);

            function initNavigation() {
                navItems.forEach(item => {
                    item.addEventListener('click', function() {
                        const view = this.getAttribute('data-view');
                        showView(view);
                        navItems.forEach(nav => nav.classList.remove('active'));
                        this.classList.add('active');
                        if (view !== 'new-bl') resetForm();
                        if (view === 'user-management' && currentUserRole === 'admin') {
                            loadUsers();
                        }
                    });
                });
            }
            function showView(viewName) {
                Object.values(views).forEach(v => v && (v.style.display = 'none'));
                if (views[viewName]) views[viewName].style.display = 'block';
            }

            // Utils date
            function addDays(date, n) { const d = new Date(date); d.setDate(d.getDate() + n); return d; }
            function formatISO(date) {
                const d = new Date(date);
                const m = String(d.getMonth()+1).padStart(2,'0');
                const day = String(d.getDate()).padStart(2,'0');
                return `${d.getFullYear()}-${m}-${day}`;
            }
            function formatDisplay(date) {
                if (!date) return '-';
                const d = new Date(date);
                if (Number.isNaN(d.getTime())) return '-';
                const m = String(d.getMonth()+1).padStart(2,'0');
                const day = String(d.getDate()).padStart(2,'0');
                return `${day}/${m}/${d.getFullYear()}`;
            }

            function calculateRelances(dateEmpotage) {
                if (!dateEmpotage) return {};
                const r1 = addDays(dateEmpotage, 22);
                const r2 = addDays(r1, 7);
                const r3 = addDays(r2, 7);
                const r4 = addDays(r3, 7);
                const alerte = addDays(r4, 60);
                return {
                    relance_r1: formatISO(r1),
                    relance_r2: formatISO(r2),
                    relance_r3: formatISO(r3),
                    relance_r4: formatISO(r4),
                    date_alerte_banque: formatISO(alerte)
                };
            }

            async function loadBLs() {
                try {
                    const res = await fetch(API_URL);
                    bls = await res.json();
                    updateStats();
                    updateTable();
                } catch (e) {
                    showNotification("Erreur de chargement des BL", "error");
                    console.error(e);
                }
            }

            async function loadUsers() {
                if (currentUserRole !== 'admin') return;
                
                try {
                    const res = await fetch(USER_API_URL);
                    users = await res.json();
                    renderUsersTable(users);
                } catch (error) {
                    console.error('Erreur chargement utilisateurs:', error);
                    showNotification('Erreur chargement utilisateurs', 'error');
                }
            }

            async function handleFormSubmit(e) {
                e.preventDefault();
                const formData = new FormData(blForm);
                const blData = Object.fromEntries(formData.entries());

                // Cast & validation
                blData.poids = parseFloat(blData.poids || '0');
                blData.statut = blData.statut || 'pending';
                // Ne pas inclure date_empotage ou relances
                delete blData.date_empotage;
                delete blData.relance_r1;
                delete blData.relance_r2;
                delete blData.relance_r3;
                delete blData.relance_r4;
                delete blData.date_alerte_banque;

                try {
                    const method = editMode ? 'PUT' : 'POST';
                    const res = await fetch(API_URL, {
                        method,
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(blData)
                    });
                    const data = await res.json();
                    showNotification(data.message || (editMode ? 'BL mis à jour' : 'BL créé'), 'success', 5000);
                    resetForm();
                    document.querySelector('.nav-item[data-view="dashboard"]').click();
                    await loadBLs();
                } catch (e) {
                    showNotification("Erreur lors de l'enregistrement", "error");
                    console.error(e);
                }
            }

            async function handleUserFormSubmit(e) {
                e.preventDefault();
                if (currentUserRole !== 'admin') return;
                
                const formData = new FormData(userForm);
                const userData = Object.fromEntries(formData.entries());
                
                try {
                    const res = await fetch(USER_API_URL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(userData)
                    });
                    const data = await res.json();
                    if (data.message) {
                        showNotification(data.message, 'success');
                        userForm.reset();
                        loadUsers();
                    } else {
                        showNotification(data.error || 'Erreur création utilisateur', 'error');
                    }
                } catch (error) {
                    showNotification('Erreur création utilisateur', 'error');
                    console.error(error);
                }
            }

            async function updateEmpotageDate(id, date) {
                if (!date) return;
                const blData = { id, date_empotage: date, ...calculateRelances(date) };
                try {
                    const res = await fetch(API_URL, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(blData)
                    });
                    const data = await res.json();
                    showNotification(data.message || 'Date empotage mise à jour', 'success', 5000);
                    await loadBLs();
                } catch (e) {
                    showNotification("Erreur lors de la mise à jour de la date", "error");
                    console.error(e);
                }
            }

            async function deleteBL(id) {
                if (!confirm('Voulez-vous vraiment supprimer ce BL ?')) return;
                try {
                    const res = await fetch(API_URL, {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    });
                    const data = await res.json();
                    showNotification(data.message || 'BL supprimé', 'success', 5000);
                    await loadBLs();
                } catch (e) {
                    showNotification("Erreur lors de la suppression", "error");
                    console.error(e);
                }
            }

            function resetForm() {
                editMode = false;
                blForm.reset();
                blIdInput.value = '';
                formTitle.innerHTML = '<i class="fas fa-plus-circle"></i> Créer un nouveau BL';
                blForm.querySelector('.primary-btn').innerHTML = '<i class="fas fa-save"></i> Enregistrer';
                setMaxDateForInputs();
            }

            function editBL(bl) {
                editMode = true;
                blIdInput.value = bl.id;
                document.getElementById('banque').value = bl.banque;
                document.getElementById('client').value = bl.client;
                document.getElementById('transitaire').value = bl.transitaire;
                document.getElementById('produit').value = bl.produit;
                document.getElementById('numero_das').value = bl.numero_das;
                document.getElementById('poids').value = bl.poids;
                document.getElementById('date_accord_banque').value = bl.date_accord_banque || '';
                formTitle.innerHTML = '<i class="fas fa-edit"></i> Modifier le BL';
                blForm.querySelector('.primary-btn').innerHTML = '<i class="fas fa-save"></i> Mettre à jour';
                document.querySelector('.nav-item[data-view="new-bl"]').click();
                setMaxDateForInputs();
            }

            function filterBLs(items, filters) {
                return items.filter(bl => {
                    return (!filters.banque || bl.banque === filters.banque) &&
                           (!filters.transitaire || bl.transitaire === filters.transitaire) &&
                           (!filters.numero_das || bl.numero_das.toLowerCase().includes(filters.numero_das.toLowerCase()));
                });
            }

            function groupSimilarBLs(items) {
                const res = [];
                let i = 0;
                const arr = [...items].sort((a,b) => (a.banque+a.transitaire).localeCompare(b.banque+b.transitaire));
                while (i < arr.length) {
                    const cur = arr[i];
                    let j = i;
                    let total = 0;
                    let count = 0;
                    while (j < arr.length && arr[j].banque === cur.banque && arr[j].transitaire === cur.transitaire) {
                        total += parseFloat(arr[j].poids) || 0;
                        count++; j++;
                    }
                    res.push(...arr.slice(i, j));
                    if (count > 1) {
                        res.push({ isSummary: true, banque: cur.banque, transitaire: cur.transitaire, count, totalPoids: total.toFixed(2) });
                    }
                    i = j;
                }
                return res;
            }

            function updateTable() {
                const filters = Object.fromEntries(new FormData(searchForm).entries());
                const shouldGroup = groupSimilarCheckbox.checked;
                let rows = filterBLs(bls, filters);
                if (shouldGroup) rows = groupSimilarBLs(rows);
                renderTable(rows);
            }

            function renderTable(items) {
                tableBody.innerHTML = '';
                if (!items.length) {
                    const row = document.createElement('tr');
                    row.innerHTML = `<td colspan="14" style="text-align:center; padding:3rem; color: var(--primary-blue); font-style: italic;">
                        <i class="fas fa-inbox" style="font-size:2rem; margin-bottom:1rem; display:block;"></i>Aucun BL trouvé
                    </td>`;
                    tableBody.appendChild(row);
                    return;
                }

                items.forEach(item => {
                    const tr = document.createElement('tr');

                    if (item.isSummary) {
                        tr.classList.add('group-summary');
                        tr.innerHTML = `
                            <td><i class="fas fa-layer-group"></i></td>
                            <td></td><td></td>
                            <td><strong>Total (${item.count} BL)</strong></td>
                            <td><strong>${item.count}</strong></td>
                            <td><strong>${item.totalPoids} kg</strong></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>`;
                    } else {
                        if (item.statut === 'completed') tr.classList.add('completed');
                        const today = new Date().toISOString().split('T')[0];
                        tr.innerHTML = `
                            <td><i class="fas fa-university"></i> ${item.banque}</td>
                            <td><i class="fas fa-user"></i> ${item.client}</td>
                            <td><i class="fas fa-truck"></i> ${item.transitaire}</td>
                            <td><i class="fas fa-box"></i> ${item.produit}</td>
                            <td><i class="fas fa-hashtag"></i> ${item.numero_das}</td>
                            <td><i class="fas fa-weight"></i> ${item.poids} kg</td>
                            <td><input type="date" class="date-input" data-id="${item.id}" value="${item.date_empotage || ''}" max="${today}"></td>
                            <td class="relance-cell">${formatDisplay(item.relance_r1)}</td>
                            <td class="relance-cell">${formatDisplay(item.relance_r2)}</td>
                            <td class="relance-cell">${formatDisplay(item.relance_r3)}</td>
                            <td class="relance-cell">${formatDisplay(item.relance_r4)}</td>
                            <td class="alert-cell">${formatDisplay(item.date_alerte_banque)}</td>
                            <td>
                                <button class="status-btn ${item.statut === 'completed' ? 'status-completed' : 'status-pending'}" data-id="${item.id}" data-status="${item.statut}">
                                    <i class="fas ${item.statut === 'completed' ? 'fa-check-circle' : 'fa-clock'}"></i>
                                    ${item.statut === 'completed' ? 'Terminé' : 'En cours'}
                                </button>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="edit-btn" data-id="${item.id}"><i class="fas fa-edit"></i> Modifier</button>
                                    <button class="delete-btn" data-id="${item.id}" style="${currentUserRole !== 'admin' ? 'display: none;' : ''}"><i class="fas fa-trash"></i> Supprimer</button>
                                </div>
                            </td>`;
                    }
                    tableBody.appendChild(tr);
                });

                // Binder actions
                document.querySelectorAll('.status-btn').forEach(btn => {
                    btn.addEventListener('click', async function() {
                        const id = this.getAttribute('data-id');
                        const current = this.getAttribute('data-status');
                        const next = current === 'completed' ? 'pending' : 'completed';
                        try {
                            const res = await fetch(API_URL, {
                                method: 'PUT',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ id, statut: next })
                            });
                            const data = await res.json();
                            showNotification(data.message || 'Statut mis à jour', 'success', 5000);
                            await loadBLs();
                        } catch (e) {
                            showNotification("Erreur de mise à jour du statut", "error");
                            console.error(e);
                        }
                    });
                });

                document.querySelectorAll('.edit-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        const bl = bls.find(b => b.id == id);
                        if (bl) editBL(bl);
                    });
                });

                document.querySelectorAll('.delete-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        deleteBL(id);
                    });
                });

                document.querySelectorAll('.date-input').forEach(input => {
                    input.addEventListener('change', function() {
                        const id = this.getAttribute('data-id');
                        const date = this.value;
                        updateEmpotageDate(id, date);
                    });
                });
            }

            function renderUsersTable(users) {
                usersTableBody.innerHTML = '';
                if (!users.length) {
                    const row = document.createElement('tr');
                    row.innerHTML = `<td colspan="6" style="text-align:center; padding:2rem; color: var(--primary-blue); font-style: italic;">
                        <i class="fas fa-users" style="font-size:1.5rem; margin-bottom:0.5rem; display:block;"></i>Aucun utilisateur trouvé
                    </td>`;
                    usersTableBody.appendChild(row);
                    return;
                }

                users.forEach(user => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${user.id}</td>
                        <td>${user.username}</td>
                        <td>${user.role === 'admin' ? 'Administrateur' : 'Utilisateur Standard'}</td>
                        <td>${new Date(user.created_at).toLocaleDateString('fr-FR')}</td>
                        <td>${user.is_active ? 'Actif' : 'Inactif'}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="edit-btn" data-user-id="${user.id}"><i class="fas fa-edit"></i></button>
                                <button class="delete-btn" data-user-id="${user.id}"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>`;
                    usersTableBody.appendChild(tr);
                });
            }

            function exportToExcel() {
                const filters = Object.fromEntries(new FormData(searchForm).entries());
                const shouldGroup = groupSimilarCheckbox.checked;
                let data = filterBLs(bls, filters);
                if (shouldGroup) data = groupSimilarBLs(data);

                // Create workbook and worksheet
                const wb = XLSX.utils.book_new();
                
                // Prepare data for export
                const exportData = data.map(item => {
                    if (item.isSummary) {
                        return {
                            'Banque': '',
                            'Client': '',
                            'Transitaire': '',
                            'Produit': `Total (${item.count} BL)`,
                            'N° DAS': item.count,
                            'Poids (kg)': item.totalPoids,
                            'Date Empotage': '',
                            'Relance R1': '',
                            'Relance R2': '',
                            'Relance R3': '',
                            'Relance R4': '',
                            'Alerte Banque': '',
                            'Statut': ''
                        };
                    }
                    return {
                        'Banque': item.banque,
                        'Client': item.client,
                        'Transitaire': item.transitaire,
                        'Produit': item.produit,
                        'N° DAS': item.numero_das,
                        'Poids (kg)': item.poids,
                        'Date Empotage': formatDisplay(item.date_empotage),
                        'Relance R1': formatDisplay(item.relance_r1),
                        'Relance R2': formatDisplay(item.relance_r2),
                        'Relance R3': formatDisplay(item.relance_r3),
                        'Relance R4': formatDisplay(item.relance_r4),
                        'Alerte Banque': formatDisplay(item.date_alerte_banque),
                        'Statut': item.statut === 'completed' ? 'Terminé' : 'En cours'
                    };
                });

                // Create worksheet
                const ws = XLSX.utils.json_to_sheet(exportData);
                
                // Set column widths
                const colWidths = [
                    { wch: 15 }, { wch: 15 }, { wch: 15 }, 
                    { wch: 20 }, { wch: 15 }, { wch: 12 },
                    { wch: 15 }, { wch: 12 }, { wch: 12 },
                    { wch: 12 }, { wch: 12 }, { wch: 15 },
                    { wch: 12 }
                ];
                ws['!cols'] = colWidths;
                
                // Add worksheet to workbook
                XLSX.utils.book_append_sheet(wb, ws, 'Liste des BLs');
                
                // Generate Excel file
                XLSX.writeFile(wb, 'Liste_des_BLs_' + new Date().toISOString().slice(0, 10) + '.xlsx');
                showNotification('Exportation Excel réussie', 'success', 5000);
            }

            function exportToPDF() {
                try {
                    const { jsPDF } = window.jspdf;
                    const doc = new jsPDF();
                    const filters = Object.fromEntries(new FormData(searchForm).entries());
                    const shouldGroup = groupSimilarCheckbox.checked;
                    let data = filterBLs(bls, filters);
                    if (shouldGroup) data = groupSimilarBLs(data);

                    const logo = new Image();
                    logo.src = 'ace.png';
                    logo.crossOrigin = 'anonymous';

                    const generatePDF = () => {
                        // En-tête
                        doc.setFontSize(16);
                        doc.setFont('helvetica', 'bold');
                        doc.setTextColor(30, 64, 175);
                        doc.text('LISTE DES BL', 105, 20, { align: 'center' });
                        
                        doc.setFontSize(10);
                        doc.setFont('helvetica', 'normal');
                        doc.setTextColor(100, 116, 139);
                        doc.text(`Exporté le ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}`, 105, 28, { align: 'center' });

                        // Tableau
                        const columns = [
                            { header: 'Banque', dataKey: 'banque' },
                            { header: 'Client', dataKey: 'client' },
                            { header: 'Transitaire', dataKey: 'transitaire' },
                            { header: 'Produit', dataKey: 'produit' },
                            { header: 'N°DAS', dataKey: 'numero_das' },
                            { header: 'Poids', dataKey: 'poids' },
                            { header: 'Date Empotage', dataKey: 'date_empotage' },
                            { header: 'R1', dataKey: 'relance_r1' },
                            { header: 'R2', dataKey: 'relance_r2' },
                            { header: 'R3', dataKey: 'relance_r3' },
                            { header: 'R4', dataKey: 'relance_r4' },
                            { header: 'Alerte Banque', dataKey: 'date_alerte_banque' },
                            { header: 'Statut', dataKey: 'statut' }
                        ];
                        
                        const rows = data.map(item => {
                            if (item.isSummary) {
                                return {
                                    banque: '',
                                    client: '',
                                    transitaire: '',
                                    produit: `Total (${item.count} BL)`,
                                    numero_das: item.count,
                                    poids: `${item.totalPoids} kg`,
                                    date_empotage: '',
                                    relance_r1: '',
                                    relance_r2: '',
                                    relance_r3: '',
                                    relance_r4: '',
                                    date_alerte_banque: '',
                                    statut: ''
                                };
                            }
                            return {
                                banque: item.banque,
                                client: item.client,
                                transitaire: item.transitaire,
                                produit: item.produit,
                                numero_das: item.numero_das,
                                poids: `${item.poids} kg`,
                                date_empotage: formatDisplay(item.date_empotage),
                                relance_r1: formatDisplay(item.relance_r1),
                                relance_r2: formatDisplay(item.relance_r2),
                                relance_r3: formatDisplay(item.relance_r3),
                                relance_r4: formatDisplay(item.relance_r4),
                                date_alerte_banque: formatDisplay(item.date_alerte_banque),
                                statut: item.statut === 'completed' ? 'Terminé' : 'En cours'
                            };
                        });

                        doc.autoTable({
                            columns: columns,
                            body: rows,
                            startY: 40,
                            theme: 'grid',
                            styles: { 
                                fontSize: 8, 
                                cellPadding: 3, 
                                textColor: [51, 65, 85], 
                                font: 'helvetica',
                                lineColor: [226, 232, 240]
                            },
                            headStyles: { 
                                fillColor: [30, 64, 175], 
                                textColor: [255, 255, 255], 
                                fontStyle: 'bold',
                                lineWidth: 0.1
                            },
                            alternateRowStyles: {
                                fillColor: [248, 250, 252]
                            },
                            margin: { top: 40 },
                            didDrawPage: (data) => {
                                // Ajouter le logo après le rendu de la première page
                                if (data.pageNumber === 1) {
                                    try {
                                        doc.addImage(logo, 'PNG', 14, 10, 40, 20);
                                    } catch (e) {
                                        console.warn('Échec du chargement du logo:', e);
                                    }
                                }
                                
                                // Footer avec numéro de page
                                const pageCount = doc.internal.getNumberOfPages();
                                doc.setFontSize(10);
                                doc.setTextColor(100, 116, 139);
                                for (let i = 1; i <= pageCount; i++) {
                                    doc.setPage(i);
                                    doc.text(`Page ${i} sur ${pageCount}`, doc.internal.pageSize.width / 2, doc.internal.pageSize.height - 10, { align: 'center' });
                                }
                            }
                        });

                        doc.save('Liste_des_BL_' + new Date().toISOString().slice(0, 10) + '.pdf');
                        showNotification('Exportation PDF réussie', 'success', 5000);
                    };

                    logo.onload = generatePDF;
                    logo.onerror = () => {
                        console.warn('Échec du chargement du logo, génération du PDF sans logo.');
                        generatePDF();
                    };
                } catch (e) {
                    console.error('Erreur lors de l\'exportation PDF:', e);
                    showNotification('Erreur lors de l\'exportation PDF', 'error');
                }
            }

            function showNotification(message, type = 'info', duration = 3000) {
                const notification = document.createElement('div');
                notification.className = 'notification';
                notification.style.background = type === 'success'
                    ? 'linear-gradient(135deg, var(--light-green), #059669)'
                    : type === 'error'
                        ? 'linear-gradient(135deg, var(--red), #dc2626)'
                        : type === 'warning'
                            ? 'linear-gradient(135deg, #f59e0b, #d97706)'
                            : 'linear-gradient(135deg, var(--light-blue), var(--primary-blue))';
                notification.innerHTML = `
                    <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : type === 'warning' ? 'exclamation-triangle' : 'info'}-circle"></i>
                    ${message}
                    <span class="close-btn"><i class="fas fa-times"></i></span>`;
                document.body.appendChild(notification);
                setTimeout(() => { notification.style.transform = 'translateX(0)'; }, 100);
                if (duration > 0) {
                    setTimeout(() => {
                        notification.style.transform = 'translateX(400px)';
                        setTimeout(() => notification.remove(), 300);
                    }, duration);
                }
                notification.querySelector('.close-btn').addEventListener('click', () => {
                    notification.style.transform = 'translateX(400px)';
                    setTimeout(() => notification.remove(), 300);
                });

                // Ajouter à l'historique
                const historyItem = document.createElement('div');
                historyItem.className = 'notification-history-item';
                historyItem.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : type === 'warning' ? 'exclamation-triangle' : 'info'}-circle"></i> ${message}`;
                notificationHistory.prepend(historyItem);
                notificationHistoryItems.push(historyItem);
                if (notificationHistoryItems.length > 10) {
                    const oldest = notificationHistoryItems.shift();
                    oldest.remove();
                }
            }

            // Raccourcis clavier
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'n') { e.preventDefault(); document.querySelector('.nav-item[data-view="new-bl"]').click(); }
                if (e.key === 'Escape') {
                    profilePanel.classList.remove('active');
                    notificationHistory.classList.remove('active');
                }
            });
            setTimeout(() => { document.body.style.opacity = '1'; }, 100);
        });
    </script>
</body>
</html>