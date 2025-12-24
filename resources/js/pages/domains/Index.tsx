import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { type BreadcrumbItem } from '@/types';
import { Search, Globe, Plus, CheckCircle2, Clock, XCircle } from 'lucide-react';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import 'dayjs/locale/id';

dayjs.extend(relativeTime);
dayjs.locale('id');

// Determine breadcrumbs based on route
const getBreadcrumbs = (): BreadcrumbItem[] => {
  const path = window.location.pathname;
  if (path.startsWith('/customer/domains')) {
    return [
      {
        title: 'Domain Saya',
        href: '/customer/domains',
      },
    ];
  }
  return [
    {
      title: 'Manajemen Domain',
      href: '/admin/domains',
    },
  ];
};

const breadcrumbs = getBreadcrumbs();

interface Domain {
  id: string;
  name: string;
  status: 'active' | 'pending' | 'expired';
  customer_id: string;
  customer?: {
    id: string;
    name: string;
    email: string;
  };
  rdash_domain_id?: number | null;
  rdash_sync_status?: 'pending' | 'synced' | 'failed' | null;
  rdash_synced_at?: string | null;
  rdash_verification_status?: number | null;
  rdash_required_document?: boolean;
  auto_renew?: boolean;
  created_at: string;
}

interface Props {
  domains: {
    data: Domain[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
  };
  filters?: {
    customer_id?: string;
    name?: string;
    status?: string;
  };
}

function getStatusBadge(status: string) {
  const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    active: 'default',
    pending: 'secondary',
    expired: 'destructive',
  };

  const labels: Record<string, string> = {
    active: 'Aktif',
    pending: 'Menunggu',
    expired: 'Kedaluwarsa',
  };

  return (
    <Badge variant={variants[status] || 'outline'}>
      {labels[status] || status.charAt(0).toUpperCase() + status.slice(1)}
    </Badge>
  );
}

function getRdashSyncStatusBadge(status?: string | null) {
  if (!status) return null;

  const variants: Record<string, 'default' | 'secondary' | 'destructive'> = {
    synced: 'default',
    pending: 'secondary',
    failed: 'destructive',
  };

  const colors: Record<string, string> = {
    synced: 'text-green-600',
    pending: 'text-yellow-600',
    failed: 'text-red-600',
  };

  const labels: Record<string, string> = {
    synced: 'Tersinkronisasi',
    pending: 'Menunggu',
    failed: 'Gagal',
  };

  return (
    <Badge variant={variants[status] || 'secondary'} className={colors[status]}>
      {labels[status] || status.charAt(0).toUpperCase() + status.slice(1)}
    </Badge>
  );
}

export default function DomainIndex({ domains, filters = {} }: Props) {
  const [searchQuery, setSearchQuery] = useState(filters.name || '');

  const handleSearch = (value: string) => {
    setSearchQuery(value);
    const basePath = window.location.pathname.startsWith('/customer') ? '/customer/domains' : '/admin/domains';
    router.get(basePath, { name: value || undefined }, { preserveState: true });
  };

  const handleFilterChange = (key: string, value: string) => {
    const basePath = window.location.pathname.startsWith('/customer') ? '/customer/domains' : '/admin/domains';
    router.get(basePath, { [key]: value || undefined }, { preserveState: true });
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={window.location.pathname.startsWith('/customer') ? 'Domain Saya' : 'Manajemen Domain'} />
      <div className="p-4 md:p-6 space-y-6">
        {/* Header */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">
              {window.location.pathname.startsWith('/customer') ? 'Domain Saya' : 'Manajemen Domain'}
            </h1>
            <p className="text-muted-foreground mt-1">
              {window.location.pathname.startsWith('/customer') 
                ? 'Kelola semua domain yang terdaftar melalui sistem. Daftarkan domain baru, pantau status verifikasi, dan kelola pengaturan domain Anda di sini.' 
                : 'Kelola semua domain yang terdaftar melalui sistem. Pantau status domain, verifikasi, dan kelola pengaturan untuk semua customer.'}
            </p>
          </div>
          <Link href={window.location.pathname.startsWith('/customer') ? '/customer/domains/create' : '/admin/domains/create'}>
            <Button className="w-full md:w-auto">
              <Plus className="w-4 h-4 mr-2" />
              Daftarkan Domain Baru
            </Button>
          </Link>
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
                    placeholder="Cari berdasarkan nama domain..."
                    value={searchQuery}
                    onChange={(e) => handleSearch(e.target.value)}
                    className="pl-10"
                  />
                </div>
              </div>

              {/* Status Filter */}
              <div className="w-full md:w-48">
                <Select
                  value={filters.status || 'all'}
                  onValueChange={(value) => handleFilterChange('status', value)}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Filter berdasarkan status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">Semua Status</SelectItem>
                    <SelectItem value="active">Aktif</SelectItem>
                    <SelectItem value="pending">Menunggu</SelectItem>
                    <SelectItem value="expired">Kedaluwarsa</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Domains List */}
        {domains.data.length === 0 ? (
          <Card>
            <CardContent className="p-12 text-center">
              <Globe className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
              <h3 className="text-lg font-semibold mb-2">Tidak Ada Domain Ditemukan</h3>
              <p className="text-muted-foreground mb-4">
                {searchQuery || filters.status
                  ? 'Coba sesuaikan filter pencarian Anda atau hapus filter untuk melihat semua domain'
                  : 'Mulai dengan mendaftarkan domain baru untuk mengelola website dan layanan online Anda'}
              </p>
              {!searchQuery && !filters.status && (
                <Link href={window.location.pathname.startsWith('/customer') ? '/customer/domains/create' : '/admin/domains/create'}>
                  <Button>Daftarkan Domain Baru</Button>
                </Link>
              )}
            </CardContent>
          </Card>
        ) : (
          <div className="space-y-4">
            {domains.data.map((domain) => (
              <Card key={domain.id} className="hover:shadow-lg transition-shadow">
                <CardContent className="p-5">
                  <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div className="flex-1">
                      <div className="flex items-center gap-3 mb-2">
                        <Globe className="h-5 w-5 text-primary" />
                        <h3 className="font-semibold text-lg">{domain.name}</h3>
                        {getStatusBadge(domain.status)}
                        {getRdashSyncStatusBadge(domain.rdash_sync_status)}
                      </div>
                      
                      {domain.customer && (
                        <div className="text-sm text-muted-foreground mb-2">
                          Customer: <strong>{domain.customer.name}</strong> ({domain.customer.email})
                        </div>
                      )}

                      <div className="flex flex-wrap gap-2 text-xs text-muted-foreground">
                        {domain.rdash_domain_id && (
                          <span>ID RDASH: {domain.rdash_domain_id}</span>
                        )}
                        {domain.auto_renew && (
                          <Badge variant="outline" className="text-xs">Perpanjangan Otomatis</Badge>
                        )}
                        {domain.rdash_required_document && (
                          <Badge variant="outline" className="text-xs bg-yellow-50 text-yellow-700 border-yellow-200">Dokumen Diperlukan</Badge>
                        )}
                        <span>Terdaftar {dayjs(domain.created_at).fromNow()}</span>
                      </div>
                    </div>

                    <div className="flex gap-2">
                      <Link href={`${window.location.pathname.startsWith('/customer') ? '/customer' : '/admin'}/domains/${domain.id}`}>
                        <Button size="sm" variant="outline">Lihat Detail</Button>
                      </Link>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}

        {/* Pagination */}
        {domains.links && domains.links.length > 3 && (
          <Card>
            <CardContent className="p-4">
              <div className="flex flex-col md:flex-row items-center justify-between gap-4">
                <div className="text-sm text-muted-foreground">
                  Menampilkan {((domains.current_page - 1) * domains.per_page) + 1} sampai{' '}
                  {Math.min(domains.current_page * domains.per_page, domains.total)} dari {domains.total} domain
                </div>
                <div className="flex flex-wrap gap-2 justify-center">
                  {domains.links.map((link, index) => (
                    <Link
                      key={index}
                      href={link.url || '#'}
                      className={`px-3 py-2 rounded-md text-sm font-medium transition-colors ${
                        link.active
                          ? 'bg-primary text-primary-foreground'
                          : 'bg-background hover:bg-muted text-foreground'
                      } ${!link.url ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''}`}
                      dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                  ))}
                </div>
              </div>
            </CardContent>
          </Card>
        )}
      </div>
    </AppLayout>
  );
}

