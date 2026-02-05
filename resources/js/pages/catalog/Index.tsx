import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Server,
    HardDrive,
    Zap,
    Globe,
    Package,
    CheckCircle2,
    Star,
    Shield,
    Clock,
    Tag,
    Percent,
    Database,
    Mail,
    HardDriveIcon,
    Cpu,
    MemoryStick,
    Wifi,
    Folder,
    Lock,
    Cloud,
    Activity,
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Catalog', href: '/catalog' },
];

interface Plan {
    id: string;
    code: string;
    billing_cycle: string;
    price_cents: number;
    monthly_price_cents?: number;
    annual_discount_percent?: number;
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
    price_cents?: number;
    annual_discount_percent?: number;
    duration_1_month_enabled?: boolean;
    duration_12_months_enabled?: boolean;
    metadata?: {
        description?: string;
        features?: string[];
        popular?: boolean;
        starting_price?: number;
    };
    features?: ProductFeature[];
    plans?: Plan[];
    monthly_plans?: Plan[];
    annual_plans?: Plan[];
}

interface CatalogProps {
    products: Product[];
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

function formatPrice(amount: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
}

// Map feature key/label to appropriate icon
function getFeatureIcon(key: string, label?: string): React.ComponentType<{ className?: string }> {
    const searchText = (label || key).toLowerCase();
    
    // Website/Domain related
    if (searchText.includes('website') || searchText.includes('domain') || searchText.includes('site')) {
        return Globe;
    }
    
    // SSL/Security
    if (searchText.includes('ssl') || searchText.includes('certificate') || searchText.includes('security')) {
        return Lock;
    }
    
    // Email
    if (searchText.includes('email') || searchText.includes('mail')) {
        return Mail;
    }
    
    // Storage/Disk
    if (searchText.includes('storage') || searchText.includes('disk') || searchText.includes('space') || searchText.includes('gb')) {
        return HardDriveIcon;
    }
    
    // CPU
    if (searchText.includes('cpu') || searchText.includes('core') || searchText.includes('processor')) {
        return Cpu;
    }
    
    // RAM/Memory
    if (searchText.includes('ram') || searchText.includes('memory')) {
        return MemoryStick;
    }
    
    // Bandwidth/Network
    if (searchText.includes('bandwidth') || searchText.includes('transfer') || searchText.includes('network') || searchText.includes('mbps')) {
        return Wifi;
    }
    
    // Backup
    if (searchText.includes('backup') || searchText.includes('backup')) {
        return Folder;
    }
    
    // Database
    if (searchText.includes('database') || searchText.includes('db') || searchText.includes('mysql')) {
        return Database;
    }
    
    // Cloud/CDN
    if (searchText.includes('cloud') || searchText.includes('cdn')) {
        return Cloud;
    }
    
    // Performance/Monitoring
    if (searchText.includes('uptime') || searchText.includes('monitoring') || searchText.includes('performance')) {
        return Activity;
    }
    
    // Default icons based on common patterns
    if (searchText.includes('free') || searchText.includes('gratis')) {
        return Star;
    }
    
    // Default fallback
    return CheckCircle2;
}

export default function Catalog({ products }: CatalogProps) {
    const [promoCode, setPromoCode] = useState('');
    const [promoError, setPromoError] = useState('');
    const [appliedPromo, setAppliedPromo] = useState<{ code: string; discount: number } | null>(null);

    // Group products by type
    const groupedProducts = products.reduce((acc, product) => {
        if (!acc[product.type]) {
            acc[product.type] = [];
        }
        acc[product.type].push(product);
        return acc;
    }, {} as Record<string, Product[]>);

    const productTypes = Object.keys(groupedProducts);

    const handleApplyPromo = (e: React.FormEvent) => {
        e.preventDefault();
        setPromoError('');
        
        // TODO: Validasi promo code dengan backend
        // Untuk sekarang, simulasikan validasi
        if (promoCode.trim().length < 3) {
            setPromoError('Kode promo minimal 3 karakter');
            return;
        }

        // Simulasi: jika kode promo valid, set discount
        // Di production, ini akan dipanggil ke API backend
        setAppliedPromo({
            code: promoCode.toUpperCase(),
            discount: 10, // 10% discount
        });
        setPromoCode('');
    };

    const handleRemovePromo = () => {
        setAppliedPromo(null);
        setPromoCode('');
        setPromoError('');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Product Catalog" />
            <div className="p-4 lg:p-6 space-y-8">
                {/* Header */}
                <div className="text-center max-w-3xl mx-auto">
                    <h1 className="text-3xl lg:text-4xl font-bold tracking-tight mb-3">
                        Pilih Layanan <span className="text-gradient-primary">Terbaik</span> untuk Anda
                    </h1>
                    <p className="text-muted-foreground text-lg">
                        Hosting tercepat dengan fitur lengkap, dukungan 24/7, dan jaminan uptime 99.9%
                    </p>
                </div>

                {/* Promo Code Section */}
                <Card variant="premium" className="max-w-2xl mx-auto">
                    <CardContent className="p-4">
                        <div className="flex flex-col sm:flex-row gap-3 items-center">
                            <div className="flex-1 w-full">
                                <form onSubmit={handleApplyPromo} className="flex gap-2">
                                    <div className="flex-1 relative">
                                        <Tag className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                        <Input
                                            type="text"
                                            placeholder="Masukkan kode promo"
                                            value={promoCode}
                                            onChange={(e) => setPromoCode(e.target.value)}
                                            className="pl-9"
                                            disabled={!!appliedPromo}
                                        />
                                    </div>
                                    {appliedPromo ? (
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={handleRemovePromo}
                                        >
                                            Hapus
                                        </Button>
                                    ) : (
                                        <Button type="submit" variant="gradient">
                                            Terapkan
                                        </Button>
                                    )}
                                </form>
                                {promoError && (
                                    <p className="text-sm text-red-600 mt-2">{promoError}</p>
                                )}
                                {appliedPromo && (
                                    <div className="mt-2 flex items-center gap-2 text-sm text-emerald-600">
                                        <Percent className="h-4 w-4" />
                                        <span>
                                            Kode promo <strong>{appliedPromo.code}</strong> aktif - Diskon {appliedPromo.discount}%
                                        </span>
                                    </div>
                                )}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Trust indicators */}
                <div className="flex flex-wrap items-center justify-center gap-6 text-sm text-muted-foreground">
                    <div className="flex items-center gap-2">
                        <div className="h-8 w-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                            <Shield className="h-4 w-4 text-emerald-600" />
                        </div>
                        <span>SSL Gratis</span>
                    </div>
                    <div className="flex items-center gap-2">
                        <div className="h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            <Clock className="h-4 w-4 text-blue-600" />
                        </div>
                        <span>Uptime 99.9%</span>
                    </div>
                    <div className="flex items-center gap-2">
                        <div className="h-8 w-8 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                            <Zap className="h-4 w-4 text-purple-600" />
                        </div>
                        <span>NVMe SSD</span>
                    </div>
                </div>

                {/* Products by Type */}
                {productTypes.length === 0 ? (
                    <Card variant="premium">
                        <CardContent className="p-12 text-center">
                            <div className="h-16 w-16 rounded-2xl bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)] flex items-center justify-center mx-auto mb-4">
                                <Package className="h-8 w-8 text-white" />
                            </div>
                            <h3 className="text-lg font-semibold mb-2">Tidak Ada Produk</h3>
                            <p className="text-muted-foreground max-w-md mx-auto">
                                Tidak ada produk tersedia saat ini. Silakan cek kembali nanti.
                            </p>
                        </CardContent>
                    </Card>
                ) : (
                    productTypes.map((type) => {
                        const typeConfig = getProductTypeConfig(type);
                        const typeProducts = groupedProducts[type];

                        return (
                            <section key={type} className="space-y-4">
                                {/* Section Header */}
                                <div className="flex items-center gap-3">
                                    {/* <div className={`h-10 w-10 rounded-xl bg-gradient-to-br ${typeConfig.bgColor} flex items-center justify-center`}>
                                        <TypeIcon className="h-5 w-5 text-white" />
                                    </div> */}
                                    <div>
                                        <h2 className="text-xl font-bold">{typeConfig.label}</h2>
                                        <p className="text-sm text-muted-foreground">
                                            {typeProducts.length} paket tersedia
                                        </p>
                                    </div>
                                </div>

                                {/* Products Grid - Google AI Style */}
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    {typeProducts.map((product) => {
                                        const isPopular = product.metadata?.popular;
                                        const monthlyPrice = product.price_cents || 0;
                                        const annualDiscountPercent = product.annual_discount_percent || 0;
                                        const hasAnnualDiscount = annualDiscountPercent > 0 && (product.duration_12_months_enabled ?? true);
                                        
                                        // Calculate annual pricing
                                        const annualPriceWithoutDiscount = monthlyPrice * 12;
                                        const annualDiscountAmount = hasAnnualDiscount 
                                            ? Math.round(annualPriceWithoutDiscount * (annualDiscountPercent / 100))
                                            : 0;
                                        const annualPriceWithDiscount = annualPriceWithoutDiscount - annualDiscountAmount;
                                        
                                        // Determine which price to show (prioritize annual if available)
                                        const showAnnual = hasAnnualDiscount && (product.duration_12_months_enabled ?? true);
                                        const displayPrice = showAnnual ? (annualPriceWithDiscount / 12) : monthlyPrice;
                                        const originalPrice = showAnnual ? monthlyPrice : null;
                                        const durationLabel = showAnnual ? '12 bulan' : '1 bulan';

                                        return (
                                            <Card
                                                key={product.id}
                                                className="relative overflow-hidden flex flex-col h-full border-2 hover:border-primary/50 transition-all"
                                            >
                                                {/* Popular badge */}
                                                {isPopular && (
                                                    <div className="absolute top-0 right-0 px-3 py-1 bg-gradient-to-r from-[var(--gradient-start)] to-[var(--gradient-end)] text-white text-xs font-semibold rounded-bl-xl z-10">
                                                        <Star className="h-3 w-3 inline mr-1" />
                                                        POPULER
                                                    </div>
                                                )}

                                                <CardHeader className="text-center pb-4">
                                                    <h3 className="text-xl font-bold mb-2">
                                                        {product.name}
                                                    </h3>
                                                    {product.metadata?.description && (
                                                        <p className="text-sm text-muted-foreground leading-relaxed">
                                                            {product.metadata.description}
                                                        </p>
                                                    )}
                                                </CardHeader>

                                                <CardContent className="flex-1 flex flex-col space-y-6">
                                                    {/* Pricing Section - Google AI Style */}
                                                    <div className="text-center space-y-2">
                                                        <div className="flex items-baseline justify-center gap-2">
                                                            {originalPrice && (
                                                                <span className="text-base text-muted-foreground line-through">
                                                                    {formatPrice(originalPrice)}
                                                                </span>
                                                            )}
                                                            <span className={`text-3xl font-bold ${hasAnnualDiscount ? 'text-emerald-600' : 'text-foreground'}`}>
                                                                {formatPrice(displayPrice)}
                                                            </span>
                                                            <span className="text-sm text-muted-foreground">/bln</span>
                                                        </div>
                                                        {hasAnnualDiscount && (
                                                            <p className="text-xs text-muted-foreground">
                                                                untuk {durationLabel}
                                                            </p>
                                                        )}
                                                    </div>

                                                    {/* CTA Button */}
                                                    <Link href={route('catalog.show', product.slug)} className="block">
                                                        <Button 
                                                            variant="default" 
                                                            className="w-full h-12 text-base font-medium"
                                                            size="lg"
                                                        >
                                                            Dapatkan {product.name}
                                                        </Button>
                                                    </Link>

                                                    {/* Features List - Google AI Style */}
                                                    {(() => {
                                                        const hasProductFeatures = product.features && product.features.length > 0;
                                                        const hasMetadataFeatures = product.metadata?.features && product.metadata.features.length > 0;
                                                        
                                                        if (!hasProductFeatures && !hasMetadataFeatures) {
                                                            return null;
                                                        }
                                                        
                                                        return (
                                                            <div className="space-y-4 pt-4 border-t">
                                                                {/* Product Features from Database */}
                                                                {hasProductFeatures && (
                                                                    <>
                                                                        {product.features!.map((feature) => {
                                                                            const displayLabel = feature.label || feature.key;
                                                                            const displayValue = feature.value + (feature.unit ? ` ${feature.unit}` : '');
                                                                            const IconComponent = getFeatureIcon(feature.key, feature.label);
                                                                            return (
                                                                                <div key={feature.id} className="flex items-start gap-3">
                                                                                    <div className="flex-shrink-0 mt-0.5">
                                                                                        <IconComponent className="h-5 w-5 text-primary" />
                                                                                    </div>
                                                                                    <div className="flex-1 min-w-0">
                                                                                        <p className="font-semibold text-sm">
                                                                                            {displayValue ? `${displayLabel}: ${displayValue}` : displayLabel}
                                                                                        </p>
                                                                                    </div>
                                                                                </div>
                                                                            );
                                                                        })}
                                                                    </>
                                                                )}
                                                                
                                                                {/* Separator between sections */}
                                                                {hasProductFeatures && hasMetadataFeatures && (
                                                                    <div className="pt-2 pb-2 border-t border-b border-muted/50">
                                                                        <p className="text-xs font-medium text-muted-foreground uppercase tracking-wider text-center">
                                                                            Fitur Tambahan
                                                                        </p>
                                                                    </div>
                                                                )}
                                                                
                                                                {/* Metadata Features */}
                                                                {hasMetadataFeatures && (
                                                                    <>
                                                                        {product.metadata!.features!.map((feature, idx) => {
                                                                            const parts = feature.split(':');
                                                                            const label = parts[0] || feature;
                                                                            const value = parts.slice(1).join(':').trim() || '';
                                                                            const IconComponent = getFeatureIcon(label, label);
                                                                            return (
                                                                                <div key={`metadata-${idx}`} className="flex items-start gap-3">
                                                                                    <div className="flex-shrink-0 mt-0.5">
                                                                                        <IconComponent className="h-5 w-5 text-primary" />
                                                                                    </div>
                                                                                    <div className="flex-1 min-w-0">
                                                                                        <p className="font-semibold text-sm">
                                                                                            {value ? `${label}: ${value}` : label}
                                                                                        </p>
                                                                                    </div>
                                                                                </div>
                                                                            );
                                                                        })}
                                                                    </>
                                                                )}
                                                            </div>
                                                        );
                                                    })()}
                                                </CardContent>
                                            </Card>
                                        );
                                    })}
                                </div>
                            </section>
                        );
                    })
                )}
            </div>
        </AppLayout>
    );
}
