<?php
require_once 'includes/auth.php';
requireLogin();

$user = getCurrentUser();
$settings = getUserSettings($user);
$body_classes = 'theme-' . $settings['accent'] . ($settings['reduced_motion'] ? ' reduced-motion' : '');
$classes = getClasses();
$filter_class = $_GET['class'] ?? '';

if ($filter_class && !isValidClass($filter_class)) {
    $filter_class = '';
}

$all_homework = getHomework();

$class_stats = [];
foreach ($classes as $cl) {
    $class_hw = array_filter($all_homework, fn($hw) => ($hw['class'] ?? '') === $cl);
    $total = count($class_hw);
    $expired = count(array_filter($class_hw, fn($hw) => strtotime($hw['deadline']) < strtotime('today')));
    $active = $total - $expired;

    $subjects = [];
    foreach ($class_hw as $hw) {
        $s = $hw['subject'] ?? '—';
        $subjects[$s] = ($subjects[$s] ?? 0) + 1;
    }
    arsort($subjects);

    $class_stats[$cl] = [
        'total' => $total,
        'active' => $active,
        'expired' => $expired,
        'subjects' => $subjects
    ];
}

$total_all = count($all_homework);
$expired_all = count(array_filter($all_homework, fn($hw) => strtotime($hw['deadline']) < strtotime('today')));
$active_all = $total_all - $expired_all;

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
    <title>Статистика - Розробка</title>
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
            <h1>📊 Статистика</h1>
        </div>

        <div class="stats-overview">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_all; ?></div>
                <div class="stat-label">Всього завдань</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-active"><?php echo $active_all; ?></div>
                <div class="stat-label">Активних</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-expired"><?php echo $expired_all; ?></div>
                <div class="stat-label">Прострочених</div>
            </div>
        </div>

        <div class="filter-bar">
            <div class="filter-section">
                <div class="filter-label">Клас:</div>
                <div class="filter-buttons">
                    <a href="stats.php" class="filter-btn <?php echo !$filter_class ? 'active' : ''; ?>">Усі</a>
                    <?php foreach ($classes as $cl): ?>
                        <a href="stats.php?class=<?php echo $cl; ?>" class="filter-btn <?php echo $filter_class === $cl ? 'active' : ''; ?>">
                            <?php echo $cl; ?> клас
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php
        $display_stats = $filter_class ? [$filter_class => $class_stats[$filter_class]] : $class_stats;
        ?>

        <div class="homework-grid">
            <?php foreach ($display_stats as $cl => $st): ?>
                <div class="homework-card stat-class-card">
                    <div class="homework-header">
                        <h3><?php echo $cl; ?> клас</h3>
                        <span class="class-badge"><?php echo $st['total']; ?> ДЗ</span>
                    </div>
                    <div class="homework-body">
                        <div class="stat-mini">
                            <span class="stat-mini-item">✅ <?php echo $st['active']; ?> активних</span>
                            <span class="stat-mini-item">⏰ <?php echo $st['expired']; ?> прострочених</span>
                        </div>
                        <?php if ($st['subjects']): ?>
                            <div class="stat-subjects">
                                <small>Топ предметів:</small>
                                <?php
                                $top = array_slice($st['subjects'], 0, 3);
                                foreach ($top as $subj => $cnt):
                                ?>
                                    <span class="subject-badge"><?php echo htmlspecialchars($subj); ?> (<?php echo $cnt; ?>)</span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="homework-footer">
                        <a href="dashboard.php?class=<?php echo $cl; ?>" class="btn btn-small">Переглянути ДЗ</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
