<?php

namespace App\Http\Controllers\Domain\Catalog;

use App\Application\Catalog\CheckoutCatalogService;
use App\Domain\Catalog\Contracts\PlanRepository;
use App\Domain\Catalog\Contracts\ProductRepository;
use App\Http\Controllers\Controller;
use App\Models\SettingApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function __construct(
        private ProductRepository $productRepository,
        private PlanRepository $planRepository,
        private CheckoutCatalogService $checkoutCatalogService
    ) {}

    /**
     * Halaman catalog untuk guest (tidak perlu login)
     */
    public function guest(): Response
    {
        $products = $this->productRepository->findAllActive();

        // Load plans dan features untuk setiap product
        foreach ($products as $product) {
            $product->plans = $this->planRepository->findByProduct($product->id);
            $product->load('features');
        }

        // Get setting dari database
        $setting = SettingApp::first();
        $companyName = $setting?->nama_app ?? config('app.name', 'AbaHost');
        $companyLogo = $setting?->logo ? Storage::url($setting->logo) : null;

        return Inertia::render('catalog/Guest', [
            'products' => $products,
            'companyName' => $companyName,
            'companyLogo' => $companyLogo,
        ]);
    }

    public function index(): Response
    {
        // Jika user belum login, redirect ke halaman guest
        if (! Auth::check()) {
            return $this->guest();
        }

        $products = $this->productRepository->findAllActive();

        return Inertia::render('catalog/Index', [
            'products' => $products,
        ]);
    }

    public function show(string $slug): Response
    {
        $product = $this->productRepository->findBySlug($slug);

        if (! $product) {
            abort(404);
        }

        $plans = $this->planRepository->findByProduct($product->id);
        $product->load('features');

        return Inertia::render('catalog/Show', [
            'product' => $product,
            'plans' => $plans,
        ]);
    }

    /**
     * Halaman detail produk untuk guest (tidak perlu login)
     */
    public function guestShow(string $slug): Response
    {
        $product = $this->productRepository->findBySlug($slug);

        if (! $product) {
            abort(404);
        }

        $plans = $this->planRepository->findByProduct($product->id);
        $product->load('features');

        // Get setting dari database
        $setting = SettingApp::first();
        $companyName = $setting?->nama_app ?? config('app.name', 'AbaHost');
        $companyLogo = $setting?->logo ? Storage::url($setting->logo) : null;

        return Inertia::render('catalog/GuestShow', [
            'product' => $product,
            'plans' => $plans,
            'companyName' => $companyName,
            'companyLogo' => $companyLogo,
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'plan_id' => ['required', 'string'],
            'payment_method' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! $user) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu untuk melakukan pembayaran.');
        }

        $customer = $user->customer;

        if (! $customer) {
            return redirect()->back()
                ->withErrors(['error' => 'Customer profile tidak ditemukan. Silakan lengkapi profil Anda.']);
        }

        try {
            $payment = $this->checkoutCatalogService->execute($customer, [
                'plan_id' => $request->plan_id,
                'payment_method' => $request->payment_method,
            ]);

            // Redirect ke halaman payment
            // Setelah redirect, halaman pembayaran akan otomatis refresh setiap 5 detik
            // untuk mengecek status pembayaran. Setelah webhook Midtrans mengirim notifikasi,
            // status akan terupdate secara real-time (maksimal 5 detik delay)
            return redirect()->route('customer.payments.show', $payment->id)
                ->with('success', 'Pembayaran berhasil dibuat. Silakan selesaikan pembayaran Anda.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }
}
