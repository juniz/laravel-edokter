<?php

namespace Tests\Feature;

use App\Http\Controllers\PenilaianPreInduksiController;
use App\Services\PenilaianPreInduksiService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PenilaianPreInduksiControllerTest extends TestCase
{
    public function test_download_pdf_missing_no_rawat_aborts_400()
    {
        $controller = new PenilaianPreInduksiController();
        $request = Request::create('/penilaian-pre-induksi/download-pdf', 'GET', []);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Parameter no_rawat is required.');

        $controller->downloadPdf($request);
    }

    public function test_download_pdf_success()
    {
        $controller = new PenilaianPreInduksiController();
        $request = Request::create('/penilaian-pre-induksi/download-pdf', 'GET', [
            'no_rawat' => '2026/09/16/000001',
            'tanggal' => '2026-09-16 08:00:00',
        ]);

        $mockService = $this->createMock(PenilaianPreInduksiService::class);
        $mockService->expects($this->once())
            ->method('generatePdf')
            ->with('2026/09/16/000001', '2026-09-16 08:00:00')
            ->willReturn('%PDF-1.4 mock binary');

        $response = $controller->downloadPdf($request, $mockService);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('inline; filename="PENILAIAN_PRE_INDUKSI_2026-09-16-000001_', $response->headers->get('Content-Disposition'));
        $this->assertEquals('%PDF-1.4 mock binary', $response->getContent());
    }

    public function test_upload_pdf_missing_no_rawat_returns_400_json()
    {
        $controller = new PenilaianPreInduksiController();
        $request = Request::create('/penilaian-pre-induksi/upload-pdf', 'POST', []);

        $response = $controller->uploadPdf($request);

        $this->assertEquals(400, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Parameter no_rawat is required.', $data['message']);
    }

    public function test_upload_pdf_success()
    {
        $controller = new PenilaianPreInduksiController();
        $request = Request::create('/penilaian-pre-induksi/upload-pdf', 'POST', [
            'no_rawat' => '2026/09/16/000001',
            'tanggal' => '2026-09-16 08:00:00',
        ]);

        $mockService = $this->createMock(PenilaianPreInduksiService::class);
        $mockService->expects($this->once())
            ->method('generateAndUploadPdf')
            ->with('2026/09/16/000001', '2026-09-16 08:00:00')
            ->willReturn([
                'success' => true,
                'message' => 'Berkas penilaian pre induksi berhasil disimpan dan diunggah.',
                'pdf_url' => 'https://simrs.rsbhayangkaranganjuk.com/webapps/berkasrawat/pages/upload/PENILAIAN_PRE_INDUKSI_2026-09-16-000001_20260916080000.pdf',
                'is_replaced' => false,
            ]);

        $response = $controller->uploadPdf($request, $mockService);

        $this->assertEquals(200, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Berkas penilaian pre induksi berhasil disimpan dan diunggah.', $data['message']);
        $this->assertEquals('https://simrs.rsbhayangkaranganjuk.com/webapps/berkasrawat/pages/upload/PENILAIAN_PRE_INDUKSI_2026-09-16-000001_20260916080000.pdf', $data['pdf_url']);
        $this->assertFalse($data['is_replaced']);
    }

    public function test_upload_pdf_failure_returns_500()
    {
        $controller = new PenilaianPreInduksiController();
        $request = Request::create('/penilaian-pre-induksi/upload-pdf', 'POST', [
            'no_rawat' => '2026/09/16/000001',
        ]);

        $mockService = $this->createMock(PenilaianPreInduksiService::class);
        $mockService->expects($this->once())
            ->method('generateAndUploadPdf')
            ->with('2026/09/16/000001', null)
            ->willReturn([
                'success' => false,
                'message' => 'Gagal upload berkas di server webapps.',
                'pdf_url' => null,
                'is_replaced' => false,
            ]);

        $response = $controller->uploadPdf($request, $mockService);

        $this->assertEquals(500, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Gagal upload berkas di server webapps.', $data['message']);
    }

    public function test_upload_pdf_exception_returns_500()
    {
        $controller = new PenilaianPreInduksiController();
        $request = Request::create('/penilaian-pre-induksi/upload-pdf', 'POST', [
            'no_rawat' => '2026/09/16/000001',
        ]);

        $mockService = $this->createMock(PenilaianPreInduksiService::class);
        $mockService->expects($this->once())
            ->method('generateAndUploadPdf')
            ->willThrowException(new \RuntimeException('Database connection lost'));

        $response = $controller->uploadPdf($request, $mockService);

        $this->assertEquals(500, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('Database connection lost', $data['message']);
    }
}
