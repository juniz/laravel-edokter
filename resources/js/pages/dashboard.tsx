import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
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
} from 'recharts';
import {
	Users,
	ShoppingCart,
	CreditCard,
	Server,
	AlertCircle,
	TrendingUp,
	FileText,
	Ticket,
	Globe,
	ArrowRight,
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

const COLORS = ['#0ea5e9', '#14b8a6', '#f97316', '#9333ea', '#ec4899'];

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
		const statusColors: Record<string, string> = {
			paid: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
			pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
			unpaid: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
			overdue: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
			cancelled: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
		};
		return (
			<span
				className={`px-2 py-1 text-xs font-semibold rounded-full ${
					statusColors[status] || 'bg-gray-100 text-gray-800'
				}`}
			>
				{status.toUpperCase()}
			</span>
		);
	};

	return (
		<div className="flex flex-col gap-6 p-4">
			{/* Summary Cards */}
			<div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
				<Card>
					<CardHeader className="flex flex-row items-center justify-between pb-2">
						<CardTitle className="text-sm font-medium text-muted-foreground">
							Total Pengguna
						</CardTitle>
						<Users className="h-4 w-4 text-muted-foreground" />
					</CardHeader>
					<CardContent>
						<div className="text-2xl font-bold">{stats?.totalUsers || 0}</div>
						<p className="text-xs text-muted-foreground mt-1">Semua pengguna terdaftar</p>
					</CardContent>
				</Card>

				<Card>
					<CardHeader className="flex flex-row items-center justify-between pb-2">
						<CardTitle className="text-sm font-medium text-muted-foreground">
							Total Pesanan
						</CardTitle>
						<ShoppingCart className="h-4 w-4 text-muted-foreground" />
					</CardHeader>
					<CardContent>
						<div className="text-2xl font-bold">{stats?.totalOrders || 0}</div>
						<p className="text-xs text-muted-foreground mt-1">
							{stats?.pendingOrders || 0} pending
						</p>
					</CardContent>
				</Card>

				<Card>
					<CardHeader className="flex flex-row items-center justify-between pb-2">
						<CardTitle className="text-sm font-medium text-muted-foreground">
							Total Langganan
						</CardTitle>
						<Server className="h-4 w-4 text-muted-foreground" />
					</CardHeader>
					<CardContent>
						<div className="text-2xl font-bold">{stats?.totalSubscriptions || 0}</div>
						<p className="text-xs text-muted-foreground mt-1">
							{stats?.activeSubscriptions || 0} aktif
						</p>
					</CardContent>
				</Card>

				<Card>
					<CardHeader className="flex flex-row items-center justify-between pb-2">
						<CardTitle className="text-sm font-medium text-muted-foreground">
							Total Pendapatan
						</CardTitle>
						<TrendingUp className="h-4 w-4 text-muted-foreground" />
					</CardHeader>
					<CardContent>
						<div className="text-2xl font-bold">
							{formatCurrency(stats?.totalRevenue || 0)}
						</div>
						<p className="text-xs text-muted-foreground mt-1">
							Bulan ini: {formatCurrency(stats?.monthlyRevenue || 0)}
						</p>
					</CardContent>
				</Card>
			</div>

			{/* Charts */}
			<div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
				{/* Monthly Revenue Chart */}
				{charts?.monthlyRevenue && charts.monthlyRevenue.length > 0 && (
					<Card>
						<CardHeader>
							<CardTitle>Pendapatan Bulanan</CardTitle>
						</CardHeader>
						<CardContent>
							<ResponsiveContainer width="100%" height={300}>
								<BarChart data={charts.monthlyRevenue}>
									<XAxis dataKey="name" stroke="#6b7280" />
									<YAxis stroke="#6b7280" />
									<Tooltip
										formatter={(value: number) => formatCurrency(value)}
										contentStyle={{ backgroundColor: 'rgba(255, 255, 255, 0.95)' }}
									/>
									<Bar dataKey="revenue" fill="#0ea5e9" radius={[4, 4, 0, 0]} />
								</BarChart>
							</ResponsiveContainer>
						</CardContent>
					</Card>
				)}

				{/* Monthly Orders Chart */}
				{charts?.monthlyOrders && charts.monthlyOrders.length > 0 && (
					<Card>
						<CardHeader>
							<CardTitle>Pesanan Bulanan</CardTitle>
						</CardHeader>
						<CardContent>
							<ResponsiveContainer width="100%" height={300}>
								<LineChart data={charts.monthlyOrders}>
									<XAxis dataKey="name" stroke="#6b7280" />
									<YAxis stroke="#6b7280" />
									<Tooltip />
									<Line
										type="monotone"
										dataKey="orders"
										stroke="#14b8a6"
										strokeWidth={2}
									/>
								</LineChart>
							</ResponsiveContainer>
						</CardContent>
					</Card>
				)}

				{/* Subscription Status */}
				{charts?.subscriptionStatus && charts.subscriptionStatus.length > 0 && (
					<Card>
						<CardHeader>
							<CardTitle>Status Langganan</CardTitle>
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
										outerRadius={80}
										label
									>
										{charts.subscriptionStatus.map((entry, index) => (
											<Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
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
				<Card>
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
							<div className="space-y-4">
								{recentOrders.map((order) => (
									<div
										key={order.id}
										className="flex items-center justify-between p-3 border rounded-lg"
									>
										<div className="flex-1">
											<p className="font-medium">{order.customer || 'N/A'}</p>
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
							<p className="text-sm text-muted-foreground text-center py-4">
								Tidak ada pesanan terbaru
							</p>
						)}
					</CardContent>
				</Card>

				{/* Pending Invoices */}
				<Card>
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
							<div className="space-y-4">
								{pendingInvoices.map((invoice) => (
									<div
										key={invoice.id}
										className="flex items-center justify-between p-3 border rounded-lg"
									>
										<div className="flex-1">
											<p className="font-medium">{invoice.number}</p>
											<p className="text-sm text-muted-foreground">
												{invoice.customer || 'N/A'}
											</p>
											<p className="text-sm font-semibold mt-1">
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
							<p className="text-sm text-muted-foreground text-center py-4">
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
	recentOrders,
}: DashboardProps) {
	const formatCurrency = (amount: number, currency: string = 'IDR') => {
		return new Intl.NumberFormat('id-ID', {
			style: 'currency',
			currency: currency,
			minimumFractionDigits: 0,
		}).format(amount);
	};

	const getStatusBadge = (status: string) => {
		const statusColors: Record<string, string> = {
			active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
			paid: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
			unpaid: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
			overdue: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
			open: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
			in_progress: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
			closed: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
		};
		return (
			<span
				className={`px-2 py-1 text-xs font-semibold rounded-full ${
					statusColors[status] || 'bg-gray-100 text-gray-800'
				}`}
			>
				{status.toUpperCase()}
			</span>
		);
	};

	const getPriorityBadge = (priority: string) => {
		const priorityColors: Record<string, string> = {
			low: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
			normal: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
			high: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
			urgent: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
		};
		return (
			<span
				className={`px-2 py-1 text-xs font-semibold rounded-full ${
					priorityColors[priority] || 'bg-gray-100 text-gray-800'
				}`}
			>
				{priority.toUpperCase()}
			</span>
		);
	};

	return (
		<div className="flex flex-col gap-6 p-4">
			{/* Welcome Section */}
			<div className="bg-gradient-to-r from-primary/10 to-primary/5 rounded-lg p-6 border">
				<h2 className="text-2xl font-bold mb-2">Selamat Datang!</h2>
				<p className="text-muted-foreground">
					Kelola langganan hosting, domain, dan dukungan Anda dari satu tempat.
				</p>
			</div>

			{/* Summary Cards */}
			<div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
				<Card>
					<CardHeader className="flex flex-row items-center justify-between pb-2">
						<CardTitle className="text-sm font-medium text-muted-foreground">
							Langganan Aktif
						</CardTitle>
						<Server className="h-4 w-4 text-muted-foreground" />
					</CardHeader>
					<CardContent>
						<div className="text-2xl font-bold">{stats?.activeSubscriptions || 0}</div>
						<p className="text-xs text-muted-foreground mt-1">
							{stats?.expiringSoon || 0} akan berakhir dalam 30 hari
						</p>
					</CardContent>
				</Card>

				<Card>
					<CardHeader className="flex flex-row items-center justify-between pb-2">
						<CardTitle className="text-sm font-medium text-muted-foreground">
							Invoice Belum Dibayar
						</CardTitle>
						<CreditCard className="h-4 w-4 text-muted-foreground" />
					</CardHeader>
					<CardContent>
						<div className="text-2xl font-bold">{stats?.unpaidInvoices || 0}</div>
						<p className="text-xs text-muted-foreground mt-1">Menunggu pembayaran</p>
					</CardContent>
				</Card>

				<Card>
					<CardHeader className="flex flex-row items-center justify-between pb-2">
						<CardTitle className="text-sm font-medium text-muted-foreground">
							Tiket Terbuka
						</CardTitle>
						<Ticket className="h-4 w-4 text-muted-foreground" />
					</CardHeader>
					<CardContent>
						<div className="text-2xl font-bold">{stats?.openTickets || 0}</div>
						<p className="text-xs text-muted-foreground mt-1">Butuh perhatian</p>
					</CardContent>
				</Card>

				<Card>
					<CardHeader className="flex flex-row items-center justify-between pb-2">
						<CardTitle className="text-sm font-medium text-muted-foreground">
							Domain
						</CardTitle>
						<Globe className="h-4 w-4 text-muted-foreground" />
					</CardHeader>
					<CardContent>
						<div className="text-2xl font-bold">{stats?.domains || 0}</div>
						<p className="text-xs text-muted-foreground mt-1">Domain terdaftar</p>
					</CardContent>
				</Card>
			</div>

			{/* Quick Actions */}
			<div className="grid grid-cols-1 md:grid-cols-3 gap-4">
				<Link href={route('customer.domains.create')}>
					<Card className="cursor-pointer hover:shadow-md transition-shadow h-full">
						<CardHeader>
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
					<Card className="cursor-pointer hover:shadow-md transition-shadow h-full">
						<CardHeader>
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
					<Card className="cursor-pointer hover:shadow-md transition-shadow h-full">
						<CardHeader>
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
				<Card>
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
						<div className="space-y-4">
							{subscriptions.map((subscription) => (
								<div
									key={subscription.id}
									className="flex items-center justify-between p-4 border rounded-lg hover:bg-accent/50 transition-colors"
								>
									<div className="flex-1">
										<p className="font-semibold">{subscription.product}</p>
										<p className="text-sm text-muted-foreground">{subscription.plan}</p>
										<div className="flex gap-4 mt-2 text-xs text-muted-foreground">
											{subscription.start_at && (
												<span>Mulai: {subscription.start_at}</span>
											)}
											{subscription.end_at && (
												<span>Berakhir: {subscription.end_at}</span>
											)}
											{subscription.next_renewal_at && (
												<span>Renewal: {subscription.next_renewal_at}</span>
											)}
										</div>
									</div>
									<div className="flex items-center gap-3">
										{getStatusBadge(subscription.status)}
										{subscription.auto_renew && (
											<span className="text-xs text-muted-foreground">
												Auto-renew
											</span>
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
				<Card>
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
							<div className="space-y-4">
								{recentInvoices.map((invoice) => (
									<div
										key={invoice.id}
										className="flex items-center justify-between p-3 border rounded-lg"
									>
										<div className="flex-1">
											<p className="font-medium">{invoice.number}</p>
											<p className="text-sm font-semibold mt-1">
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
							<p className="text-sm text-muted-foreground text-center py-4">
								Tidak ada invoice
							</p>
						)}
					</CardContent>
				</Card>

				{/* Recent Tickets */}
				<Card>
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
							<div className="space-y-4">
								{recentTickets.map((ticket) => (
									<div
										key={ticket.id}
										className="flex items-center justify-between p-3 border rounded-lg"
									>
										<div className="flex-1">
											<p className="font-medium">{ticket.subject}</p>
											<div className="flex gap-2 mt-2">
												{getStatusBadge(ticket.status)}
												{getPriorityBadge(ticket.priority)}
											</div>
											{ticket.created_at && (
												<p className="text-xs text-muted-foreground mt-1">
													{ticket.created_at}
												</p>
											)}
										</div>
									</div>
								))}
							</div>
						) : (
							<p className="text-sm text-muted-foreground text-center py-4">
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
					recentOrders={recentOrders}
				/>
			)}
		</AppLayout>
	);
}
