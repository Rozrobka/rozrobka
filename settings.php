<?php
require_once 'includes/auth.php';
requireLogin();

$user = getCurrentUser();
$settings = getUserSettings($user);
$error = '';
$success = '';

$accent_options = [
    'violet' => 'Фіолетовий',
    'cyan' => 'Блакитний',
    'rose' => 'Рожевий',
    'emerald' => 'Смарагдовий'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $accent = $_POST['accent'] ?? 'violet';
    $reduced_motion = isset($_POST['reduced_motion']);

    if (empty($fullname)) {
        $error = 'Ім’я не може бути порожнім';
    } elseif (!array_key_exists($accent, $accent_options)) {
        $error = 'Оберіть коректний акцентний колір';
    } else {
        $updated = updateUser($user['id'], [
            'fullname' => $fullname,
            'settings' => [
                'accent' => $accent,
                'reduced_motion' => $reduced_motion
            ]
        ]);

        if ($updated) {
            $user = $updated;
            $settings = getUserSettings($user);
            $success = 'Налаштування збережено';
        } else {
            $error = 'Не вдалося зберегти налаштування';
        }
    }
}

$body_classes = 'theme-' . $settings['accent'] . ($settings['reduced_motion'] ? ' reduced-motion' : '');

function getRoleLabel($role) {
    $labels = [
        'student' => 'Учень',
        'teacher' => 'Учитель',
        'admin' => 'Адміністратор'
    ];
    return $labels[$role] ?? $role;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Налаштування - Розробка</title>
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
                <a href="logout.php" class="btn btn-small">Вийти</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="settings-layout">
            <div class="form-card settings-card">
                <div class="form-header">
                    <h1>⚙️ Налаштування</h1>
                    <p>Персоналізуйте профіль і вигляд сайту</p>
                </div>

                <?php if ($error): ?>
                    <div class="error-message">⚠️ <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="success-message">✅ <?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="POST" class="homework-form">
                    <div class="form-group">
                        <label for="fullname">Ваше ім’я *</label>
                        <input
                            type="text"
                            id="fullname"
                            name="fullname"
                            required
                            value="<?php echo htmlspecialchars($user['fullname']); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="accent">Акцентний колір (у розробці)</label>
                        <select id="accent" name="accent">
                            <?php foreach ($accent_options as $value => $label): ?>
                                <option value="<?php echo htmlspecialchars($value); ?>" <?php echo $settings['accent'] === $value ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <label class="settings-toggle">
                        <input type="checkbox" name="reduced_motion" <?php echo $settings['reduced_motion'] ? 'checked' : ''; ?>>
                        <span>
                            <strong>Зменшити анімації</strong>
                            <small>Менше руху та легше навантаження для браузера</small>
                        </span>
                    </label>

                    <div class="form-actions">
                        <a href="dashboard.php" class="btn btn-secondary">Скасувати</a>
                        <button type="submit" class="btn btn-primary">💾 Зберегти</button>
                    </div>
                </form>
            </div>

            <div class="settings-preview homework-card">
                <div class="homework-header">
                    <div>
                        <span class="subject-badge">Дизайн</span>
                        <h3>Попередній перегляд</h3>
                    </div>
                    <span class="class-badge">Тема</span>
                </div>
                <div class="homework-body">
                    <p>Зміни застосуються після збереження. Колір і анімації будуть використовуватися на всіх сторінках сайту.</p>
                </div>
                <div class="homework-footer">
                    <div class="homework-meta">
                        <div class="meta-item">
                            <span class="meta-icon">🎨</span>
                            <span><?php echo htmlspecialchars($accent_options[$settings['accent']]); ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-icon">⚡</span>
                            <span><?php echo $settings['reduced_motion'] ? 'Анімації зменшено' : 'Анімації увімкнено'; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
