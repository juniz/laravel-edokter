import React, { useEffect, useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Copy,
    ExternalLink,
    CheckCircle2,
    Clock,
    XCircle,
    Building2,
    QrCode,
    Store,
    ArrowLeft,
    CreditCard,
    ShieldCheck,
    AlertCircle,
    RefreshCw,
    Wallet,
} from 'lucide-react';

interface Payment {
    id: string;
    status: string;
    amount_cents: number;
    provider: string;
    provider_ref: string;
    created_at: string;
    invoice: {
        id: string;
        number: string;
        total_cents: number;
        currency: string;
    };
}

interface PaymentShowProps {
    payment: Payment;
    redirect_url?: string;
    va_number?: string;
    payment_code?: string;
    qr_code_url?: string;
    payment_method?: string;
    expiry_time?: string;
}

export default function PaymentShow({
    payment,
    redirect_url,
    va_number,
    payment_code,
    qr_code_url,
    payment_method,
    expiry_time,
}: PaymentShowProps) {
    const [copied, setCopied] = useState<string | null>(null);
    const [timeLeft, setTimeLeft] = useState<{
        hours: number;
        minutes: number;
        seconds: number;
    } | null>(null);

    // Auto-refresh for pending payments
    useEffect(() => {
        if (payment.status === 'pending') {
            const interval = setInterval(() => {
                router.reload({ only: ['payment'] });
            }, 5000); // Check every 5 seconds

            return () => clearInterval(interval);
        }
    }, [payment.status]);

    // Countdown Timer logic
    useEffect(() => {
        if (!expiry_time || payment.status !== 'pending') return;

        const calculateTimeLeft = () => {
            const difference = +new Date(expiry_time) - +new Date();
            
            if (difference > 0) {
                return {
                    hours: Math.floor((difference / (1000 * 60 * 60)) % 24) + Math.floor(difference / (1000 * 60 * 60 * 24)) * 24, // Include days in hours
                    minutes: Math.floor((difference / 1000 / 60) % 60),
                    seconds: Math.floor((difference / 1000) % 60),
                };
            }
            return null;
        };

        setTimeLeft(calculateTimeLeft());

        const timer = setInterval(() => {
            const remaining = calculateTimeLeft();
            setTimeLeft(remaining);
            if (!remaining) clearInterval(timer);
        }, 1000);

        return () => clearInterval(timer);
    }, [expiry_time, payment.status]);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Pembayaran', href: '/payments' },
        { title: payment.invoice.number, href: route('customer.payments.show', payment.id) },
    ];

    const formatPrice = (cents: number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: payment.invoice.currency || 'IDR',
            minimumFractionDigits: 0,
        }).format(cents);
    };

    const getStatusConfig = (status: string) => {
        const config: Record<string, { label: string; color: string; bgColor: string; icon: React.ReactNode; description: string }> = {
            pending: {
                label: 'Menunggu Pembayaran',
                color: 'text-amber-600',
                bgColor: 'bg-amber-50 dark:bg-amber-900/20',
                icon: <Clock className="w-12 h-12 text-amber-500 mb-4" />,
                description: 'Silakan selesaikan pembayaran Anda sebelum batas waktu berakhir.',
            },
            succeeded: {
                label: 'Pembayaran Berhasil',
                color: 'text-emerald-600',
                bgColor: 'bg-emerald-50 dark:bg-emerald-900/20',
                icon: <CheckCircle2 className="w-12 h-12 text-emerald-500 mb-4" />,
                description: 'Terima kasih! Pembayaran Anda telah kami terima.',
            },
            failed: {
                label: 'Pembayaran Gagal',
                color: 'text-red-600',
                bgColor: 'bg-red-50 dark:bg-red-900/20',
                icon: <XCircle className="w-12 h-12 text-red-500 mb-4" />,
                description: 'Maaf, pembayaran Anda gagal atau telah kadaluarsa. Silakan coba lagi.',
            },
        };

        return config[status] || {
            label: status,
            color: 'text-gray-600',
            bgColor: 'bg-gray-50',
            icon: <AlertCircle className="w-12 h-12 text-gray-500 mb-4" />,
            description: '',
        };
    };

    const copyToClipboard = (text: string, label: string) => {
        navigator.clipboard.writeText(text);
        setCopied(label);
        setTimeout(() => setCopied(null), 2000);
    };

    const statusConfig = getStatusConfig(payment.status);
    const isVA = va_number && (payment_method?.includes('_va') || payment_method === 'mandiri_va');
    const isQRIS = payment_method === 'qris' && qr_code_url;
    const isConvenienceStore = payment_code && (payment_method === 'indomaret' || payment_method === 'alfamart');
    const isEWallet = ['gopay', 'shopeepay', 'dana', 'ovo', 'linkaja'].includes(payment_method || '');

    const getPaymentMethodIcon = () => {
        if (isVA) return <Building2 className="w-6 h-6 text-primary" />;
        if (isQRIS) return <QrCode className="w-6 h-6 text-primary" />;
        if (isConvenienceStore) return <Store className="w-6 h-6 text-primary" />;
        if (isEWallet || payment_method === 'qris') return <Wallet className="w-6 h-6 text-primary" />;
        return <CreditCard className="w-6 h-6 text-primary" />;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Pembayaran - ${payment.invoice.number}`} />

            <div className="max-w-3xl mx-auto p-4 lg:p-8 space-y-8">
                {/* Status Header */}
                <div className={`text-center p-8 rounded-2xl ${statusConfig.bgColor} border border-border/50`}>
                    <div className="flex justify-center animate-in zoom-in duration-300">
                        {statusConfig.icon}
                    </div>
                    <h1 className={`text-2xl font-bold ${statusConfig.color} mb-2`}>
                        {statusConfig.label}
                    </h1>
                    <p className="text-muted-foreground max-w-md mx-auto mb-4">
                        {statusConfig.description}
                    </p>

                    {/* Countdown Timer Display */}
                    {timeLeft && payment.status === 'pending' && (
                        <div className="inline-flex items-center gap-2 px-4 py-2 bg-white/50 dark:bg-black/20 rounded-lg border border-border/50 shadow-sm animate-pulse">
                            <Clock className="w-4 h-4 text-amber-600" />
                            <span className="font-mono font-bold text-amber-700 dark:text-amber-500">
                                {timeLeft.hours.toString().padStart(2, '0')}:
                                {timeLeft.minutes.toString().padStart(2, '0')}:
                                {timeLeft.seconds.toString().padStart(2, '0')}
                            </span>
                            <span className="text-xs text-muted-foreground font-medium">Batas Waktu</span>
                        </div>
                    )}
                </div>

                {/* Main Content */}
                <Card variant="premium" className="overflow-hidden">
                    <div className="h-2 bg-gradient-to-r from-[var(--gradient-start)] to-[var(--gradient-end)]" />
                    <CardHeader className="text-center pb-2">
                        <p className="text-sm font-medium text-muted-foreground uppercase tracking-wider">Total Tagihan</p>
                        <div className="text-4xl font-bold text-gradient-primary py-2">
                            {formatPrice(payment.amount_cents)}
                        </div>
                        <Badge variant="outline" className="w-fit mx-auto mt-2">
                            INV: {payment.invoice.number}
                        </Badge>
                    </CardHeader>

                    {payment.status === 'pending' && (
                        <CardContent className="space-y-6 pt-6">
                            {/* Payment Instruction Box */}
                            <div className="bg-muted/30 rounded-xl p-6 border border-border/50 space-y-4">
                                <div className="flex items-center gap-3 mb-4">
                                    <div className="h-10 w-10 rounded-lg bg-primary/10 flex items-center justify-center">
                                        {getPaymentMethodIcon()}
                                    </div>
                                    <div>
                                        <p className="text-sm text-muted-foreground">Metode Pembayaran</p>
                                        <p className="font-semibold capitalize text-lg">
                                            {payment_method?.replace(/_/g, ' ')}
                                        </p>
                                    </div>
                                </div>

                                {isVA && (
                                    <div className="space-y-2">
                                        <p className="text-sm font-medium text-center text-muted-foreground">Virtual Account Number</p>
                                        <div className="flex items-center gap-2 p-4 bg-white dark:bg-black/20 border-2 border-primary/20 rounded-xl group hover:border-primary transition-colors">
                                            <span className="font-mono text-2xl font-bold tracking-wider flex-1 text-center">
                                                {va_number}
                                            </span>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                onClick={() => copyToClipboard(va_number!, 'va')}
                                                className="hover:bg-primary/10 hover:text-primary"
                                            >
                                                {copied === 'va' ? <CheckCircle2 className="w-5 h-5" /> : <Copy className="w-5 h-5" />}
                                            </Button>
                                        </div>
                                        <p className="text-xs text-center text-muted-foreground mt-2">
                                            Salin nomor VA ini ke aplikasi m-banking atau ATM Anda
                                        </p>
                                    </div>
                                )}

                                {isQRIS && qr_code_url && (
                                    <div className="text-center space-y-4">
                                        <div className="bg-white p-4 rounded-xl inline-block shadow-sm">
                                            <img src={qr_code_url} alt="QRIS Code" className="w-64 h-64" />
                                        </div>
                                        <p className="text-sm text-muted-foreground">
                                            Scan QR Code di atas dengan aplikasi pembayaran apa saja (GoPay, OVO, Dana, mobile banking)
                                        </p>
                                    </div>
                                )}

                                {isConvenienceStore && (
                                    <div className="space-y-2">
                                        <p className="text-sm font-medium text-center text-muted-foreground">Kode Pembayaran</p>
                                        <div className="flex items-center gap-2 p-4 bg-white dark:bg-black/20 border-2 border-primary/20 rounded-xl">
                                            <span className="font-mono text-2xl font-bold tracking-wider flex-1 text-center">
                                                {payment_code}
                                            </span>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                onClick={() => copyToClipboard(payment_code!, 'code')}
                                            >
                                                {copied === 'code' ? <CheckCircle2 className="w-5 h-5" /> : <Copy className="w-5 h-5" />}
                                            </Button>
                                        </div>
                                    </div>
                                )}

                                {redirect_url && (
                                    <div className="space-y-4 pt-2">
                                        <Button
                                            variant="gradient"
                                            size="lg"
                                            className="w-full h-12 text-lg shadow-lg shadow-primary/25 hover:shadow-primary/40 animate-pulse"
                                            onClick={() => window.open(redirect_url, '_blank')}
                                        >
                                            Bayar Sekarang
                                            <ExternalLink className="w-5 h-5 ml-2" />
                                        </Button>
                                        <p className="text-xs text-center text-muted-foreground">
                                            Halaman pembayaran akan terbuka di tab baru
                                        </p>
                                    </div>
                                )}
                            </div>

                            {/* Verification Info */}
                            <div className="flex items-start gap-3 p-4 bg-blue-50 dark:bg-blue-900/10 rounded-lg text-blue-700 dark:text-blue-300 text-sm">
                                <ShieldCheck className="w-5 h-5 flex-shrink-0 mt-0.5" />
                                <p>
                                    Pembayaran Anda akan diverifikasi secara otomatis. Halaman ini akan memuat ulang sendiri setelah pembayaran berhasil.
                                </p>
                            </div>
                        </CardContent>
                    )}

                    {payment.status === 'succeeded' && (
                        <CardContent className="text-center pb-8">
                            <Button variant="outline" className="mt-4" onClick={() => router.visit('/dashboard')}>
                                <ArrowLeft className="w-4 h-4 mr-2" />
                                Kembali ke Dashboard
                            </Button>
                        </CardContent>
                    )}
                </Card>

                {/* Footer details */}
                <div className="grid grid-cols-2 text-sm text-muted-foreground gap-4 max-w-lg mx-auto">
                    <div className="flex justify-between border-b border-dashed border-gray-300 pb-2">
                        <span>Tanggal Order</span>
                        <span className="font-medium text-foreground">{new Date(payment.created_at).toLocaleDateString('id-ID')}</span>
                    </div>
                    <div className="flex justify-between border-b border-dashed border-gray-300 pb-2">
                        <span>Referensi ID</span>
                        <span className="font-mono text-foreground">{payment.provider_ref}</span>
                    </div>
                </div>

                <div className="text-center pt-8">
                    <Button variant="ghost" className="text-muted-foreground hover:text-foreground" onClick={() => router.reload()}>
                        <RefreshCw className="w-4 h-4 mr-2" />
                        Cek Status Pembayaran
                    </Button>
                </div>
            </div>
        </AppLayout>
    );
}
