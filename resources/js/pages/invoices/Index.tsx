import React, { useState, useEffect } from 'react';
import { Head, Link, useForm, router, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    FileText,
    Calendar,
    AlertCircle,
    CheckCircle2,
    Clock,
    XCircle,
    Download,
    Eye,
    ArrowRight,
    Receipt,
    Building2,
    Wallet,
    Store,
    ShieldCheck,
    Loader2,
    RotateCcw,
    Check,
    Search,
} from 'lucide-react';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import 'dayjs/locale/id';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';

dayjs.extend(relativeTime);
dayjs.locale('id');

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Invoices', href: '/customer/invoices' },
];

interface Invoice {
    id: string;
    number: string;
    status: string;
    total_cents: number;
    currency: string;
    due_at: string;
    created_at: string;
    paid_at?: string;
    pending_payment_id?: string;
}

interface InvoicesProps {
    invoices: {
        data: Invoice[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: { url: string | null; label: string; active: boolean }[];
    };
    filters?: {
        status?: string;
        search?: string;
        per_page?: number;
    };
}

function getStatusConfig(status: string) {
    const config: Record<string, { variant: 'success' | 'warning' | 'error' | 'info' | 'default'; icon: React.ElementType; label: string; color: string }> = {
        unpaid: { variant: 'warning', icon: Clock, label: 'Belum Dibayar', color: 'from-amber-500 to-orange-500' },
        paid: { variant: 'success', icon: CheckCircle2, label: 'Lunas', color: 'from-emerald-500 to-green-500' },
        overdue: { variant: 'error', icon: AlertCircle, label: 'Jatuh Tempo', color: 'from-red-500 to-rose-500' },
        void: { variant: 'default', icon: XCircle, label: 'Dibatalkan', color: 'from-gray-500 to-slate-500' },
        refunded: { variant: 'info', icon: RotateCcw, label: 'Dikembalikan', color: 'from-blue-500 to-cyan-500' },
        partial: { variant: 'info', icon: RotateCcw, label: 'Sebagian', color: 'from-blue-500 to-cyan-500' },
    };
    return config[status] || { variant: 'default', icon: FileText, label: status, color: 'from-gray-500 to-slate-500' };
}

function getDaysUntilDue(dueDate: string) {
    return dayjs(dueDate).diff(dayjs(), 'day');
}

export default function Invoices({ invoices, filters = {} }: InvoicesProps) {
    const [selectedInvoice, setSelectedInvoice] = useState<Invoice | null>(null);
    const [isPaymentModalOpen, setIsPaymentModalOpen] = useState(false);
    const [selectedPaymentMethod, setSelectedPaymentMethod] = useState('bca_va');
    const [searchQuery, setSearchQuery] = useState(filters.search || '');
    const [debounceTimer, setDebounceTimer] = useState<NodeJS.Timeout | null>(null);

    const { props } = usePage();
    const flash = (props?.flash as { open_payment_modal?: boolean; invoice_id?: string }) ?? {};

    const paymentForm = useForm({
        payment_method: 'bca_va',
    });

    const formatCurrency = (cents: number, currency: string = 'IDR') => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: 0,
        }).format(cents);
    };

    const handleSearch = (value: string) => {
        setSearchQuery(value);
        
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }

        const timer = setTimeout(() => {
            const params = new URLSearchParams(window.location.search);
            if (value) {
                params.set('search', value);
            } else {
                params.delete('search');
            }
            params.delete('page'); // Reset to first page on new search
            router.get(`/customer/invoices?${params.toString()}`, {}, { preserveState: true, preserveScroll: false });
        }, 500);

        setDebounceTimer(timer);
    };

    const handleFilterChange = (key: string, value: string) => {
        const params = new URLSearchParams(window.location.search);
        if (value && value !== 'all') {
            params.set(key, value);
        } else {
            params.delete(key);
        }
        params.delete('page'); // Reset to first page on filter change
        router.get(`/customer/invoices?${params.toString()}`, {}, { preserveState: true, preserveScroll: false });
    };

    // Handle flash message untuk membuka modal pembayaran
    useEffect(() => {
        if (flash.open_payment_modal && flash.invoice_id) {
            const invoice = invoices.data.find((inv) => inv.id === flash.invoice_id);
            if (invoice) {
                setSelectedInvoice(invoice);
                setIsPaymentModalOpen(true);
            }
        }
    }, [flash.open_payment_modal, flash.invoice_id, invoices]);

    const handleOpenPayment = (invoice: Invoice) => {
        // Jika sudah ada payment pending, langsung redirect ke halaman payment
        if (invoice.pending_payment_id) {
            router.visit(route('customer.payments.show', invoice.pending_payment_id));
            return;
        }

        // Jika belum ada payment pending, buka modal untuk pilih metode pembayaran
        setSelectedInvoice(invoice);
        setIsPaymentModalOpen(true);
    };

    const handleDownloadPdf = (invoiceId: string) => {
        // Gunakan window.open untuk bypass Inertia dan download PDF
        window.open(route('customer.invoices.download', invoiceId), '_blank');
    };

    const handleProcessPayment = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedInvoice) return;

        paymentForm.transform((data) => ({
            ...data,
            payment_method: selectedPaymentMethod,
        }));

        paymentForm.post(route('customer.invoices.pay', selectedInvoice.id), {
            onSuccess: () => setIsPaymentModalOpen(false),
        });
    };

    const totalUnpaid = invoices.data
        .filter(inv => inv.status === 'unpaid' || inv.status === 'overdue')
        .reduce((sum, inv) => sum + inv.total_cents, 0);

    const unpaidCount = invoices.data.filter(inv => inv.status === 'unpaid').length;
    const overdueCount = invoices.data.filter(inv => inv.status === 'overdue').length;
    const paidCount = invoices.data.filter(inv => inv.status === 'paid').length;

    const getPaymentLogo = (method: string): string | null => {
        const logos: Record<string, string> = {
            // Bank
            bca_va: '/images/payment/bank/bca.png',
            bni_va: '/images/payment/bank/bni.png',
            bri_va: '/images/payment/bank/bri.png',
            mandiri_va: '/images/payment/bank/mandiri.png',
            permata_va: '/images/payment/bank/permata.png',
            // E-Wallet
            gopay: '/images/payment/wallet/gopay.png',
            shopeepay: '/images/payment/wallet/shopeepay.png',
            dana: '/images/payment/wallet/dana.png',
            ovo: '/images/payment/wallet/ovo.png',
            linkaja: '/images/payment/wallet/linkaja.png',
        };
        return logos[method] || null;
    };

    const paymentMethods = [
        {
            category: 'Bank Transfer',
            methods: [
                { value: 'bca_va', label: 'BCA Virtual Account', icon: Building2 },
                { value: 'bni_va', label: 'BNI Virtual Account', icon: Building2 },
                { value: 'bri_va', label: 'BRI Virtual Account', icon: Building2 },
                { value: 'mandiri_va', label: 'Mandiri Virtual Account', icon: Building2 },
            ],
        },
        {
            category: 'E-Wallet',
            methods: [
                { value: 'gopay', label: 'GoPay', icon: Wallet },
                { value: 'shopeepay', label: 'ShopeePay', icon: Wallet },
                { value: 'dana', label: 'DANA', icon: Wallet },
                { value: 'ovo', label: 'OVO', icon: Wallet },
            ],
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="My Invoices" />
            <div className="p-4 lg:p-6 space-y-6">
                {/* Header */}
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">My Invoices</h1>
                    <p className="text-muted-foreground mt-1">
                        Daftar tagihan dan riwayat pembayaran Anda
                    </p>
                </div>

                {/* Stats Cards */}
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card variant="stat">
                        <CardContent className="p-4">
                            <div className="flex items-center gap-3">
                                <div className="h-10 w-10 rounded-xl bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)] flex items-center justify-center">
                                    <FileText className="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <p className="text-2xl font-bold">{invoices.total}</p>
                                    <p className="text-xs text-muted-foreground">Total Invoice</p>
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
                                    <p className="text-2xl font-bold">{unpaidCount}</p>
                                    <p className="text-xs text-muted-foreground">Belum Dibayar</p>
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
                                    <p className="text-2xl font-bold">{overdueCount}</p>
                                    <p className="text-xs text-muted-foreground">Jatuh Tempo</p>
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
                                    <p className="text-2xl font-bold">{paidCount}</p>
                                    <p className="text-xs text-muted-foreground">Lunas</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Total Unpaid Alert */}
                {totalUnpaid > 0 && (
                    <Card className="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border-amber-200 dark:border-amber-800">
                        <CardContent className="p-4">
                            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div className="flex items-center gap-3">
                                    <div className="h-10 w-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center">
                                        <Receipt className="h-5 w-5 text-white" />
                                    </div>
                                    <div>
                                        <p className="font-semibold text-amber-800 dark:text-amber-200">
                                            Total Tagihan Belum Dibayar
                                        </p>
                                        <p className="text-2xl font-bold text-amber-900 dark:text-amber-100">
                                            {formatCurrency(totalUnpaid)}
                                        </p>
                                    </div>
                                </div>
                                <Button variant="warning">
                                    Bayar Semua
                                    <ArrowRight className="h-4 w-4 ml-2" />
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Filters */}
                <Card>
                    <CardContent className="p-4">
                        <div className="flex flex-col md:flex-row gap-4">
                            <div className="flex-1">
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
                                    <Input
                                        type="text"
                                        placeholder="Cari nomor invoice..."
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
                                        <SelectValue placeholder="Filter Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Semua Status</SelectItem>
                                        <SelectItem value="paid">Lunas</SelectItem>
                                        <SelectItem value="unpaid">Belum Dibayar</SelectItem>
                                        <SelectItem value="overdue">Jatuh Tempo</SelectItem>
                                        <SelectItem value="void">Dibatalkan</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Invoices List */}
                {invoices.data.length === 0 ? (
                    <Card variant="premium">
                        <CardContent className="p-12 text-center">
                            <div className="h-16 w-16 rounded-2xl bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)] flex items-center justify-center mx-auto mb-4">
                                <FileText className="h-8 w-8 text-white" />
                            </div>
                            <h3 className="text-lg font-semibold mb-2">Belum Ada Invoice</h3>
                            <p className="text-muted-foreground max-w-md mx-auto">
                                Invoice akan muncul di sini setelah Anda melakukan pemesanan
                            </p>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="space-y-3">
                        {invoices.data.map((invoice) => {
                            const statusConfig = getStatusConfig(invoice.status);
                            const StatusIcon = statusConfig.icon;
                            const daysUntilDue = getDaysUntilDue(invoice.due_at);
                            const isOverdue = invoice.status === 'overdue' || (invoice.status === 'unpaid' && daysUntilDue < 0);

                            return (
                                <Card key={invoice.id} variant="premium" className="overflow-hidden">
                                    {/* Status accent bar */}
                                    <div className={`h-1 bg-gradient-to-r ${statusConfig.color}`} />

                                    <CardContent className="p-5">
                                        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                            <div className="flex items-center gap-4">
                                                <div className={`h-12 w-12 rounded-xl bg-gradient-to-br ${statusConfig.color} flex items-center justify-center flex-shrink-0`}>
                                                    <FileText className="h-6 w-6 text-white" />
                                                </div>
                                                <div>
                                                    <div className="flex items-center gap-3 mb-1">
                                                        <h3 className="font-semibold text-lg">{invoice.number}</h3>
                                                        <Badge variant={`${statusConfig.variant}-soft` as any}>
                                                            <StatusIcon className="h-3 w-3 mr-1" />
                                                            {statusConfig.label}
                                                        </Badge>
                                                    </div>
                                                    <div className="flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
                                                        <div className="flex items-center gap-1">
                                                            <Calendar className="h-4 w-4" />
                                                            <span>
                                                                {invoice.status === 'paid'
                                                                    ? `Dibayar ${dayjs(invoice.paid_at || invoice.created_at).format('DD MMM YYYY')}`
                                                                    : `Jatuh tempo ${dayjs(invoice.due_at).format('DD MMM YYYY')}`
                                                                }
                                                            </span>
                                                        </div>
                                                        {isOverdue && invoice.status !== 'paid' && (
                                                            <span className="text-red-600 dark:text-red-400 font-medium flex items-center gap-1">
                                                                <AlertCircle className="h-4 w-4" />
                                                                Sudah jatuh tempo
                                                            </span>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="flex items-center gap-4">
                                                <div className="text-right">
                                                    <p className="text-2xl font-bold">
                                                        {formatCurrency(invoice.total_cents, invoice.currency)}
                                                    </p>
                                                </div>
                                                <div className="flex gap-2">
                                                    <Link href={route('customer.invoices.show', invoice.id)}>
                                                        <Button variant="outline" size="sm">
                                                            <Eye className="h-4 w-4 mr-1" />
                                                            Lihat
                                                        </Button>
                                                    </Link>
                                                    {(invoice.status === 'unpaid' || invoice.status === 'overdue') && (
                                                        <>
                                                            {invoice.pending_payment_id && (
                                                              <Button
                                                                    variant="icon"
                                                                    size="sm"
                                                                    className="text-blue-600 hover:text-blue-700 hover:bg-blue-50"
                                                                    title="Cek Status Pembayaran"
                                                                    onClick={() => router.post(route('customer.invoices.check-payment', invoice.id))}
                                                                >
                                                                    <Loader2 className="h-4 w-4" />
                                                                </Button>
                                                            )}
                                                            <Button
                                                                variant="gradient"
                                                                size="sm"
                                                                onClick={() => handleOpenPayment(invoice)}
                                                            >
                                                                <Wallet className="h-4 w-4 mr-1" />
                                                                Bayar
                                                            </Button>
                                                        </>
                                                    )}
                                                    {invoice.status === 'paid' && (
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() => handleDownloadPdf(invoice.id)}
                                                        >
                                                            <Download className="h-4 w-4 mr-1" />
                                                            PDF
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

                {/* Pagination */}
                {invoices.links && invoices.links.length > 3 && (
                    <Card>
                        <CardContent className="p-4">
                            <div className="flex flex-col md:flex-row items-center justify-between gap-4">
                                <div className="text-sm text-muted-foreground">
                                    Menampilkan {((invoices.current_page - 1) * invoices.per_page) + 1} sampai{' '}
                                    {Math.min(invoices.current_page * invoices.per_page, invoices.total)} dari {invoices.total} invoice
                                </div>
                                <div className="flex flex-wrap gap-2 justify-center">
                                    {invoices.links.map((link, index) => (
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

            {/* Payment Modal */}
            <Dialog open={isPaymentModalOpen} onOpenChange={setIsPaymentModalOpen}>
                <DialogContent className="max-w-4xl max-h-[90vh] flex flex-col p-0">
                    <form onSubmit={handleProcessPayment} className="flex flex-col h-full max-h-[90vh]">
                        <div className="flex-1 overflow-y-auto min-h-0">
                            <div className="grid grid-cols-1 lg:grid-cols-3 h-full">
                                {/* Left: Payment Methods */}
                                <div className="lg:col-span-2 p-6 space-y-6">
                                    <DialogHeader>
                                        <DialogTitle>Pilih Metode Pembayaran</DialogTitle>
                                        <DialogDescription>
                                            Silakan pilih metode pembayaran yang Anda inginkan
                                        </DialogDescription>
                                    </DialogHeader>

                                    <RadioGroup
                                        value={selectedPaymentMethod}
                                        onValueChange={setSelectedPaymentMethod}
                                        className="gap-6"
                                    >
                                        {paymentMethods.map((category) => (
                                            <div key={category.category} className="space-y-3">
                                                <h4 className="font-medium text-sm text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    {category.category}
                                                </h4>
                                                <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    {category.methods.map((method) => {
                                                        const Icon = method.icon;
                                                        const isSelected = selectedPaymentMethod === method.value;
                                                        const logoPath = getPaymentLogo(method.value);
                                                        return (
                                                            <div key={method.value}>
                                                                <label
                                                                    className={`relative flex items-center justify-center p-4 rounded-xl border-2 cursor-pointer transition-all ${
                                                                        isSelected
                                                                            ? 'border-primary bg-primary/5'
                                                                            : 'border-transparent bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800'
                                                                    }`}
                                                                >
                                                                    <RadioGroupItem
                                                                        value={method.value}
                                                                        id={method.value}
                                                                        className="sr-only"
                                                                    />
                                                                    <div className={`flex items-center justify-center p-3 rounded-lg ${
                                                                        isSelected
                                                                            ? 'bg-white dark:bg-white/90'
                                                                            : 'bg-white dark:bg-white/80'
                                                                    }`}>
                                                                        {logoPath ? (
                                                                            <img 
                                                                                src={logoPath} 
                                                                                alt={method.label}
                                                                                className="h-12 w-auto object-contain"
                                                                            />
                                                                        ) : (
                                                                            <Icon className={`w-8 h-8 ${isSelected ? 'text-primary' : 'text-gray-700 dark:text-gray-300'}`} />
                                                                        )}
                                                                    </div>
                                                                    {isSelected && (
                                                                        <div className="absolute top-2 right-2">
                                                                            <div className="w-5 h-5 bg-primary rounded-full flex items-center justify-center">
                                                                                <Check className="w-3 h-3 text-white" />
                                                                            </div>
                                                                        </div>
                                                                    )}
                                                                </label>
                                                            </div>
                                                        );
                                                    })}
                                                </div>
                                            </div>
                                        ))}
                                    </RadioGroup>
                                </div>

                                {/* Right: Order Summary */}
                                <div className="bg-gray-50/50 dark:bg-gray-900/50 border-l border-gray-200 dark:border-gray-800 p-6 flex flex-col h-full">
                                    <h3 className="font-semibold text-lg mb-6">Ringkasan Pembayaran</h3>

                                    {selectedInvoice && (
                                        <div className="space-y-4 flex-1">
                                            <div className="space-y-2 pb-4 border-b border-gray-200 dark:border-gray-800">
                                                <div className="flex justify-between text-sm">
                                                    <span className="text-gray-600 dark:text-gray-400">Nomor Invoice</span>
                                                    <span className="font-medium">{selectedInvoice.number}</span>
                                                </div>
                                                <div className="flex justify-between text-sm">
                                                    <span className="text-gray-600 dark:text-gray-400">Tanggal</span>
                                                    <span className="font-medium">{dayjs(selectedInvoice.created_at).format('DD MMM YYYY')}</span>
                                                </div>
                                                <div className="flex justify-between text-sm">
                                                    <span className="text-gray-600 dark:text-gray-400">Jatuh Tempo</span>
                                                    <span className="font-medium">{dayjs(selectedInvoice.due_at).format('DD MMM YYYY')}</span>
                                                </div>
                                            </div>

                                            <div className="pt-4 pb-4 border-b border-gray-200 dark:border-gray-800">
                                                <div className="flex justify-between items-center mb-2">
                                                    <span className="text-lg font-semibold">Total Tagihan</span>
                                                    <span className="text-2xl font-bold text-primary">
                                                        {formatCurrency(selectedInvoice.total_cents, selectedInvoice.currency)}
                                                    </span>
                                                </div>
                                            </div>

                                            <div className="flex items-start gap-2 p-3 bg-blue-50 dark:bg-blue-900/10 text-blue-700 dark:text-blue-300 rounded-lg text-sm">
                                                <ShieldCheck className="w-5 h-5 flex-shrink-0 mt-0.5" />
                                                <p>Pembayaran aman & terenkripsi. Konfirmasi otomatis.</p>
                                            </div>
                                        </div>
                                    )}

                                    <Button
                                        type="submit"
                                        size="lg"
                                        variant="gradient"
                                        className="w-full mt-6 shadow-lg shadow-primary/20"
                                        disabled={paymentForm.processing}
                                    >
                                        {paymentForm.processing ? (
                                            <>
                                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                Memproses...
                                            </>
                                        ) : (
                                            <>
                                                Bayar Sekarang
                                                <ArrowRight className="ml-2 h-4 w-4" />
                                            </>
                                        )}
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
