import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Server,
    HardDrive,
    Zap,
    Globe,
    Package,
    ArrowRight,
    CheckCircle2,
    Star,
    Shield,
    Clock,
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Catalog', href: '/catalog' },
];

interface Product {
    id: string;
    name: string;
    slug: string;
    type: string;
    status: string;
    metadata?: {
        description?: string;
        features?: string[];
        popular?: boolean;
        starting_price?: number;
    };
}

interface CatalogProps {
    products: Product[];
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

export default function Catalog({ products }: CatalogProps) {
    // Group products by type
    const groupedProducts = products.reduce((acc, product) => {
        if (!acc[product.type]) {
            acc[product.type] = [];
        }
        acc[product.type].push(product);
        return acc;
    }, {} as Record<string, Product[]>);

    const productTypes = Object.keys(groupedProducts);

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
                        const TypeIcon = typeConfig.icon;
                        const typeProducts = groupedProducts[type];

                        return (
                            <section key={type} className="space-y-4">
                                {/* Section Header */}
                                <div className="flex items-center gap-3">
                                    <div className={`h-10 w-10 rounded-xl bg-gradient-to-br ${typeConfig.bgColor} flex items-center justify-center`}>
                                        <TypeIcon className="h-5 w-5 text-white" />
                                    </div>
                                    <div>
                                        <h2 className="text-xl font-bold">{typeConfig.label}</h2>
                                        <p className="text-sm text-muted-foreground">
                                            {typeProducts.length} paket tersedia
                                        </p>
                                    </div>
                                </div>

                                {/* Products Grid */}
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    {typeProducts.map((product) => {
                                        const isPopular = product.metadata?.popular;

                                        return (
                                            <Card
                                                key={product.id}
                                                variant="premium"
                                                className={`relative overflow-hidden group ${isPopular ? 'ring-2 ring-primary/50' : ''}`}
                                            >
                                                {/* Popular badge */}
                                                {isPopular && (
                                                    <div className="absolute top-0 right-0 px-3 py-1 bg-gradient-to-r from-[var(--gradient-start)] to-[var(--gradient-end)] text-white text-xs font-semibold rounded-bl-xl">
                                                        <Star className="h-3 w-3 inline mr-1" />
                                                        POPULER
                                                    </div>
                                                )}

                                                {/* Type accent bar */}
                                                <div className={`h-1 bg-gradient-to-r ${typeConfig.bgColor}`} />

                                                <CardHeader className="pb-2">
                                                    <div className="flex items-start gap-3">
                                                        <div className={`h-12 w-12 rounded-xl bg-gradient-to-br ${typeConfig.bgColor} flex items-center justify-center flex-shrink-0`}>
                                                            <TypeIcon className="h-6 w-6 text-white" />
                                                        </div>
                                                        <div className="flex-1 min-w-0">
                                                            <h3 className="font-semibold text-lg leading-tight truncate">
                                                                {product.name}
                                                            </h3>
                                                            <Badge variant="outline" className="mt-1">
                                                                {typeConfig.label}
                                                            </Badge>
                                                        </div>
                                                    </div>
                                                </CardHeader>

                                                <CardContent className="space-y-4">
                                                    {/* Description */}
                                                    {product.metadata?.description && (
                                                        <p className="text-sm text-muted-foreground line-clamp-2">
                                                            {product.metadata.description}
                                                        </p>
                                                    )}

                                                    {/* Features Preview */}
                                                    {product.metadata?.features && product.metadata.features.length > 0 && (
                                                        <ul className="space-y-2">
                                                            {product.metadata.features.slice(0, 4).map((feature, idx) => (
                                                                <li key={idx} className="flex items-start gap-2 text-sm">
                                                                    <CheckCircle2 className="h-4 w-4 text-emerald-600 mt-0.5 flex-shrink-0" />
                                                                    <span className="text-muted-foreground">{feature}</span>
                                                                </li>
                                                            ))}
                                                            {product.metadata.features.length > 4 && (
                                                                <li className="text-sm text-primary font-medium">
                                                                    + {product.metadata.features.length - 4} fitur lainnya
                                                                </li>
                                                            )}
                                                        </ul>
                                                    )}

                                                    {/* Price & CTA */}
                                                    <div className="pt-4 border-t">
                                                        {product.metadata?.starting_price && (
                                                            <div className="mb-3">
                                                                <p className="text-xs text-muted-foreground">Mulai dari</p>
                                                                <p className="text-2xl font-bold">
                                                                    {formatPrice(product.metadata.starting_price)}
                                                                    <span className="text-sm font-normal text-muted-foreground">/bulan</span>
                                                                </p>
                                                            </div>
                                                        )}
                                                        <Link href={route('catalog.show', product.slug)}>
                                                            <Button variant="gradient" className="w-full">
                                                                Lihat Detail
                                                                <ArrowRight className="h-4 w-4 ml-2 group-hover:translate-x-1 transition-transform" />
                                                            </Button>
                                                        </Link>
                                                    </div>
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
