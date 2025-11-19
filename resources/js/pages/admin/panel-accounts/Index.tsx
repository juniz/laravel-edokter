import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { UserCircle, Eye } from 'lucide-react';
import dayjs from 'dayjs';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Panel Accounts', href: '/admin/panel-accounts' },
];

interface PanelAccount {
  id: string;
  username: string;
  domain: string;
  status: string;
  last_sync_at?: string;
  server?: {
    name: string;
    type: string;
  };
  subscription?: {
    id: string;
    product: {
      name: string;
    };
    plan: {
      code: string;
    };
  };
}

interface PanelAccountsProps {
  accounts: {
    data: PanelAccount[];
    links: any;
    meta: any;
  };
}

export default function PanelAccountsIndex({ accounts }: PanelAccountsProps) {
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
      <Head title="Panel Accounts" />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">Panel Accounts</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">Kelola akun panel hosting</p>
          </div>
        </div>

        {accounts.data.length === 0 ? (
          <Card className="bg-white dark:bg-gray-800 shadow-md">
            <CardContent className="p-8 text-center">
              <UserCircle className="w-12 h-12 mx-auto mb-4 text-gray-400" />
              <p className="text-gray-600 dark:text-gray-400">Belum ada panel account.</p>
            </CardContent>
          </Card>
        ) : (
          <div className="space-y-4">
            {accounts.data.map((account) => (
              <Card key={account.id} className="bg-white dark:bg-gray-800 shadow-md hover:shadow-lg transition-shadow">
                <CardContent className="p-6">
                  <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div className="flex-1">
                      <div className="flex items-center gap-3 mb-2">
                        <h3 className="text-lg font-semibold">{account.username}</h3>
                        <Badge className={getStatusBadge(account.status)}>
                          {account.status.toUpperCase()}
                        </Badge>
                      </div>
                      <div className="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        <p>Domain: <span className="font-semibold">{account.domain}</span></p>
                        {account.server && (
                          <p>Server: <span className="font-semibold">{account.server.name} ({account.server.type})</span></p>
                        )}
                        {account.subscription && (
                          <p>Subscription: <span className="font-semibold">{account.subscription.product.name} - {account.subscription.plan.code}</span></p>
                        )}
                        {account.last_sync_at && (
                          <p>Last Sync: {dayjs(account.last_sync_at).format('DD MMM YYYY HH:mm')}</p>
                        )}
                      </div>
                    </div>
                    <div>
                      <Link href={route('admin.panel-accounts.show', account.id)}>
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
        {accounts.links && accounts.links.length > 3 && (
          <div className="flex justify-center gap-2">
            {accounts.links.map((link: any, index: number) => (
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

