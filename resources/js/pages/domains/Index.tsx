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
import {
    Search,
    Globe,
    Plus,
    ExternalLink,
    Calendar,
    RefreshCw,
    Shield,
    AlertCircle,
    CheckCircle2,
    Clock,
    XCircle,
    Settings,
} from 'lucide-react';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import 'dayjs/locale/id';

dayjs.extend(relativeTime);
dayjs.locale('id');

// Determine breadcrumbs based on route
const getBreadcrumbs = (): BreadcrumbItem[] => {
    const path = window.location.pathname;
    if (path.startsWith('/customer/domains')) {
        return [{ title: 'Domain Saya', href: '/customer/domains' }];
    }
    return [{ title: 'Manajemen Domain', href: '/admin/domains' }];
};

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
    expires_at?: string;
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

function getStatusConfig(status: string) {
    const config: Record<string, { variant: 'success' | 'warning' | 'error' | 'info' | 'default'; icon: React.ElementType; label: string }> = {
        active: { variant: 'success', icon: CheckCircle2, label: 'Aktif' },
        pending: { variant: 'warning', icon: Clock, label: 'Menunggu' },
        expired: { variant: 'error', icon: XCircle, label: 'Kedaluwarsa' },
    };
    return config[status] || { variant: 'default', icon: Globe, label: status };
}

function getDaysUntilExpiry(expiresAt?: string) {
    if (!expiresAt) return null;
    const days = dayjs(expiresAt).diff(dayjs(), 'day');
    return days;
}

export default function DomainIndex({ domains, filters = {} }: Props) {
    const [searchQuery, setSearchQuery] = useState(filters.name || '');
    const breadcrumbs = getBreadcrumbs();
    const isCustomer = window.location.pathname.startsWith('/customer');

    const handleSearch = (value: string) => {
        setSearchQuery(value);
        const basePath = isCustomer ? '/customer/domains' : '/admin/domains';
        router.get(basePath, { name: value || undefined }, { preserveState: true });
    };

    const handleFilterChange = (key: string, value: string) => {
        const basePath = isCustomer ? '/customer/domains' : '/admin/domains';
        router.get(basePath, { [key]: value || undefined }, { preserveState: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={isCustomer ? 'Domain Saya' : 'Manajemen Domain'} />
            <div className="p-4 lg:p-6 space-y-6">
                {/* Header */}
                <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">
                            {isCustomer ? 'Domain Saya' : 'Manajemen Domain'}
                        </h1>
                        <p className="text-muted-foreground mt-1">
                            {isCustomer
                                ? 'Kelola domain yang terdaftar dan pantau status verifikasi'
                                : 'Kelola semua domain untuk semua customer'}
                        </p>
                    </div>
                    <Link href={isCustomer ? '/customer/domains/create' : '/admin/domains/create'}>
                        <Button variant="gradient" className="w-full lg:w-auto">
                            <Plus className="w-4 h-4 mr-2" />
                            Daftarkan Domain Baru
                        </Button>
                    </Link>
                </div>

                {/* Stats Cards */}
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card variant="stat">
                        <CardContent className="p-4">
                            <div className="flex items-center gap-3">
                                <div className="h-10 w-10 rounded-xl bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)] flex items-center justify-center">
                                    <Globe className="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <p className="text-2xl font-bold">{domains.total}</p>
                                    <p className="text-xs text-muted-foreground">Total Domain</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card variant="stat">
                        <CardContent className="p-4">
                            <div className="flex items-center gap-3">
                                <div className="h-10 w-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-500 flex items-center justify-center">
                                    <CheckCircle2 className="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <p className="text-2xl font-bold">
                                        {domains.data.filter(d => d.status === 'active').length}
                                    </p>
                                    <p className="text-xs text-muted-foreground">Aktif</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card variant="stat">
                        <CardContent className="p-4">
                            <div className="flex items-center gap-3">
                                <div className="h-10 w-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center">
                                    <Clock className="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <p className="text-2xl font-bold">
                                        {domains.data.filter(d => d.status === 'pending').length}
                                    </p>
                                    <p className="text-xs text-muted-foreground">Menunggu</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card variant="stat">
                        <CardContent className="p-4">
                            <div className="flex items-center gap-3">
                                <div className="h-10 w-10 rounded-xl bg-gradient-to-br from-red-500 to-rose-500 flex items-center justify-center">
                                    <AlertCircle className="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <p className="text-2xl font-bold">
                                        {domains.data.filter(d => d.status === 'expired').length}
                                    </p>
                                    <p className="text-xs text-muted-foreground">Kedaluwarsa</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filters */}
                <Card variant="premium">
                    <CardContent className="p-4">
                        <div className="flex flex-col md:flex-row gap-4">
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
                            <div className="w-full md:w-48">
                                <Select
                                    value={filters.status || 'all'}
                                    onValueChange={(value) => handleFilterChange('status', value)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Filter status" />
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
                    <Card variant="premium">
                        <CardContent className="p-12 text-center">
                            <div className="h-16 w-16 rounded-2xl bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)] flex items-center justify-center mx-auto mb-4">
                                <Globe className="h-8 w-8 text-white" />
                            </div>
                            <h3 className="text-lg font-semibold mb-2">Tidak Ada Domain Ditemukan</h3>
                            <p className="text-muted-foreground mb-6 max-w-md mx-auto">
                                {searchQuery || filters.status
                                    ? 'Coba sesuaikan filter pencarian Anda'
                                    : 'Mulai dengan mendaftarkan domain baru untuk website Anda'}
                            </p>
                            {!searchQuery && !filters.status && (
                                <Link href={isCustomer ? '/customer/domains/create' : '/admin/domains/create'}>
                                    <Button variant="gradient">
                                        <Plus className="w-4 h-4 mr-2" />
                                        Daftarkan Domain Baru
                                    </Button>
                                </Link>
                            )}
                        </CardContent>
                    </Card>
                ) : (
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        {domains.data.map((domain) => {
                            const statusConfig = getStatusConfig(domain.status);
                            const StatusIcon = statusConfig.icon;
                            const daysUntilExpiry = getDaysUntilExpiry(domain.expires_at);

                            return (
                                <Card
                                    key={domain.id}
                                    variant="premium"
                                    className="group"
                                >
                                    <CardContent className="p-5">
                                        <div className="flex items-start justify-between gap-4">
                                            <div className="flex-1 min-w-0">
                                                {/* Domain Name & Status */}
                                                <div className="flex items-center gap-3 mb-3">
                                                    <div className="h-10 w-10 rounded-xl bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)] flex items-center justify-center flex-shrink-0">
                                                        <Globe className="h-5 w-5 text-white" />
                                                    </div>
                                                    <div className="min-w-0">
                                                        <h3 className="font-semibold text-lg truncate">
                                                            {domain.name}
                                                        </h3>
                                                        <div className="flex items-center gap-2 mt-1">
                                                            <Badge variant={`${statusConfig.variant}-soft`}>
                                                                <StatusIcon className="h-3 w-3 mr-1" />
                                                                {statusConfig.label}
                                                            </Badge>
                                                            {domain.rdash_sync_status === 'synced' && (
                                                                <Badge variant="info-soft">
                                                                    <CheckCircle2 className="h-3 w-3 mr-1" />
                                                                    Synced
                                                                </Badge>
                                                            )}
                                                        </div>
                                                    </div>
                                                </div>

                                                {/* Customer info (admin only) */}
                                                {domain.customer && !isCustomer && (
                                                    <p className="text-sm text-muted-foreground mb-3">
                                                        <span className="font-medium">{domain.customer.name}</span>
                                                        <span className="mx-1">•</span>
                                                        {domain.customer.email}
                                                    </p>
                                                )}

                                                {/* Meta info */}
                                                <div className="flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                                                    <div className="flex items-center gap-1">
                                                        <Calendar className="h-4 w-4" />
                                                        <span>Terdaftar {dayjs(domain.created_at).fromNow()}</span>
                                                    </div>
                                                    {domain.auto_renew && (
                                                        <div className="flex items-center gap-1 text-emerald-600 dark:text-emerald-400">
                                                            <RefreshCw className="h-4 w-4" />
                                                            <span>Auto-renew</span>
                                                        </div>
                                                    )}
                                                    {domain.rdash_required_document && (
                                                        <Badge variant="warning-soft" className="text-xs">
                                                            <AlertCircle className="h-3 w-3 mr-1" />
                                                            Dokumen Diperlukan
                                                        </Badge>
                                                    )}
                                                </div>

                                                {/* Expiry warning */}
                                                {daysUntilExpiry !== null && daysUntilExpiry <= 30 && daysUntilExpiry > 0 && (
                                                    <div className="mt-3 p-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                                                        <p className="text-sm text-amber-700 dark:text-amber-400 flex items-center gap-2">
                                                            <AlertCircle className="h-4 w-4" />
                                                            Kedaluwarsa dalam {daysUntilExpiry} hari
                                                        </p>
                                                    </div>
                                                )}
                                            </div>

                                            {/* Actions */}
                                            <div className="flex flex-col gap-2">
                                                <Link href={`${isCustomer ? '/customer' : '/admin'}/domains/${domain.id}`}>
                                                    <Button variant="outline" size="sm" className="w-full">
                                                        <Settings className="h-4 w-4 mr-1" />
                                                        Kelola
                                                    </Button>
                                                </Link>
                                                <a
                                                    href={`https://${domain.name}`}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="inline-flex"
                                                >
                                                    <Button variant="ghost" size="sm" className="w-full">
                                                        <ExternalLink className="h-4 w-4 mr-1" />
                                                        Kunjungi
                                                    </Button>
                                                </a>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            );
                        })}
                    </div>
                )}

                {/* Pagination */}
                {domains.links && domains.links.length > 3 && (
                    <Card variant="premium">
                        <CardContent className="p-4">
                            <div className="flex flex-col md:flex-row items-center justify-between gap-4">
                                <div className="text-sm text-muted-foreground">
                                    Menampilkan {(domains.current_page - 1) * domains.per_page + 1} sampai{' '}
                                    {Math.min(domains.current_page * domains.per_page, domains.total)} dari {domains.total} domain
                                </div>
                                <div className="flex flex-wrap gap-2 justify-center">
                                    {domains.links.map((link, index) => (
                                        <Link
                                            key={index}
                                            href={link.url || '#'}
                                            className={`px-4 py-2 rounded-lg text-sm font-medium transition-all ${
                                                link.active
                                                    ? 'bg-gradient-to-r from-[var(--gradient-start)] to-[var(--gradient-end)] text-white shadow-md'
                                                    : 'bg-muted hover:bg-muted/80 text-foreground'
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
