import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import dayjs from 'dayjs';

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
}

interface InvoicesProps {
  invoices: Invoice[];
}

export default function Invoices({ invoices }: InvoicesProps) {
  const formatPrice = (cents: number, currency: string = 'IDR') => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: currency,
      minimumFractionDigits: 0,
    }).format(cents);
  };

  const getStatusBadge = (status: string) => {
    const colors: Record<string, string> = {
      unpaid: 'bg-yellow-500',
      paid: 'bg-green-500',
      overdue: 'bg-red-500',
      void: 'bg-gray-500',
      refunded: 'bg-blue-500',
    };
    return colors[status] || 'bg-gray-500';
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Invoices" />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">My Invoices</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">Daftar tagihan Anda</p>
          </div>
        </div>

        {invoices.length === 0 ? (
          <Card className="bg-white dark:bg-gray-800 shadow-md">
            <CardContent className="p-8 text-center">
              <p className="text-gray-600 dark:text-gray-400">Belum ada invoice.</p>
            </CardContent>
          </Card>
        ) : (
          <div className="space-y-4">
            {invoices.map((invoice) => (
              <Card key={invoice.id} className="bg-white dark:bg-gray-800 shadow-md hover:shadow-lg transition-shadow">
                <CardContent className="p-6">
                  <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div className="flex-1">
                      <div className="flex items-center gap-3 mb-2">
                        <h3 className="text-lg font-semibold">{invoice.number}</h3>
                        <Badge className={getStatusBadge(invoice.status)}>
                          {invoice.status.toUpperCase()}
                        </Badge>
                      </div>
                      <div className="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        <p>Amount: <span className="font-semibold">{formatPrice(invoice.total_cents, invoice.currency)}</span></p>
                        <p>Due Date: {dayjs(invoice.due_at).format('DD MMM YYYY')}</p>
                        {invoice.status === 'overdue' && (
                          <p className="text-red-600 font-semibold">Overdue!</p>
                        )}
                      </div>
                    </div>
                    <div className="flex gap-2">
                      <Link href={route('customer.invoices.show', invoice.id)}>
                        <Button variant="outline">View</Button>
                      </Link>
                      {invoice.status === 'unpaid' && (
                        <Button>Pay Now</Button>
                      )}
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}
      </div>
    </AppLayout>
  );
}

