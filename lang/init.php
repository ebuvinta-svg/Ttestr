<?php
session_start();

// --- CSRF Token ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];


// --- Language Selection ---
// Default language is English
$default_lang = 'en';

// Supported languages
$supported_langs = ['en', 'ru'];

// Check for language change via GET parameter
if (isset($_GET['lang']) && in_array($_GET['lang'], $supported_langs)) {
    $_SESSION['lang'] = $_GET['lang'];
}

// Determine the language to use
$lang_code = isset($_SESSION['lang']) ? $_SESSION['lang'] : $default_lang;

// Include the language file
$lang_file = __DIR__ . '/' . $lang_code . '.php';
if (file_exists($lang_file)) {
    $lang = require $lang_file;
} else {
    // Fallback to default language if file not found
    $lang = require __DIR__ . '/' . $default_lang . '.php';
}
?>
