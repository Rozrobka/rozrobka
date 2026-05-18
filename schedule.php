<?php
require_once 'includes/auth.php';
requireLogin();

$user = getCurrentUser();
$settings = getUserSettings($user);
$body_classes = 'theme-' . $settings['accent'] . ($settings['reduced_motion'] ? ' reduced-motion' : '');

function getRoleLabel($role) {
    $labels = ['student' => 'Учень', 'teacher' => 'Учитель', 'admin' => 'Адміністратор'];
    return $labels[$role] ?? $role;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Розклад - Розробка</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?php echo htmlspecialchars($body_classes); ?>">
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <a href="dashboard.php" class="nav-logo"><h2>📚 Розробка</h2></a>
            </div>
            <div class="nav-user">
                <span class="user-info">
                    <strong><?php echo htmlspecialchars($user['fullname']); ?></strong>
                    <small><?php echo getRoleLabel($user['role']); ?></small>
                </span>
                <a href="notifications.php" class="btn btn-small btn-bell">
                    🔔
                    <span class="bell-dot"></span>
                </a>
                <a href="stats.php" class="btn btn-small">📊 Статистика</a>
                <a href="schedule.php" class="btn btn-small">📅 Розклад</a>
                <a href="settings.php" class="btn btn-small">⚙️</a>
                <a href="logout.php" class="btn btn-small">Вийти</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="empty-state">
            <div class="empty-icon">🚧</div>
            <h3>Розклад уроків</h3>
            <p>Цей розділ знаходиться в розробці.</p>
            <p>Незабаром тут з'явиться розклад уроків для кожного класу НУШ.</p>
            <a href="dashboard.php" class="btn btn-secondary">← Повернутися до завдань</a>
        </div>
    </div>
</body>
</html>
