<?php

namespace App\Http\Controllers\Domain\Catalog;

use App\Domain\Catalog\Contracts\PlanRepository;
use App\Http\Controllers\Controller;
use App\Models\Domain\Catalog\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function __construct(
        private PlanRepository $planRepository
    ) {}

    public function index(Request $request): Response
    {
        $query = \App\Models\Domain\Catalog\Plan::with('product');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $plans = $query->latest()->paginate(15);
        $products = Product::where('status', 'active')->get();

        return Inertia::render('admin/plans/Index', [
            'plans' => $plans,
            'products' => $products,
            'filters' => $request->only('product_id'),
        ]);
    }

    public function create(): Response
    {
        $products = Product::where('status', 'active')->get();

        return Inertia::render('admin/plans/Form', [
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'string'],
            'code' => ['required', 'string', 'max:255', 'unique:plans,code'],
            'price_cents' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'trial_days' => ['nullable', 'integer', 'min:0'],
            'setup_fee_cents' => ['nullable', 'integer', 'min:0'],
            'duration_1_month_enabled' => ['required', 'boolean'],
            'duration_12_months_enabled' => ['required', 'boolean'],
        ]);

        $plan = $this->planRepository->create($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan berhasil dibuat.');
    }

    public function show(string $id): Response
    {
        $plan = $this->planRepository->findByUlid($id);

        if (! $plan) {
            abort(404);
        }

        return Inertia::render('admin/plans/Show', [
            'plan' => $plan->load(['product', 'features']),
        ]);
    }

    public function edit(string $id): Response
    {
        $plan = $this->planRepository->findByUlid($id);

        if (! $plan) {
            abort(404);
        }

        $products = Product::where('status', 'active')->get();

        return Inertia::render('admin/plans/Form', [
            'plan' => $plan,
            'products' => $products,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $plan = $this->planRepository->findByUlid($id);

        if (! $plan) {
            abort(404);
        }

        $validated = $request->validate([
            'product_id' => ['required', 'string'],
            'code' => ['required', 'string', 'max:255', 'unique:plans,code,'.$id],
            'price_cents' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'trial_days' => ['nullable', 'integer', 'min:0'],
            'setup_fee_cents' => ['nullable', 'integer', 'min:0'],
            'duration_1_month_enabled' => ['required', 'boolean'],
            'duration_12_months_enabled' => ['required', 'boolean'],
        ]);

        $plan->update($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $plan = $this->planRepository->findByUlid($id);

        if (! $plan) {
            abort(404);
        }

        $plan->delete();

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan berhasil dihapus.');
    }
}
