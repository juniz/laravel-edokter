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
            // Create new Imagick instance
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

            // Create PDF path
            // $pdfPath = 'uploads/' . pathinfo($this->fileName, PATHINFO_FILENAME) . '.pdf';

            // // Store the PDF file
            // Storage::disk('public')->put($pdfPath, $imagick->getImagesBlob());

            // $path = $pdfPath;

            // Convert binary data to base64
            $pdfBase64 = base64_encode($imagick->getImagesBlob());
            // Storage::disk('public')->put($pdfPath, base64_decode($pdfBase64));

            $payload = [
                "file" => $pdfBase64,
                "no_rawat" => $this->noRawat,
                "kd_berkas" => $this->kdBerkas,
                "nama_file" => $this->fileName,
            ];

            // dd($payload);

            $response = Http::post("https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php", $payload);

            // dd($response->json());

            // Clean up
            $imagick->clear();
            $imagick->destroy();

            // dd($response->json());
            if (!$response->json()['success']) {
                throw new \Exception($response->json()['message']);
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

    public function updatedKdBerkas()
    {
        dd($this->kdBerkas);
    }

    public function render()
    {
        return view('livewire.component.upload-berkas-digital');
    }
}
