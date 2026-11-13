<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comrades Preparatory School Portal</title>
    <style>
        :root {
            --primary: #1e3a8a;
            --primary-hover: #1d4ed8;
            --bg: #f8fafc;
            --surface: #ffffff;
            --text: #334155;
            --border: #e2e8f0;
            --danger: #dc2626;
            --success: #166534;
            --warning: #d97706;
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--bg); color: var(--text); margin: 0; padding: 20px; }
        .container { max-width: 1300px; margin: 0 auto; }
        
        /* HEADER STYLING WITH LOGO AND MOTTO */
        header { 
            background-color: var(--primary); 
            color: white; 
            padding: 20px; 
            border-radius: 8px 8px 0 0; 
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            text-align: left;
        }
        header img.school-logo {
            width: 85px;
            height: auto;
            border-radius: 6px;
            background: #ffffff;
            padding: 3px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .header-title-container {
            display: flex;
            flex-direction: column;
        }
        .school-motto {
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 1px;
            color: #fef08a; /* Soft Gold Highlight */
            text-transform: uppercase;
            margin-top: 3px;
        }

        /* NAVIGATION BAR WITH ALL SYSTEM MODULES */
        nav { background: #1e293b; padding: 10px 15px; border-radius: 0 0 8px 8px; margin-bottom: 20px; display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; }
        nav a { color: #f8fafc; text-decoration: none; font-weight: 600; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; transition: background-color 0.2s; }
        nav a:hover, nav a.active { background-color: var(--primary-hover); }
        nav a.highlight { background-color: #166534; }
        nav a.highlight:hover { background-color: #15803d; }

        .alert { padding: 12px; background-color: #dbeafe; color: #1e40af; border-left: 4px solid var(--primary); margin-bottom: 20px; border-radius: 4px; }
        .grid { display: grid; grid-template-columns: 380px 1fr; gap: 20px; }
        @media (max-width: 950px) { 
            header { flex-direction: column; text-align: center; }
            .grid { grid-template-columns: 1fr; } 
        }
        .card { background: var(--surface); border-radius: 8px; padding: 18px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .card h2 { margin-top: 0; font-size: 1.15rem; color: var(--primary); border-bottom: 2px solid var(--border); padding-bottom: 8px; }
        .form-group { margin-bottom: 10px; }
        .form-group label { display: block; font-weight: 600; font-size: 0.8rem; margin-bottom: 3px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 7px 10px; border: 1px solid var(--border); border-radius: 4px; box-sizing: border-box; font-size: 0.9rem; }
        button.btn { width: 100%; background-color: var(--primary); color: white; border: none; padding: 10px; font-weight: 600; border-radius: 4px; cursor: pointer; margin-top: 10px; }
        button.btn:hover { background-color: var(--primary-hover); }
        table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        th, td { padding: 8px 10px; text-align: left; border-bottom: 1px solid var(--border); }
        th { background-color: #f1f5f9; font-weight: 600; }
        .badge { padding: 3px 6px; border-radius: 10px; font-size: 0.75rem; font-weight: 600; display: inline-block; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #b45309; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .actions a, .actions button { text-decoration: none; margin-right: 4px; font-weight: 600; }
        .actions a.edit { color: var(--primary); }
        .actions a.delete { color: var(--danger); }
    </style>
</head>
<body>
<div class="container">
    <header>
        <img src="badge.jpg" alt="Comrades Preparatory School Badge" class="school-logo">
        <div class="header-title-container">
            <h1 style="margin:0; font-size: 1.8rem;">Comrades Preparatory School</h1>
            
            <div class="school-motto">FOR A BRIGHT FUTURE</div>
        </div>
    </header>
    <!-- <nav>
        <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
        <a href="index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>">Pupils & ID Cards</a>
        <a href="classes.php" class="<?= $current_page === 'classes.php' ? 'active' : '' ?>">Classes & Roster</a>
        <a href="teachers.php" class="<?= $current_page === 'teachers.php' ? 'active' : '' ?>">Teachers & Staff</a>
        <a href="class_teacher_dashboard.php" class="<?= $current_page === 'class_teacher_dashboard.php' ? 'active' : '' ?>">Class Teacher Desk</a>
        <a href="subjects_exams.php" class="<?= $current_page === 'subjects_exams.php' ? 'active' : '' ?>">Subjects & Exams</a>
        <a href="fees.php" class="<?= $current_page === 'fees.php' ? 'active' : '' ?>">Fee Management</a>
        <a href="expenditure.php" class="<?= $current_page === 'expenditure.php' ? 'active' : '' ?>">Termly Expenditure</a>
        <a href="payslip.php" class="<?= $current_page === 'payslip.php' ? 'active' : '' ?>">Teacher Payslips</a>
        <a href="promote_pupils.php" class="highlight <?= $current_page === 'promote_pupils.php' ? 'active' : '' ?>">Year-End Promotion</a> -->
       

        <nav>
    <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
    <a href="index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>">Pupils & ID Cards</a>
    
    <a href="classes.php" class="<?= $current_page === 'classes.php' ? 'active' : '' ?>">Classes & Roster</a>
    <a href="teachers.php" class="<?= $current_page === 'teachers.php' ? 'active' : '' ?>">Teachers & Staff</a>
    <a href="class_teacher_dashboard.php" class="<?= $current_page === 'class_teacher_dashboard.php' ? 'active' : '' ?>">Class Teacher Desk</a>
    <a href="subjects_exams.php" class="<?= $current_page === 'subjects_exams.php' ? 'active' : '' ?>">Subjects & Exams</a>
    <a href="fees.php" class="<?= $current_page === 'fees.php' ? 'active' : '' ?>">Fee Management</a>
    <a href="expenditure.php" class="<?= $current_page === 'expenditure.php' ? 'active' : '' ?>">Termly Expenditure</a>
    <a href="payslip.php" class="<?= $current_page === 'payslip.php' ? 'active' : '' ?>">Teacher Payslips</a>
    <a href="promote_pupils.php" class="highlight <?= $current_page === 'promote_pupils.php' ? 'active' : '' ?>">Year-End Promotion</a>
</nav>
    </nav>