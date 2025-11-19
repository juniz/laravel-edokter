import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem } from '@/types';
import { Package, Eye } from 'lucide-react';
import dayjs from 'dayjs';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Orders', href: '/admin/orders' },
];

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

interface Order {
  id: string;
  status: string;
  total_cents: number;
  currency: string;
  placed_at: string;
  created_at: string;
  customer?: {
    name: string;
    email: string;
  };
  items?: OrderItem[];
}

interface OrdersProps {
  orders: {
    data: Order[];
    links: any;
    meta: any;
  };
}

export default function AdminOrdersIndex({ orders }: OrdersProps) {
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
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">Orders</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">Kelola semua pesanan</p>
          </div>
        </div>

        {orders.data.length === 0 ? (
          <Card className="bg-white dark:bg-gray-800 shadow-md">
            <CardContent className="p-8 text-center">
              <Package className="w-12 h-12 mx-auto mb-4 text-gray-400" />
              <p className="text-gray-600 dark:text-gray-400">Belum ada order.</p>
            </CardContent>
          </Card>
        ) : (
          <div className="space-y-4">
            {orders.data.map((order) => (
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
                        {order.customer && (
                          <p>Customer: <span className="font-semibold">{order.customer.name} ({order.customer.email})</span></p>
                        )}
                        <p>Total: <span className="font-semibold">{formatPrice(order.total_cents, order.currency)}</span></p>
                        <p>Placed: {dayjs(order.placed_at || order.created_at).format('DD MMM YYYY HH:mm')}</p>
                      </div>
                    </div>
                    <div>
                      <Link href={route('admin.orders.show', order.id)}>
                        <Button variant="outline">
                          <Eye className="w-4 h-4 mr-2" />
                          View Details
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
        {orders.links && orders.links.length > 3 && (
          <div className="flex justify-center gap-2">
            {orders.links.map((link: any, index: number) => (
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

