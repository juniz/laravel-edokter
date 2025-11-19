import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Edit, Server, UserCircle } from 'lucide-react';
import dayjs from 'dayjs';

interface PanelAccount {
  id: string;
  username: string;
  domain: string;
  status: string;
  subscription: {
    id: string;
    product: {
      name: string;
    };
  };
}

interface ProvisionTask {
  id: string;
  action: string;
  status: string;
  created_at: string;
}

interface Server {
  id: string;
  name: string;
  type: string;
  endpoint: string;
  status: string;
  created_at: string;
  updated_at: string;
  panelAccounts?: PanelAccount[];
  provisionTasks?: ProvisionTask[];
}

interface ServerShowProps {
  server: Server;
}

export default function ServerShow({ server }: ServerShowProps) {
  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Servers', href: '/admin/servers' },
    { title: server.name, href: route('admin.servers.show', server.id) },
  ];

  const getStatusBadge = (status: string) => {
    const colors: Record<string, string> = {
      active: 'bg-green-500',
      maintenance: 'bg-yellow-500',
      disabled: 'bg-gray-500',
    };
    return colors[status] || 'bg-gray-500';
  };

  const getTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
      cpanel: 'cPanel',
      directadmin: 'DirectAdmin',
      proxmox: 'Proxmox',
    };
    return labels[type] || type;
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={server.name} />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">{server.name}</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">{server.endpoint}</p>
          </div>
          <div className="flex gap-2">
            <Badge className={getStatusBadge(server.status)}>
              {server.status.toUpperCase()}
            </Badge>
            <Link href={route('admin.servers.edit', server.id)}>
              <Button variant="outline">
                <Edit className="w-4 h-4 mr-2" />
                Edit
              </Button>
            </Link>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-2">
            <Card className="bg-white dark:bg-gray-800 shadow-md">
              <CardHeader>
                <CardTitle>Server Details</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Type</span>
                  <span className="font-semibold">{getTypeLabel(server.type)}</span>
                </div>
                <Separator />
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Status</span>
                  <Badge className={getStatusBadge(server.status)}>
                    {server.status}
                  </Badge>
                </div>
                <Separator />
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Endpoint</span>
                  <span className="font-mono text-sm">{server.endpoint}</span>
                </div>
                <Separator />
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Created</span>
                  <span>{dayjs(server.created_at).format('DD MMM YYYY HH:mm')}</span>
                </div>
                <Separator />
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Updated</span>
                  <span>{dayjs(server.updated_at).format('DD MMM YYYY HH:mm')}</span>
                </div>
              </CardContent>
            </Card>

            {server.panelAccounts && server.panelAccounts.length > 0 && (
              <Card className="bg-white dark:bg-gray-800 shadow-md mt-6">
                <CardHeader>
                  <CardTitle>Panel Accounts ({server.panelAccounts.length})</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-4">
                    {server.panelAccounts.map((account) => (
                      <div key={account.id} className="flex justify-between items-center p-4 border rounded-lg">
                        <div>
                          <h4 className="font-semibold">{account.username}</h4>
                          <p className="text-sm text-gray-600 dark:text-gray-400">
                            {account.domain} • {account.subscription.product.name}
                          </p>
                        </div>
                        <Badge>{account.status}</Badge>
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>
            )}

            {server.provisionTasks && server.provisionTasks.length > 0 && (
              <Card className="bg-white dark:bg-gray-800 shadow-md mt-6">
                <CardHeader>
                  <CardTitle>Recent Provision Tasks</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-2">
                    {server.provisionTasks.slice(0, 5).map((task) => (
                      <div key={task.id} className="flex justify-between items-center p-3 border rounded">
                        <div>
                          <span className="font-medium">{task.action}</span>
                          <span className="text-sm text-gray-600 dark:text-gray-400 ml-2">
                            {dayjs(task.created_at).format('DD MMM YYYY HH:mm')}
                          </span>
                        </div>
                        <Badge>{task.status}</Badge>
                      </div>
                    ))}
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
                <Link href={route('admin.servers.edit', server.id)} className="block">
                  <Button variant="outline" className="w-full">
                    <Edit className="w-4 h-4 mr-2" />
                    Edit Server
                  </Button>
                </Link>
                <Link href={route('admin.panel-accounts.index', { server_id: server.id })} className="block">
                  <Button variant="outline" className="w-full">
                    <UserCircle className="w-4 h-4 mr-2" />
                    View Accounts
                  </Button>
                </Link>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}

