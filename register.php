<?php
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $fullname = trim($_POST['fullname'] ?? '');
    $role = $_POST['role'] ?? 'student';
    
    if (empty($username) || empty($password) || empty($fullname)) {
        $error = 'Заповніть усі поля';
    } elseif (strlen($username) < 3) {
        $error = 'Логін має містити щонайменше 3 символи';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль має містити щонайменше 6 символів';
    } elseif ($password !== $password_confirm) {
        $error = 'Паролі не збігаються';
    } elseif (!in_array($role, ['student', 'teacher', 'admin'])) {
        $error = 'Неправильна роль';
    } else {
        $user = createUser($username, $password, $role, $fullname);
        if ($user) {
            $success = 'Реєстрація успішна! Тепер ви можете увійти.';
        } else {
            $error = 'Користувач із таким логіном уже існує';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Реєстрація - Розробка</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>📚 Розробка</h1>
                <p>Створіть новий акаунт</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message">
                    ✅ <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="fullname">Повне ім'я</label>
                    <input 
                        type="text" 
                        id="fullname" 
                        name="fullname" 
                        required
                        value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="username">Логін</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required
                        autocomplete="username"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="role">Роль</label>
                    <select id="role" name="role" required>
                        <option value="student" <?php echo ($_POST['role'] ?? '') === 'student' ? 'selected' : ''; ?>>
                            Учень
                        </option>
                        <option value="teacher" <?php echo ($_POST['role'] ?? '') === 'teacher' ? 'selected' : ''; ?>>
                            Учитель
                        </option>
                        <option value="admin" <?php echo ($_POST['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>
                            Адміністратор
                        </option>
                    </select>
                    <div class="role-warning">
                        ⚠️ Якщо ви реєструєтесь від імені вчителя, хоча насправді ви учень, ваш акаунт буде заблоковано та видалено.
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        autocomplete="new-password"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password_confirm">Підтвердіть пароль</label>
                    <input 
                        type="password" 
                        id="password_confirm" 
                        name="password_confirm" 
                        required
                        autocomplete="new-password"
                    >
                </div>
                
                <button type="submit" class="btn btn-primary">
                    Зареєструватися
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Вже є акаунт? <a href="login.php">Увійти</a></p>
            </div>
        </div>
    </div>
</body>
</html>
