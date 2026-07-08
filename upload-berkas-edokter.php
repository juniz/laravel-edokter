<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Accept');

// Enable error reporting for debugging
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Get raw POST data
    $rawData = file_get_contents('php://input');

    // Check if we have multipart form data
    if (isset($_POST['no_rawat'])) {
        // Handle regular form data
        $noRawat = $_POST['no_rawat'];
        $kdBerkas = $_POST['kd_berkas'];
        $namaFile = $_POST['nama_file'];
        $fileContent = $_POST['file'];
    } else {
        // Try to parse JSON data
        $data = json_decode($rawData, true);
        if (!$data) {
            throw new Exception('Invalid JSON data received');
        }
        
        $noRawat = $data['no_rawat'] ?? null;
        $kdBerkas = $data['kd_berkas'] ?? null;
        $namaFile = $data['nama_file'] ?? null;
        $fileContent = $data['file'] ?? null;
    }

    // Validate required fields
    // if (!$noRawat || !$kdBerkas || !$namaFile || !$fileContent) {
    //     throw new Exception('Missing required fields');
    // }
    
    if (!$noRawat){
        throw new Exception('No Rawat tidak boleh kosong');
    }
    
    if (!$kdBerkas){
        throw new Exception('Kd Berkas tidak boleh kosong');
    }
    
    // if (!$namaFile){
    //     throw new Exception('Nama File tidak boleh kosong');
    // }
    
    if (!$fileContent){
        throw new Exception('File tidak boleh kosong');
    }
    
    $pdfContent = base64_decode($fileContent);
    if ($pdfContent === false) {
        throw new Exception('Invalid base64 file content');
    }
    
    // Create upload directory if it doesn't exist
    $uploadDir = __DIR__ . '/berkasrawat/pages/upload/';
    // if (!file_exists($uploadDir)) {
    //     mkdir($uploadDir, 0777, true);
    // }

    // Generate unique filename
    if(!$namaFile){
        $filename = 'RM' . '_' . $kdBerkas . '_' . date('YmdHis') . '.pdf'; 
    }else{
        $filename = $namaFile. '.pdf';
    }
    
    $filepath = $uploadDir.$filename;
    $dataFile = 'pages/upload/'.$filename;
    
    // Save file
    if (!file_put_contents($filepath, $pdfContent)) {
        throw new Exception('File gambar gagal disimpan');
    }

    // Save the file
    // Connect to database
        $db = new PDO(
            'mysql:host=192.168.3.200:6671;dbname=sik',
            'pelaksana',
            '5DLngElEFDRNf0VP5EPzPIg7h+sfOLPP4IogjMcg5k4=',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Insert record into database
        $stmt = $db->prepare("
            INSERT INTO berkas_digital_perawatan 
            (no_rawat, kode, lokasi_file) 
            VALUES (?, ?, ?)
        ");
        
        $stmt->execute([$noRawat, $kdBerkas, $dataFile]);

        echo json_encode([
            'success' => true,
            'message' => 'File uploaded successfully',
            'data' => [
                'filename' => $filename,
                'filepath' => $filepath
            ]
        ]);

} catch (Exception $e) {
    // http_response_code(200);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}