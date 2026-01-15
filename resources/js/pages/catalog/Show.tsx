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
    Tag,
} from 'lucide-react';

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
    price_cents: number;
    currency: string;
    annual_discount_percent?: number;
    trial_days?: number;
    setup_fee_cents: number;
    duration_1_month_enabled: boolean;
    duration_12_months_enabled: boolean;
    metadata?: {
        description?: string;
        features?: string[];
    };
    features?: ProductFeature[];
}

interface CatalogShowProps {
    product: Product;
    pphRate?: number;
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

export default function CatalogShow({ product, pphRate = 0.11 }: CatalogShowProps) {
    const [isPaymentModalOpen, setIsPaymentModalOpen] = useState(false);
    // Set default duration to 12 if 1 month is not available or price is 0
    const defaultDuration = (product.duration_1_month_enabled ?? true) && product.price_cents && product.price_cents > 0 ? 1 : 12;
    const [selectedDuration, setSelectedDuration] = useState<1 | 12>(defaultDuration);
    const [selectedPaymentMethod, setSelectedPaymentMethod] = useState('bca_va');
    const [isSubmittingCheckout, setIsSubmittingCheckout] = useState(false);

    const typeConfig = getProductTypeConfig(product.type);
    const TypeIcon = typeConfig.icon;

    // Calculate annual discount
    const annualDiscountPercent = product.annual_discount_percent ?? 0;
    const hasAnnualDiscount = annualDiscountPercent > 0;
    const monthlyPrice = product.price_cents;
    const annualPriceWithoutDiscount = monthlyPrice * 12;
    const annualDiscountAmount = hasAnnualDiscount 
        ? Math.round(annualPriceWithoutDiscount * (annualDiscountPercent / 100))
        : 0;
    const annualPriceWithDiscount = annualPriceWithoutDiscount - annualDiscountAmount;

  const addToCartForm = useForm({
    product_id: product.id,
    qty: 1,
  });

    const checkoutForm = useForm({
        product_id: product.id,
        payment_method: 'bca_va',
        duration_months: 1,
    });

    const formatPrice = (cents: number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(cents);
    };

  const handleAddToCart = () => {
    addToCartForm.post(route('customer.cart.add'), {
      preserveScroll: true,
    });
  };

    const handleOpenPaymentModal = (duration?: 1 | 12) => {
        // Set duration jika diberikan, otherwise set default berdasarkan yang enabled dan harga tersedia
        if (duration) {
            setSelectedDuration(duration);
        } else if ((product.duration_1_month_enabled ?? true) && product.price_cents && product.price_cents > 0) {
            setSelectedDuration(1);
        } else if (product.duration_12_months_enabled ?? true) {
            setSelectedDuration(12);
        }
        setIsPaymentModalOpen(true);
    };

    const handleClosePaymentModal = () => {
        // Reset semua state saat modal ditutup
        setIsPaymentModalOpen(false);
        setSelectedDuration(1);
        setSelectedPaymentMethod('bca_va');
        setIsSubmittingCheckout(false);
    };

    const handleCheckout = (e: React.FormEvent) => {
        e.preventDefault();
        
        // Prevent duplicate submission
        if (isSubmittingCheckout || checkoutForm.processing) {
            return;
        }

        // Set flag untuk mencegah multiple submissions
        setIsSubmittingCheckout(true);

        checkoutForm.transform((data) => ({
            ...data,
            payment_method: selectedPaymentMethod,
            duration_months: selectedDuration,
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
                    {/* <div className={`h-20 w-20 rounded-2xl bg-gradient-to-br ${typeConfig.bgColor} flex items-center justify-center flex-shrink-0`}>
                        <TypeIcon className="h-10 w-10 text-white" />
                    </div> */}
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

                    {/* Product Pricing */}
                    <div className="space-y-4">
                        <h2 className="text-xl font-bold">Pilih Paket</h2>
                        <Card
                            variant="premium"
                            className="relative overflow-hidden ring-2 ring-primary"
                        >
                            <CardContent className="p-5">
                                <div className="mb-4">
                                    <h3 className="font-semibold text-lg">{product.name}</h3>
                                    {/* <p className="text-sm text-muted-foreground">
                                        {product.trial_days && (
                                            <Badge variant="success-soft" className="ml-2">
                                                {product.trial_days} hari trial
                                            </Badge>
                                        )}
                                    </p> */}
                                </div>

                                {/* Harga berdasarkan durasi */}
                                <div className="mb-4 space-y-3">
                                    {(product.duration_1_month_enabled ?? true) && product.price_cents && product.price_cents > 0 && (
                                        <div className="p-3 rounded-lg border bg-muted/30">
                                            <div className="flex items-center justify-between">
                                                <div>
                                                    <p className="text-sm text-muted-foreground">1 Bulan</p>
                                                    <p className="text-lg font-semibold">
                                                        {formatPrice(product.price_cents)}
                                                    </p>
                                                </div>
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => handleOpenPaymentModal(1)}
                                                >
                                                    Pilih
                                                </Button>
                                            </div>
                                        </div>
                                    )}
                                    
                                    {(product.duration_12_months_enabled ?? true) && product.price_cents && product.price_cents > 0 && (
                                        <div className={`p-3 rounded-lg border relative ${
                                            (product.duration_1_month_enabled ?? true) 
                                                ? 'bg-primary/5 border-primary/20' 
                                                : 'bg-muted/30'
                                        } ${hasAnnualDiscount ? 'ring-2 ring-emerald-500/30' : ''}`}>
                                            {hasAnnualDiscount && (
                                                <div className="absolute -top-2 -right-2 z-10">
                                                    <Badge className="bg-gradient-to-r from-emerald-500 to-green-500 text-white text-xs font-bold shadow-md">
                                                        <Tag className="h-3 w-3 inline mr-1" />
                                                        {annualDiscountPercent}% OFF
                                                    </Badge>
                                                </div>
                                            )}
                                            <div className="flex items-center justify-between">
                                                <div className="flex-1">
                                                    <div className="flex items-center gap-2 mb-1">
                                                        <p className="text-sm font-medium">12 Bulan</p>
                                                        {(product.duration_1_month_enabled ?? true) && (
                                                            <Badge variant="outline" className="text-xs">
                                                                Hemat
                                                            </Badge>
                                                        )}
                                                    </div>
                                                    {hasAnnualDiscount ? (
                                                        <div className="space-y-1">
                                                            <div className="flex items-baseline gap-2">
                                                                <span className="text-sm text-muted-foreground line-through">
                                                                    {formatPrice(annualPriceWithoutDiscount)}
                                                                </span>
                                                                <span className="text-lg font-semibold text-emerald-600">
                                                                    {formatPrice(annualPriceWithDiscount)}
                                                                </span>
                                                            </div>
                                                            <p className="text-xs text-emerald-600 font-medium">
                                                                Hemat {formatPrice(annualDiscountAmount)}
                                                            </p>
                                                            <p className="text-xs text-muted-foreground">
                                                                {formatPrice(Math.round(annualPriceWithDiscount / 12))}/bulan
                                                            </p>
                                                        </div>
                                                    ) : (
                                                        <>
                                                            <p className="text-lg font-semibold">
                                                                {formatPrice(annualPriceWithoutDiscount)}
                                                            </p>
                                                            <p className="text-xs text-muted-foreground">
                                                                {formatPrice(product.price_cents)}/bulan
                                                            </p>
                                                        </>
                                                    )}
                                                </div>
                                                <Button
                                                    variant={(product.duration_1_month_enabled ?? true) || hasAnnualDiscount ? "default" : "outline"}
                                                    size="sm"
                                                    onClick={() => handleOpenPaymentModal(12)}
                                                    className="ml-3"
                                                >
                                                    Pilih
                                                </Button>
                                            </div>
                                        </div>
                                    )}
                                    
                                    {product.setup_fee_cents > 0 && (
                                        <p className="text-xs text-muted-foreground text-center">
                                            + Setup fee: {formatPrice(product.setup_fee_cents)}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Button
                                        variant="gradient"
                                        className="w-full"
                                        onClick={() => handleOpenPaymentModal()}
                                        disabled={addToCartForm.processing}
                                    >
                                        <Wallet className="h-4 w-4 mr-2" />
                                        Bayar Sekarang
                                    </Button>
                                    <Button
                                        variant="outline"
                                        className="w-full"
                                        onClick={() => handleAddToCart()}
                                        disabled={addToCartForm.processing}
                                    >
                                        <ShoppingCart className="h-4 w-4 mr-2" />
                                        Tambah ke Cart
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>

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

                                        {checkoutForm.errors.product_id && (
                                            <div className="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                                                <InputError message={checkoutForm.errors.product_id} />
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
                                                <>
                                                    <div className="flex gap-3">
                                                        <div className={`h-12 w-12 rounded-xl bg-gradient-to-br ${typeConfig.bgColor} flex items-center justify-center flex-shrink-0`}>
                                                            <TypeIcon className="h-6 w-6 text-white" />
                                                        </div>
                                                        <div>
                                                            <p className="font-semibold">{product.name}</p>
                                                        </div>
                                                    </div>

                                                    <Separator />

                                                    {/* Durasi Selection */}
                                                    {(((product.duration_1_month_enabled ?? true) && product.price_cents && product.price_cents > 0) || (product.duration_12_months_enabled ?? true)) && (
                                                        <div>
                                                            <Label className="text-sm font-semibold mb-2 block">Pilih Durasi</Label>
                                                            <RadioGroup value={selectedDuration.toString()} onValueChange={(value) => setSelectedDuration(parseInt(value) as 1 | 12)}>
                                                                <div className="space-y-2">
                                                                    {(product.duration_1_month_enabled ?? true) && product.price_cents && product.price_cents > 0 && (
                                                                        <label className={`flex items-center justify-between p-3 border-2 rounded-lg cursor-pointer transition-all ${
                                                                            selectedDuration === 1 ? 'border-primary bg-primary/5' : 'border-border hover:border-muted-foreground/30'
                                                                        }`}>
                                                                            <div className="flex items-center space-x-2">
                                                                                <RadioGroupItem value="1" id="duration_1" />
                                                                                <Label htmlFor="duration_1" className="cursor-pointer font-medium">1 Bulan</Label>
                                                                            </div>
                                                                            <span className="font-semibold">{formatPrice(product.price_cents)}</span>
                                                                        </label>
                                                                    )}
                                                                    {(product.duration_12_months_enabled ?? true) && product.price_cents && product.price_cents > 0 && (
                                                                        <label className={`flex items-center justify-between p-3 border-2 rounded-lg cursor-pointer transition-all relative ${
                                                                            selectedDuration === 12 ? 'border-primary bg-primary/5' : 'border-border hover:border-muted-foreground/30'
                                                                        } ${hasAnnualDiscount ? 'ring-1 ring-emerald-500/30' : ''}`}>
                                                                            {hasAnnualDiscount && (
                                                                                <div className="absolute -top-2 -right-2">
                                                                                    <Badge className="bg-gradient-to-r from-emerald-500 to-green-500 text-white text-[10px] font-bold">
                                                                                        {annualDiscountPercent}% OFF
                                                                                    </Badge>
                                                                                </div>
                                                                            )}
                                                                            <div className="flex items-center space-x-2">
                                                                                <RadioGroupItem value="12" id="duration_12" />
                                                                                <div className="flex flex-col">
                                                                                    <Label htmlFor="duration_12" className="cursor-pointer font-medium">12 Bulan</Label>
                                                                                    {hasAnnualDiscount && (
                                                                                        <span className="text-xs text-emerald-600 font-medium">
                                                                                            Hemat {formatPrice(annualDiscountAmount)}
                                                                                        </span>
                                                                                    )}
                                                                                </div>
                                                                            </div>
                                                                            <div className="flex flex-col items-end">
                                                                                {hasAnnualDiscount ? (
                                                                                    <>
                                                                                        <span className="text-xs text-muted-foreground line-through">
                                                                                            {formatPrice(annualPriceWithoutDiscount)}
                                                                                        </span>
                                                                                        <span className="font-semibold text-emerald-600">
                                                                                            {formatPrice(annualPriceWithDiscount)}
                                                                                        </span>
                                                                                    </>
                                                                                ) : (
                                                                                    <span className="font-semibold">{formatPrice(annualPriceWithoutDiscount)}</span>
                                                                                )}
                                                                            </div>
                                                                        </label>
                                                                    )}
                                                                </div>
                                                            </RadioGroup>
                                                            <InputError message={checkoutForm.errors.duration_months} className="mt-2" />
                                                        </div>
                                                    )}

                                                    <Separator />

                                                    <div className="space-y-2 text-sm">
                                                        <div className="flex justify-between">
                                                            <span className="text-muted-foreground">Harga Paket ({selectedDuration} bulan)</span>
                                                            <span className="font-medium">
                                                                {(() => {
                                                                    if (selectedDuration === 12 && hasAnnualDiscount) {
                                                                        return formatPrice(annualPriceWithDiscount);
                                                                    }
                                                                    if (product.price_cents && product.price_cents > 0) {
                                                                        return formatPrice(product.price_cents * selectedDuration);
                                                                    }
                                                                    return '—';
                                                                })()}
                                                            </span>
                                                        </div>
                                                        {selectedDuration === 12 && hasAnnualDiscount && (
                                                            <div className="flex justify-between items-center py-1 px-2 rounded bg-emerald-50 dark:bg-emerald-950/20">
                                                                <span className="text-muted-foreground flex items-center gap-1 text-xs">
                                                                    <Tag className="h-3 w-3 text-emerald-600" />
                                                                    Diskon Tahunan ({annualDiscountPercent}%)
                                                                </span>
                                                                <span className="font-semibold text-emerald-600 text-xs">
                                                                    -{formatPrice(annualDiscountAmount)}
                                                                </span>
                                                            </div>
                                                        )}
                                                        {product.setup_fee_cents > 0 && (
                                                            <div className="flex justify-between">
                                                                <span className="text-muted-foreground">Biaya Setup</span>
                                                                <span className="font-medium">{formatPrice(product.setup_fee_cents)}</span>
                                                            </div>
                                                        )}
                                                    </div>

                                                    <Separator />

                                                    {/* Calculate totals with PPH */}
                                                    {(() => {
                                                        // Calculate product price with annual discount if applicable
                                                        let productPrice = 0;
                                                        if (selectedDuration === 12 && hasAnnualDiscount) {
                                                            productPrice = annualPriceWithDiscount;
                                                        } else if (product.price_cents && product.price_cents > 0) {
                                                            productPrice = product.price_cents * selectedDuration;
                                                        }
                                                        
                                                        const subtotal = productPrice + (product.setup_fee_cents || 0);
                                                        const tax = Math.round(subtotal * pphRate);
                                                        const total = subtotal + tax;
                                                        
                                                        return (
                                                            <>
                                                                <div className="space-y-2 text-sm">
                                                                    <div className="flex justify-between">
                                                                        <span className="text-muted-foreground">
                                                                            Pajak (PPH {((pphRate * 100).toFixed(0))}%)
                                                                        </span>
                                                                        <span className="font-medium">{formatPrice(tax)}</span>
                                                                    </div>
                                                                </div>

                                                                <Separator />

                                                                <div className="flex justify-between items-center">
                                                                    <span className="font-semibold">Total</span>
                                                                    <span className="text-2xl font-bold text-primary">
                                                                        {formatPrice(total)}
                                                                    </span>
                                                                </div>
                                                            </>
                                                        );
                                                    })()}

                                                    <Button
                                                        type="submit"
                                                        variant="gradient"
                                                        className="w-full"
                                                        size="lg"
                                                        disabled={checkoutForm.processing || isSubmittingCheckout}
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
