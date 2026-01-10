import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { type BreadcrumbItem } from '@/types';
import {
    Search,
    Shield,
    CheckCircle2,
    Info,
    ShoppingCart,
    Lock,
    Zap,
    Award,
    Clock,
    Star,
} from 'lucide-react';

// Determine breadcrumbs based on route
const getBreadcrumbs = (): BreadcrumbItem[] => {
    const path = window.location.pathname;
    if (path.startsWith('/customer/ssl')) {
        return [{ title: 'SSL Certificates', href: '/customer/ssl' }];
    }
    return [{ title: 'Manajemen SSL', href: '/admin/ssl' }];
};

interface SslProductFeatures {
    domain?: string;
    issuance?: string;
    warranty?: string;
    site_seal?: string;
    validation?: string;
    description?: string;
    authentication_level?: string;
    subdomain?: string;
}

interface SslProduct {
    id: number;
    provider: string;
    brand: string;
    name: string;
    ssl_type: string;
    is_wildcard: number | boolean;
    is_refundable: number | boolean;
    max_period: number;
    status: number;
    features: SslProductFeatures;
    price?: number | null;
    currency?: string;
    created_at?: string;
    updated_at?: string;
}

interface PaginationMeta {
    current_page: number;
    from: number;
    last_page: number;
    path: string;
    per_page: number;
    to: number;
    total: number;
}

interface PaginationLinks {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
}

interface Props {
    products: SslProduct[];
    links: PaginationLinks;
    meta: PaginationMeta;
    filters: {
        name?: string;
        provider?: string;
        brand?: string;
        ssl_type?: string;
        is_wildcard?: string;
        status?: number;
        page?: number;
        limit?: number;
    };
}

function getSslTypeConfig(sslType: string) {
    const config: Record<string, { color: string; bgColor: string; label: string; description: string }> = {
        DV: {
            color: 'text-blue-700 dark:text-blue-300',
            bgColor: 'bg-blue-100 dark:bg-blue-900/30 border-blue-200 dark:border-blue-800',
            label: 'Domain Validation',
            description: 'Validasi domain otomatis, penerbitan cepat',
        },
        OV: {
            color: 'text-emerald-700 dark:text-emerald-300',
            bgColor: 'bg-emerald-100 dark:bg-emerald-900/30 border-emerald-200 dark:border-emerald-800',
            label: 'Organization Validation',
            description: 'Validasi organisasi, tingkat kepercayaan tinggi',
        },
        EV: {
            color: 'text-purple-700 dark:text-purple-300',
            bgColor: 'bg-purple-100 dark:bg-purple-900/30 border-purple-200 dark:border-purple-800',
            label: 'Extended Validation',
            description: 'Validasi penuh, green bar di browser',
        },
    };
    return config[sslType] || {
        color: 'text-gray-700',
        bgColor: 'bg-gray-100 border-gray-200',
        label: sslType,
        description: '',
    };
}

function formatPrice(price: number | null | undefined, currency: string = 'IDR'): string {
    if (!price) return 'Hubungi Sales';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 0,
    }).format(price);
}

export default function SslIndex({ products, links, meta, filters = {} }: Props) {
    const [searchQuery, setSearchQuery] = useState(filters.name || '');
    const [selectedBrand, setSelectedBrand] = useState(filters.brand || 'all');
    const [selectedSslType, setSelectedSslType] = useState(filters.ssl_type || 'all');
    const [selectedWildcard, setSelectedWildcard] = useState(filters.is_wildcard || 'all');
    const breadcrumbs = getBreadcrumbs();
    const isCustomer = window.location.pathname.startsWith('/customer');

    const brands = Array.from(new Set(products.map((p) => p.brand))).sort();

    const handleSearch = (value: string) => {
        setSearchQuery(value);
        updateFilters({ name: value || undefined });
    };

    const handleFilterChange = (key: string, value: string) => {
        if (key === 'brand') setSelectedBrand(value);
        if (key === 'ssl_type') setSelectedSslType(value);
        if (key === 'is_wildcard') setSelectedWildcard(value);
        updateFilters({ [key]: value !== 'all' ? value : undefined });
    };

    const updateFilters = (newFilters: Record<string, string | undefined>) => {
        const basePath = isCustomer ? '/customer/ssl' : '/admin/ssl';
        const params = new URLSearchParams();
        const mergedFilters = { ...filters, ...newFilters };

        Object.entries(mergedFilters).forEach(([key, value]) => {
            if (value && value !== 'all' && key !== 'page') {
                params.set(key, value.toString());
            }
        });
        params.delete('page');
        router.get(`${basePath}?${params.toString()}`, {}, { preserveState: true, preserveScroll: false });
    };

    const handlePageChange = (url: string | null) => {
        if (!url) return;
        try {
            const urlObj = new URL(url);
            const params = new URLSearchParams(urlObj.search);
            const basePath = isCustomer ? '/customer/ssl' : '/admin/ssl';
            router.get(`${basePath}?${params.toString()}`, {}, { preserveState: true, preserveScroll: false });
        } catch {
            const pageMatch = url.match(/[?&]page=(\d+)/);
            if (pageMatch) {
                const page = pageMatch[1];
                const params = new URLSearchParams(window.location.search);
                params.set('page', page);
                const basePath = isCustomer ? '/customer/ssl' : '/admin/ssl';
                router.get(`${basePath}?${params.toString()}`, {}, { preserveState: true, preserveScroll: false });
            }
        }
    };

    const handlePurchase = (productId: number) => {
        alert(`Fitur purchase SSL untuk product ID ${productId} akan segera tersedia`);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={isCustomer ? 'SSL Certificates' : 'Manajemen SSL'} />
            <div className="p-4 lg:p-6 space-y-6">
                {/* Header */}
                <div className="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">
                            {isCustomer ? 'SSL Certificates' : 'Manajemen SSL'}
                        </h1>
                        <p className="text-muted-foreground mt-1 max-w-2xl">
                            {isCustomer
                                ? 'Pilih SSL certificate untuk mengamankan website Anda dengan enkripsi terpercaya'
                                : 'Kelola SSL certificates untuk semua customer'}
                        </p>
                    </div>
                </div>

                {/* SSL Type Explainer */}
                {isCustomer && (
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <Card className="border-blue-200 dark:border-blue-800 bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20">
                            <CardContent className="p-4">
                                <div className="flex items-center gap-3 mb-2">
                                    <div className="h-10 w-10 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center">
                                        <Zap className="h-5 w-5 text-white" />
                                    </div>
                                    <div>
                                        <p className="font-semibold">DV SSL</p>
                                        <p className="text-xs text-muted-foreground">Domain Validation</p>
                                    </div>
                                </div>
                                <p className="text-sm text-muted-foreground">
                                    Penerbitan cepat dalam hitungan menit. Cocok untuk blog dan website personal.
                                </p>
                            </CardContent>
                        </Card>
                        <Card className="border-emerald-200 dark:border-emerald-800 bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20">
                            <CardContent className="p-4">
                                <div className="flex items-center gap-3 mb-2">
                                    <div className="h-10 w-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-500 flex items-center justify-center">
                                        <Shield className="h-5 w-5 text-white" />
                                    </div>
                                    <div>
                                        <p className="font-semibold">OV SSL</p>
                                        <p className="text-xs text-muted-foreground">Organization Validation</p>
                                    </div>
                                </div>
                                <p className="text-sm text-muted-foreground">
                                    Validasi organisasi untuk kepercayaan lebih. Cocok untuk bisnis dan e-commerce.
                                </p>
                            </CardContent>
                        </Card>
                        <Card className="border-purple-200 dark:border-purple-800 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20">
                            <CardContent className="p-4">
                                <div className="flex items-center gap-3 mb-2">
                                    <div className="h-10 w-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                                        <Award className="h-5 w-5 text-white" />
                                    </div>
                                    <div>
                                        <p className="font-semibold">EV SSL</p>
                                        <p className="text-xs text-muted-foreground">Extended Validation</p>
                                    </div>
                                </div>
                                <p className="text-sm text-muted-foreground">
                                    Tingkat kepercayaan tertinggi. Cocok untuk bank dan aplikasi finansial.
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                )}

                {/* Filters */}
                <Card variant="premium">
                    <CardContent className="p-4">
                        <div className="flex flex-col md:flex-row gap-4">
                            <div className="flex-1">
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        placeholder="Cari berdasarkan nama SSL..."
                                        value={searchQuery}
                                        onChange={(e) => handleSearch(e.target.value)}
                                        className="pl-10"
                                    />
                                </div>
                            </div>
                            <div className="w-full md:w-48">
                                <Select value={selectedBrand} onValueChange={(value) => handleFilterChange('brand', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Filter Brand" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Semua Brand</SelectItem>
                                        {brands.map((brand) => (
                                            <SelectItem key={brand} value={brand}>{brand}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="w-full md:w-48">
                                <Select value={selectedSslType} onValueChange={(value) => handleFilterChange('ssl_type', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Filter Tipe" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Semua Tipe</SelectItem>
                                        <SelectItem value="DV">DV (Domain Validation)</SelectItem>
                                        <SelectItem value="OV">OV (Organization)</SelectItem>
                                        <SelectItem value="EV">EV (Extended)</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="w-full md:w-48">
                                <Select value={selectedWildcard} onValueChange={(value) => handleFilterChange('is_wildcard', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Filter Wildcard" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Semua</SelectItem>
                                        <SelectItem value="1">Wildcard</SelectItem>
                                        <SelectItem value="0">Non-Wildcard</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* SSL Products */}
                {products.length === 0 ? (
                    <Card variant="premium">
                        <CardContent className="p-12 text-center">
                            <div className="h-16 w-16 rounded-2xl bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)] flex items-center justify-center mx-auto mb-4">
                                <Shield className="h-8 w-8 text-white" />
                            </div>
                            <h3 className="text-lg font-semibold mb-2">Tidak Ada SSL Product Ditemukan</h3>
                            <p className="text-muted-foreground max-w-md mx-auto">
                                {searchQuery || selectedBrand !== 'all' || selectedSslType !== 'all' || selectedWildcard !== 'all'
                                    ? 'Coba sesuaikan filter pencarian Anda'
                                    : 'Tidak ada produk SSL yang tersedia saat ini'}
                            </p>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {products.map((product) => {
                            const isWildcard = typeof product.is_wildcard === 'number' ? product.is_wildcard === 1 : product.is_wildcard;
                            const isRefundable = typeof product.is_refundable === 'number' ? product.is_refundable === 1 : product.is_refundable;
                            const sslTypeConfig = getSslTypeConfig(product.ssl_type);

                            return (
                                <Card key={product.id} variant="premium" className="flex flex-col overflow-hidden group">
                                    {/* Type accent bar */}
                                    <div className={`h-1.5 ${
                                        product.ssl_type === 'DV' ? 'bg-gradient-to-r from-blue-500 to-cyan-500' :
                                        product.ssl_type === 'OV' ? 'bg-gradient-to-r from-emerald-500 to-green-500' :
                                        product.ssl_type === 'EV' ? 'bg-gradient-to-r from-purple-500 to-pink-500' :
                                        'bg-gradient-to-r from-gray-500 to-slate-500'
                                    }`} />

                                    <CardHeader className="pb-2">
                                        <div className="flex items-start justify-between">
                                            <div className="flex items-center gap-3">
                                                <div className={`h-12 w-12 rounded-xl flex items-center justify-center ${
                                                    product.ssl_type === 'DV' ? 'bg-gradient-to-br from-blue-500 to-cyan-500' :
                                                    product.ssl_type === 'OV' ? 'bg-gradient-to-br from-emerald-500 to-green-500' :
                                                    product.ssl_type === 'EV' ? 'bg-gradient-to-br from-purple-500 to-pink-500' :
                                                    'bg-gradient-to-br from-gray-500 to-slate-500'
                                                }`}>
                                                    <Lock className="h-6 w-6 text-white" />
                                                </div>
                                                <div>
                                                    <h3 className="font-semibold text-lg leading-tight">{product.name}</h3>
                                                    <p className="text-sm text-muted-foreground">{product.brand}</p>
                                                </div>
                                            </div>
                                        </div>

                                        {/* Badges */}
                                        <div className="flex flex-wrap gap-2 mt-3">
                                            <Badge variant="outline" className={sslTypeConfig.bgColor}>
                                                {product.ssl_type}
                                            </Badge>
                                            {isWildcard && (
                                                <Badge variant="outline" className="bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 border-orange-200 dark:border-orange-800">
                                                    <Star className="h-3 w-3 mr-1" />
                                                    Wildcard
                                                </Badge>
                                            )}
                                            {isRefundable && (
                                                <Badge variant="outline" className="bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 border-green-200 dark:border-green-800">
                                                    Refundable
                                                </Badge>
                                            )}
                                        </div>
                                    </CardHeader>

                                    <CardContent className="flex-1 flex flex-col pt-0">
                                        {/* Features */}
                                        {product.features && Object.keys(product.features).length > 0 && (
                                            <div className="space-y-2 mb-4 text-sm">
                                                {product.features.domain && (
                                                    <div className="flex items-start gap-2">
                                                        <CheckCircle2 className="h-4 w-4 text-emerald-600 mt-0.5 flex-shrink-0" />
                                                        <span className="text-muted-foreground">
                                                            <strong>Domain:</strong> {product.features.domain}
                                                        </span>
                                                    </div>
                                                )}
                                                {product.features.issuance && (
                                                    <div className="flex items-start gap-2">
                                                        <Clock className="h-4 w-4 text-blue-600 mt-0.5 flex-shrink-0" />
                                                        <span className="text-muted-foreground">
                                                            <strong>Issuance:</strong> {product.features.issuance}
                                                        </span>
                                                    </div>
                                                )}
                                                {product.features.warranty && (
                                                    <div className="flex items-start gap-2">
                                                        <Shield className="h-4 w-4 text-purple-600 mt-0.5 flex-shrink-0" />
                                                        <span className="text-muted-foreground">
                                                            <strong>Warranty:</strong> {product.features.warranty}
                                                        </span>
                                                    </div>
                                                )}
                                                {product.features.validation && (
                                                    <div className="flex items-start gap-2">
                                                        <Info className="h-4 w-4 text-gray-600 mt-0.5 flex-shrink-0" />
                                                        <span className="text-muted-foreground">{product.features.validation}</span>
                                                    </div>
                                                )}
                                            </div>
                                        )}

                                        <div className="text-xs text-muted-foreground mb-4">
                                            Max Period: {product.max_period} tahun
                                        </div>

                                        {/* Price & Action */}
                                        <div className="mt-auto pt-4 border-t">
                                            <div className="flex items-end justify-between mb-4">
                                                <div>
                                                    <p className="text-xs text-muted-foreground">Mulai dari</p>
                                                    <p className="text-2xl font-bold">
                                                        {formatPrice(product.price, product.currency)}
                                                    </p>
                                                    <p className="text-xs text-muted-foreground">/tahun</p>
                                                </div>
                                            </div>

                                            {isCustomer && (
                                                <Button
                                                    variant="gradient"
                                                    className="w-full"
                                                    onClick={() => handlePurchase(product.id)}
                                                    disabled={product.status !== 1}
                                                >
                                                    <ShoppingCart className="w-4 h-4 mr-2" />
                                                    {product.status === 1 ? 'Beli Sekarang' : 'Tidak Tersedia'}
                                                </Button>
                                            )}
                                        </div>
                                    </CardContent>
                                </Card>
                            );
                        })}
                    </div>
                )}

                {/* Pagination */}
                {meta && meta.last_page > 1 && (
                    <Card variant="premium">
                        <CardContent className="p-4">
                            <div className="flex flex-col md:flex-row items-center justify-between gap-4">
                                <div className="text-sm text-muted-foreground">
                                    Menampilkan {meta.from} sampai {meta.to} dari {meta.total} produk SSL
                                </div>
                                <div className="flex flex-wrap gap-2 justify-center">
                                    {links.prev && (
                                        <Button variant="outline" size="sm" onClick={() => handlePageChange(links.prev)}>
                                            Previous
                                        </Button>
                                    )}
                                    <span className="px-4 py-2 rounded-lg text-sm font-medium bg-gradient-to-r from-[var(--gradient-start)] to-[var(--gradient-end)] text-white">
                                        Page {meta.current_page} of {meta.last_page}
                                    </span>
                                    {links.next && (
                                        <Button variant="outline" size="sm" onClick={() => handlePageChange(links.next)}>
                                            Next
                                        </Button>
                                    )}
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
