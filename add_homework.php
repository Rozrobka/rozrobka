<?php
require_once 'includes/auth.php';
requireRole(['teacher', 'admin']);

$user = getCurrentUser();
$settings = getUserSettings($user);
$body_classes = 'theme-' . $settings['accent'] . ($settings['reduced_motion'] ? ' reduced-motion' : '');
$error = '';
$classes = getClasses();
$subjects_by_class = readJSON('subjects.json');
$selected_class = $_POST['class'] ?? '5';
$selected_subject = $_POST['subject'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $class = trim($_POST['class'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $deadline = $_POST['deadline'] ?? '';
    
    if (empty($title) || empty($subject) || empty($class) || empty($description) || empty($deadline)) {
        $error = 'Заповніть усі поля';
    } elseif (strlen($title) < 3) {
        $error = 'Назва має містити щонайменше 3 символи';
    } elseif (!isValidClass($class)) {
        $error = 'Оберіть клас від 5 до 9';
    } elseif (!isValidSubjectForClass($class, $subject)) {
        $error = 'Оберіть предмет, який відповідає вибраному класу';
    } elseif (strtotime($deadline) < strtotime('today')) {
        $error = 'Дедлайн не може бути в минулому';
    } else {
        addHomework($title, $subject, $class, $description, $deadline, $user['username']);
        header('Location: dashboard.php?class=' . urlencode($class) . '&subject=' . urlencode($subject));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Додати ДЗ - Розробка</title>
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
                    <small><?php
                    $role_labels = ['student' => 'Учень', 'teacher' => 'Учитель', 'admin' => 'Адміністратор'];
                    echo $role_labels[$user['role']] ?? $user['role'];
                    ?></small>
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
        <div class="form-card">
            <div class="form-header">
                <h1>➕ Додати домашнє завдання</h1>
                <p>Оберіть клас НУШ, предмет і заповніть деталі завдання</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="homework-form">
                <div class="form-group">
                    <label for="title">Назва завдання *</label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        required
                        placeholder="Наприклад: Рівняння"
                        value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="class">Клас НУШ *</label>
                    <select id="class" name="class" required>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo htmlspecialchars($class); ?>" <?php echo $selected_class === $class ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($class); ?> клас
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-hint">Доступні класи: 5, 6, 7, 8, 9</small>
                </div>

                <div class="form-group">
                    <label for="subject">Предмет *</label>
                    <select id="subject" name="subject" required data-selected="<?php echo htmlspecialchars($selected_subject); ?>">
                    </select>
                    <small class="form-hint">Список предметів автоматично змінюється залежно від класу</small>
                </div>
                
                <div class="form-group">
                    <label for="deadline">Термін здачі *</label>
                    <input 
                        type="date" 
                        id="deadline" 
                        name="deadline" 
                        required
                        min="<?php echo date('Y-m-d'); ?>"
                        value="<?php echo htmlspecialchars($_POST['deadline'] ?? ''); ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="description">Опис завдання *</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        required
                        rows="6"
                        placeholder="Наприклад: Розв'язати 10 задач із підручника"
                    ><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <a href="dashboard.php" class="btn btn-secondary">Скасувати</a>
                    <button type="submit" class="btn btn-primary">
                        ✅ Створити завдання
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const subjectsByClass = <?php echo json_encode($subjects_by_class, JSON_UNESCAPED_UNICODE); ?>;
        const classSelect = document.getElementById('class');
        const subjectSelect = document.getElementById('subject');

        function updateSubjects() {
            const selectedClass = classSelect.value;
            const selectedSubject = subjectSelect.dataset.selected;
            const subjects = subjectsByClass[selectedClass] || [];

            subjectSelect.innerHTML = '';

            subjects.forEach(function(subject) {
                const option = document.createElement('option');
                option.value = subject;
                option.textContent = subject;

                if (subject === selectedSubject) {
                    option.selected = true;
                }

                subjectSelect.appendChild(option);
            });

            if (!subjects.includes(selectedSubject)) {
                subjectSelect.dataset.selected = '';
            }
        }

        classSelect.addEventListener('change', function() {
            subjectSelect.dataset.selected = '';
            updateSubjects();
        });

        updateSubjects();
    </script>
</body>
</html>
