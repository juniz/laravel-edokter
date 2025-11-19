import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import 'dayjs/locale/id';

dayjs.extend(relativeTime);
dayjs.locale('id');

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Orders', href: '/customer/orders' },
];

interface Order {
  id: string;
  status: string;
  total_cents: number;
  currency: string;
  placed_at: string;
  created_at: string;
}

interface OrdersProps {
  orders: Order[];
}

export default function Orders({ orders }: OrdersProps) {
  const formatPrice = (cents: number, currency: string = 'IDR') => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: currency,
      minimumFractionDigits: 0,
    }).format(cents);
  };

  const getStatusBadge = (status: string) => {
    const colors: Record<string, string> = {
      pending: 'bg-yellow-500',
      paid: 'bg-green-500',
      cancelled: 'bg-red-500',
      refunded: 'bg-blue-500',
      failed: 'bg-gray-500',
    };
    return colors[status] || 'bg-gray-500';
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Orders" />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">My Orders</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">Riwayat pesanan Anda</p>
          </div>
        </div>

        {orders.length === 0 ? (
          <Card className="bg-white dark:bg-gray-800 shadow-md">
            <CardContent className="p-8 text-center">
              <p className="text-gray-600 dark:text-gray-400">Belum ada order.</p>
              <Link href={route('catalog.index')}>
                <Button className="mt-4">Browse Products</Button>
              </Link>
            </CardContent>
          </Card>
        ) : (
          <div className="space-y-4">
            {orders.map((order) => (
              <Card key={order.id} className="bg-white dark:bg-gray-800 shadow-md hover:shadow-lg transition-shadow">
                <CardContent className="p-6">
                  <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div className="flex-1">
                      <div className="flex items-center gap-3 mb-2">
                        <h3 className="text-lg font-semibold">Order #{order.id.slice(0, 8)}</h3>
                        <Badge className={getStatusBadge(order.status)}>
                          {order.status.toUpperCase()}
                        </Badge>
                      </div>
                      <div className="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        <p>Total: <span className="font-semibold">{formatPrice(order.total_cents, order.currency)}</span></p>
                        <p>Placed: {dayjs(order.placed_at || order.created_at).format('DD MMM YYYY HH:mm')}</p>
                      </div>
                    </div>
                    <div>
                      <Link href={route('customer.orders.show', order.id)}>
                        <Button variant="outline">View Details</Button>
                      </Link>
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

