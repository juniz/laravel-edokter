import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import dayjs from 'dayjs';

interface OrderItem {
  id: string;
  product: {
    name: string;
  };
  plan?: {
    code: string;
  };
  qty: number;
  unit_price_cents: number;
  total_cents: number;
}

interface Invoice {
  id: string;
  number: string;
  status: string;
  total_cents: number;
}

interface Order {
  id: string;
  status: string;
  currency: string;
  subtotal_cents: number;
  discount_cents: number;
  tax_cents: number;
  total_cents: number;
  placed_at: string;
  items: OrderItem[];
  invoices?: Invoice[];
}

interface OrderShowProps {
  order: Order;
}

export default function OrderShow({ order }: OrderShowProps) {
  const formatPrice = (cents: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: order.currency,
      minimumFractionDigits: 0,
    }).format(cents);
  };

  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Orders', href: '/customer/orders' },
    { title: `Order #${order.id.slice(0, 8)}`, href: route('customer.orders.show', order.id) },
  ];

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`Order #${order.id.slice(0, 8)}`} />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold">Order #{order.id.slice(0, 8)}</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">
              Placed on {dayjs(order.placed_at).format('DD MMMM YYYY HH:mm')}
            </p>
          </div>
          <Badge className={order.status === 'paid' ? 'bg-green-500' : 'bg-yellow-500'}>
            {order.status.toUpperCase()}
          </Badge>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-2">
            <Card className="bg-white dark:bg-gray-800 shadow-md">
              <CardHeader>
                <CardTitle>Order Items</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  {order.items.map((item) => (
                    <div key={item.id} className="flex justify-between items-start pb-4 border-b last:border-0">
                      <div>
                        <h4 className="font-semibold">{item.product.name}</h4>
                        {item.plan && (
                          <p className="text-sm text-gray-600 dark:text-gray-400">Plan: {item.plan.code}</p>
                        )}
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
                <CardTitle>Order Summary</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <div className="flex justify-between">
                  <span>Subtotal</span>
                  <span>{formatPrice(order.subtotal_cents)}</span>
                </div>
                {order.discount_cents > 0 && (
                  <div className="flex justify-between text-green-600">
                    <span>Discount</span>
                    <span>-{formatPrice(order.discount_cents)}</span>
                  </div>
                )}
                {order.tax_cents > 0 && (
                  <div className="flex justify-between">
                    <span>Tax</span>
                    <span>{formatPrice(order.tax_cents)}</span>
                  </div>
                )}
                <Separator />
                <div className="flex justify-between text-lg font-bold">
                  <span>Total</span>
                  <span>{formatPrice(order.total_cents)}</span>
                </div>
              </CardContent>
            </Card>

            {order.invoices && order.invoices.length > 0 && (
              <Card className="bg-white dark:bg-gray-800 shadow-md mt-4">
                <CardHeader>
                  <CardTitle>Invoices</CardTitle>
                </CardHeader>
                <CardContent>
                  {order.invoices.map((invoice) => (
                    <Link
                      key={invoice.id}
                      href={route('customer.invoices.show', invoice.id)}
                      className="block p-3 rounded hover:bg-gray-100 dark:hover:bg-gray-700 mb-2"
                    >
                      <div className="flex justify-between items-center">
                        <span className="font-medium">{invoice.number}</span>
                        <Badge className={invoice.status === 'paid' ? 'bg-green-500' : 'bg-yellow-500'}>
                          {invoice.status}
                        </Badge>
                      </div>
                    </Link>
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

