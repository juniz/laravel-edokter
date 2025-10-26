<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DokumenOnlineController extends Controller
{
    protected $baseUrl = 'https://simrs.rsbhayangkaranganjuk.com/webapps/perpustakaan';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('loginauth');
    }

    public function index()
    {
        return view('dokumen-online.index');
    }

    /**
     * Get list of folders from perpustakaan
     */
    public function getFolders()
    {
        try {
            // Return predefined folders based on the image structure
            $folders = [
                [
                    'name' => '01.ICD',
                    'path' => '01.ICD',
                    'size' => '4.00 KB',
                    'modified' => 'Oct 26 15:37'
                ],
                [
                    'name' => '02.PNPK',
                    'path' => '02.PNPK',
                    'size' => '4.00 KB',
                    'modified' => 'Oct 26 15:46'
                ],
                [
                    'name' => '03.TKMKB',
                    'path' => '03.TKMKB',
                    'size' => '4.00 KB',
                    'modified' => 'Oct 26 15:47'
                ]
            ];

            return response()->json(['success' => true, 'folders' => $folders]);
        } catch (\Exception $e) {
            Log::error('Error fetching folders: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error fetching folders'], 500);
        }
    }

    /**
     * Get list of documents in a specific folder
     */
    public function getDocuments($folder)
    {
        try {
            // Decode folder name to handle special characters
            $folder = urldecode($folder);

            // Build the URL to fetch documents from the folder
            $url = $this->baseUrl . '/' . urlencode($folder);

            Log::info("Fetching documents from: " . $url);

            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
                ])
                ->get($url);

            $documents = [];

            if ($response->successful()) {
                $html = $response->body();

                // Try to parse documents from HTML
                $documents = $this->parseDocumentsFromHtml($html, $folder);

                // Also try alternative parsing method
                if (empty($documents)) {
                    $documents = $this->parseDocumentsAlternative($html, $folder);
                }

                Log::info("Found " . count($documents) . " documents in folder: " . $folder);
            } else {
                Log::warning("Failed to fetch documents. HTTP Status: " . $response->status());
            }

            // If no documents found or failed to fetch, return sample data
            if (empty($documents)) {
                Log::info("No documents found, returning sample data for: " . $folder);
                // Return sample PDF documents
                $documents = $this->getSampleDocuments($folder);
            }

            return response()->json(['success' => true, 'documents' => $documents]);
        } catch (\Exception $e) {
            Log::error('Error fetching documents: ' . $e->getMessage());
            // Return sample documents as fallback
            $documents = $this->getSampleDocuments($folder);
            return response()->json(['success' => true, 'documents' => $documents]);
        }
    }

    /**
     * Get sample documents for a folder
     */
    private function getSampleDocuments($folder)
    {
        // Build full URL to the folder
        $baseUrl = $this->baseUrl . '/' . urlencode($folder);

        // Define sample documents based on folder structure from server
        $sampleDocs = [
            '01.ICD' => [
                ['name' => 'Indonesian Coding Standard v1.pdf', 'url' => $baseUrl . '/Indonesian Coding Standard v1.pdf', 'size' => '6.4 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'List ICD9CM 2010 Indonesian Modification .pdf', 'url' => $baseUrl . '/List ICD9CM 2010 Indonesian Modification .pdf', 'size' => '865 KB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'List Of ICD 10 IM TERBARU 190525.pdf', 'url' => $baseUrl . '/List Of ICD 10 IM TERBARU 190525.pdf', 'size' => '2.1 MB', 'modified' => 'Oct 26 15:37'],
            ],
            '02.PNPK' => [
                ['name' => 'KMK_No_HK_01_07_MENKES_1645_2024_ttg_Rujuk_Balik_Penyakit.pdf', 'url' => $baseUrl . '/KMK_No_HK_01_07_MENKES_1645_2024_ttg_Rujuk_Balik_Penyakit.pdf', 'size' => '1.1 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK 2023 TATA LAKSANA PERDARAHAN SALURAN CERNA.pdf', 'url' => $baseUrl . '/PNPK 2023 TATA LAKSANA PERDARAHAN SALURAN CERNA.pdf', 'size' => '1.2 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK DM TIPE 2 DEWASA 2020.pdf', 'url' => $baseUrl . '/PNPK DM TIPE 2 DEWASA 2020.pdf', 'size' => '1.9 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK KARIES GIGI 2025.pdf', 'url' => $baseUrl . '/PNPK KARIES GIGI 2025.pdf', 'size' => '4.6 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK OTITIS MEDIA SUPURATIF KRONIK.pdf', 'url' => $baseUrl . '/PNPK OTITIS MEDIA SUPURATIF KRONIK.pdf', 'size' => '1.4 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK PELAYANAN KEDOKTERAN JIWA 2015.pdf', 'url' => $baseUrl . '/PNPK PELAYANAN KEDOKTERAN JIWA 2015.pdf', 'size' => '4.3 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK PENATAKSANAAN FRAKTUR.pdf', 'url' => $baseUrl . '/PNPK PENATAKSANAAN FRAKTUR.pdf', 'size' => '863 KB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK PENYAKIT JARINGAN PULPA DAN PERIRADIKULER 2023.pdf', 'url' => $baseUrl . '/PNPK PENYAKIT JARINGAN PULPA DAN PERIRADIKULER 2023.pdf', 'size' => '23.8 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK PERDOSRI SPM REHAB MEDIK.pdf', 'url' => $baseUrl . '/PNPK PERDOSRI SPM REHAB MEDIK.pdf', 'size' => '481 KB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK RINOSINUSITIS KRONIK.pdf', 'url' => $baseUrl . '/PNPK RINOSINUSITIS KRONIK.pdf', 'size' => '1.8 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA ANESTESIOLOGI DAN TERAPI INTENSIF 2022.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA ANESTESIOLOGI DAN TERAPI INTENSIF 2022.pdf', 'size' => '2.1 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA BATU SALURAN KEMIH.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA BATU SALURAN KEMIH.pdf', 'size' => '652 KB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA CEDERA OTAK TRAUMATIK 2022.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA CEDERA OTAK TRAUMATIK 2022.pdf', 'size' => '1.1 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA DERMATITIS SEBOROIK.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA DERMATITIS SEBOROIK.pdf', 'size' => '892 KB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA DM PADA ANAK.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA DM PADA ANAK.pdf', 'size' => '2.1 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA EPILEPSI PADA ANAK.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA EPILEPSI PADA ANAK.pdf', 'size' => '695 KB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA GAGAL JANTUNG PADA ANAK.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA GAGAL JANTUNG PADA ANAK.pdf', 'size' => '1.8 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA GAGAL JANTUNG.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA GAGAL JANTUNG.pdf', 'size' => '4.0 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA GLAUKOMA 2023.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA GLAUKOMA 2023.pdf', 'size' => '1.4 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA HEPATITIS B.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA HEPATITIS B.pdf', 'size' => '1.0 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATALAKSANA HEPATITIS C.pdf', 'url' => $baseUrl . '/PNPK TATALAKSANA HEPATITIS C.pdf', 'size' => '3.0 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA HIPERBILIRUBINEMIA.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA HIPERBILIRUBINEMIA.pdf', 'size' => '1.6 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA HIPERTENSI DEWASA.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA HIPERTENSI DEWASA.pdf', 'size' => '3.0 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA HIPERTENSI PADA ANAK.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA HIPERTENSI PADA ANAK.pdf', 'size' => '1.2 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATALAKSANA HIV 2019.pdf', 'url' => $baseUrl . '/PNPK TATALAKSANA HIV 2019.pdf', 'size' => '1.5 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA INFEKSI DENGUE ANAK DAN REMAJA.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA INFEKSI DENGUE ANAK DAN REMAJA.pdf', 'size' => '2.0 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA INFEKSI DENGUE PADA DEWASA 2020.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA INFEKSI DENGUE PADA DEWASA 2020.pdf', 'size' => '705 KB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATALAKSANA INFEKSI INTRA ABDOMINAL.pdf', 'url' => $baseUrl . '/PNPK TATALAKSANA INFEKSI INTRA ABDOMINAL.pdf', 'size' => '2.4 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA KARIES GIGI.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA KARIES GIGI.pdf', 'size' => '2.4 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA KATARAK PADA DEWASA 2018.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA KATARAK PADA DEWASA 2018.pdf', 'size' => '694 KB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA KOMPIKASI KEHAMILAN 2017.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA KOMPIKASI KEHAMILAN 2017.pdf', 'size' => '1.6 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA KUSTA.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA KUSTA.pdf', 'size' => '1.1 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA LUKA BAKAR.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA LUKA BAKAR.pdf', 'size' => '1.9 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANAN ANGINA PECTORIS STABIL.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANAN ANGINA PECTORIS STABIL.pdf', 'size' => '591 KB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA NYERI.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA NYERI.pdf', 'size' => '1.1 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA OSTEOPOROSIS 2023.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA OSTEOPOROSIS 2023.pdf', 'size' => '1.5 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA OSTEOSARKOMA.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA OSTEOSARKOMA.pdf', 'size' => '701 KB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA PENYAKIT GINJAL KRONIK 2023.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA PENYAKIT GINJAL KRONIK 2023.pdf', 'size' => '2.0 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA PENYAKIT GINJAL TAHAP AKHIR.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA PENYAKIT GINJAL TAHAP AKHIR.pdf', 'size' => '2.2 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA PNEUMONIA PADA DEWASA 2023.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA PNEUMONIA PADA DEWASA 2023.pdf', 'size' => '981 KB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA PPOK.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA PPOK.pdf', 'size' => '894 KB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA REHAB MEDIK.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA REHAB MEDIK.pdf', 'size' => '20.6 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA RETINOBLASTOMA.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA RETINOBLASTOMA.pdf', 'size' => '1.7 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA RETINOPATI DIABETIKA.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA RETINOPATI DIABETIKA.pdf', 'size' => '779 KB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA SEPSIS 2017.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA SEPSIS 2017.pdf', 'size' => '2.3 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA SEPSIS PADA ANAK 2021.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA SEPSIS PADA ANAK 2021.pdf', 'size' => '1.1 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA SINDROMA KORONER AKUT.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA SINDROMA KORONER AKUT.pdf', 'size' => '539 KB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA STROKE 2019.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA STROKE 2019.pdf', 'size' => '739 KB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA STUNTING 2022.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA STUNTING 2022.pdf', 'size' => '1.3 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK_TATA_LAKSANA_TINDAKAN_RESUSITASI,_STABILISASI,_DAN_TRANSPOR.pdf', 'url' => $baseUrl . '/PNPK_TATA_LAKSANA_TINDAKAN_RESUSITASI,_STABILISASI,_DAN_TRANSPOR.pdf', 'size' => '2.0 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA TONSILITIS.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA TONSILITIS.pdf', 'size' => '535 KB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA TRAUMA.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA TRAUMA.pdf', 'size' => '2.9 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA TUBERKULOSIS 2019.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA TUBERKULOSIS 2019.pdf', 'size' => '1.1 MB', 'modified' => 'Oct 26 15:37'],
                ['name' => 'PNPK TATA LAKSANA TULI SENSORINEURAL KONGENITAL.pdf', 'url' => $baseUrl . '/PNPK TATA LAKSANA TULI SENSORINEURAL KONGENITAL.pdf', 'size' => '3.1 MB', 'modified' => 'Oct 26 15:37'],
            ],
            '03.TKMKB' => [
                ['name' => 'Kepmenkes_nomor_HK_01_07_MENKES_1186_2022_PPK_Dokter_FKTP_Mei_2022.pdf', 'url' => $baseUrl . '/Kepmenkes_nomor_HK_01_07_MENKES_1186_2022_PPK_Dokter_FKTP_Mei_2022.pdf', 'size' => '18.9 MB', 'modified' => 'Oct 26 15:47'],
                ['name' => 'TKMKB BPJS PUSAT 2025 - ASFIKSIA.pdf', 'url' => $baseUrl . '/TKMKB BPJS PUSAT 2025 - ASFIKSIA.pdf', 'size' => '6.3 MB', 'modified' => 'Oct 26 15:47'],
                ['name' => 'TKMKB BPJS PUSAT 2025 - ASMA.pdf', 'url' => $baseUrl . '/TKMKB BPJS PUSAT 2025 - ASMA.pdf', 'size' => '6.2 MB', 'modified' => 'Oct 26 15:47'],
                ['name' => 'TKMKB BPJS PUSAT 2025 - DHF.pdf', 'url' => $baseUrl . '/TKMKB BPJS PUSAT 2025 - DHF.pdf', 'size' => '6.2 MB', 'modified' => 'Oct 26 15:47'],
                ['name' => 'TKMKB BPJS PUSAT 2025 - ESWL.pdf', 'url' => $baseUrl . '/TKMKB BPJS PUSAT 2025 - ESWL.pdf', 'size' => '6.2 MB', 'modified' => 'Oct 26 15:47'],
                ['name' => 'TKMKB BPJS PUSAT 2025 - GE.pdf', 'url' => $baseUrl . '/TKMKB BPJS PUSAT 2025 - GE.pdf', 'size' => '6.2 MB', 'modified' => 'Oct 26 15:47'],
                ['name' => 'TKMKB BPJS PUSAT 2025 - GERD.pdf', 'url' => $baseUrl . '/TKMKB BPJS PUSAT 2025 - GERD.pdf', 'size' => '6.3 MB', 'modified' => 'Oct 26 15:47'],
                ['name' => 'TKMKB BPJS PUSAT 2025 - ICTERUS.pdf', 'url' => $baseUrl . '/TKMKB BPJS PUSAT 2025 - ICTERUS.pdf', 'size' => '6.2 MB', 'modified' => 'Oct 26 15:47'],
                ['name' => 'TKMKB BPJS PUSAT 2025 - KATARAK.pdf', 'url' => $baseUrl . '/TKMKB BPJS PUSAT 2025 - KATARAK.pdf', 'size' => '6.2 MB', 'modified' => 'Oct 26 15:47'],
                ['name' => 'TKMKB BPJS PUSAT 2025 - PCI.pdf', 'url' => $baseUrl . '/TKMKB BPJS PUSAT 2025 - PCI.pdf', 'size' => '6.3 MB', 'modified' => 'Oct 26 15:47'],
                ['name' => 'TKMKB BPJS PUSAT 2025 - PNEUMONIA DEWASA.pdf', 'url' => $baseUrl . '/TKMKB BPJS PUSAT 2025 - PNEUMONIA DEWASA.pdf', 'size' => '6.3 MB', 'modified' => 'Oct 26 15:47'],
                ['name' => 'TKMKB BPJS PUSAT 2025 - SEPSIS.pdf', 'url' => $baseUrl . '/TKMKB BPJS PUSAT 2025 - SEPSIS.pdf', 'size' => '6.2 MB', 'modified' => 'Oct 26 15:47'],
                ['name' => 'TKMKB BPJS PUSAT 2025 - STROKE.pdf', 'url' => $baseUrl . '/TKMKB BPJS PUSAT 2025 - STROKE.pdf', 'size' => '6.4 MB', 'modified' => 'Oct 26 15:47'],
                ['name' => 'TKMKB BPJS PUSAT 2025 - TF.pdf', 'url' => $baseUrl . '/TKMKB BPJS PUSAT 2025 - TF.pdf', 'size' => '6.2 MB', 'modified' => 'Oct 26 15:47'],
                ['name' => 'TKMKB BPJS PUSAT 2025- TF.pdf', 'url' => $baseUrl . '/TKMKB BPJS PUSAT 2025- TF.pdf', 'size' => '6.2 MB', 'modified' => 'Oct 26 15:47'],
                ['name' => 'TKMKB BPJS PUSAT 2025 - VENTILATOR.pdf', 'url' => $baseUrl . '/TKMKB BPJS PUSAT 2025 - VENTILATOR.pdf', 'size' => '6.2 MB', 'modified' => 'Oct 26 15:47'],
            ]
        ];

        return $sampleDocs[$folder] ?? [];
    }

    /**
     * Alternative parsing method using regex
     */
    private function parseDocumentsAlternative($html, $folder)
    {
        $documents = [];

        // Pattern to match table rows with links
        preg_match_all('/<tr[^>]*>([\s\S]*?)<\/tr>/i', $html, $rows);

        foreach ($rows[1] as $rowHtml) {
            // Extract link from row
            preg_match('/<a[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $rowHtml, $linkMatch);

            if (isset($linkMatch[1]) && isset($linkMatch[2])) {
                $href = trim($linkMatch[1]);
                $text = trim(strip_tags($linkMatch[2]));

                // Check if it's a PDF file
                if (preg_match('/\.pdf$/i', $href) || preg_match('/\.pdf/i', $href)) {
                    // Extract size and modified date from row
                    preg_match_all('/<td[^>]*>([^<]+)<\/td>/i', $rowHtml, $cells);

                    $size = '';
                    $modified = '';

                    if (isset($cells[1]) && count($cells[1]) >= 3) {
                        $size = trim($cells[1][1] ?? '');
                        $modified = trim($cells[1][2] ?? '');
                    }

                    // Build full URL if relative
                    if (strpos($href, 'http') !== 0) {
                        if (strpos($href, '/') === 0) {
                            $docUrl = str_replace('/webapps/perpustakaan', '', $this->baseUrl) . $href;
                        } else {
                            $docUrl = $this->baseUrl . '/' . urlencode($folder) . '/' . $href;
                        }
                    } else {
                        $docUrl = $href;
                    }

                    $documents[] = [
                        'name' => $text ?: basename($href),
                        'url' => $docUrl,
                        'size' => $size,
                        'modified' => $modified
                    ];
                }
            }
        }

        return $documents;
    }

    /**
     * Parse documents from HTML
     */
    private function parseDocumentsFromHtml($html, $folder)
    {
        $documents = [];

        // Create DOMDocument to parse HTML
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        // Find all links in the HTML
        $links = $xpath->query('//a[@href]');

        foreach ($links as $link) {
            /** @var \DOMElement $link */
            $href = $link->getAttribute('href');
            $text = trim($link->textContent);

            // Check if it's a PDF file or seems to be a document
            if (preg_match('/\.pdf$/i', $href)) {
                // Build full URL if relative
                if (strpos($href, 'http') !== 0) {
                    // Handle relative URLs
                    if (strpos($href, '/') === 0) {
                        // Absolute path from server root
                        $docUrl = str_replace('/webapps/perpustakaan', '', $this->baseUrl) . $href;
                    } else {
                        // Relative to current folder
                        $docUrl = $this->baseUrl . '/' . urlencode($folder) . '/' . $href;
                    }
                } else {
                    $docUrl = $href;
                }

                // Find parent row to get size and date info
                $row = $link;
                while ($row && strtolower($row->nodeName) !== 'tr') {
                    $row = $row->parentNode;
                }

                $size = '';
                $modified = '';

                if ($row) {
                    $cells = $xpath->query('td', $row);
                    if ($cells->length >= 3) {
                        $size = trim($cells->item(1)->textContent ?? '');
                        $modified = trim($cells->item(2)->textContent ?? '');
                    }
                }

                $documents[] = [
                    'name' => $text ?: basename($href),
                    'url' => $docUrl,
                    'size' => $size,
                    'modified' => $modified
                ];
            }
        }

        // Remove duplicates
        $uniqueDocuments = [];
        $seenUrls = [];

        foreach ($documents as $doc) {
            if (!in_array($doc['url'], $seenUrls)) {
                $uniqueDocuments[] = $doc;
                $seenUrls[] = $doc['url'];
            }
        }

        return $uniqueDocuments;
    }

    /**
     * Stream document directly
     */
    public function streamDocument(Request $request)
    {
        $url = $request->get('url');

        if (!$url) {
            return response('URL not provided', 400);
        }

        try {
            $response = Http::timeout(30)
                ->withOptions(['stream' => true])
                ->get($url);

            if ($response->successful()) {
                return response($response->body(), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="document.pdf"',
                ]);
            }

            return response('Failed to fetch document', 500);
        } catch (\Exception $e) {
            Log::error('Error streaming document: ' . $e->getMessage());
            return response('Error streaming document', 500);
        }
    }
}
