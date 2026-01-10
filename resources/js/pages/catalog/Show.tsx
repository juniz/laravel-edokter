import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import InputError from '@/components/input-error';
import {
    Wallet,
    Building2,
    ShoppingCart,
    Check,
    ArrowLeft,
    CheckCircle2,
    Star,
    Zap,
    Shield,
    Clock,
    Server,
    HardDrive,
    Globe,
    Package,
    ArrowRight,
} from 'lucide-react';

interface Plan {
    id: string;
    code: string;
    billing_cycle: string;
    price_cents: number;
    currency: string;
    trial_days?: number;
    setup_fee_cents: number;
}

interface ProductFeature {
    id: string;
    key: string;
    value: string;
    label?: string;
    unit?: string;
    display_order?: number;
}

interface Product {
    id: string;
    name: string;
    slug: string;
    type: string;
    status: string;
    metadata?: {
        description?: string;
        features?: string[];
    };
    features?: ProductFeature[];
    plans?: Plan[];
}

interface CatalogShowProps {
    product: Product;
    plans: Plan[];
}

function getProductTypeConfig(type: string) {
    const config: Record<string, { icon: React.ElementType; color: string; bgColor: string; label: string }> = {
        hosting_shared: {
            icon: HardDrive,
            color: 'text-blue-600',
            bgColor: 'from-blue-500 to-cyan-500',
            label: 'Shared Hosting',
        },
        vps: {
            icon: Server,
            color: 'text-purple-600',
            bgColor: 'from-purple-500 to-pink-500',
            label: 'VPS',
        },
        addon: {
            icon: Package,
            color: 'text-emerald-600',
            bgColor: 'from-emerald-500 to-green-500',
            label: 'Addon',
        },
        domain: {
            icon: Globe,
            color: 'text-orange-600',
            bgColor: 'from-orange-500 to-amber-500',
            label: 'Domain',
        },
    };
    return config[type] || {
        icon: Package,
        color: 'text-gray-600',
        bgColor: 'from-gray-500 to-slate-500',
        label: type.replace('_', ' ').toUpperCase(),
    };
}

export default function CatalogShow({ product, plans }: CatalogShowProps) {
    const [isPaymentModalOpen, setIsPaymentModalOpen] = useState(false);
    const [selectedPlan, setSelectedPlan] = useState<Plan | null>(null);
    const [selectedPaymentMethod, setSelectedPaymentMethod] = useState('bca_va');
    const [isSubmittingCheckout, setIsSubmittingCheckout] = useState(false);

    const typeConfig = getProductTypeConfig(product.type);
    const TypeIcon = typeConfig.icon;

  const addToCartForm = useForm({
    product_id: product.id,
    plan_id: '',
    qty: 1,
  });

    const checkoutForm = useForm({
        plan_id: '',
            payment_method: 'bca_va',
    });

    const formatPrice = (cents: number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(cents);
    };

  const handleAddToCart = (plan: Plan) => {
    addToCartForm.transform((data) => ({
      ...data,
      plan_id: plan.id,
    }));

    addToCartForm.post(route('customer.cart.add'), {
      preserveScroll: true,
    });
  };

    const handleOpenPaymentModal = (plan: Plan) => {
        setSelectedPlan(plan);
        setIsPaymentModalOpen(true);
    };

    const handleClosePaymentModal = () => {
        // Reset semua state saat modal ditutup
        setIsPaymentModalOpen(false);
        setSelectedPlan(null);
        setSelectedPaymentMethod('bca_va');
        setIsSubmittingCheckout(false);
    };

    const handleCheckout = (e: React.FormEvent) => {
        e.preventDefault();
        
        // Prevent duplicate submission
        if (!selectedPlan || isSubmittingCheckout || checkoutForm.processing) {
            return;
        }

        // Set flag untuk mencegah multiple submissions
        setIsSubmittingCheckout(true);

        checkoutForm.transform((data) => ({
            ...data,
            plan_id: selectedPlan.id,
            payment_method: selectedPaymentMethod,
        }));

        checkoutForm.post(route('catalog.checkout'), {
            onSuccess: () => {
                // Backend akan redirect ke halaman pembayaran (payments.show)
                // Halaman pembayaran akan otomatis refresh setiap 5 detik untuk mengecek status pembayaran
                // Setelah webhook Midtrans mengirim notifikasi, status akan terupdate secara real-time
                setIsPaymentModalOpen(false);
                setIsSubmittingCheckout(false);
            },
            onError: (errors: Record<string, string>) => {
                console.error('Checkout failed:', errors);
                // Reset flag jika terjadi error agar user bisa coba lagi
                setIsSubmittingCheckout(false);
            },
            onFinish: () => {
                // Reset flag setelah request selesai (success atau error)
                setIsSubmittingCheckout(false);
            },
        });
    };

    const getPaymentLogo = (method: string): string | null => {
        const logos: Record<string, string> = {
            // Bank
            bca_va: '/images/payment/bank/bca.png',
            bni_va: '/images/payment/bank/bni.png',
            bri_va: '/images/payment/bank/bri.png',
            mandiri_va: '/images/payment/bank/mandiri.png',
            permata_va: '/images/payment/bank/permata.png',
            // E-Wallet
            gopay: '/images/payment/wallet/gopay.png',
            shopeepay: '/images/payment/wallet/shopeepay.png',
            dana: '/images/payment/wallet/dana.png',
            ovo: '/images/payment/wallet/ovo.png',
            linkaja: '/images/payment/wallet/linkaja.png',
        };
        return logos[method] || null;
    };

    const paymentMethods = [
        {
            category: 'Bank Transfer',
            methods: [
                { value: 'bca_va', label: 'BCA Virtual Account', icon: Building2 },
                { value: 'bni_va', label: 'BNI Virtual Account', icon: Building2 },
                { value: 'bri_va', label: 'BRI Virtual Account', icon: Building2 },
                { value: 'mandiri_va', label: 'Mandiri Virtual Account', icon: Building2 },
            ],
        },
        {
            category: 'E-Wallet',
            methods: [
                { value: 'gopay', label: 'GoPay', icon: Wallet },
                { value: 'shopeepay', label: 'ShopeePay', icon: Wallet },
                { value: 'dana', label: 'DANA', icon: Wallet },
                { value: 'ovo', label: 'OVO', icon: Wallet },
            ],
        },
    ];

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Catalog', href: '/catalog' },
        { title: product.name, href: route('catalog.show', product.slug) },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={product.name} />
            <div className="p-4 lg:p-6 space-y-6">
                {/* Header */}
                <div className="flex flex-col lg:flex-row lg:items-start gap-6">
                    <div className={`h-20 w-20 rounded-2xl bg-gradient-to-br ${typeConfig.bgColor} flex items-center justify-center flex-shrink-0`}>
                        <TypeIcon className="h-10 w-10 text-white" />
                    </div>
                    <div className="flex-1">
                        <div className="flex items-center gap-3 mb-2">
                            <Badge variant="outline">{typeConfig.label}</Badge>
                        </div>
                        <h1 className="text-3xl lg:text-4xl font-bold tracking-tight mb-2">
                            {product.name}
                        </h1>
                        {product.metadata?.description && (
                            <p className="text-muted-foreground text-lg max-w-2xl">
                                {product.metadata.description}
                            </p>
                        )}
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Product Features */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Product Features (CPU, RAM, Bandwidth, etc) */}
                        {product.features && product.features.length > 0 && (
                            <Card variant="premium">
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <div className={`h-8 w-8 rounded-lg bg-gradient-to-br ${typeConfig.bgColor} flex items-center justify-center`}>
                                            <Server className="h-4 w-4 text-white" />
                                        </div>
                                        Spesifikasi Teknis
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                        {product.features.map((feature) => (
                                            <div
                                                key={feature.id}
                                                className="p-3 rounded-lg border bg-muted/30"
                                            >
                                                <p className="text-xs text-muted-foreground mb-1">
                                                    {feature.label || feature.key}
                                                </p>
                                                <p className="text-base font-semibold">
                                                    {feature.value}
                                                    {feature.unit && (
                                                        <span className="text-sm text-muted-foreground font-normal ml-1">
                                                            {feature.unit}
                                                        </span>
                                                    )}
                                                </p>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Metadata Features (descriptive features) */}
                        {product.metadata?.features && product.metadata.features.length > 0 && (
                            <Card variant="premium">
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <div className={`h-8 w-8 rounded-lg bg-gradient-to-br ${typeConfig.bgColor} flex items-center justify-center`}>
                                            <CheckCircle2 className="h-4 w-4 text-white" />
                                        </div>
                                        Fitur Yang Anda Dapatkan
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        {product.metadata.features.map((feature, idx) => (
                                            <div
                                                key={idx}
                                                className="flex items-start gap-3 p-3 rounded-lg bg-muted/50"
                                            >
                                                <CheckCircle2 className="h-5 w-5 text-emerald-600 mt-0.5 flex-shrink-0" />
                                                <span className="text-sm">{feature}</span>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Why Choose Us */}
                        <Card variant="premium">
                            <CardHeader>
                                <CardTitle>Mengapa Memilih Kami?</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div className="text-center p-4">
                                        <div className="h-12 w-12 rounded-xl bg-gradient-to-br from-emerald-500 to-green-500 flex items-center justify-center mx-auto mb-3">
                                            <Shield className="h-6 w-6 text-white" />
                                        </div>
                                        <p className="font-semibold text-sm">SSL Gratis</p>
                                        <p className="text-xs text-muted-foreground">Untuk semua domain</p>
                                    </div>
                                    <div className="text-center p-4">
                                        <div className="h-12 w-12 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center mx-auto mb-3">
                                            <Clock className="h-6 w-6 text-white" />
                                        </div>
                                        <p className="font-semibold text-sm">Uptime 99.9%</p>
                                        <p className="text-xs text-muted-foreground">Jaminan SLA</p>
                                    </div>
                                    <div className="text-center p-4">
                                        <div className="h-12 w-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center mx-auto mb-3">
                                            <Zap className="h-6 w-6 text-white" />
                                        </div>
                                        <p className="font-semibold text-sm">NVMe SSD</p>
                                        <p className="text-xs text-muted-foreground">10x lebih cepat</p>
                                    </div>
                                    <div className="text-center p-4">
                                        <div className="h-12 w-12 rounded-xl bg-gradient-to-br from-orange-500 to-amber-500 flex items-center justify-center mx-auto mb-3">
                                            <Star className="h-6 w-6 text-white" />
                                        </div>
                                        <p className="font-semibold text-sm">Support 24/7</p>
                                        <p className="text-xs text-muted-foreground">Siap membantu</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Plans */}
                    <div className="space-y-4">
                        <h2 className="text-xl font-bold">Pilih Paket</h2>
                        {plans.map((plan, index) => {
                            const isPopular = index === Math.floor(plans.length / 2);

                            return (
                                <Card
                                    key={plan.id}
                                    variant="premium"
                                    className={`relative overflow-hidden ${isPopular ? 'ring-2 ring-primary' : ''}`}
                                >
                                    {/* Popular badge */}
                                    {isPopular && (
                                        <div className="absolute top-0 right-0 px-3 py-1 bg-gradient-to-r from-[var(--gradient-start)] to-[var(--gradient-end)] text-white text-xs font-semibold rounded-bl-xl">
                                            POPULER
                                        </div>
                                    )}

                                    <CardContent className="p-5">
                                        <div className="mb-4">
                                            <h3 className="font-semibold text-lg">{plan.code}</h3>
                                            <p className="text-sm text-muted-foreground">
                                                {plan.billing_cycle}
                                                {plan.trial_days && (
                                                    <Badge variant="success-soft" className="ml-2">
                                                        {plan.trial_days} hari trial
                                                    </Badge>
                                                )}
                                            </p>
                                        </div>

                                        <div className="mb-4">
                                            <p className="text-3xl font-bold">
                                                {formatPrice(plan.price_cents)}
                                            </p>
                                            {plan.setup_fee_cents > 0 && (
                                                <p className="text-sm text-muted-foreground">
                                                    + Setup fee: {formatPrice(plan.setup_fee_cents)}
                                                </p>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <Button
                                                variant="gradient"
                                                className="w-full"
                                                onClick={() => handleOpenPaymentModal(plan)}
                                                disabled={addToCartForm.processing}
                                            >
                                                <Wallet className="h-4 w-4 mr-2" />
                                                Bayar Sekarang
                                            </Button>
                                            <Button
                                                variant="outline"
                                                className="w-full"
                                                onClick={() => handleAddToCart(plan)}
                                                disabled={addToCartForm.processing}
                                            >
                                                <ShoppingCart className="h-4 w-4 mr-2" />
                                                Tambah ke Cart
                                            </Button>
                                        </div>
                                    </CardContent>
                                </Card>
                            );
                        })}

                        <Link href="/catalog" className="block">
                            <Button variant="ghost" className="w-full">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Kembali ke Catalog
                            </Button>
                        </Link>
                    </div>
                </div>

                {/* Payment Method Modal */}
                <Dialog open={isPaymentModalOpen} onOpenChange={setIsPaymentModalOpen}>
                    <DialogContent className="max-w-4xl max-h-[90vh] flex flex-col p-0">
                        <form onSubmit={handleCheckout} className="flex flex-col h-full max-h-[90vh]">
                            {/* Header */}
                            <div className="px-6 py-4 border-b bg-gradient-to-r from-[var(--gradient-start)]/10 to-[var(--gradient-end)]/10 flex-shrink-0">
                                <DialogHeader>
                                    <DialogTitle className="text-2xl font-bold flex items-center gap-2">
                                        <div className="h-10 w-10 rounded-xl bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)] flex items-center justify-center">
                                            <ShoppingCart className="w-5 h-5 text-white" />
                                        </div>
                                        Checkout
                                    </DialogTitle>
                                    <DialogDescription className="text-base mt-1">
                                        Lengkapi pembayaran untuk mengaktifkan layanan
                                    </DialogDescription>
                                </DialogHeader>
                            </div>

                            {/* Content */}
                            <div className="flex-1 overflow-y-auto min-h-0">
                                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 p-6">
                                    {/* Left - Payment Methods */}
                                    <div className="lg:col-span-2 space-y-4">
                                        <Card variant="premium">
                                            <CardHeader>
                                                <CardTitle className="text-lg">Pilih Metode Pembayaran</CardTitle>
                                            </CardHeader>
                                            <CardContent>
                                                <RadioGroup value={selectedPaymentMethod} onValueChange={setSelectedPaymentMethod}>
                                                    <div className="space-y-4">
                                                        {paymentMethods.map((category) => (
                                                            <div key={category.category}>
                                                                <Label className="text-sm font-semibold text-muted-foreground mb-2 block">
                                                                    {category.category}
                                                                </Label>
                                                                <div className="grid grid-cols-2 gap-2">
                                                                    {category.methods.map((method) => {
                                                                        const Icon = method.icon;
                                                                        const isSelected = selectedPaymentMethod === method.value;
                                                                        const logoPath = getPaymentLogo(method.value);
                                                                        return (
                                                                            <label
                                                                                key={method.value}
                                                                                className={`relative flex items-center justify-center p-4 border-2 rounded-xl cursor-pointer transition-all ${
                                                                                    isSelected
                                                                                        ? 'border-primary bg-primary/5 shadow-md'
                                                                                        : 'border-border hover:border-muted-foreground/30 hover:bg-muted/30'
                                                                                }`}
                                                                            >
                                                                                <RadioGroupItem value={method.value} id={method.value} className="sr-only" />
                                                                                <div className={`flex items-center justify-center p-3 rounded-lg ${
                                                                                    isSelected
                                                                                        ? 'bg-white dark:bg-white/90'
                                                                                        : 'bg-white dark:bg-white/80'
                                                                                }`}>
                                                                                    {logoPath ? (
                                                                                        <img 
                                                                                            src={logoPath} 
                                                                                            alt={method.label}
                                                                                            className="h-12 w-auto object-contain"
                                                                                        />
                                                                                    ) : (
                                                                                        <Icon className={`w-8 h-8 ${isSelected ? 'text-primary' : 'text-muted-foreground'}`} />
                                                                                    )}
                                                                                </div>
                                                                                {isSelected && (
                                                                                    <div className="absolute top-2 right-2">
                                                                                        <div className="w-5 h-5 bg-primary rounded-full flex items-center justify-center">
                                                                                            <Check className="w-3 h-3 text-white" />
                                                                                        </div>
                                                                                    </div>
                                                                                )}
                                                                            </label>
                                                                        );
                                                                    })}
                                                                </div>
                                                            </div>
                                                        ))}
                                                    </div>
                                                </RadioGroup>
                                                <InputError message={checkoutForm.errors.payment_method} className="mt-4" />
                                            </CardContent>
                                        </Card>

                                        {checkoutForm.errors.plan_id && (
                                            <div className="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                                                <InputError message={checkoutForm.errors.plan_id} />
                                            </div>
                                        )}
                                    </div>

                                    {/* Right - Order Summary */}
                                    <div>
                                        <Card className="sticky top-6 bg-muted/30">
                                            <CardHeader>
                                                <CardTitle className="text-lg">Ringkasan Pesanan</CardTitle>
                                            </CardHeader>
                                            <CardContent className="space-y-4">
                                                {selectedPlan && (
                                                    <>
                                                        <div className="flex gap-3">
                                                            <div className={`h-12 w-12 rounded-xl bg-gradient-to-br ${typeConfig.bgColor} flex items-center justify-center flex-shrink-0`}>
                                                                <TypeIcon className="h-6 w-6 text-white" />
                                                            </div>
                                                            <div>
                                                                <p className="font-semibold">{product.name}</p>
                                                                <p className="text-sm text-muted-foreground">{selectedPlan.code}</p>
                                                            </div>
                                                        </div>

                                                        <Separator />

                                                        <div className="space-y-2 text-sm">
                                                            <div className="flex justify-between">
                                                                <span className="text-muted-foreground">Harga Paket</span>
                                                                <span className="font-medium">{formatPrice(selectedPlan.price_cents)}</span>
                                                            </div>
                                                            {selectedPlan.setup_fee_cents > 0 && (
                                                                <div className="flex justify-between">
                                                                    <span className="text-muted-foreground">Biaya Setup</span>
                                                                    <span className="font-medium">{formatPrice(selectedPlan.setup_fee_cents)}</span>
                                                                </div>
                                                            )}
                                                        </div>

                                                        <Separator />

                                                        <div className="flex justify-between items-center">
                                                            <span className="font-semibold">Total</span>
                                                            <span className="text-2xl font-bold text-primary">
                                                                {formatPrice(selectedPlan.price_cents + (selectedPlan.setup_fee_cents || 0))}
                                                            </span>
                                                        </div>

                                                        <Button
                                                            type="submit"
                                                            variant="gradient"
                                                            className="w-full"
                                                            size="lg"
                                                            disabled={checkoutForm.processing || !selectedPlan || isSubmittingCheckout}
                                                        >
                                                            {checkoutForm.processing || isSubmittingCheckout ? (
                                                                <>
                                                                    <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2" />
                                                                    Memproses...
                                                                </>
                                                            ) : (
                                                                <>
                                                                    Bayar Sekarang
                                                                    <ArrowRight className="w-4 h-4 ml-2" />
                                                                </>
                                                            )}
                                                        </Button>

                                                        <Button
                                                            type="button"
                                                            variant="ghost"
                                                            className="w-full"
                                                            onClick={handleClosePaymentModal}
                                                        >
                                                            <ArrowLeft className="w-4 h-4 mr-2" />
                                                            Kembali
                                                        </Button>
                                                    </>
                                                )}
                                            </CardContent>
                                        </Card>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
        </AppLayout>
    );
}
