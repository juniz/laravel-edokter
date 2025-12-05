<?php

/**
 * PDF Proxy untuk mengatasi masalah CORS
 * File ini harus ditempatkan di server PDF (https://simrs.rsbhayangkaranganjuk.com)
 * 
 * Usage: https://simrs.rsbhayangkaranganjuk.com/pdf-proxy.php?file=path/to/file.pdf
 */

// Konfigurasi
$allowedOrigins = [
    'http://localhost:8000',
    'http://localhost',
    'https://dokter.rsbhayangkaranganjuk.com', // Ganti dengan domain aplikasi Anda
    // Tambahkan domain lain yang diizinkan
];

// Base path untuk file PDF
// Opsi 1: Absolute path dari root sistem (disarankan)
$basePath = $_SERVER['DOCUMENT_ROOT'] . '/webapps/berkasrawat/';

// Opsi 2: Relative path dari document root (jika opsi 1 tidak bekerja)
// $basePath = '/webapps/berkasrawat/';

// Opsi 3: Jika file ada di luar document root
// $basePath = '/var/www/webapps/berkasrawat/'; // Sesuaikan dengan struktur server Anda

// Pastikan basePath ada dan readable
// Jika basePath tidak ada, coba alternatif
if (!is_dir($basePath)) {
    // Coba alternatif: relative path dari document root
    $altPath = $_SERVER['DOCUMENT_ROOT'] . '/webapps/berkasrawat/';
    if (is_dir($altPath)) {
        $basePath = $altPath;
    } else {
        // Coba absolute path
        $altPath2 = '/webapps/berkasrawat/';
        if (is_dir($altPath2)) {
            $basePath = $altPath2;
        }
        // Jika masih tidak ada, akan error saat validasi file
    }
}
$maxFileSize = 50 * 1024 * 1024; // 50MB max file size
$allowedExtensions = ['pdf'];

// Fungsi untuk mendapatkan origin dari request
function getOrigin()
{
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        return $_SERVER['HTTP_ORIGIN'];
    }
    if (isset($_SERVER['HTTP_REFERER'])) {
        $parsed = parse_url($_SERVER['HTTP_REFERER']);
        return $parsed['scheme'] . '://' . $parsed['host'];
    }
    return '*';
}

// Fungsi untuk set CORS headers
function setCorsHeaders($allowedOrigins)
{
    $origin = getOrigin();

    // Cek apakah origin diizinkan
    if (in_array($origin, $allowedOrigins) || in_array('*', $allowedOrigins)) {
        header('Access-Control-Allow-Origin: ' . $origin);
    } else {
        // Jika origin tidak diizinkan, gunakan origin pertama atau *
        header('Access-Control-Allow-Origin: ' . (count($allowedOrigins) > 0 ? $allowedOrigins[0] : '*'));
    }

    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Range');
    header('Access-Control-Expose-Headers: Content-Length, Content-Range, Accept-Ranges');
    header('Access-Control-Max-Age: 3600');
}

// Handle OPTIONS request (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    setCorsHeaders($allowedOrigins);
    http_response_code(200);
    exit;
}

// Set CORS headers untuk semua request
setCorsHeaders($allowedOrigins);

// Validasi parameter file
if (!isset($_GET['file']) || empty($_GET['file'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Parameter file diperlukan',
        'message' => 'Gunakan: ?file=path/to/file.pdf'
    ]);
    exit;
}

$filePath = $_GET['file'];

// Security: Hapus path traversal attempts
$filePath = str_replace(['../', '..\\', '..'], '', $filePath);
$filePath = ltrim($filePath, '/\\');

// Validasi extension
$extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
if (!in_array($extension, $allowedExtensions)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'File type tidak diizinkan',
        'message' => 'Hanya file PDF yang diizinkan'
    ]);
    exit;
}

// Build full file path
// Normalize path untuk menghindari double slashes
$normalizedBasePath = rtrim(str_replace('\\', '/', $basePath), '/');
$normalizedFilePath = ltrim(str_replace('\\', '/', $filePath), '/');
$fullPath = $normalizedBasePath . '/' . $normalizedFilePath;

// Normalize path (remove double slashes, resolve . and ..)
$fullPath = str_replace(['//', '\\'], '/', $fullPath);

// Debug mode (set ke false di production)
// Set ke true untuk mendapatkan informasi detail saat error
$debugMode = true; // Set ke false setelah selesai debugging

// Validasi file exists
if (!file_exists($fullPath)) {
    http_response_code(404);
    header('Content-Type: application/json');

    $errorData = [
        'error' => 'File tidak ditemukan',
        'message' => 'File PDF tidak ditemukan di server',
        'requested_path' => $filePath,
        'full_path' => $fullPath,
        'base_path' => $basePath
    ];

    // Jika debug mode, tambahkan info tambahan
    if ($debugMode) {
        $errorData['debug'] = [
            'file_exists' => file_exists($fullPath),
            'is_readable' => is_readable($fullPath),
            'realpath' => realpath($basePath),
            'base_path_exists' => is_dir($basePath),
            'base_path_readable' => is_readable($basePath)
        ];

        // Coba cari file dengan beberapa variasi path
        $possiblePaths = [
            $fullPath,
            rtrim($basePath, '/') . '/' . ltrim($filePath, '/'),
            $basePath . $filePath,
            $_SERVER['DOCUMENT_ROOT'] . '/webapps/berkasrawat/' . $filePath,
            '/webapps/berkasrawat/' . $filePath,
            dirname($basePath) . '/' . $filePath,
            realpath($basePath) . '/' . $filePath
        ];

        // Hapus duplikat
        $possiblePaths = array_unique($possiblePaths);

        $errorData['debug']['possible_paths'] = [];
        foreach ($possiblePaths as $possiblePath) {
            if ($possiblePath) {
                $errorData['debug']['possible_paths'][] = [
                    'path' => $possiblePath,
                    'exists' => file_exists($possiblePath),
                    'is_readable' => is_readable($possiblePath),
                    'is_file' => is_file($possiblePath)
                ];
            }
        }

        // Cek apakah base directory ada
        $errorData['debug']['base_path_info'] = [
            'base_path' => $basePath,
            'base_path_exists' => is_dir($basePath),
            'base_path_readable' => is_readable($basePath),
            'document_root' => $_SERVER['DOCUMENT_ROOT'],
            'script_filename' => $_SERVER['SCRIPT_FILENAME'],
            'script_name' => $_SERVER['SCRIPT_NAME']
        ];

        // Coba list beberapa file di base directory untuk verifikasi
        if (is_dir($basePath) && is_readable($basePath)) {
            $dirContents = @scandir($basePath);
            if ($dirContents) {
                $errorData['debug']['base_directory_contents'] = array_slice($dirContents, 0, 10); // First 10 items
            }
        }
    }

    echo json_encode($errorData, JSON_PRETTY_PRINT);
    exit;
}

// Validasi file size
$fileSize = filesize($fullPath);
if ($fileSize > $maxFileSize) {
    http_response_code(413);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'File terlalu besar',
        'message' => 'Ukuran file melebihi batas maksimum (' . ($maxFileSize / 1024 / 1024) . 'MB)'
    ]);
    exit;
}

// Validasi bahwa ini benar-benar file PDF
$mimeType = mime_content_type($fullPath);
if ($mimeType !== 'application/pdf' && $mimeType !== 'application/octet-stream') {
    // Cek dengan membaca beberapa byte pertama
    $handle = fopen($fullPath, 'rb');
    $header = fread($handle, 4);
    fclose($handle);

    if ($header !== '%PDF') {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'File tidak valid',
            'message' => 'File bukan merupakan PDF yang valid'
        ]);
        exit;
    }
}

// Set headers untuk PDF
header('Content-Type: application/pdf');
header('Accept-Ranges: bytes');
header('Cache-Control: public, max-age=3600');
header('Content-Disposition: inline; filename="' . basename($filePath) . '"');

// Handle Range requests (untuk partial content / streaming)
// PDF.js sering menggunakan Range requests untuk optimasi loading
$range = null;
if (isset($_SERVER['HTTP_RANGE'])) {
    $range = $_SERVER['HTTP_RANGE'];
}

if ($range) {
    // Parse range header
    if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
        $start = intval($matches[1]);
        $end = $matches[2] !== '' ? intval($matches[2]) : $fileSize - 1;

        // Validasi range
        if ($start < 0 || $start >= $fileSize || $end >= $fileSize || $start > $end) {
            http_response_code(416); // Range Not Satisfiable
            header('Content-Range: bytes */' . $fileSize);
            header('Content-Length: 0');
            exit;
        }

        $length = $end - $start + 1;
        http_response_code(206); // Partial Content
        header('Content-Range: bytes ' . $start . '-' . $end . '/' . $fileSize);
        header('Content-Length: ' . $length);

        // Output partial content dengan buffering untuk performa
        $handle = fopen($fullPath, 'rb');
        if ($handle) {
            fseek($handle, $start);
            $remaining = $length;
            $chunkSize = 8192; // 8KB chunks

            while ($remaining > 0 && !feof($handle)) {
                $readSize = min($chunkSize, $remaining);
                $data = fread($handle, $readSize);
                if ($data === false) break;
                echo $data;
                $remaining -= strlen($data);
                flush(); // Flush output buffer
            }
            fclose($handle);
        }
    } else {
        // Invalid range format, return full file
        header('Content-Length: ' . $fileSize);
        readfile($fullPath);
    }
} else {
    // No range request, output full file
    header('Content-Length: ' . $fileSize);
    readfile($fullPath);
}

exit;
