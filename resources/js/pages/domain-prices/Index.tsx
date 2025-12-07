import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { type BreadcrumbItem } from '@/types';
import { Percent, Search, Tags, X, Loader2, Info } from 'lucide-react';
import axios from 'axios';

// Determine breadcrumbs based on route
const getBreadcrumbs = (): BreadcrumbItem[] => {
  const path = window.location.pathname;
  if (path.startsWith('/customer/domain-prices')) {
    return [
      {
        title: 'Domain Prices',
        href: '/customer/domain-prices',
      },
    ];
  }
  return [
    {
      title: 'Domain Management',
      href: '/admin/domains',
    },
    {
      title: 'Domain Prices',
      href: '/admin/domain-prices',
    },
  ];
};

const breadcrumbs = getBreadcrumbs();

interface DomainExtension {
  id: number;
  extension: string;
  status: number;
  status_label: string;
  status_badge: string;
  sell_option: number;
  enable_whois_protection: number;
  enable_whois_protection_label: string;
  enable_whois_protection_badge: string;
  registry_id: number;
  registry_name: string;
}

interface DomainPrice {
  id: number;
  extension: string;
  domain_extension: DomainExtension;
  currency: string;
  price: number;
  renew_price: number;
  transfer_price: number;
  redemption_price: number;
  proxy_price: number;
  registration: Record<string, number | string>;
  renewal: Record<string, number | string>;
  transfer: string;
  redemption: string;
  proxy: string;
  promo: boolean;
  promo_registration?: {
    registration: Record<string, string>;
    description?: string | null;
  } | null;
}

interface PaginationMeta {
  current_page: number;
  from: number;
  last_page: number;
  path: string;
  per_page: number;
  to: number;
  total: number;
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
}

interface PaginationLinks {
  first: string | null;
  last: string | null;
  prev: string | null;
  next: string | null;
}

interface Props {
  prices: {
    data: DomainPrice[];
    links: PaginationLinks;
    meta: PaginationMeta;
  };
  filters: {
    extension?: string;
    promo?: string;
    page?: number;
    limit?: number;
  };
}

function formatCurrency(amount: number | string | undefined | null, currency: string = 'IDR'): string {
  if (!amount || amount === '' || amount === '0' || amount === '0.00') {
    return '-';
  }

  const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;

  if (isNaN(numAmount) || numAmount <= 0) {
    return '-';
  }

  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency,
    minimumFractionDigits: 0,
  }).format(numAmount);
}

export default function DomainPricesIndex({ prices, filters = {} }: Props) {
  const [searchExtension, setSearchExtension] = useState(filters.extension || '');
  const [selectedPromo, setSelectedPromo] = useState(filters.promo || 'all');
  const [debounceTimer, setDebounceTimer] = useState<NodeJS.Timeout | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [selectedPriceId, setSelectedPriceId] = useState<number | null>(null);
  const [priceDetail, setPriceDetail] = useState<DomainPrice | null>(null);
  const [isLoadingDetail, setIsLoadingDetail] = useState(false);
  const [errorDetail, setErrorDetail] = useState<string | null>(null);

  const isCustomerRoute = window.location.pathname.startsWith('/customer/domain-prices');
  const basePath = isCustomerRoute ? '/customer/domain-prices' : '/admin/domain-prices';

  const handleSearch = (value: string) => {
    setSearchExtension(value);

    if (debounceTimer) {
      clearTimeout(debounceTimer);
    }

    const timer = setTimeout(() => {
      updateFilters({ extension: value || undefined });
    }, 500);

    setDebounceTimer(timer);
  };

  const handleFilterChange = (key: string, value: string) => {
    if (key === 'promo') {
      setSelectedPromo(value);
    }
    updateFilters({ [key]: value !== 'all' ? value : undefined });
  };

  const updateFilters = (newFilters: Record<string, string | number | undefined>) => {
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

    try {
      const urlObj = new URL(url);
      const params = new URLSearchParams(urlObj.search);
      router.get(`${basePath}?${params.toString()}`, {}, { preserveState: true, preserveScroll: false });
    } catch (e) {
      const pageMatch = url.match(/[?&]page=(\d+)/);
      if (pageMatch) {
        const page = pageMatch[1];
        const params = new URLSearchParams(window.location.search);
        params.set('page', page);
        router.get(`${basePath}?${params.toString()}`, {}, { preserveState: true, preserveScroll: false });
      }
    }
  };

  const handleResetFilters = () => {
    setSearchExtension('');
    setSelectedPromo('all');
    router.get(basePath, {}, { preserveState: true, preserveScroll: false });
  };

  const hasActiveFilters = searchExtension || (selectedPromo && selectedPromo !== 'all');

  const handleRowClick = (priceId: number) => {
    setSelectedPriceId(priceId);
    setIsModalOpen(true);
    setIsLoadingDetail(true);
    setErrorDetail(null);
    setPriceDetail(null);

    // Fetch price detail
    axios
      .get(`${basePath}/${priceId}`)
      .then((response) => {
        if (response.data.success && response.data.data) {
          setPriceDetail(response.data.data);
        } else {
          setErrorDetail('Gagal memuat detail harga domain');
        }
      })
      .catch((error) => {
        console.error('Error fetching price detail:', error);
        setErrorDetail(error.response?.data?.message || 'Gagal memuat detail harga domain');
      })
      .finally(() => {
        setIsLoadingDetail(false);
      });
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setSelectedPriceId(null);
    setPriceDetail(null);
    setErrorDetail(null);
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Domain Prices" />
      <div className="p-4 md:p-6 space-y-6">
        {/* Header */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Domain Prices</h1>
            <p className="text-muted-foreground mt-1">
              {isCustomerRoute
                ? 'Lihat harga domain terbaru untuk berbagai ekstensi. Pilih domain dengan harga terbaik untuk kebutuhan Anda.'
                : 'Lihat harga domain dari RDASH untuk setiap ekstensi. Data ini bersifat read-only dan mengikuti harga terbaru dari RDASH.'}
            </p>
          </div>
        </div>

        {/* Filters */}
        <Card>
          <CardContent className="p-4">
            <div className="flex flex-col md:flex-row gap-4 items-end">
              <div className="flex-1 w-full">
                <label className="text-sm font-medium mb-2 block">Domain Extension</label>
                <div className="relative">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                  <Input
                    placeholder="Filter by domain extension. Example: .co.id"
                    value={searchExtension}
                    onChange={(e) => handleSearch(e.target.value)}
                    className="pl-10"
                  />
                </div>
              </div>
              <div className="w-full md:w-48">
                <label className="text-sm font-medium mb-2 block">Filter by domain promotion</label>
                <Select value={selectedPromo} onValueChange={(value) => handleFilterChange('promo', value)}>
                  <SelectTrigger>
                    <SelectValue placeholder="All" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All</SelectItem>
                    <SelectItem value="true">Promo</SelectItem>
                    <SelectItem value="false">Normal</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              {hasActiveFilters && (
                <Button
                  type="button"
                  variant="outline"
                  onClick={handleResetFilters}
                  className="w-full md:w-auto"
                >
                  <X className="w-4 h-4 mr-2" />
                  Reset
                </Button>
              )}
            </div>
          </CardContent>
        </Card>

        {/* Prices Table */}
        <Card>
          <CardHeader className="flex flex-row items-center justify-between gap-4">
            <CardTitle className="flex items-center gap-2">
              <Tags className="w-5 h-5" />
              Daftar Harga Domain
            </CardTitle>
          </CardHeader>
          <CardContent className="p-0">
            {prices.data.length === 0 ? (
              <div className="p-8 text-center text-muted-foreground">
                Tidak ada data harga domain yang ditemukan.
              </div>
            ) : (
              <div className="overflow-x-auto">
                <table className="min-w-full text-sm">
                  <thead className="bg-muted/40">
                    <tr>
                      <th className="px-4 py-3 text-left font-medium text-xs text-muted-foreground uppercase tracking-wide">
                        Extension
                      </th>
                      <th className="px-4 py-3 text-right font-medium text-xs text-muted-foreground uppercase tracking-wide">
                        Registration (1 tahun)
                      </th>
                      <th className="px-4 py-3 text-right font-medium text-xs text-muted-foreground uppercase tracking-wide">
                        Renewal (1 tahun)
                      </th>
                      <th className="px-4 py-3 text-right font-medium text-xs text-muted-foreground uppercase tracking-wide">
                        Transfer
                      </th>
                      <th className="px-4 py-3 text-right font-medium text-xs text-muted-foreground uppercase tracking-wide">
                        Redemption
                      </th>
                      <th className="px-4 py-3 text-right font-medium text-xs text-muted-foreground uppercase tracking-wide">
                        Proxy
                      </th>
                      <th className="px-4 py-3 text-center font-medium text-xs text-muted-foreground uppercase tracking-wide">
                        Promo
                      </th>
                      {!isCustomerRoute && (
                        <th className="px-4 py-3 text-left font-medium text-xs text-muted-foreground uppercase tracking-wide">
                          Registry
                        </th>
                      )}
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-border/60">
                    {prices.data.map((price) => {
                      const promoPrice = price.promo_registration?.registration?.['1'];
                      const hasPromo = price.promo && !!promoPrice;

                      return (
                        <tr
                          key={price.id}
                          className="hover:bg-muted/40 cursor-pointer"
                          onClick={() => handleRowClick(price.id)}
                        >
                          <td className="px-4 py-3 whitespace-nowrap">
                            <div className="flex flex-col">
                              <span className="text-sm font-medium">{price.extension}</span>
                              {price.domain_extension.status_badge === 'success' && (
                                <Badge variant="outline" className="w-fit mt-1 text-xs">
                                  {price.domain_extension.status_label}
                                </Badge>
                              )}
                            </div>
                          </td>
                          <td className="px-4 py-3 text-right whitespace-nowrap">
                            <div className="flex flex-col items-end">
                              {hasPromo ? (
                                <>
                                  <span className="text-sm font-medium text-emerald-600">
                                    {formatCurrency(promoPrice, price.currency)}
                                  </span>
                                  <span className="text-xs text-muted-foreground line-through">
                                    {formatCurrency(price.price, price.currency)}
                                  </span>
                                </>
                              ) : (
                                <span className="text-sm font-medium">
                                  {formatCurrency(price.price, price.currency)}
                                </span>
                              )}
                            </div>
                          </td>
                          <td className="px-4 py-3 text-right whitespace-nowrap">
                            <span className="text-sm font-medium">
                              {formatCurrency(price.renew_price, price.currency)}
                            </span>
                          </td>
                          <td className="px-4 py-3 text-right whitespace-nowrap">
                            <span className="text-sm font-medium">
                              {formatCurrency(price.transfer, price.currency)}
                            </span>
                          </td>
                          <td className="px-4 py-3 text-right whitespace-nowrap">
                            <span className="text-sm font-medium">
                              {formatCurrency(price.redemption, price.currency)}
                            </span>
                          </td>
                          <td className="px-4 py-3 text-right whitespace-nowrap">
                            <span className="text-sm font-medium">
                              {formatCurrency(price.proxy, price.currency)}
                            </span>
                          </td>
                          <td className="px-4 py-3 text-center">
                            {hasPromo ? (
                              <Badge
                                variant="outline"
                                className="bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800 flex items-center gap-1 justify-center"
                              >
                                <Percent className="w-3 h-3" />
                                Promo
                              </Badge>
                            ) : (
                              <span className="text-xs text-muted-foreground">-</span>
                            )}
                          </td>
                          {!isCustomerRoute && (
                            <td className="px-4 py-3 whitespace-nowrap">
                              <span className="text-xs text-muted-foreground">
                                {price.domain_extension.registry_name}
                              </span>
                            </td>
                          )}
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </div>
            )}
          </CardContent>
        </Card>

        {/* Pagination */}
        {prices.meta && prices.meta.links && prices.meta.links.length > 3 && (
          <Card>
            <CardContent className="p-4">
              <div className="flex flex-col md:flex-row items-center justify-between gap-4">
                <div className="text-sm text-muted-foreground">
                  Menampilkan {prices.meta.from} sampai {prices.meta.to} dari {prices.meta.total} harga domain
                </div>
                <div className="flex flex-wrap gap-2 justify-center">
                  {prices.meta.links.map((link, index) => (
                    <Link
                      key={index}
                      href={link.url || '#'}
                      className={`px-3 py-2 rounded-md text-sm font-medium transition-colors ${
                        link.active
                          ? 'bg-primary text-primary-foreground'
                          : 'bg-background hover:bg-muted text-foreground'
                      } ${!link.url ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''}`}
                      dangerouslySetInnerHTML={{ __html: link.label }}
                      onClick={(e) => {
                        if (link.url) {
                          e.preventDefault();
                          handlePageChange(link.url);
                        }
                      }}
                    />
                  ))}
                </div>
              </div>
            </CardContent>
          </Card>
        )}

        {/* Detail Modal */}
        <Dialog open={isModalOpen} onOpenChange={setIsModalOpen}>
          <DialogContent className="max-w-5xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
              <DialogTitle className="flex items-center gap-2 text-xl">
                {priceDetail ? (
                  <>
                    {priceDetail.extension}
                    {priceDetail.promo && priceDetail.promo_registration?.registration && (
                      <Badge className="ml-2 bg-emerald-600 text-white">
                        <Percent className="w-3 h-3 mr-1" />
                        Promo
                      </Badge>
                    )}
                  </>
                ) : (
                  'Detail Harga Domain'
                )}
              </DialogTitle>
            </DialogHeader>

            {isLoadingDetail ? (
              <div className="flex items-center justify-center py-12">
                <Loader2 className="h-8 w-8 animate-spin text-muted-foreground" />
                <span className="ml-2 text-muted-foreground">Memuat detail harga...</span>
              </div>
            ) : errorDetail ? (
              <div className="flex flex-col items-center justify-center py-12">
                <X className="h-8 w-8 text-destructive mb-2" />
                <p className="text-destructive">{errorDetail}</p>
                <Button variant="outline" onClick={handleCloseModal} className="mt-4">
                  Tutup
                </Button>
              </div>
            ) : priceDetail ? (
              <div className="space-y-6">
                {/* Main Price Table */}
                <div className="overflow-x-auto">
                  <table className="min-w-full text-sm border-collapse">
                    <thead>
                      <tr className="border-b">
                        <th className="px-4 py-3 text-left font-semibold uppercase text-xs tracking-wide">YEAR</th>
                        <th className="px-4 py-3 text-right font-semibold uppercase text-xs tracking-wide">REGISTRATION</th>
                        <th className="px-4 py-3 text-right font-semibold uppercase text-xs tracking-wide">RENEWAL</th>
                        <th className="px-4 py-3 text-right font-semibold uppercase text-xs tracking-wide">TRANSFER</th>
                        <th className="px-4 py-3 text-right font-semibold uppercase text-xs tracking-wide">REDEMPTION</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y">
                      {Array.from({ length: 10 }, (_, i) => i + 1).map((year) => {
                        const yearKey = year.toString();
                        // Try multiple ways to access the data
                        const normalPrice = 
                          priceDetail.registration?.[yearKey] || 
                          priceDetail.registration?.[year] ||
                          (priceDetail.registration && typeof priceDetail.registration === 'object' ? priceDetail.registration[yearKey] : null);
                        const promoPrice = 
                          priceDetail.promo_registration?.registration?.[yearKey] || 
                          priceDetail.promo_registration?.registration?.[year] ||
                          (priceDetail.promo_registration?.registration && typeof priceDetail.promo_registration.registration === 'object' ? priceDetail.promo_registration.registration[yearKey] : null);
                        const hasPromo = promoPrice && promoPrice !== '' && promoPrice !== null && promoPrice !== undefined;
                        const renewalPrice = 
                          priceDetail.renewal?.[yearKey] || 
                          priceDetail.renewal?.[year] ||
                          (priceDetail.renewal && typeof priceDetail.renewal === 'object' ? priceDetail.renewal[yearKey] : null);
                        const transferPrice = year === 1 ? (priceDetail.transfer || priceDetail.transfer_price || null) : null;
                        const redemptionPrice = year === 1 ? (priceDetail.redemption || priceDetail.redemption_price || null) : null;

                        return (
                          <tr key={year} className="hover:bg-muted/40">
                            <td className="px-4 py-3 font-medium">{year}</td>
                            <td className="px-4 py-3 text-right">
                              {hasPromo ? (
                                <div className="flex flex-col items-end gap-1">
                                  <span className="text-red-600 dark:text-red-400 font-medium">
                                    {formatCurrency(promoPrice, priceDetail.currency)}
                                  </span>
                                  <span className="text-xs text-muted-foreground line-through">
                                    {formatCurrency(normalPrice, priceDetail.currency)}
                                  </span>
                                </div>
                              ) : normalPrice && normalPrice !== 0 ? (
                                <span>{formatCurrency(normalPrice, priceDetail.currency)}</span>
                              ) : (
                                <span className="text-muted-foreground">-</span>
                              )}
                            </td>
                            <td className="px-4 py-3 text-right">
                              {renewalPrice && renewalPrice !== 0 ? (
                                <span>{formatCurrency(renewalPrice, priceDetail.currency)}</span>
                              ) : (
                                <span className="text-muted-foreground">-</span>
                              )}
                            </td>
                            <td className="px-4 py-3 text-right">
                              {transferPrice && transferPrice !== '0' && transferPrice !== '0.00' ? (
                                <span>{formatCurrency(transferPrice, priceDetail.currency)}</span>
                              ) : (
                                <span className="text-muted-foreground">-</span>
                              )}
                            </td>
                            <td className="px-4 py-3 text-right">
                              {redemptionPrice && redemptionPrice !== '0' && redemptionPrice !== '0.00' ? (
                                <span>{formatCurrency(redemptionPrice, priceDetail.currency)}</span>
                              ) : (
                                <span className="text-muted-foreground">-</span>
                              )}
                            </td>
                          </tr>
                        );
                      })}
                    </tbody>
                  </table>
                </div>

                {/* Promo Terms */}
                {priceDetail.promo_registration?.description && (
                  <div className="space-y-3 pt-4 border-t">
                    <h3 className="text-lg font-semibold">Ketentuan Promo:</h3>
                    <div
                      className="prose prose-sm max-w-none dark:prose-invert [&_ul]:list-none [&_ul]:space-y-2 [&_li]:flex [&_li]:items-start [&_li]:gap-2 [&_li]:before:content-[''] [&_li]:before:w-2 [&_li]:before:h-2 [&_li]:before:mt-2 [&_li]:before:flex-shrink-0 [&_li]:before:bg-orange-500 [&_li]:before:rounded-sm [&_li]:before:rotate-45 [&_p]:m-0 [&_strong]:font-semibold"
                      dangerouslySetInnerHTML={{
                        __html: priceDetail.promo_registration.description,
                      }}
                    />
                  </div>
                )}
              </div>
            ) : null}
          </DialogContent>
        </Dialog>
      </div>
    </AppLayout>
  );
}

