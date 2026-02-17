import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Server,
    Calendar,
    RefreshCw,
    ArrowRight,
    Clock,
    AlertCircle,
    CheckCircle2,
    XCircle,
    Zap,
    HardDrive,
    Plus,
    Settings,
} from 'lucide-react';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import 'dayjs/locale/id';

dayjs.extend(relativeTime);
dayjs.locale('id');

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Subscriptions', href: '/customer/subscriptions' },
];

interface Subscription {
    id: string;
    status: string;
    product: {
        name: string;
        type?: string;
    };
    plan: {
        code: string;
        name?: string;
    } | null;
    start_at: string;
    end_at?: string;
    next_renewal_at?: string;
    auto_renew: boolean;
    provisioning_status?: string;
}

interface SubscriptionsProps {
    subscriptions: Subscription[];
}

function getStatusConfig(status: string) {
    const config: Record<string, { variant: 'success' | 'warning' | 'error' | 'info' | 'default'; icon: React.ElementType; label: string; color: string }> = {
        trialing: { variant: 'info', icon: Clock, label: 'Trial', color: 'from-blue-500 to-cyan-500' },
        active: { variant: 'success', icon: CheckCircle2, label: 'Aktif', color: 'from-emerald-500 to-green-500' },
        past_due: { variant: 'warning', icon: AlertCircle, label: 'Jatuh Tempo', color: 'from-amber-500 to-orange-500' },
        suspended: { variant: 'warning', icon: AlertCircle, label: 'Disuspend', color: 'from-orange-500 to-red-500' },
        cancelled: { variant: 'error', icon: XCircle, label: 'Dibatalkan', color: 'from-red-500 to-rose-500' },
        terminated: { variant: 'default', icon: XCircle, label: 'Dihentikan', color: 'from-gray-500 to-slate-500' },
    };
    return config[status] || { variant: 'default', icon: Server, label: status, color: 'from-gray-500 to-slate-500' };
}

function getDaysUntilRenewal(renewalDate?: string) {
    if (!renewalDate) return null;
    return dayjs(renewalDate).diff(dayjs(), 'day');
}

function getProductIcon(type?: string) {
    switch (type) {
        case 'vps':
            return Zap;
        case 'hosting_shared':
        default:
            return HardDrive;
    }
}

export default function Subscriptions({ subscriptions }: SubscriptionsProps) {
    const activeCount = subscriptions.filter(s => s.status === 'active').length;
    const trialCount = subscriptions.filter(s => s.status === 'trialing').length;
    const expiringSoonCount = subscriptions.filter(s => {
        const days = getDaysUntilRenewal(s.next_renewal_at);
        return days !== null && days <= 7 && days >= 0;
    }).length;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="My Subscriptions" />
            <div className="p-4 lg:p-6 space-y-6">
                {/* Header */}
                <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">My Subscriptions</h1>
                        <p className="text-muted-foreground mt-1">
                            Kelola langganan hosting dan layanan Anda
                        </p>
                    </div>
                    <Link href={route('catalog.index')}>
                        <Button variant="gradient" className="w-full lg:w-auto">
                            <Plus className="w-4 h-4 mr-2" />
                            Tambah Layanan Baru
                        </Button>
                    </Link>
                </div>

                {/* Stats Cards */}
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card variant="stat">
                        <CardContent className="p-4">
                            <div className="flex items-center gap-3">
                                <div className="h-10 w-10 rounded-xl bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)] flex items-center justify-center">
                                    <Server className="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <p className="text-2xl font-bold">{subscriptions.length}</p>
                                    <p className="text-xs text-muted-foreground">Total Langganan</p>
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
                                    <p className="text-2xl font-bold">{activeCount}</p>
                                    <p className="text-xs text-muted-foreground">Aktif</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card variant="stat">
                        <CardContent className="p-4">
                            <div className="flex items-center gap-3">
                                <div className="h-10 w-10 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center">
                                    <Clock className="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <p className="text-2xl font-bold">{trialCount}</p>
                                    <p className="text-xs text-muted-foreground">Trial</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card variant="stat">
                        <CardContent className="p-4">
                            <div className="flex items-center gap-3">
                                <div className="h-10 w-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center">
                                    <AlertCircle className="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <p className="text-2xl font-bold">{expiringSoonCount}</p>
                                    <p className="text-xs text-muted-foreground">Segera Berakhir</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Subscriptions List */}
                {subscriptions.length === 0 ? (
                    <Card variant="premium">
                        <CardContent className="p-12 text-center">
                            <div className="h-16 w-16 rounded-2xl bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)] flex items-center justify-center mx-auto mb-4">
                                <Server className="h-8 w-8 text-white" />
                            </div>
                            <h3 className="text-lg font-semibold mb-2">Belum Ada Langganan</h3>
                            <p className="text-muted-foreground mb-6 max-w-md mx-auto">
                                Mulai dengan melihat paket hosting kami dan pilih yang sesuai dengan kebutuhan Anda
                            </p>
                            <Link href={route('catalog.index')}>
                                <Button variant="gradient">
                                    <Plus className="w-4 h-4 mr-2" />
                                    Lihat Paket Hosting
                                </Button>
                            </Link>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        {subscriptions.map((subscription) => {
                            const statusConfig = getStatusConfig(subscription.status);
                            const StatusIcon = statusConfig.icon;
                            const ProductIcon = getProductIcon(subscription.product.type);
                            const daysUntilRenewal = getDaysUntilRenewal(subscription.next_renewal_at);

                            return (
                                <Card key={subscription.id} variant="premium" className="overflow-hidden">
                                    {/* Status accent bar */}
                                    <div className={`h-1 bg-gradient-to-r ${statusConfig.color}`} />

                                    <CardContent className="p-5">
                                        <div className="flex items-start gap-4">
                                            {/* Product Icon */}
                                            <div className={`h-12 w-12 rounded-xl bg-gradient-to-br ${statusConfig.color} flex items-center justify-center flex-shrink-0`}>
                                                <ProductIcon className="h-6 w-6 text-white" />
                                            </div>

                                            {/* Content */}
                                            <div className="flex-1 min-w-0">
                                                <div className="flex items-start justify-between gap-2 mb-2">
                                                    <div>
                                                        <h3 className="font-semibold text-lg truncate">
                                                            {subscription.product.name}
                                                        </h3>
                                                        <p className="text-sm text-muted-foreground">
                                                            {subscription.plan?.name || subscription.plan?.code || 'Default'}
                                                        </p>
                                                    </div>
                                                    <Badge variant={`${statusConfig.variant}-soft`} className="flex-shrink-0">
                                                        <StatusIcon className="h-3 w-3 mr-1" />
                                                        {statusConfig.label}
                                                    </Badge>
                                                </div>

                                                {/* Info rows */}
                                                <div className="space-y-2 text-sm">
                                                    <div className="flex items-center gap-4 text-muted-foreground">
                                                        <div className="flex items-center gap-1">
                                                            <Calendar className="h-4 w-4" />
                                                            <span>Mulai: {dayjs(subscription.start_at).format('DD MMM YYYY')}</span>
                                                        </div>
                                                        {subscription.auto_renew && (
                                                            <div className="flex items-center gap-1 text-emerald-600 dark:text-emerald-400">
                                                                <RefreshCw className="h-4 w-4" />
                                                                <span>Auto-renew</span>
                                                            </div>
                                                        )}
                                                    </div>

                                                    {subscription.next_renewal_at && (
                                                        <div className="flex items-center gap-1 text-muted-foreground">
                                                            <Clock className="h-4 w-4" />
                                                            <span>
                                                                Next Renewal: {dayjs(subscription.next_renewal_at).format('DD MMM YYYY')}
                                                                {daysUntilRenewal !== null && daysUntilRenewal <= 7 && daysUntilRenewal >= 0 && (
                                                                    <span className="ml-2 text-amber-600 dark:text-amber-400 font-medium">
                                                                        ({daysUntilRenewal} hari lagi)
                                                                    </span>
                                                                )}
                                                            </span>
                                                        </div>
                                                    )}

                                                    {subscription.end_at && (
                                                        <div className="flex items-center gap-1 text-muted-foreground">
                                                            <Calendar className="h-4 w-4" />
                                                            <span>Berakhir: {dayjs(subscription.end_at).format('DD MMM YYYY')}</span>
                                                        </div>
                                                    )}
                                                </div>

                                                {/* Warning for expiring soon */}
                                                {daysUntilRenewal !== null && daysUntilRenewal <= 7 && daysUntilRenewal >= 0 && (
                                                    <div className="mt-3 p-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                                                        <p className="text-sm text-amber-700 dark:text-amber-400 flex items-center gap-2">
                                                            <AlertCircle className="h-4 w-4" />
                                                            Langganan akan berakhir dalam {daysUntilRenewal} hari
                                                        </p>
                                                    </div>
                                                )}

                                                {/* Actions */}
                                                <div className="flex gap-2 mt-4">
                                                    <Link href={route('customer.subscriptions.show', subscription.id)}>
                                                        <Button variant="outline" size="sm">
                                                            <Settings className="h-4 w-4 mr-1" />
                                                            Kelola
                                                        </Button>
                                                    </Link>
                                                    {subscription.status === 'active' && (
                                                        <Button variant="ghost" size="sm">
                                                            <RefreshCw className="h-4 w-4 mr-1" />
                                                            Perpanjang
                                                        </Button>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            );
                        })}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
