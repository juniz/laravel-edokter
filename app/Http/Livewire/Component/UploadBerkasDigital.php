<?php

namespace App\Http\Livewire\Component;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Jantinnerezo\LivewireAlert\LivewireAlert;

use Imagick;
use ImagickException;

class UploadBerkasDigital extends Component
{
    use WithFileUploads, LivewireAlert;

    public $file;
    public $fileName;
    public $masterBerkas;
    public $kdBerkas;
    public $noRawat;
    public function mount($noRawat)
    {
        $this->noRawat = $noRawat;
        $this->kdBerkas = "B00";
        $this->masterBerkas = DB::table('master_berkas_digital')->get();
    }

    public function hydrate()
    {
        $this->masterBerkas = DB::table('master_berkas_digital')->get();
    }

    public function uploadFile()
    {
        $this->validate([
            'file' => 'required|file|max:10240|mimes:pdf,jpeg,png,gif,jpg', // Validate file
            // 'fileName' => 'required|string|max:255', // Validate file name
            'kdBerkas' => 'required', // Validate file name
        ], [
            'file' => 'File tidak boleh kosong',
            // 'fileName' => 'Nama file tidak boleh kosong',
            'kdBerkas' => 'Kode berkas tidak boleh kosong',
        ]);

        try {
            $extension = strtolower($this->file->getClientOriginalExtension());
            $mime = $this->file->getMimeType();

            if ($extension === 'pdf' || $mime === 'application/pdf') {
                // PDF already in PDF format, no need for Ghostscript / Imagick rasterization
                $pdfBase64 = base64_encode(file_get_contents($this->file->getRealPath()));
            } else {
                // Convert image to PDF format using Imagick
                $imagick = new Imagick();

                // Read image from temporary path
                $imagick->readImage($this->file->getRealPath());

                // Set white background for transparent images
                $imagick->setImageBackgroundColor('white');
                $imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);

                // Convert to PDF format
                $imagick->setImageFormat('pdf');

                // Set PDF page size to A4
                $imagick->setImageResolution(300, 300);
                $imagick->resizeImage(2480, 3508, Imagick::FILTER_LANCZOS, 1, true);

                // Convert binary data to base64
                $pdfBase64 = base64_encode($imagick->getImagesBlob());

                // Clean up
                $imagick->clear();
                $imagick->destroy();
            }

            $fileName = $this->fileName;
            if (empty($fileName)) {
                $fileName = pathinfo($this->file->getClientOriginalName(), PATHINFO_FILENAME);
            }
            $fileName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $fileName);
            if (empty($fileName)) {
                $fileName = 'BERKAS_' . str_replace('/', '-', $this->noRawat) . '_' . date('YmdHis');
            }

            $payload = [
                "file" => $pdfBase64,
                "no_rawat" => $this->noRawat,
                "kd_berkas" => $this->kdBerkas,
                "nama_file" => $fileName,
            ];

            $response = Http::post("https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php", $payload);

            if (!$response->successful()) {
                throw new \Exception('Gagal menghubungi server webapps upload: ' . $response->status());
            }

            $resJson = $response->json();
            if (!$resJson || !isset($resJson['success']) || !$resJson['success']) {
                $errorMsg = $resJson['message'] ?? 'Gagal upload berkas di server webapps';
                throw new \Exception($errorMsg);
            }

            // Reset the inputs
            $this->reset(['file', 'fileName']);

            // Close the modal
            $this->dispatchBrowserEvent('close-modal', ['id' => 'modal-upload-berkas']);
            $this->emit('close-modal', ['id' => 'modal-upload-berkas']);
            $this->alert('success', 'Berkas berhasil diupload');
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal Upload', [
                'position' => 'center',
                'timer' => 3000,
                'text' => $e->getMessage(),
                'toast' => false,
                'showCancelButton' => false,
                'showConfirmButton' => true,
                'confirmButtonText' => 'OK',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.component.upload-berkas-digital');
    }
}
