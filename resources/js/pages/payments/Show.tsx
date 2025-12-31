import React, { useEffect } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Copy, ExternalLink, CheckCircle, Clock, XCircle, Building2, QrCode, Store } from 'lucide-react';

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
}

export default function PaymentShow({
  payment,
  redirect_url,
  va_number,
  payment_code,
  qr_code_url,
  payment_method,
}: PaymentShowProps) {
  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Pembayaran', href: '/payments' },
    { title: payment.invoice.number, href: route('payments.show', payment.id) },
  ];

  const formatPrice = (cents: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: payment.invoice.currency || 'IDR',
      minimumFractionDigits: 0,
    }).format(cents);
  };

  const getStatusBadge = (status: string) => {
    const statusConfig: Record<string, { label: string; className: string; icon: React.ReactNode }> = {
      pending: {
        label: 'Menunggu Pembayaran',
        className: 'bg-yellow-500',
        icon: <Clock className="w-4 h-4" />,
      },
      succeeded: {
        label: 'Berhasil',
        className: 'bg-green-500',
        icon: <CheckCircle className="w-4 h-4" />,
      },
      failed: {
        label: 'Gagal',
        className: 'bg-red-500',
        icon: <XCircle className="w-4 h-4" />,
      },
    };

    return statusConfig[status] || {
      label: status,
      className: 'bg-gray-500',
      icon: null,
    };
  };

  const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
  };

  const statusConfig = getStatusBadge(payment.status);
  const isVA = va_number && (payment_method?.includes('_va') || payment_method === 'mandiri_va');
  const isQRIS = payment_method === 'qris' && qr_code_url;
  const isConvenienceStore = payment_code && (payment_method === 'indomaret' || payment_method === 'alfamart');

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`Pembayaran - ${payment.invoice.number}`} />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">Pembayaran</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">Invoice: {payment.invoice.number}</p>
          </div>
          <Badge className={`${statusConfig.className} flex items-center gap-2`}>
            {statusConfig.icon}
            {statusConfig.label}
          </Badge>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {/* Payment Details */}
          <Card className="bg-white dark:bg-gray-800 shadow-md">
            <CardHeader>
              <CardTitle>Detail Pembayaran</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex justify-between">
                <span className="text-gray-600 dark:text-gray-400">Total Pembayaran</span>
                <span className="font-semibold text-lg">{formatPrice(payment.amount_cents)}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600 dark:text-gray-400">Metode Pembayaran</span>
                <span className="font-semibold capitalize">{payment_method?.replace('_', ' ') || 'N/A'}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600 dark:text-gray-400">Provider</span>
                <span className="font-semibold uppercase">{payment.provider}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600 dark:text-gray-400">Reference</span>
                <span className="font-mono text-sm">{payment.provider_ref}</span>
              </div>
            </CardContent>
          </Card>

          {/* Payment Instructions */}
          <Card className="bg-white dark:bg-gray-800 shadow-md">
            <CardHeader>
              <CardTitle>Instruksi Pembayaran</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {isVA && (
                <div className="space-y-3">
                  <div className="flex items-center gap-2 text-lg font-semibold">
                    <Building2 className="w-5 h-5" />
                    Virtual Account Number
                  </div>
                  <div className="flex items-center gap-2 p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
                    <span className="font-mono text-xl flex-1">{va_number}</span>
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => copyToClipboard(va_number!)}
                    >
                      <Copy className="w-4 h-4" />
                    </Button>
                  </div>
                  <p className="text-sm text-gray-600 dark:text-gray-400">
                    Gunakan Virtual Account ini untuk melakukan pembayaran melalui ATM, Mobile Banking, atau Internet Banking.
                  </p>
                </div>
              )}

              {isQRIS && (
                <div className="space-y-3">
                  <div className="flex items-center gap-2 text-lg font-semibold">
                    <QrCode className="w-5 h-5" />
                    QR Code
                  </div>
                  {qr_code_url && (
                    <div className="flex justify-center">
                      <img src={qr_code_url} alt="QR Code" className="w-64 h-64 border rounded-lg" />
                    </div>
                  )}
                  <p className="text-sm text-gray-600 dark:text-gray-400 text-center">
                    Scan QR Code ini menggunakan aplikasi e-wallet atau mobile banking Anda.
                  </p>
                </div>
              )}

              {isConvenienceStore && (
                <div className="space-y-3">
                  <div className="flex items-center gap-2 text-lg font-semibold">
                    <Store className="w-5 h-5" />
                    Payment Code
                  </div>
                  <div className="flex items-center gap-2 p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
                    <span className="font-mono text-xl flex-1">{payment_code}</span>
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => copyToClipboard(payment_code!)}
                    >
                      <Copy className="w-4 h-4" />
                    </Button>
                  </div>
                  <p className="text-sm text-gray-600 dark:text-gray-400">
                    Gunakan kode pembayaran ini untuk melakukan pembayaran di {payment_method === 'indomaret' ? 'Indomaret' : 'Alfamart'} terdekat.
                  </p>
                </div>
              )}

              {redirect_url && (
                <div className="space-y-3">
                  <p className="text-sm text-gray-600 dark:text-gray-400">
                    Klik tombol di bawah untuk melanjutkan ke halaman pembayaran.
                  </p>
                  <Button
                    className="w-full"
                    onClick={() => window.open(redirect_url, '_blank')}
                  >
                    <ExternalLink className="w-4 h-4 mr-2" />
                    Lanjutkan Pembayaran
                  </Button>
                </div>
              )}

              {payment.status === 'pending' && !redirect_url && !va_number && !qr_code_url && !payment_code && (
                <p className="text-sm text-gray-600 dark:text-gray-400">
                  Menunggu konfirmasi pembayaran...
                </p>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </AppLayout>
  );
}

