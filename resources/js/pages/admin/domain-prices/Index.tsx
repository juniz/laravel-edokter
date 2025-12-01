import React, { useMemo } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { Percent, Search, Tags } from 'lucide-react';

interface DomainPrice {
  id: number;
  extension: string;
  price: number;
  renew_price: number;
  transfer_price: number;
  currency: string;
  promo: boolean;
  redemption_price?: number;
  [key: string]: unknown;
}

interface Props {
  prices: DomainPrice[];
  filters: {
    extension?: string;
    promo?: string;
  };
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Domain Management',
    href: '/admin/domains',
  },
  {
    title: 'Domain Prices',
    href: '/admin/domain-prices',
  },
];

function formatCurrency(amount: number | undefined | null, currency: string = 'IDR'): string {
  if (!amount || amount <= 0) {
    return '-';
  }

  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency,
    minimumFractionDigits: 0,
  }).format(amount);
}

export default function DomainPricesIndex({ prices, filters }: Props) {
  const [searchExtension, setSearchExtension] = React.useState(filters.extension ?? '');

  const filteredPrices = useMemo(() => {
    return prices.filter((price) => {
      const matchExtension =
        !searchExtension || price.extension.toLowerCase().includes(searchExtension.toLowerCase());

      return matchExtension;
    });
  }, [prices, searchExtension]);

  const handleResetFilter = () => {
    setSearchExtension('');
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
              Lihat harga domain dari RDASH untuk setiap ekstensi. Data ini bersifat read-only dan
              mengikuti harga terbaru dari RDASH.
            </p>
          </div>
        </div>

        {/* Filters */}
        <Card>
          <CardContent className="p-4">
            <div className="flex flex-col md:flex-row gap-4 items-center">
              <div className="flex-1 w-full">
                <div className="relative">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                  <Input
                    placeholder="Cari ekstensi, misal .co.id, .id, .com ..."
                    value={searchExtension}
                    onChange={(e) => setSearchExtension(e.target.value)}
                    className="pl-10"
                  />
                </div>
              </div>
              <div className="flex gap-2">
                <Button
                  type="button"
                  variant="outline"
                  onClick={handleResetFilter}
                  disabled={!searchExtension}
                >
                  Reset
                </Button>
              </div>
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
            {filteredPrices.length === 0 ? (
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
                        Registration
                      </th>
                      <th className="px-4 py-3 text-right font-medium text-xs text-muted-foreground uppercase tracking-wide">
                        Renewal
                      </th>
                      <th className="px-4 py-3 text-right font-medium text-xs text-muted-foreground uppercase tracking-wide">
                        Transfer
                      </th>
                      <th className="px-4 py-3 text-right font-medium text-xs text-muted-foreground uppercase tracking-wide">
                        Restore
                      </th>
                      <th className="px-4 py-3 text-center font-medium text-xs text-muted-foreground uppercase tracking-wide">
                        Promo
                      </th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-border/60">
                    {filteredPrices.map((price) => {
                      const redemption =
                        (price.redemption_price as number | undefined) ??
                        (price.metadata &&
                          (price.metadata as { redemption_price?: number }).redemption_price);

                      return (
                        <tr key={price.id} className="hover:bg-muted/40">
                          <td className="px-4 py-3 whitespace-nowrap text-sm font-medium">
                            {price.extension}
                          </td>
                          <td className="px-4 py-3 text-right whitespace-nowrap">
                            {formatCurrency(price.price, price.currency)}
                          </td>
                          <td className="px-4 py-3 text-right whitespace-nowrap">
                            {formatCurrency(price.renew_price, price.currency)}
                          </td>
                          <td className="px-4 py-3 text-right whitespace-nowrap">
                            {formatCurrency(price.transfer_price, price.currency)}
                          </td>
                          <td className="px-4 py-3 text-right whitespace-nowrap">
                            {formatCurrency(redemption ?? 0, price.currency)}
                          </td>
                          <td className="px-4 py-3 text-center">
                            {price.promo ? (
                              <Badge
                                variant="outline"
                                className="bg-emerald-50 text-emerald-700 border-emerald-200 flex items-center gap-1 justify-center"
                              >
                                <Percent className="w-3 h-3" />
                                Promo
                              </Badge>
                            ) : (
                              <span className="text-xs text-muted-foreground">Normal</span>
                            )}
                          </td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}


