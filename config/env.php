<?php

function loadEnv($path) {
    if (!file_exists($path)) {
        throw new Exception('.env.local file not found');
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Split by first occurrence of =
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        // Remove quotes if present
        if (strpos($value, '"') === 0 || strpos($value, "'") === 0) {
            $value = trim($value, '"\'');
        }

        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

// Load environment variables
try {
    loadEnv(__DIR__ . '/../.env.local');
} catch (Exception $e) {
    die('Error loading .env.local file: ' . $e->getMessage());
}