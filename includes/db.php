<?php

function readJSON($filename) {
    $filepath = __DIR__ . '/../data/' . $filename;
    
    if (!file_exists($filepath)) {
        return [];
    }
    
    $content = file_get_contents($filepath);
    $data = json_decode($content, true);
    
    return $data ? $data : [];
}

function writeJSON($filename, $data) {
    $filepath = __DIR__ . '/../data/' . $filename;
    
    $dir = dirname($filepath);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($filepath, $json);
}

function getUsers() {
    return readJSON('users.json');
}

function saveUsers($users) {
    return writeJSON('users.json', $users);
}

function getUserByUsername($username) {
    $users = getUsers();
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            return $user;
        }
    }
    return null;
}

function updateUser($user_id, $updates) {
    $users = getUsers();
    
    foreach ($users as $index => $user) {
        if ($user['id'] === $user_id) {
            $users[$index] = array_merge($user, $updates);
            saveUsers($users);
            return $users[$index];
        }
    }
    
    return false;
}

function getDefaultSettings() {
    return [
        'accent' => 'violet',
        'reduced_motion' => false
    ];
}

function getUserSettings($user) {
    return array_merge(getDefaultSettings(), $user['settings'] ?? []);
}

function createUser($username, $password, $role = 'student', $fullname = '') {
    $users = getUsers();
    
    if (getUserByUsername($username)) {
        return false;
    }
    
    $user = [
        'id' => uniqid(),
        'username' => $username,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'role' => $role,
        'fullname' => $fullname,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $users[] = $user;
    saveUsers($users);
    
    return $user;
}

function getHomework() {
    return readJSON('homework.json');
}

function saveHomework($homework) {
    return writeJSON('homework.json', $homework);
}

function getClasses() {
    return ['5', '6', '7', '8', '9'];
}

function getSubjects($class) {
    $subjects = readJSON('subjects.json');
    return $subjects[$class] ?? [];
}

function isValidClass($class) {
    return in_array($class, getClasses());
}

function isValidSubjectForClass($class, $subject) {
    return in_array($subject, getSubjects($class));
}

function addHomework($title, $subject, $class, $description, $deadline, $created_by) {
    $homework_list = getHomework();
    
    $homework = [
        'id' => uniqid(),
        'title' => $title,
        'subject' => $subject,
        'class' => $class,
        'description' => $description,
        'deadline' => $deadline,
        'created_by' => $created_by,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $homework_list[] = $homework;
    saveHomework($homework_list);
    
    return $homework;
}

function getHomeworkSorted() {
    $homework = getHomework();
    
    usort($homework, function($a, $b) {
        return strtotime($a['deadline']) - strtotime($b['deadline']);
    });
    
    return $homework;
}
