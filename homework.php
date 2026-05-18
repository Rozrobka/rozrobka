<?php
require_once 'includes/auth.php';
requireLogin();

$user = getCurrentUser();
$settings = getUserSettings($user);
$body_classes = 'theme-' . $settings['accent'] . ($settings['reduced_motion'] ? ' reduced-motion' : '');

$id = $_GET['id'] ?? '';
$homework = null;

if ($id) {
    $all = getHomework();
    foreach ($all as $hw) {
        if ($hw['id'] === $id) {
            $homework = $hw;
            break;
        }
    }
}

if (!$homework) {
    header('Location: dashboard.php');
    exit;
}

$author = getUserByUsername($homework['created_by'] ?? '');
$author_name = $author ? $author['fullname'] : ($homework['created_by'] ?? 'Невідомо');
$subject = $homework['subject'] ?? 'Без предмета';
$class = $homework['class'] ?? '—';

function getRoleLabel($role) {
    $labels = ['student' => 'Учень', 'teacher' => 'Учитель', 'admin' => 'Адміністратор'];
    return $labels[$role] ?? $role;
}

function formatDateFull($date) {
    $timestamp = strtotime($date);
    $today = strtotime('today');
    $tomorrow = strtotime('tomorrow');

    $months = ['січня', 'лютого', 'березня', 'квітня', 'травня', 'червня',
               'липня', 'серпня', 'вересня', 'жовтня', 'листопада', 'грудня'];

    $formatted = date('j', $timestamp) . ' ' . $months[(int)date('n', $timestamp) - 1] . ' ' . date('Y', $timestamp);

    if ($timestamp < $today) {
        return '<span class="deadline-expired">Прострочено (' . $formatted . ')</span>';
    } elseif ($timestamp < $tomorrow) {
        return '<span class="deadline-today">Сьогодні (' . $formatted . ')</span>';
    } else {
        return $formatted;
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($homework['title']); ?> - Розробка</title>
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
        <div class="homework-detail">
            <div class="detail-header">
                <span class="subject-badge"><?php echo htmlspecialchars($subject); ?></span>
                <span class="class-badge"><?php echo htmlspecialchars($class); ?> клас</span>
            </div>

            <h1 class="detail-title"><?php echo htmlspecialchars($homework['title']); ?></h1>

            <div class="detail-description">
                <?php echo nl2br(htmlspecialchars($homework['description'])); ?>
            </div>

            <div class="detail-meta">
                <div class="meta-item">
                    <span class="meta-icon">📅</span>
                    <span>Термін: <?php echo formatDateFull($homework['deadline']); ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-icon">👤</span>
                    <span>Додав: <?php echo htmlspecialchars($author_name); ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-icon">🕐</span>
                    <span>Створено: <?php echo htmlspecialchars($homework['created_at']); ?></span>
                </div>
            </div>

            <div class="detail-actions">
                <a href="dashboard.php?class=<?php echo urlencode($class); ?>&subject=<?php echo urlencode($subject); ?>" class="btn btn-secondary">
                    ← До списку завдань
                </a>
            </div>
        </div>
    </div>
</body>
</html>
