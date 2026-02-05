import React, { useState } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import InputError from '@/components/input-error';
import DomainSearch, { type DomainResult } from '@/components/DomainSearch';
import {
    Wallet,
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
    // Set default duration to 12 if 1 month is not available or price is 0
    const defaultDuration = (product.duration_1_month_enabled ?? true) && product.price_cents && product.price_cents > 0 ? 1 : 12;
    const [selectedDuration, setSelectedDuration] = useState<1 | 12>(defaultDuration);
    
    // Domain State
    const [selectedDomains, setSelectedDomains] = useState<DomainResult[]>([]);

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
        domains: [] as Array<{
            domain: string;
            price_cents: number;
            original_price_cents: number;
            discount_percent: number;
        }>,
    });

    const formatPrice = (cents: number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(cents);
    };
    
    const handleAddDomain = (domain: DomainResult) => {
        if (selectedDomains.some((d) => d.domain === domain.domain)) return;
        setSelectedDomains([...selectedDomains, domain]);
    };

    const handleRemoveDomain = (domainName: string) => {
        setSelectedDomains(selectedDomains.filter((d) => d.domain !== domainName));
    };

  const handleAddToCart = () => {
    addToCartForm.post(route('customer.cart.add'), {
      preserveScroll: true,
    });
  };

    const handleOpenPaymentModal = (duration?: 1 | 12) => {
        const finalDuration = duration || selectedDuration;
        router.visit(route('catalog.guest.checkout', { 
            slug: product.slug, 
            duration: finalDuration 
        }));
    };

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
                        {/* Domain Search Upsell */}
                        <Card variant="premium">
                            <CardHeader>
                                <div className="flex items-center gap-2">
                                    <div className="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg text-blue-600">
                                        <Globe className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <CardTitle className="text-lg">
                                            Tambah Domain
                                        </CardTitle>
                                        <div className="text-sm text-muted-foreground">
                                            Cari dan tambahkan domain untuk website Anda
                                        </div>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <DomainSearch
                                    onAddDomain={handleAddDomain}
                                    onRemoveDomain={(d) => handleRemoveDomain(d)}
                                    selectedDomains={selectedDomains}
                                />
                            </CardContent>
                        </Card>
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

                {/* Payment Method Modal removed */}
            </div>
        </AppLayout>
    );
}
