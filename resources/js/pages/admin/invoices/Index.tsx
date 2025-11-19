import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { FileText, Eye, Download } from 'lucide-react';
import dayjs from 'dayjs';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Invoices', href: '/admin/invoices' },
];

interface Invoice {
  id: string;
  number: string;
  status: string;
  total_cents: number;
  currency: string;
  due_at: string;
  created_at: string;
  customer?: {
    name: string;
    email: string;
  };
}

interface InvoicesProps {
  invoices: {
    data: Invoice[];
    links: any;
    meta: any;
  };
}

export default function AdminInvoicesIndex({ invoices }: InvoicesProps) {
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
      partial: 'bg-orange-500',
    };
    return colors[status] || 'bg-gray-500';
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Invoices" />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">Invoices</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">Kelola semua tagihan</p>
          </div>
        </div>

        {invoices.data.length === 0 ? (
          <Card className="bg-white dark:bg-gray-800 shadow-md">
            <CardContent className="p-8 text-center">
              <FileText className="w-12 h-12 mx-auto mb-4 text-gray-400" />
              <p className="text-gray-600 dark:text-gray-400">Belum ada invoice.</p>
            </CardContent>
          </Card>
        ) : (
          <div className="space-y-4">
            {invoices.data.map((invoice) => (
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
                        {invoice.customer && (
                          <p>Customer: <span className="font-semibold">{invoice.customer.name} ({invoice.customer.email})</span></p>
                        )}
                        <p>Amount: <span className="font-semibold">{formatPrice(invoice.total_cents, invoice.currency)}</span></p>
                        <p>Due Date: {dayjs(invoice.due_at).format('DD MMM YYYY')}</p>
                        {invoice.status === 'overdue' && (
                          <p className="text-red-600 font-semibold">Overdue!</p>
                        )}
                      </div>
                    </div>
                    <div className="flex gap-2">
                      <Link href={route('admin.invoices.show', invoice.id)}>
                        <Button variant="outline">
                          <Eye className="w-4 h-4 mr-2" />
                          View
                        </Button>
                      </Link>
                      <Link href={route('admin.invoices.download', invoice.id)}>
                        <Button variant="outline">
                          <Download className="w-4 h-4 mr-2" />
                          Download
                        </Button>
                      </Link>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}

        {/* Pagination */}
        {invoices.links && invoices.links.length > 3 && (
          <div className="flex justify-center gap-2">
            {invoices.links.map((link: any, index: number) => (
              <Link
                key={index}
                href={link.url || '#'}
                className={`px-4 py-2 rounded ${
                  link.active
                    ? 'bg-blue-600 text-white'
                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                } ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}`}
              >
                <span dangerouslySetInnerHTML={{ __html: link.label }} />
              </Link>
            ))}
          </div>
        )}
      </div>
    </AppLayout>
  );
}

