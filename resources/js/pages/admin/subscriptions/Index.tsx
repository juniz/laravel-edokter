import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { CreditCard, Eye } from 'lucide-react';
import dayjs from 'dayjs';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Subscriptions', href: '/admin/subscriptions' },
];

interface Subscription {
  id: string;
  status: string;
  product: {
    name: string;
  };
  plan: {
    code: string;
  };
  start_at: string;
  end_at?: string;
  next_renewal_at?: string;
  auto_renew: boolean;
  customer?: {
    name: string;
    email: string;
  };
}

interface SubscriptionsProps {
  subscriptions: {
    data: Subscription[];
    links: any;
    meta: any;
  };
}

export default function AdminSubscriptionsIndex({ subscriptions }: SubscriptionsProps) {
  const getStatusBadge = (status: string) => {
    const colors: Record<string, string> = {
      trialing: 'bg-blue-500',
      active: 'bg-green-500',
      past_due: 'bg-yellow-500',
      suspended: 'bg-orange-500',
      cancelled: 'bg-red-500',
      terminated: 'bg-gray-500',
    };
    return colors[status] || 'bg-gray-500';
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Subscriptions" />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">Subscriptions</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">Kelola semua langganan</p>
          </div>
        </div>

        {subscriptions.data.length === 0 ? (
          <Card className="bg-white dark:bg-gray-800 shadow-md">
            <CardContent className="p-8 text-center">
              <CreditCard className="w-12 h-12 mx-auto mb-4 text-gray-400" />
              <p className="text-gray-600 dark:text-gray-400">Belum ada subscription.</p>
            </CardContent>
          </Card>
        ) : (
          <div className="space-y-4">
            {subscriptions.data.map((subscription) => (
              <Card key={subscription.id} className="bg-white dark:bg-gray-800 shadow-md hover:shadow-lg transition-shadow">
                <CardContent className="p-6">
                  <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div className="flex-1">
                      <div className="flex items-center gap-3 mb-2">
                        <h3 className="text-lg font-semibold">{subscription.product.name}</h3>
                        <Badge className={getStatusBadge(subscription.status)}>
                          {subscription.status.toUpperCase()}
                        </Badge>
                        {subscription.auto_renew && (
                          <Badge variant="outline">Auto Renew</Badge>
                        )}
                      </div>
                      <div className="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        {subscription.customer && (
                          <p>Customer: <span className="font-semibold">{subscription.customer.name} ({subscription.customer.email})</span></p>
                        )}
                        <p>Plan: <span className="font-semibold">{subscription.plan.code}</span></p>
                        <p>Started: {dayjs(subscription.start_at).format('DD MMM YYYY')}</p>
                        {subscription.end_at && (
                          <p>Ends: {dayjs(subscription.end_at).format('DD MMM YYYY')}</p>
                        )}
                        {subscription.next_renewal_at && (
                          <p>Next Renewal: {dayjs(subscription.next_renewal_at).format('DD MMM YYYY')}</p>
                        )}
                      </div>
                    </div>
                    <div>
                      <Link href={route('admin.subscriptions.show', subscription.id)}>
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
        {subscriptions.links && subscriptions.links.length > 3 && (
          <div className="flex justify-center gap-2">
            {subscriptions.links.map((link: any, index: number) => (
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

