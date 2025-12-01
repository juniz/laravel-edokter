import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { type BreadcrumbItem } from '@/types';
import { Search, Shield, CheckCircle2, XCircle, Info, ShoppingCart } from 'lucide-react';

// Determine breadcrumbs based on route
const getBreadcrumbs = (): BreadcrumbItem[] => {
  const path = window.location.pathname;
  if (path.startsWith('/customer/ssl')) {
    return [
      {
        title: 'SSL Certificates',
        href: '/customer/ssl',
      },
    ];
  }
  return [
    {
      title: 'Manajemen SSL',
      href: '/admin/ssl',
    },
  ];
};

const breadcrumbs = getBreadcrumbs();

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

function getSslTypeBadge(sslType: string) {
  const variants: Record<string, 'default' | 'secondary' | 'outline'> = {
    DV: 'default',
    OV: 'secondary',
    EV: 'outline',
  };

  const colors: Record<string, string> = {
    DV: 'bg-blue-100 text-blue-800 border-blue-200',
    OV: 'bg-green-100 text-green-800 border-green-200',
    EV: 'bg-purple-100 text-purple-800 border-purple-200',
  };

  return (
    <Badge variant="outline" className={colors[sslType] || ''}>
      {sslType}
    </Badge>
  );
}

function getWildcardBadge(isWildcard: number | boolean) {
  const isWild = typeof isWildcard === 'number' ? isWildcard === 1 : isWildcard;
  if (!isWild) return null;

  return (
    <Badge variant="outline" className="bg-orange-100 text-orange-800 border-orange-200">
      Wildcard
    </Badge>
  );
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

  // Get unique brands from products
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
    const basePath = window.location.pathname.startsWith('/customer') ? '/customer/ssl' : '/admin/ssl';
    const params = new URLSearchParams();
    
    // Merge existing filters with new filters
    const mergedFilters = { ...filters, ...newFilters };
    
    Object.entries(mergedFilters).forEach(([key, value]) => {
      if (value && value !== 'all' && key !== 'page') {
        params.set(key, value.toString());
      }
    });

    // Reset to page 1 when filters change
    params.delete('page');

    router.get(`${basePath}?${params.toString()}`, {}, { preserveState: true, preserveScroll: false });
  };

  const handlePageChange = (url: string | null) => {
    if (!url) return;
    
    // Extract query params from full URL
    try {
      const urlObj = new URL(url);
      const params = new URLSearchParams(urlObj.search);
      const basePath = window.location.pathname.startsWith('/customer') ? '/customer/ssl' : '/admin/ssl';
      
      router.get(`${basePath}?${params.toString()}`, {}, { preserveState: true, preserveScroll: false });
    } catch (e) {
      // If URL parsing fails, try to extract page number from URL
      const pageMatch = url.match(/[?&]page=(\d+)/);
      if (pageMatch) {
        const page = pageMatch[1];
        const params = new URLSearchParams(window.location.search);
        params.set('page', page);
        const basePath = window.location.pathname.startsWith('/customer') ? '/customer/ssl' : '/admin/ssl';
        router.get(`${basePath}?${params.toString()}`, {}, { preserveState: true, preserveScroll: false });
      }
    }
  };

  const handlePurchase = (productId: number) => {
    // TODO: Implement purchase flow - redirect to purchase page
    // router.visit(`/customer/ssl/${productId}/purchase`);
    alert(`Fitur purchase SSL untuk product ID ${productId} akan segera tersedia`);
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={window.location.pathname.startsWith('/customer') ? 'SSL Certificates' : 'Manajemen SSL'} />
      <div className="p-4 md:p-6 space-y-6">
        {/* Header */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">
              {window.location.pathname.startsWith('/customer') ? 'SSL Certificates' : 'Manajemen SSL'}
            </h1>
            <p className="text-muted-foreground mt-1">
              {window.location.pathname.startsWith('/customer')
                ? 'Pilih dan beli SSL certificate untuk mengamankan website Anda. Tersedia berbagai pilihan SSL dari brand terpercaya dengan harga kompetitif.'
                : 'Kelola SSL certificates untuk semua customer. Monitor status, manage orders, dan lihat statistik SSL.'}
            </p>
          </div>
        </div>

        {/* Filters */}
        <Card>
          <CardContent className="p-4">
            <div className="flex flex-col md:flex-row gap-4">
              {/* Search */}
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

              {/* Brand Filter */}
              <div className="w-full md:w-48">
                <Select
                  value={selectedBrand}
                  onValueChange={(value) => handleFilterChange('brand', value)}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Filter Brand" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">Semua Brand</SelectItem>
                    {brands.map((brand) => (
                      <SelectItem key={brand} value={brand}>
                        {brand}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>

              {/* SSL Type Filter */}
              <div className="w-full md:w-48">
                <Select
                  value={selectedSslType}
                  onValueChange={(value) => handleFilterChange('ssl_type', value)}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Filter Tipe SSL" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">Semua Tipe</SelectItem>
                    <SelectItem value="DV">Domain Validation (DV)</SelectItem>
                    <SelectItem value="OV">Organization Validation (OV)</SelectItem>
                    <SelectItem value="EV">Extended Validation (EV)</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              {/* Wildcard Filter */}
              <div className="w-full md:w-48">
                <Select
                  value={selectedWildcard}
                  onValueChange={(value) => handleFilterChange('is_wildcard', value)}
                >
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

        {/* SSL Products List */}
        {products.length === 0 ? (
          <Card>
            <CardContent className="p-12 text-center">
              <Shield className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
              <h3 className="text-lg font-semibold mb-2">Tidak Ada SSL Product Ditemukan</h3>
              <p className="text-muted-foreground">
                {searchQuery || selectedBrand !== 'all' || selectedSslType !== 'all' || selectedWildcard !== 'all'
                  ? 'Coba sesuaikan filter pencarian Anda atau hapus filter untuk melihat semua produk SSL'
                  : 'Tidak ada produk SSL yang tersedia saat ini'}
              </p>
            </CardContent>
          </Card>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {products.map((product) => {
              const isWildcard = typeof product.is_wildcard === 'number' ? product.is_wildcard === 1 : product.is_wildcard;
              const isRefundable = typeof product.is_refundable === 'number' ? product.is_refundable === 1 : product.is_refundable;

              return (
                <Card key={product.id} className="hover:shadow-lg transition-shadow flex flex-col">
                  <CardHeader>
                    <div className="flex items-start justify-between mb-2">
                      <div className="flex items-center gap-2">
                        <Shield className="h-5 w-5 text-primary" />
                        <CardTitle className="text-lg">{product.name}</CardTitle>
                      </div>
                    </div>
                    <div className="flex flex-wrap gap-2 mb-2">
                      {getSslTypeBadge(product.ssl_type)}
                      {getWildcardBadge(product.is_wildcard)}
                      {isRefundable && (
                        <Badge variant="outline" className="bg-green-50 text-green-700 border-green-200">
                          Refundable
                        </Badge>
                      )}
                    </div>
                    <div className="text-sm text-muted-foreground">
                      <div className="font-medium">{product.brand}</div>
                      <div className="text-xs mt-1">Max Period: {product.max_period} tahun</div>
                    </div>
                  </CardHeader>
                  <CardContent className="flex-1 flex flex-col">
                    {/* Features */}
                    {product.features && Object.keys(product.features).length > 0 && (
                      <div className="space-y-2 mb-4">
                        {product.features.domain && (
                          <div className="flex items-start gap-2 text-sm">
                            <CheckCircle2 className="h-4 w-4 text-green-600 mt-0.5 flex-shrink-0" />
                            <span className="text-muted-foreground">
                              <strong>Domain:</strong> {product.features.domain}
                            </span>
                          </div>
                        )}
                        {product.features.issuance && (
                          <div className="flex items-start gap-2 text-sm">
                            <Info className="h-4 w-4 text-blue-600 mt-0.5 flex-shrink-0" />
                            <span className="text-muted-foreground">
                              <strong>Issuance:</strong> {product.features.issuance}
                            </span>
                          </div>
                        )}
                        {product.features.warranty && (
                          <div className="flex items-start gap-2 text-sm">
                            <Shield className="h-4 w-4 text-purple-600 mt-0.5 flex-shrink-0" />
                            <span className="text-muted-foreground">
                              <strong>Warranty:</strong> {product.features.warranty}
                            </span>
                          </div>
                        )}
                        {product.features.validation && (
                          <div className="flex items-start gap-2 text-sm">
                            <CheckCircle2 className="h-4 w-4 text-green-600 mt-0.5 flex-shrink-0" />
                            <span className="text-muted-foreground">{product.features.validation}</span>
                          </div>
                        )}
                        {product.features.description && (
                          <div className="text-xs text-muted-foreground mt-2 italic">
                            {product.features.description}
                          </div>
                        )}
                      </div>
                    )}

                    {/* Price */}
                    <div className="mt-auto pt-4 border-t">
                      <div className="flex items-center justify-between mb-4">
                        <div>
                          <div className="text-2xl font-bold">
                            {formatPrice(product.price, product.currency)}
                          </div>
                          <div className="text-xs text-muted-foreground">per tahun</div>
                        </div>
                      </div>

                      {/* Purchase Button */}
                      {window.location.pathname.startsWith('/customer') && (
                        <Button
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
          <Card>
            <CardContent className="p-4">
              <div className="flex flex-col md:flex-row items-center justify-between gap-4">
                <div className="text-sm text-muted-foreground">
                  Menampilkan {meta.from} sampai {meta.to} dari {meta.total} produk SSL
                </div>
                <div className="flex flex-wrap gap-2 justify-center">
                  {links.first && (
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => handlePageChange(links.first)}
                    >
                      First
                    </Button>
                  )}
                  {links.prev && (
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => handlePageChange(links.prev)}
                    >
                      Previous
                    </Button>
                  )}
                  <span className="px-3 py-2 rounded-md text-sm font-medium bg-primary text-primary-foreground">
                    Page {meta.current_page} of {meta.last_page}
                  </span>
                  {links.next && (
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => handlePageChange(links.next)}
                    >
                      Next
                    </Button>
                  )}
                  {links.last && (
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => handlePageChange(links.last)}
                    >
                      Last
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

