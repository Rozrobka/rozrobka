<?php
require_once 'includes/auth.php';
requireLogin();

$user = getCurrentUser();
$settings = getUserSettings($user);
$body_classes = 'theme-' . $settings['accent'] . ($settings['reduced_motion'] ? ' reduced-motion' : '');
$classes = getClasses();
$subjects_by_class = readJSON('subjects.json');
$filter_class = $_GET['class'] ?? '';
$filter_subject = $_GET['subject'] ?? '';
$homework_list = getHomeworkSorted();

if ($filter_class && isValidClass($filter_class)) {
    $homework_list = array_filter($homework_list, function($hw) use ($filter_class) {
        return ($hw['class'] ?? '') === $filter_class;
    });
} else {
    $filter_class = '';
}

if ($filter_subject) {
    $homework_list = array_filter($homework_list, function($hw) use ($filter_subject) {
        return ($hw['subject'] ?? '') === $filter_subject;
    });
}

$available_subjects = [];
if ($filter_class) {
    $available_subjects = getSubjects($filter_class);
} else {
    foreach ($subjects_by_class as $subjects) {
        foreach ($subjects as $subject) {
            if (!in_array($subject, $available_subjects)) {
                $available_subjects[] = $subject;
            }
        }
    }
    sort($available_subjects);
}

if ($filter_subject && !in_array($filter_subject, $available_subjects)) {
    $filter_subject = '';
}

function getRoleLabel($role) {
    $labels = [
        'student' => 'Учень',
        'teacher' => 'Учитель',
        'admin' => 'Адміністратор'
    ];
    return $labels[$role] ?? $role;
}

function formatDate($date) {
    $timestamp = strtotime($date);
    $today = strtotime('today');
    $tomorrow = strtotime('tomorrow');
    
    if ($timestamp < $today) {
        return '<span class="deadline-expired">Прострочено</span>';
    } elseif ($timestamp < $tomorrow) {
        return '<span class="deadline-today">Сьогодні</span>';
    } elseif ($timestamp < strtotime('+7 days')) {
        return '<span class="deadline-soon">' . date('d.m.Y', $timestamp) . '</span>';
    } else {
        return date('d.m.Y', $timestamp);
    }
}

function buildFilterUrl($class, $subject) {
    $params = [];

    if ($class) {
        $params['class'] = $class;
    }

    if ($subject) {
        $params['subject'] = $subject;
    }

    return 'dashboard.php' . ($params ? '?' . http_build_query($params) : '');
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Головна - Розробка</title>
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
            <h1>Домашні завдання</h1>
            <div class="header-actions">
                <?php if (hasRole(['teacher', 'admin'])): ?>
                    <a href="add_homework.php" class="btn btn-primary">
                        ➕ Додати ДЗ
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="filter-bar">
            <div class="filter-section <?php echo $filter_class ? 'filter-open' : ''; ?>">
                <button type="button" class="filter-toggle" onclick="this.parentElement.classList.toggle('filter-open')">
                    <span class="filter-label">
                        Клас НУШ
                        <?php if ($filter_class): ?>
                            <span class="filter-active-badge"><?php echo htmlspecialchars($filter_class); ?> клас</span>
                        <?php endif; ?>
                    </span>
                    <span class="filter-arrow">▾</span>
                </button>
                <div class="filter-buttons">
                    <a href="<?php echo buildFilterUrl('', $filter_subject); ?>" class="filter-btn <?php echo !$filter_class ? 'active' : ''; ?>">
                        Усі класи
                    </a>
                    <?php foreach ($classes as $class): ?>
                        <a href="<?php echo buildFilterUrl($class, ''); ?>" class="filter-btn <?php echo $filter_class === $class ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($class); ?> клас
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="filter-section <?php echo $filter_subject ? 'filter-open' : ''; ?>">
                <button type="button" class="filter-toggle" onclick="this.parentElement.classList.toggle('filter-open')">
                    <span class="filter-label">
                        Предмет
                        <?php if ($filter_subject): ?>
                            <span class="filter-active-badge"><?php echo htmlspecialchars($filter_subject); ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="filter-arrow">▾</span>
                </button>
                <div class="filter-buttons">
                    <a href="<?php echo buildFilterUrl($filter_class, ''); ?>" class="filter-btn <?php echo !$filter_subject ? 'active' : ''; ?>">
                        Усі предмети
                    </a>
                    <?php foreach ($available_subjects as $subject): ?>
                        <a href="<?php echo buildFilterUrl($filter_class, $subject); ?>" class="filter-btn <?php echo $filter_subject === $subject ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($subject); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="homework-grid">
            <?php if (count($homework_list) === 0): ?>
                <div class="empty-state">
                    <div class="empty-icon">📝</div>
                    <h3>Немає домашніх завдань</h3>
                    <p>
                        <?php if ($filter_class || $filter_subject): ?>
                            За вибраними фільтрами поки немає завдань
                        <?php else: ?>
                            Поки немає жодного завдання
                        <?php endif; ?>
                    </p>
                    <?php if (hasRole(['teacher', 'admin'])): ?>
                        <a href="add_homework.php" class="btn btn-primary">
                            Додати перше завдання
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($homework_list as $hw): ?>
                    <?php
                    $author = getUserByUsername($hw['created_by'] ?? '');
                    $author_name = $author ? $author['fullname'] : ($hw['created_by'] ?? 'Невідомо');
                    $subject = $hw['subject'] ?? 'Без предмета';
                    $class = $hw['class'] ?? '—';
                    ?>
                    <a href="homework.php?id=<?php echo urlencode($hw['id']); ?>" class="homework-card">
                        <div class="homework-header">
                            <div>
                                <span class="subject-badge"><?php echo htmlspecialchars($subject); ?></span>
                                <h3><?php echo htmlspecialchars($hw['title']); ?></h3>
                            </div>
                            <span class="class-badge"><?php echo htmlspecialchars($class); ?> клас</span>
                        </div>
                        
                        <div class="homework-body">
                            <p><?php echo nl2br(htmlspecialchars($hw['description'])); ?></p>
                        </div>
                        
                        <div class="homework-footer">
                            <div class="homework-meta">
                                <div class="meta-item">
                                    <span class="meta-icon">📅</span>
                                    <span>Термін: <?php echo formatDate($hw['deadline']); ?></span>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-icon">🎓</span>
                                    <span><?php echo htmlspecialchars($class); ?> клас</span>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-icon">👤</span>
                                    <span><?php echo htmlspecialchars($author_name); ?></span>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="support-footer">
            <div class="support-content">
                <h3>📩 Підтримка системи електронного щоденника</h3>
                <p>Якщо у вас є питання, помилки або пропозиції — зв'яжіться з нами:</p>
                <div class="support-contacts">
                    <div class="support-item">
                        <span class="support-icon">✉️</span>
                        <span>Email: <strong>rozrobka@proton.me</strong></span>
                    </div>
                    <div class="support-item">
                        <span class="support-icon">👨‍💻</span>
                        <span>Адміністратор: <strong>Rozrobka Support</strong></span>
                    </div>
                    <div class="support-item">
                        <span class="support-icon">🕐</span>
                        <span>Час відповіді: 12–24 години</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
