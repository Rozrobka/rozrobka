<?php
require_once 'includes/auth.php';
requireLogin();

$user = getCurrentUser();
$settings = getUserSettings($user);
$body_classes = 'theme-' . $settings['accent'] . ($settings['reduced_motion'] ? ' reduced-motion' : '');

$notifications = [
    [
        'icon' => '🚧',
        'title' => 'Розклад уроків',
        'text' => 'Розділ «Розклад» знаходиться в розробці. Незабаром ви зможете переглядати розклад для свого класу.',
        'time' => date('Y-m-d H:i:s')
    ],
    [
        'icon' => '✅',
        'title' => 'Система працює',
        'text' => 'Електронний щоденник «Розробка» активовано. Усі функції доступні для використання.',
        'time' => date('Y-m-d H:i:s', strtotime('-1 hour'))
    ],
    [
        'icon' => '📚',
        'title' => 'НУШ 5–9 класи',
        'text' => 'Додано підтримку предметів за стандартом НУШ для 5–9 класів.',
        'time' => date('Y-m-d H:i:s', strtotime('-3 hours'))
    ]
];

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
    <title>Сповіщення - Розробка</title>
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
        <div class="dashboard-header">
            <h1>🔔 Сповіщення</h1>
        </div>

        <div class="notifications-list">
            <?php foreach ($notifications as $n): ?>
                <div class="notification-item">
                    <span class="notification-icon"><?php echo htmlspecialchars($n['icon']); ?></span>
                    <div class="notification-body">
                        <strong><?php echo htmlspecialchars($n['title']); ?></strong>
                        <p><?php echo htmlspecialchars($n['text']); ?></p>
                        <small class="notification-time"><?php echo htmlspecialchars($n['time']); ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
