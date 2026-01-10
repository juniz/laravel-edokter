import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { StatCard } from '@/components/ui/stat-card';
import {
    BarChart,
    Bar,
    LineChart,
    Line,
    PieChart,
    Pie,
    Cell,
    XAxis,
    YAxis,
    Tooltip,
    ResponsiveContainer,
    Legend,
    Area,
    AreaChart,
} from 'recharts';
import {
    Users,
    ShoppingCart,
    CreditCard,
    Server,
    TrendingUp,
    Ticket,
    Globe,
    ArrowRight,
    Plus,
    HardDrive,
    Shield,
    RefreshCw,
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }];

interface DashboardProps {
    role: 'admin' | 'customer';
    stats?: {
        totalUsers?: number;
        totalOrders?: number;
        totalSubscriptions?: number;
        totalInvoices?: number;
        totalRevenue?: number;
        monthlyRevenue?: number;
        pendingOrders?: number;
        paidOrders?: number;
        activeSubscriptions?: number;
        expiredSubscriptions?: number;
        unpaidInvoices?: number;
        overdueInvoices?: number;
        // Customer stats
        expiringSoon?: number;
        openTickets?: number;
        domains?: number;
    };
    charts?: {
        monthlyRevenue?: Array<{ name: string; revenue: number }>;
        monthlyOrders?: Array<{ name: string; orders: number }>;
        subscriptionStatus?: Array<{ name: string; value: number }>;
    };
    recentOrders?: Array<{
        id: string;
        number?: string;
        customer?: string;
        status: string;
        total: number;
        currency: string;
        placed_at?: string;
        items_count?: number;
    }>;
    pendingInvoices?: Array<{
        id: string;
        number: string;
        customer?: string;
        status: string;
        total: number;
        currency: string;
        due_at?: string;
    }>;
    subscriptions?: Array<{
        id: string;
        product: string;
        plan: string;
        status: string;
        start_at?: string;
        end_at?: string;
        next_renewal_at?: string;
        auto_renew: boolean;
    }>;
    recentInvoices?: Array<{
        id: string;
        number: string;
        status: string;
        total: number;
        currency: string;
        due_at?: string;
        created_at?: string;
    }>;
    recentTickets?: Array<{
        id: string;
        subject: string;
        priority: string;
        status: string;
        created_at?: string;
    }>;
}

// Modern chart colors
const CHART_COLORS = {
    primary: 'hsl(217, 91%, 60%)',
    cyan: 'hsl(188, 94%, 43%)',
    purple: 'hsl(271, 81%, 56%)',
    teal: 'hsl(173, 80%, 40%)',
    orange: 'hsl(24, 95%, 53%)',
    pink: 'hsl(330, 81%, 60%)',
};

const PIE_COLORS = [CHART_COLORS.primary, CHART_COLORS.teal, CHART_COLORS.orange, CHART_COLORS.purple, CHART_COLORS.pink];

function AdminDashboard({
    stats,
    charts,
    recentOrders,
    pendingInvoices,
}: DashboardProps) {
    const formatCurrency = (amount: number, currency: string = 'IDR') => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: 0,
        }).format(amount);
    };

    const getStatusBadge = (status: string) => {
        const statusMap: Record<string, { variant: 'success' | 'warning' | 'error' | 'info' | 'default'; label: string }> = {
            paid: { variant: 'success', label: 'Paid' },
            pending: { variant: 'warning', label: 'Pending' },
            unpaid: { variant: 'error', label: 'Unpaid' },
            overdue: { variant: 'error', label: 'Overdue' },
            cancelled: { variant: 'default', label: 'Cancelled' },
            active: { variant: 'success', label: 'Active' },
        };
        const config = statusMap[status] || { variant: 'default', label: status };
        return <Badge variant={`${config.variant}-soft`}>{config.label.toUpperCase()}</Badge>;
    };

    return (
        <div className="flex flex-col gap-6 p-4 lg:p-6">
            {/* Summary Cards */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <StatCard
                    title="Total Pengguna"
                    value={stats?.totalUsers || 0}
                    description="Semua pengguna terdaftar"
                    icon={Users}
                    variant="gradient"
                />
                <StatCard
                    title="Total Pesanan"
                    value={stats?.totalOrders || 0}
                    description={`${stats?.pendingOrders || 0} pending`}
                    icon={ShoppingCart}
                    variant="gradient-purple"
                    trend={{ value: 12, type: 'up' }}
                />
                <StatCard
                    title="Total Langganan"
                    value={stats?.totalSubscriptions || 0}
                    description={`${stats?.activeSubscriptions || 0} aktif`}
                    icon={Server}
                    variant="gradient-teal"
                />
                <StatCard
                    title="Total Pendapatan"
                    value={formatCurrency(stats?.totalRevenue || 0)}
                    description={`Bulan ini: ${formatCurrency(stats?.monthlyRevenue || 0)}`}
                    icon={TrendingUp}
                    variant="gradient-orange"
                    trend={{ value: 8, type: 'up' }}
                />
            </div>

            {/* Charts */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Monthly Revenue Chart */}
                {charts?.monthlyRevenue && charts.monthlyRevenue.length > 0 && (
                    <Card variant="premium" className="overflow-hidden">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <div className="h-8 w-8 rounded-lg bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)] flex items-center justify-center">
                                    <TrendingUp className="h-4 w-4 text-white" />
                                </div>
                                Pendapatan Bulanan
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ResponsiveContainer width="100%" height={300}>
                                <AreaChart data={charts.monthlyRevenue}>
                                    <defs>
                                        <linearGradient id="colorRevenue" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="5%" stopColor={CHART_COLORS.primary} stopOpacity={0.3} />
                                            <stop offset="95%" stopColor={CHART_COLORS.primary} stopOpacity={0} />
                                        </linearGradient>
                                    </defs>
                                    <XAxis dataKey="name" stroke="#94a3b8" fontSize={12} />
                                    <YAxis stroke="#94a3b8" fontSize={12} />
                                    <Tooltip
                                        formatter={(value: number) => formatCurrency(value)}
                                        contentStyle={{
                                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                                            borderRadius: '8px',
                                            border: '1px solid #e2e8f0',
                                            boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
                                        }}
                                    />
                                    <Area
                                        type="monotone"
                                        dataKey="revenue"
                                        stroke={CHART_COLORS.primary}
                                        strokeWidth={2}
                                        fillOpacity={1}
                                        fill="url(#colorRevenue)"
                                    />
                                </AreaChart>
                            </ResponsiveContainer>
                        </CardContent>
                    </Card>
                )}

                {/* Monthly Orders Chart */}
                {charts?.monthlyOrders && charts.monthlyOrders.length > 0 && (
                    <Card variant="premium" className="overflow-hidden">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <div className="h-8 w-8 rounded-lg bg-gradient-to-br from-[var(--accent-teal)] to-[var(--accent-cyan)] flex items-center justify-center">
                                    <ShoppingCart className="h-4 w-4 text-white" />
                                </div>
                                Pesanan Bulanan
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ResponsiveContainer width="100%" height={300}>
                                <BarChart data={charts.monthlyOrders}>
                                    <defs>
                                        <linearGradient id="colorOrders" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="5%" stopColor={CHART_COLORS.teal} stopOpacity={1} />
                                            <stop offset="95%" stopColor={CHART_COLORS.cyan} stopOpacity={1} />
                                        </linearGradient>
                                    </defs>
                                    <XAxis dataKey="name" stroke="#94a3b8" fontSize={12} />
                                    <YAxis stroke="#94a3b8" fontSize={12} />
                                    <Tooltip
                                        contentStyle={{
                                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                                            borderRadius: '8px',
                                            border: '1px solid #e2e8f0',
                                            boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
                                        }}
                                    />
                                    <Bar
                                        dataKey="orders"
                                        fill="url(#colorOrders)"
                                        radius={[6, 6, 0, 0]}
                                    />
                                </BarChart>
                            </ResponsiveContainer>
                        </CardContent>
                    </Card>
                )}

                {/* Subscription Status */}
                {charts?.subscriptionStatus && charts.subscriptionStatus.length > 0 && (
                    <Card variant="premium" className="overflow-hidden">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <div className="h-8 w-8 rounded-lg bg-gradient-to-br from-[var(--accent-purple)] to-[var(--accent-pink)] flex items-center justify-center">
                                    <Server className="h-4 w-4 text-white" />
                                </div>
                                Status Langganan
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ResponsiveContainer width="100%" height={300}>
                                <PieChart>
                                    <Pie
                                        data={charts.subscriptionStatus}
                                        dataKey="value"
                                        nameKey="name"
                                        cx="50%"
                                        cy="50%"
                                        outerRadius={100}
                                        innerRadius={60}
                                        label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                                        labelLine={false}
                                    >
                                        {charts.subscriptionStatus.map((entry, index) => (
                                            <Cell key={`cell-${index}`} fill={PIE_COLORS[index % PIE_COLORS.length]} />
                                        ))}
                                    </Pie>
                                    <Tooltip />
                                    <Legend />
                                </PieChart>
                            </ResponsiveContainer>
                        </CardContent>
                    </Card>
                )}
            </div>

            {/* Recent Orders & Pending Invoices */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Recent Orders */}
                <Card variant="premium">
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle>Pesanan Terbaru</CardTitle>
                        <Button variant="ghost" size="sm" asChild>
                            <Link href={route('customer.orders.index')}>
                                Lihat Semua
                                <ArrowRight className="ml-2 h-4 w-4" />
                            </Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        {recentOrders && recentOrders.length > 0 ? (
                            <div className="space-y-3">
                                {recentOrders.map((order) => (
                                    <div
                                        key={order.id}
                                        className="flex items-center justify-between p-4 rounded-xl border bg-muted/30 hover:bg-muted/50 transition-colors"
                                    >
                                        <div className="flex-1">
                                            <p className="font-semibold">{order.customer || 'N/A'}</p>
                                            <p className="text-sm text-muted-foreground">
                                                {formatCurrency(order.total, order.currency)}
                                            </p>
                                            <p className="text-xs text-muted-foreground mt-1">
                                                {order.placed_at}
                                            </p>
                                        </div>
                                        {getStatusBadge(order.status)}
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-muted-foreground text-center py-8">
                                Tidak ada pesanan terbaru
                            </p>
                        )}
                    </CardContent>
                </Card>

                {/* Pending Invoices */}
                <Card variant="premium">
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle>Invoice Menunggu Pembayaran</CardTitle>
                        <Button variant="ghost" size="sm" asChild>
                            <Link href={route('customer.invoices.index')}>
                                Lihat Semua
                                <ArrowRight className="ml-2 h-4 w-4" />
                            </Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        {pendingInvoices && pendingInvoices.length > 0 ? (
                            <div className="space-y-3">
                                {pendingInvoices.map((invoice) => (
                                    <div
                                        key={invoice.id}
                                        className="flex items-center justify-between p-4 rounded-xl border bg-muted/30 hover:bg-muted/50 transition-colors"
                                    >
                                        <div className="flex-1">
                                            <p className="font-semibold">{invoice.number}</p>
                                            <p className="text-sm text-muted-foreground">
                                                {invoice.customer || 'N/A'}
                                            </p>
                                            <p className="text-sm font-bold mt-1">
                                                {formatCurrency(invoice.total, invoice.currency)}
                                            </p>
                                            {invoice.due_at && (
                                                <p className="text-xs text-muted-foreground mt-1">
                                                    Jatuh tempo: {invoice.due_at}
                                                </p>
                                            )}
                                        </div>
                                        {getStatusBadge(invoice.status)}
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-muted-foreground text-center py-8">
                                Tidak ada invoice menunggu pembayaran
                            </p>
                        )}
                    </CardContent>
                </Card>
            </div>
        </div>
    );
}

function CustomerDashboard({
    stats,
    subscriptions,
    recentInvoices,
    recentTickets,
}: DashboardProps) {
    const formatCurrency = (amount: number, currency: string = 'IDR') => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: 0,
        }).format(amount);
    };

    const getStatusBadge = (status: string) => {
        const statusMap: Record<string, { variant: 'success' | 'warning' | 'error' | 'info' | 'default'; label: string }> = {
            active: { variant: 'success', label: 'Active' },
            paid: { variant: 'success', label: 'Paid' },
            unpaid: { variant: 'warning', label: 'Unpaid' },
            overdue: { variant: 'error', label: 'Overdue' },
            open: { variant: 'info', label: 'Open' },
            in_progress: { variant: 'info', label: 'In Progress' },
            closed: { variant: 'default', label: 'Closed' },
        };
        const config = statusMap[status] || { variant: 'default', label: status };
        return <Badge variant={`${config.variant}-soft`}>{config.label.toUpperCase()}</Badge>;
    };

    const getPriorityBadge = (priority: string) => {
        const priorityMap: Record<string, { variant: 'success' | 'warning' | 'error' | 'info' }> = {
            low: { variant: 'info' },
            normal: { variant: 'success' },
            high: { variant: 'warning' },
            urgent: { variant: 'error' },
        };
        const config = priorityMap[priority] || { variant: 'info' };
        return <Badge variant={config.variant}>{priority.toUpperCase()}</Badge>;
    };

    return (
        <div className="flex flex-col gap-6 p-4 lg:p-6">
            {/* Welcome Section */}
            <div className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)] p-6 lg:p-8 text-white">
                {/* Background pattern */}
                <div className="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:40px_40px]" />
                
                {/* Floating orbs */}
                <div className="absolute top-4 right-4 w-32 h-32 bg-white/10 rounded-full blur-2xl" />
                <div className="absolute bottom-4 left-4 w-24 h-24 bg-white/10 rounded-full blur-2xl" />

                <div className="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h2 className="text-2xl lg:text-3xl font-bold mb-2">Selamat Datang! 👋</h2>
                        <p className="text-white/80 max-w-lg">
                            Kelola langganan hosting, domain, dan dukungan Anda dari satu tempat.
                        </p>
                    </div>
                    <div className="flex gap-3">
                        <Button
                            variant="secondary"
                            className="bg-white/20 text-white border-white/20 hover:bg-white/30"
                            asChild
                        >
                            <Link href={route('customer.domains.create')}>
                                <Plus className="h-4 w-4 mr-2" />
                                Domain Baru
                            </Link>
                        </Button>
                        <Button
                            variant="secondary"
                            className="bg-white text-primary hover:bg-white/90"
                            asChild
                        >
                            <Link href={route('catalog.index')}>
                                Lihat Paket
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>

            {/* Summary Cards */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <StatCard
                    title="Langganan Aktif"
                    value={stats?.activeSubscriptions || 0}
                    description={`${stats?.expiringSoon || 0} akan berakhir dalam 30 hari`}
                    icon={Server}
                    variant="gradient"
                />
                <StatCard
                    title="Invoice Belum Dibayar"
                    value={stats?.unpaidInvoices || 0}
                    description="Menunggu pembayaran"
                    icon={CreditCard}
                    variant="gradient-orange"
                />
                <StatCard
                    title="Tiket Terbuka"
                    value={stats?.openTickets || 0}
                    description="Butuh perhatian"
                    icon={Ticket}
                    variant="gradient-purple"
                />
                <StatCard
                    title="Domain"
                    value={stats?.domains || 0}
                    description="Domain terdaftar"
                    icon={Globe}
                    variant="gradient-teal"
                />
            </div>

            {/* Quick Actions */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Link href={route('customer.domains.create')}>
                    <Card variant="interactive" className="h-full">
                        <CardHeader>
                            <div className="h-10 w-10 rounded-xl bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)] flex items-center justify-center mb-2">
                                <Globe className="h-5 w-5 text-white" />
                            </div>
                            <CardTitle className="text-lg">Daftarkan Domain Baru</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-sm text-muted-foreground">
                                Daftarkan domain baru untuk website Anda
                            </p>
                        </CardContent>
                    </Card>
                </Link>

                <Link href={route('catalog.index')}>
                    <Card variant="interactive" className="h-full">
                        <CardHeader>
                            <div className="h-10 w-10 rounded-xl bg-gradient-to-br from-[var(--accent-purple)] to-[var(--accent-pink)] flex items-center justify-center mb-2">
                                <HardDrive className="h-5 w-5 text-white" />
                            </div>
                            <CardTitle className="text-lg">Lihat Paket Hosting</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-sm text-muted-foreground">
                                Jelajahi paket hosting yang tersedia
                            </p>
                        </CardContent>
                    </Card>
                </Link>

                <Link href={route('customer.tickets.create')}>
                    <Card variant="interactive" className="h-full">
                        <CardHeader>
                            <div className="h-10 w-10 rounded-xl bg-gradient-to-br from-[var(--accent-teal)] to-[var(--accent-cyan)] flex items-center justify-center mb-2">
                                <Ticket className="h-5 w-5 text-white" />
                            </div>
                            <CardTitle className="text-lg">Buat Tiket Dukungan</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-sm text-muted-foreground">
                                Butuh bantuan? Buat tiket dukungan baru
                            </p>
                        </CardContent>
                    </Card>
                </Link>
            </div>

            {/* Active Subscriptions */}
            {subscriptions && subscriptions.length > 0 && (
                <Card variant="premium">
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle>Langganan Aktif</CardTitle>
                        <Button variant="ghost" size="sm" asChild>
                            <Link href={route('customer.subscriptions.index')}>
                                Lihat Semua
                                <ArrowRight className="ml-2 h-4 w-4" />
                            </Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-3">
                            {subscriptions.map((subscription) => (
                                <div
                                    key={subscription.id}
                                    className="flex items-center justify-between p-4 rounded-xl border bg-muted/30 hover:bg-muted/50 transition-colors"
                                >
                                    <div className="flex items-center gap-4">
                                        <div className="h-12 w-12 rounded-xl bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)] flex items-center justify-center">
                                            <Server className="h-6 w-6 text-white" />
                                        </div>
                                        <div>
                                            <p className="font-semibold">{subscription.product}</p>
                                            <p className="text-sm text-muted-foreground">{subscription.plan}</p>
                                            <div className="flex gap-4 mt-1 text-xs text-muted-foreground">
                                                {subscription.start_at && (
                                                    <span>Mulai: {subscription.start_at}</span>
                                                )}
                                                {subscription.next_renewal_at && (
                                                    <span>Renewal: {subscription.next_renewal_at}</span>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        {getStatusBadge(subscription.status)}
                                        {subscription.auto_renew && (
                                            <div className="flex items-center gap-1 text-xs text-muted-foreground">
                                                <RefreshCw className="h-3 w-3" />
                                                Auto
                                            </div>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            )}

            {/* Recent Invoices & Tickets */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Recent Invoices */}
                <Card variant="premium">
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle>Invoice Terbaru</CardTitle>
                        <Button variant="ghost" size="sm" asChild>
                            <Link href={route('customer.invoices.index')}>
                                Lihat Semua
                                <ArrowRight className="ml-2 h-4 w-4" />
                            </Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        {recentInvoices && recentInvoices.length > 0 ? (
                            <div className="space-y-3">
                                {recentInvoices.map((invoice) => (
                                    <div
                                        key={invoice.id}
                                        className="flex items-center justify-between p-4 rounded-xl border bg-muted/30 hover:bg-muted/50 transition-colors"
                                    >
                                        <div className="flex-1">
                                            <p className="font-semibold">{invoice.number}</p>
                                            <p className="text-sm font-bold mt-1">
                                                {formatCurrency(invoice.total, invoice.currency)}
                                            </p>
                                            {invoice.due_at && (
                                                <p className="text-xs text-muted-foreground mt-1">
                                                    Jatuh tempo: {invoice.due_at}
                                                </p>
                                            )}
                                        </div>
                                        {getStatusBadge(invoice.status)}
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-muted-foreground text-center py-8">
                                Tidak ada invoice
                            </p>
                        )}
                    </CardContent>
                </Card>

                {/* Recent Tickets */}
                <Card variant="premium">
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle>Tiket Terbaru</CardTitle>
                        <Button variant="ghost" size="sm" asChild>
                            <Link href={route('customer.tickets.index')}>
                                Lihat Semua
                                <ArrowRight className="ml-2 h-4 w-4" />
                            </Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        {recentTickets && recentTickets.length > 0 ? (
                            <div className="space-y-3">
                                {recentTickets.map((ticket) => (
                                    <div
                                        key={ticket.id}
                                        className="flex items-center justify-between p-4 rounded-xl border bg-muted/30 hover:bg-muted/50 transition-colors"
                                    >
                                        <div className="flex-1">
                                            <p className="font-semibold">{ticket.subject}</p>
                                            <div className="flex gap-2 mt-2">
                                                {getStatusBadge(ticket.status)}
                                                {getPriorityBadge(ticket.priority)}
                                            </div>
                                            {ticket.created_at && (
                                                <p className="text-xs text-muted-foreground mt-2">
                                                    {ticket.created_at}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-muted-foreground text-center py-8">
                                Tidak ada tiket
                            </p>
                        )}
                    </CardContent>
                </Card>
            </div>
        </div>
    );
}

export default function Dashboard({
    role,
    stats,
    charts,
    recentOrders,
    pendingInvoices,
    subscriptions,
    recentInvoices,
    recentTickets,
}: DashboardProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            {role === 'admin' ? (
                <AdminDashboard
                    role={role}
                    stats={stats}
                    charts={charts}
                    recentOrders={recentOrders}
                    pendingInvoices={pendingInvoices}
                />
            ) : (
                <CustomerDashboard
                    role={role}
                    stats={stats}
                    subscriptions={subscriptions}
                    recentInvoices={recentInvoices}
                    recentTickets={recentTickets}
                />
            )}
        </AppLayout>
    );
}
