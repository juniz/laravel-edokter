import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { StatCard } from '@/components/ui/stat-card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Alert, AlertDescription } from '@/components/ui/alert';
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
    Tag,
    Percent,
    ChevronUp,
    Search,
    Check,
    Loader2,
    AlertCircle,
    CheckCircle2,
    XCircle,
    Copy,
    ExternalLink,
    Zap,
    Package,
    Star,
    Clock,
    Database,
    Mail,
    Cpu,
    MemoryStick,
    Wifi,
    Folder,
    Lock,
    Cloud,
    Activity,
    HardDriveIcon,
} from 'lucide-react';
import { useState, useCallback } from 'react';
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }];

interface ProductFeature {
    id: string;
    key: string;
    value: string;
    label?: string;
    unit?: string;
}

interface FeaturedProduct {
    id: string;
    name: string;
    slug: string;
    type: string;
    description: string;
    best_plan: {
        id: string;
        code: string;
        price_cents: number;
        monthly_price_cents: number;
        duration_1_month_enabled: boolean;
        duration_12_months_enabled: boolean;
    } | null;
    original_price_cents: number | null;
    discount_percent: number;
    features: ProductFeature[];
    metadata_features?: string[];
    is_popular?: boolean;
}

interface ActivePromo {
    code: string;
    type: 'percent' | 'fixed';
    value: number;
    message: string;
}

interface DashboardProps {
    role: 'admin' | 'customer';
    userName?: string;
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
    featuredProducts?: FeaturedProduct[];
    activePromo?: ActivePromo | null;
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

// Map feature key/label to appropriate icon - Copied from Catalog/Index.tsx
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
        
        // Handle variants that don't have a -soft equivalent
        if (config.variant === 'default') {
            return <Badge variant="outline">{config.label.toUpperCase()}</Badge>;
        }
        
        return <Badge variant={`${config.variant}-soft` as any}>{config.label.toUpperCase()}</Badge>;
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
    userName,
    stats,
    subscriptions,
    recentInvoices,
    recentTickets,
    featuredProducts = [],
    activePromo,
}: DashboardProps) {
    // Domain search state
    const [domainQuery, setDomainQuery] = useState('');
    const [selectedTld, setSelectedTld] = useState('.com');
    const [isSearching, setIsSearching] = useState(false);
    const [searchResults, setSearchResults] = useState<{
        domain: string;
        available: boolean;
        price?: number;
        originalPrice?: number;
        discountPercent?: number;
        isLoading?: boolean;
        error?: string;
    }[] | null>(null);

    const tldOptions = [
        { value: '.com', label: '.com' },
        { value: '.co.id', label: '.co.id' },
        { value: '.or.id', label: '.or.id' },
        { value: '.id', label: '.id' },
        { value: '.web.id', label: '.web.id' },
        { value: '.sch.id', label: '.sch.id' },
        { value: '.ac.id', label: '.ac.id' },
        { value: '.ponpes.id', label: '.ponpes.id' },
        { value: '.biz.id', label: '.biz.id' },
        { value: '.my.id', label: '.my.id' },
    ];

    // Get popular TLDs to check alongside selected TLD
    const getPopularTlds = (selected: string): string[] => {
        const popularTlds = [
            '.co.id',
            '.or.id',
            '.id',
            '.web.id',
            '.sch.id',
            '.ac.id',
            '.ponpes.id',
            '.biz.id',
            '.my.id',
            '.com',
        ];
        
        // Remove selected TLD from popular list to avoid duplicate
        return popularTlds.filter(tld => tld !== selected);
    };

    interface DomainPriceData {
        registration: Record<string, number | string>;
        promo_registration?: {
            registration: Record<string, string>;
        } | null;
        currency: string;
    }

    // Fetch domain price by extension
    const fetchDomainPrice = async (
        extension: string
    ): Promise<DomainPriceData | null> => {
        if (!extension) {
            return null;
        }

        try {
            const url = '/customer/domain-prices/by-extension';
            const response = await axios.get(url, {
                params: { extension },
            });

            if (response.data.success && response.data.data) {
                return {
                    registration: response.data.data.registration || {},
                    promo_registration: response.data.data.promo_registration || null,
                    currency: response.data.data.currency || 'IDR',
                };
            }

            return null;
        } catch (error) {
            console.error(`Error fetching price for ${extension}:`, error);
            return null;
        }
    };



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
        return <Badge variant={`${config.variant}-soft` as any}>{config.label.toUpperCase()}</Badge>;
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

    const handleDomainSearch = useCallback(async () => {
        if (!domainQuery.trim()) return;
        
        setIsSearching(true);
        
        try {
            // Split domain into name and extension
            let name = domainQuery.toLowerCase().replace(/\s+/g, '');
            let ext = selectedTld;
            
            // If user already includes extension, extract it
            if (domainQuery.includes('.')) {
                const parts = domainQuery.split('.');
                name = parts[0];
                ext = '.' + parts.slice(1).join('.');
            }

            // Check TLDs: selected TLD first, then popular alternatives
            // Similar to Checkout.tsx logic
            const popularTlds = getPopularTlds(ext);
            const tldsToCheck = [
                ext, // Selected TLD first
                ...popularTlds, // Then popular alternatives
            ];
            
            // Remove duplicates (keep selected TLD first)
            const uniqueTlds = Array.from(new Set(tldsToCheck));
            
            // Initial results with loading state
            const initialResults = uniqueTlds.map((tld) => ({
                domain: name + tld,
                available: false,
                isLoading: true,
            }));
            
            setSearchResults(initialResults);

            // Check availability for all domains in parallel
            const availabilityPromises = uniqueTlds.map((tld) => {
                const domain = name + tld;
                return fetch(
                    `/api/rdash/domains/availability/check?domain=${encodeURIComponent(domain)}`
                )
                    .then((res) => res.json())
                    .catch((error) => {
                        console.error(`Error checking ${domain}:`, error);
                        return { success: false, domain, error };
                    });
            });

            const availabilityResults = await Promise.all(availabilityPromises);

            // Get unique extensions to fetch prices
            const uniqueExtensions = Array.from(new Set(uniqueTlds));

            // Fetch prices for all extensions in parallel
            const pricePromises = uniqueExtensions.map(
                async (
                    extension
                ): Promise<{ extension: string; price: DomainPriceData | null }> => {
                    const price = await fetchDomainPrice(extension);
                    return { extension, price };
                }
            );

            const priceResults = await Promise.all(pricePromises);

            // Create a map of extension to price data
            const priceMap = new Map<string, DomainPriceData>();
            priceResults.forEach((result) => {
                if (result.price) {
                    priceMap.set(result.extension, result.price);
                }
            });

            // Combine availability and price data
            const updatedResults = initialResults.map((res, index) => {
                const availabilityResult = availabilityResults[index];
                const tld = res.domain.substring(name.length);
                const priceData = priceMap.get(tld);

                if (!availabilityResult || !availabilityResult.success) {
                    return {
                        ...res,
                        available: false,
                        isLoading: false,
                        error: 'Gagal cek',
                    };
                }

                const isAvailable =
                    availabilityResult.data?.available === 1 ||
                    availabilityResult.data?.available === true;

                // Extract price information from price data
                let price = 0;
                let originalPrice: number | undefined;
                let discountPercent: number | undefined;

                if (priceData) {
                    // Get registration price for 1 year (default period)
                    const yearKey = '1';
                    const normalPrice =
                        priceData.registration?.[yearKey] || priceData.registration?.[1];
                    const promoPrice =
                        priceData.promo_registration?.registration?.[yearKey] ||
                        priceData.promo_registration?.registration?.[1];

                    // Convert to number if string
                    const normalPriceNum =
                        typeof normalPrice === 'string'
                            ? parseFloat(normalPrice) || 0
                            : normalPrice || 0;

                    const promoPriceNum =
                        typeof promoPrice === 'string'
                            ? parseFloat(promoPrice) || 0
                            : promoPrice || 0;

                    const hasPromo =
                        promoPriceNum &&
                        promoPriceNum !== 0 &&
                        promoPriceNum !== null &&
                        promoPriceNum !== undefined;

                    if (hasPromo) {
                        price = promoPriceNum;
                        originalPrice = normalPriceNum;
                        if (originalPrice > 0) {
                            discountPercent = Math.round(
                                ((originalPrice - price) / originalPrice) * 100
                            );
                        }
                    } else {
                        price = normalPriceNum;
                    }
                }

                return {
                    ...res,
                    available: isAvailable,
                    price: isAvailable ? price : undefined,
                    originalPrice: isAvailable ? originalPrice : undefined,
                    discountPercent: isAvailable ? discountPercent : undefined,
                    isLoading: false,
                };
            });

            setSearchResults(updatedResults);
        } catch (error) {
            console.error('Domain check error:', error);
            setSearchResults([
                {
                    domain: domainQuery.toLowerCase().replace(/\s+/g, '') + selectedTld,
                    available: false,
                    isLoading: false,
                    error: 'Gagal cek',
                },
            ]);
        } finally {
            setIsSearching(false);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [domainQuery, selectedTld]);

    return (
        <div className="flex flex-col gap-6 p-4 lg:p-6 bg-muted/40 min-h-screen">
            {/* Subtle dot grid background pattern */}
            <div className="fixed inset-0 bg-[radial-gradient(circle,rgba(0,0,0,0.03)_1px,transparent_1px)] dark:bg-[radial-gradient(circle,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:24px_24px] pointer-events-none opacity-50" />
            
            {/* Welcome Message with Gradient Text */}
            <div className="relative">
                <h1 className="text-2xl lg:text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Selamat datang,{' '}
                    <span className="bg-gradient-to-r from-primary to-[hsl(193,74%,43%)] text-transparent bg-clip-text">
                        {userName || 'Pengguna'}
                    </span>!
                </h1>
                <p className="text-muted-foreground mt-1">
                    Kelola layanan hosting dan domain Anda dengan mudah
                </p>
            </div>

            {/* ========================================
                HOSTING PACKAGES GRID
                ======================================== */}
            <div className="space-y-6">
                <div className="text-center">
                    <h2 className="font-bold text-2xl lg:text-3xl tracking-tight text-gray-900 dark:text-white">
                        Paket Hosting Terbaik
                    </h2>
                    <p className="text-muted-foreground mt-2">
                        Pilih paket yang sesuai dengan kebutuhan website Anda
                    </p>
                </div>

                {featuredProducts && featuredProducts.length > 0 ? (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {featuredProducts.map((pkg) => (
                            <Card
                                key={pkg.id}
                                className="relative overflow-hidden flex flex-col h-full border-2 hover:border-primary/50 transition-all group"
                            >
                                {/* Popular badge */}
                                {pkg.is_popular && (
                                    <div className="absolute top-0 right-0 px-3 py-1 bg-gradient-to-r from-[var(--gradient-start)] to-[var(--gradient-end)] text-white text-xs font-semibold rounded-bl-xl z-10">
                                        <Star className="h-3 w-3 inline mr-1" />
                                        POPULER
                                    </div>
                                )}

                                <CardHeader className="text-center pb-4 pt-8">
                                    <h3 className="text-xl font-bold mb-2 text-gray-900 dark:text-white">
                                        {pkg.name}
                                    </h3>
                                    {pkg.description && (
                                        <p className="text-sm text-muted-foreground leading-relaxed line-clamp-2 min-h-[40px]">
                                            {pkg.description}
                                        </p>
                                    )}
                                </CardHeader>

                                <CardContent className="flex-1 flex flex-col space-y-6">
                                    {/* Pricing Section - Google AI Style */}
                                    <div className="text-center space-y-2">
                                        <div className="flex items-baseline justify-center gap-2">
                                        {/* TODO: Add proper annual/monthly logic if needed, currently matching catalog display logic implies we might need more data, but using existing best_plan */}
                                            {pkg.original_price_cents && (
                                                <span className="text-base text-muted-foreground line-through">
                                                    {formatCurrency(pkg.original_price_cents)}
                                                </span>
                                            )}
                                            {/* Green price if discounted/popular or just following design */}
                                            <span className={`text-3xl font-bold ${pkg.original_price_cents ? 'text-emerald-600' : 'text-gray-900 dark:text-white'}`}>
                                                {formatCurrency(pkg.best_plan?.monthly_price_cents || 0)}
                                            </span>
                                            <span className="text-sm text-muted-foreground">/bln</span>
                                        </div>
                                    </div>

                                    {/* CTA Button */}
                                    <Link href={route('catalog.show', pkg.slug)} className="block">
                                        <Button 
                                            variant="default" 
                                            className="w-full h-12 text-base font-medium"
                                            size="lg"
                                        >
                                            Pilih Paket
                                        </Button>
                                    </Link>

                                    {/* Features List - Google AI Style */}
                                    <div className="space-y-4 pt-4 border-t">
                                        {((pkg.features && pkg.features.length > 0) || (pkg.metadata_features && pkg.metadata_features.length > 0)) ? (
                                            <>
                                                {/* Database Features */}
                                                {pkg.features && pkg.features.map((feature, idx) => {
                                                    const displayLabel = feature.label || feature.key;
                                                    const displayValue = feature.value + (feature.unit ? ` ${feature.unit}` : '');
                                                    const IconComponent = getFeatureIcon(feature.key, feature.label);
                                                    
                                                    return (
                                                        <div key={feature.id || idx} className="flex items-start gap-3">
                                                            <div className="flex-shrink-0 mt-0.5">
                                                                <IconComponent className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                                            </div>
                                                            <div className="flex-1 min-w-0">
                                                                <p className="font-semibold text-sm">
                                                                    {displayValue ? `${displayLabel}: ${displayValue}` : displayLabel}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    );
                                                })}

                                                {/* Separator */}
                                                {pkg.features && pkg.features.length > 0 && pkg.metadata_features && pkg.metadata_features.length > 0 && (
                                                    <div className="pt-2 pb-2 border-t border-b border-muted/50">
                                                        <p className="text-xs font-medium text-muted-foreground uppercase tracking-wider text-center">
                                                            FITUR TAMBAHAN
                                                        </p>
                                                    </div>
                                                )}

                                                {/* Metadata Features (Strings) */}
                                                {pkg.metadata_features && pkg.metadata_features.map((feature, idx) => {
                                                    const parts = feature.split(':');
                                                    const label = parts[0] || feature;
                                                    const value = parts.slice(1).join(':').trim() || '';
                                                    const IconComponent = getFeatureIcon(label, label);
                                                    
                                                    return (
                                                         <div key={`meta-${idx}`} className="flex items-start gap-3">
                                                             <div className="flex-shrink-0 mt-0.5">
                                                                 <IconComponent className="h-5 w-5 text-blue-600 dark:text-blue-400" />
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
                                        ) : (
                                            <p className="text-sm text-muted-foreground text-center">Fitur lengkap tersedia di halaman detail.</p>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                ) : (
                    <div className="text-center py-12">
                        <p className="text-muted-foreground">Belum ada paket hosting yang tersedia saat ini.</p>
                    </div>
                )}
            </div>

            {/* ========================================
                DOMAIN SEARCH HERO SECTION
                ======================================== */}
            <Card className="relative overflow-hidden border-0 shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl">
                {/* Glassmorphism overlay */}
                <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-[hsl(193,74%,43%)]/5" />
                
                {/* Decorative elements */}
                <div className="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-primary/10 to-transparent rounded-full blur-3xl -translate-y-1/2 translate-x-1/2" />
                <div className="absolute bottom-0 left-0 w-48 h-48 bg-gradient-to-tr from-[hsl(193,74%,43%)]/10 to-transparent rounded-full blur-3xl translate-y-1/2 -translate-x-1/2" />

                <CardContent className="relative p-6 lg:p-8">
                    <div className="text-center mb-6">
                        <h2 className="font-bold text-2xl lg:text-3xl tracking-tight text-gray-900 dark:text-white mb-2">
                            Temukan Domain Impian Anda
                        </h2>
                        <p className="text-muted-foreground max-w-xl mx-auto">
                            Cek ketersediaan domain dan daftarkan sekarang sebelum diambil orang lain
                        </p>
                    </div>

                    {/* Search Input Group - Responsive */}
                    <div className="max-w-2xl mx-auto">
                        <div className="flex flex-col md:flex-row gap-2 md:gap-0">
                            {/* Domain Input */}
                            <div className="relative flex-1">
                                <Search className="absolute left-4 top-1/2 transform -translate-y-1/2 h-5 w-5 text-muted-foreground" />
                                <Input
                                    type="text"
                                    placeholder="Masukkan nama domain..."
                                    value={domainQuery}
                                    onChange={(e) => setDomainQuery(e.target.value)}
                                    onKeyDown={(e) => e.key === 'Enter' && handleDomainSearch()}
                                    className="h-14 pl-12 pr-4 text-lg rounded-lg md:rounded-r-none md:border-r-0 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all duration-200"
                                />
                            </div>
                            
                            {/* TLD Selector */}
                            {/* <Select value={selectedTld} onValueChange={setSelectedTld}>
                                <SelectTrigger className="h-14 w-full md:w-32 rounded-lg md:rounded-none md:border-x-0 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 text-lg font-medium">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {tldOptions.map((tld) => (
                                        <SelectItem key={tld.value} value={tld.value}>
                                            {tld.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select> */}

                            {/* Search Button */}
                            <Button 
                                onClick={handleDomainSearch}
                                disabled={isSearching || !domainQuery.trim()}
                                className="h-14 px-8 rounded-lg md:rounded-l-none text-lg font-semibold bg-primary hover:bg-primary/90 hover:scale-[1.02] transition-all duration-200 shadow-lg hover:shadow-xl"
                            >
                                {isSearching ? (
                                    <Loader2 className="h-5 w-5 animate-spin" />
                                ) : (
                                    <>
                                        <Search className="h-5 w-5 mr-2" />
                                        Cari
                                    </>
                                )}
                            </Button>
                        </div>

                        {/* Search Results with slide-down animation */}
                        <div className={`mt-4 overflow-hidden transition-all duration-300 ease-out ${searchResults ? 'max-h-[600px] opacity-100' : 'max-h-0 opacity-0'}`}>
                            {searchResults && searchResults.length > 0 && (
                                <div className="space-y-2 max-h-[600px] overflow-y-auto">
                                    {searchResults.map((result) => (
                                        <div 
                                            key={result.domain}
                                            className={`flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 rounded-xl border-2 transition-all ${
                                                result.isLoading
                                                    ? 'bg-gray-50 dark:bg-gray-900/20 border-gray-200 dark:border-gray-800 animate-pulse'
                                                    : result.available 
                                                    ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' 
                                                    : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800'
                                            }`}
                                        >
                                            <div className="flex items-center gap-3 mb-2 sm:mb-0 flex-1 min-w-0">
                                                {result.isLoading ? (
                                                    <Loader2 className="h-6 w-6 text-gray-400 animate-spin flex-shrink-0" />
                                                ) : result.available ? (
                                                    <CheckCircle2 className="h-6 w-6 text-green-600 dark:text-green-400 flex-shrink-0" />
                                                ) : (
                                                    <XCircle className="h-6 w-6 text-red-600 dark:text-red-400 flex-shrink-0" />
                                                )}
                                                <div className="min-w-0 flex-1">
                                                    <p className="font-bold text-lg truncate">{result.domain}</p>
                                                    <p className={`text-sm ${result.isLoading ? 'text-gray-500' : result.available ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'}`}>
                                                        {result.isLoading 
                                                            ? 'Memeriksa...' 
                                                            : result.available 
                                                            ? 'Domain tersedia!' 
                                                            : result.error || 'Domain tidak tersedia'}
                                                    </p>
                                                </div>
                                            </div>
                                            {result.available && result.price !== undefined && (
                                                <div className="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full sm:w-auto">
                                                    <div className="flex flex-col items-end">
                                                        <div className="flex items-center gap-2">
                                                            {result.discountPercent !== undefined && result.discountPercent > 0 && (
                                                                <Badge className="bg-[#e8ebff] text-primary border-0 text-[10px] py-0 px-1.5">
                                                                    HEMAT {result.discountPercent}%
                                                                </Badge>
                                                            )}
                                                            <span className="font-mono font-bold text-xl text-gray-900 dark:text-white">
                                                                {formatCurrency(result.price)}
                                                                <span className="text-sm text-muted-foreground font-normal">/thn</span>
                                                            </span>
                                                        </div>
                                                        {result.originalPrice !== undefined && (
                                                            <span className="text-xs text-muted-foreground line-through">
                                                                {formatCurrency(result.originalPrice)}
                                                            </span>
                                                        )}
                                                    </div>
                                                    <Link href={route('customer.domains.create')}>
                                                        <Button size="sm" className="bg-green-600 hover:bg-green-700 w-full sm:w-auto">
                                                            Daftar Sekarang
                                                        </Button>
                                                    </Link>
                                                </div>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Quick links */}
                    <p className="text-sm text-muted-foreground text-center mt-4">
                        Sudah punya domain?{' '}
                        <Link href={route('customer.domains.create')} className="text-primary hover:underline font-medium">
                            Transfer ke AbaHost
                        </Link>
                        {' '}atau{' '}
                        <Link href={route('catalog.index')} className="text-primary hover:underline font-medium">
                            pilih paket hosting
                        </Link>
                    </p>
                </CardContent>
            </Card>

            {/* Promo Banner */}
            {activePromo && (
                <div className="relative overflow-hidden rounded-xl bg-gradient-to-r from-purple-100 to-purple-50 dark:from-purple-900/30 dark:to-purple-800/20 border border-purple-200 dark:border-purple-800">
                    {/* Background pattern */}
                    <div className="absolute inset-0 bg-[linear-gradient(rgba(139,92,246,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(139,92,246,0.05)_1px,transparent_1px)] bg-[size:20px_20px]" />
                    
                    {/* Percent icon */}
                    <div className="absolute top-0 right-0 p-4">
                        <div className="relative">
                            <div className="absolute inset-0 bg-purple-200 dark:bg-purple-800/50 rounded-lg blur-sm" />
                            <div className="relative bg-purple-300 dark:bg-purple-700/50 rounded-lg p-3">
                                <Percent className="h-8 w-8 text-purple-700 dark:text-purple-300" />
                            </div>
                        </div>
                    </div>

                    <div className="relative p-6 pr-32">
                        <h3 className="text-lg font-bold text-gray-900 dark:text-white mb-1">
                            {activePromo.message}
                        </h3>
                        <p className="text-sm text-gray-700 dark:text-gray-300">
                            Dapatkan penawaran terbaik yang kami pilihkan khusus untuk Anda.
                        </p>
                    </div>
                </div>
            )}


        </div>
    );
}

export default function Dashboard({
    role,
    userName,
    stats,
    charts,
    recentOrders,
    pendingInvoices,
    subscriptions,
    recentInvoices,
    recentTickets,
    featuredProducts,
    activePromo,
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
                    userName={userName}
                    stats={stats}
                    subscriptions={subscriptions}
                    recentInvoices={recentInvoices}
                    recentTickets={recentTickets}
                    featuredProducts={featuredProducts}
                    activePromo={activePromo}
                />
            )}
        </AppLayout>
    );
}
