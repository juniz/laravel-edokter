import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Download } from 'lucide-react';
import dayjs from 'dayjs';

interface InvoiceItem {
  id: string;
  description: string;
  qty: number;
  unit_price_cents: number;
  total_cents: number;
}

interface Payment {
  id: string;
  provider: string;
  status: string;
  amount_cents: number;
  paid_at?: string;
}

interface Invoice {
  id: string;
  number: string;
  status: string;
  currency: string;
  subtotal_cents: number;
  discount_cents: number;
  tax_cents: number;
  total_cents: number;
  due_at: string;
  items: InvoiceItem[];
  payments?: Payment[];
  customer?: {
    name: string;
    email: string;
  };
  order?: {
    id: string;
  };
}

interface InvoiceShowProps {
  invoice: Invoice;
}

export default function AdminInvoiceShow({ invoice }: InvoiceShowProps) {
  const formatPrice = (cents: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: invoice.currency,
      minimumFractionDigits: 0,
    }).format(cents);
  };

  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Invoices', href: '/admin/invoices' },
    { title: invoice.number, href: route('admin.invoices.show', invoice.id) },
  ];

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={invoice.number} />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold">{invoice.number}</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">
              Due Date: {dayjs(invoice.due_at).format('DD MMMM YYYY')}
            </p>
            {invoice.customer && (
              <p className="text-gray-600 dark:text-gray-400">
                Customer: {invoice.customer.name} ({invoice.customer.email})
              </p>
            )}
          </div>
          <div className="flex gap-2">
            <Badge className={invoice.status === 'paid' ? 'bg-green-500' : 'bg-yellow-500'}>
              {invoice.status.toUpperCase()}
            </Badge>
            <Link href={route('admin.invoices.download', invoice.id)}>
              <Button variant="outline">
                <Download className="w-4 h-4 mr-2" />
                Download PDF
              </Button>
            </Link>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-2">
            <Card className="bg-white dark:bg-gray-800 shadow-md">
              <CardHeader>
                <CardTitle>Invoice Items</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  {invoice.items.map((item) => (
                    <div key={item.id} className="flex justify-between items-start pb-4 border-b last:border-0">
                      <div>
                        <h4 className="font-semibold">{item.description}</h4>
                        <p className="text-sm text-gray-600 dark:text-gray-400">Qty: {item.qty}</p>
                      </div>
                      <div className="text-right">
                        <p className="font-semibold">{formatPrice(item.total_cents)}</p>
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                          {formatPrice(item.unit_price_cents)} each
                        </p>
                      </div>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          </div>

          <div>
            <Card className="bg-white dark:bg-gray-800 shadow-md">
              <CardHeader>
                <CardTitle>Invoice Summary</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <div className="flex justify-between">
                  <span>Subtotal</span>
                  <span>{formatPrice(invoice.subtotal_cents)}</span>
                </div>
                {invoice.discount_cents > 0 && (
                  <div className="flex justify-between text-green-600">
                    <span>Discount</span>
                    <span>-{formatPrice(invoice.discount_cents)}</span>
                  </div>
                )}
                {invoice.tax_cents > 0 && (
                  <div className="flex justify-between">
                    <span>Tax</span>
                    <span>{formatPrice(invoice.tax_cents)}</span>
                  </div>
                )}
                <Separator />
                <div className="flex justify-between text-lg font-bold">
                  <span>Total</span>
                  <span>{formatPrice(invoice.total_cents)}</span>
                </div>
              </CardContent>
            </Card>

            {invoice.payments && invoice.payments.length > 0 && (
              <Card className="bg-white dark:bg-gray-800 shadow-md mt-4">
                <CardHeader>
                  <CardTitle>Payments</CardTitle>
                </CardHeader>
                <CardContent>
                  {invoice.payments.map((payment) => (
                    <div key={payment.id} className="pb-3 border-b last:border-0 mb-3">
                      <div className="flex justify-between items-center">
                        <span className="font-medium">{payment.provider}</span>
                        <Badge className={payment.status === 'succeeded' ? 'bg-green-500' : 'bg-yellow-500'}>
                          {payment.status}
                        </Badge>
                      </div>
                      <p className="text-sm text-gray-600 dark:text-gray-400">
                        {formatPrice(payment.amount_cents)}
                        {payment.paid_at && ` • Paid on ${dayjs(payment.paid_at).format('DD MMM YYYY')}`}
                      </p>
                    </div>
                  ))}
                </CardContent>
              </Card>
            )}
          </div>
        </div>
      </div>
    </AppLayout>
  );
}

