import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Eye } from 'lucide-react';
import dayjs from 'dayjs';

interface PanelAccount {
  id: string;
  username: string;
  domain: string;
  status: string;
  last_sync_at?: string;
  meta?: any;
  server?: {
    name: string;
    type: string;
    endpoint: string;
  };
  subscription?: {
    id: string;
    product: {
      name: string;
    };
    plan: {
      code: string;
    } | null;
    customer?: {
      name: string;
      email: string;
    };
  };
}

interface PanelAccountShowProps {
  account: PanelAccount;
}

export default function PanelAccountShow({ account }: PanelAccountShowProps) {
  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Panel Accounts', href: '/admin/panel-accounts' },
    { title: account.username, href: route('admin.panel-accounts.show', account.id) },
  ];

  const getStatusBadge = (status: string) => {
    const colors: Record<string, string> = {
      active: 'bg-green-500',
      suspended: 'bg-yellow-500',
      terminated: 'bg-red-500',
    };
    return colors[status] || 'bg-gray-500';
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={account.username} />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">{account.username}</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">{account.domain}</p>
          </div>
          <Badge className={getStatusBadge(account.status)}>
            {account.status.toUpperCase()}
          </Badge>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-2">
            <Card className="bg-white dark:bg-gray-800 shadow-md">
              <CardHeader>
                <CardTitle>Account Details</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Username</span>
                  <span className="font-semibold">{account.username}</span>
                </div>
                <Separator />
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Domain</span>
                  <span className="font-semibold">{account.domain}</span>
                </div>
                <Separator />
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Status</span>
                  <Badge className={getStatusBadge(account.status)}>
                    {account.status}
                  </Badge>
                </div>
                {account.last_sync_at && (
                  <>
                    <Separator />
                    <div className="flex justify-between">
                      <span className="text-gray-600 dark:text-gray-400">Last Sync</span>
                      <span>{dayjs(account.last_sync_at).format('DD MMM YYYY HH:mm')}</span>
                    </div>
                  </>
                )}
              </CardContent>
            </Card>

            {account.server && (
              <Card className="bg-white dark:bg-gray-800 shadow-md mt-6">
                <CardHeader>
                  <CardTitle>Server Information</CardTitle>
                </CardHeader>
                <CardContent className="space-y-3">
                  <div className="flex justify-between">
                    <span className="text-gray-600 dark:text-gray-400">Server Name</span>
                    <span className="font-semibold">{account.server.name}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-600 dark:text-gray-400">Type</span>
                    <span>{account.server.type}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-600 dark:text-gray-400">Endpoint</span>
                    <span className="font-mono text-sm">{account.server.endpoint}</span>
                  </div>
                </CardContent>
              </Card>
            )}

            {account.subscription && (
              <Card className="bg-white dark:bg-gray-800 shadow-md mt-6">
                <CardHeader>
                  <CardTitle>Subscription Information</CardTitle>
                </CardHeader>
                <CardContent className="space-y-3">
                  <div className="flex justify-between">
                    <span className="text-gray-600 dark:text-gray-400">Product</span>
                    <span className="font-semibold">{account.subscription.product.name}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-600 dark:text-gray-400">Plan</span>
                    <span>{account.subscription.plan?.code ?? 'Default'}</span>
                  </div>
                  {account.subscription.customer && (
                    <>
                      <Separator />
                      <div className="flex justify-between">
                        <span className="text-gray-600 dark:text-gray-400">Customer</span>
                        <span>{account.subscription.customer.name} ({account.subscription.customer.email})</span>
                      </div>
                    </>
                  )}
                  <div className="pt-2">
                    <Link href={route('admin.subscriptions.show', account.subscription.id)}>
                      <Button variant="outline" className="w-full">
                        <Eye className="w-4 h-4 mr-2" />
                        View Subscription
                      </Button>
                    </Link>
                  </div>
                </CardContent>
              </Card>
            )}
          </div>

          <div>
            <Card className="bg-white dark:bg-gray-800 shadow-md">
              <CardHeader>
                <CardTitle>Quick Actions</CardTitle>
              </CardHeader>
              <CardContent className="space-y-2">
                {account.server && (
                  <Link href={route('admin.servers.show', account.server.id)} className="block">
                    <Button variant="outline" className="w-full">
                      View Server
                    </Button>
                  </Link>
                )}
                {account.subscription && (
                  <Link href={route('admin.subscriptions.show', account.subscription.id)} className="block">
                    <Button variant="outline" className="w-full">
                      View Subscription
                    </Button>
                  </Link>
                )}
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
