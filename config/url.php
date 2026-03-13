<?php
/**
 * URL helpers that keep links working whether the app is hosted at web root
 * (e.g. /dashboard.php) or inside a subfolder (e.g. /genservic/dashboard.php).
 */

function app_base_url(): string
{
    static $basePath = null;

    if ($basePath !== null) {
        return $basePath;
    }

    $appRoot = realpath(__DIR__ . '/..') ?: '';
    $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? (realpath($_SERVER['DOCUMENT_ROOT']) ?: '') : '';

    if ($appRoot !== '' && $documentRoot !== '' && str_starts_with($appRoot, $documentRoot)) {
        $relativePath = trim(str_replace('\\', '/', substr($appRoot, strlen($documentRoot))), '/');
        $basePath = $relativePath === '' ? '' : '/' . $relativePath;

        return $basePath;
    }

    $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
    $projectName = basename($appRoot);
    $needle = '/' . $projectName . '/';

    if ($projectName !== '' && str_contains($scriptName, $needle)) {
        $basePath = '/' . $projectName;

        return $basePath;
    }

    $basePath = '';

    return $basePath;
}

function app_url(string $path = ''): string
{
    $cleanPath = ltrim($path, '/');
    $basePath = app_base_url();

    if ($cleanPath === '') {
        return $basePath === '' ? '/' : $basePath . '/';
    }

    return ($basePath === '' ? '' : $basePath) . '/' . $cleanPath;
}
