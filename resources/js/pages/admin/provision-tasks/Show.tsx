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

interface ProvisionTask {
  id: string;
  action: string;
  status: string;
  attempts: number;
  error?: string;
  created_at: string;
  updated_at: string;
  server?: {
    id: string;
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
    };
    customer?: {
      name: string;
      email: string;
    };
  };
}

interface ProvisionTaskShowProps {
  task: ProvisionTask;
}

export default function ProvisionTaskShow({ task }: ProvisionTaskShowProps) {
  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Provision Tasks', href: '/admin/provision-tasks' },
    { title: `Task #${task.id.slice(0, 8)}`, href: route('admin.provision-tasks.show', task.id) },
  ];

  const getStatusBadge = (status: string) => {
    const colors: Record<string, string> = {
      queued: 'bg-blue-500',
      running: 'bg-yellow-500',
      succeeded: 'bg-green-500',
      failed: 'bg-red-500',
    };
    return colors[status] || 'bg-gray-500';
  };

  const getActionLabel = (action: string) => {
    const labels: Record<string, string> = {
      create: 'Create Account',
      suspend: 'Suspend Account',
      unsuspend: 'Unsuspend Account',
      terminate: 'Terminate Account',
      change_plan: 'Change Plan',
      reset_password: 'Reset Password',
      sync: 'Sync Account',
    };
    return labels[action] || action;
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`Provision Task #${task.id.slice(0, 8)}`} />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
              {getActionLabel(task.action)}
            </h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">
              Created: {dayjs(task.created_at).format('DD MMMM YYYY HH:mm')}
            </p>
          </div>
          <Badge className={getStatusBadge(task.status)}>
            {task.status.toUpperCase()}
          </Badge>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-2">
            <Card className="bg-white dark:bg-gray-800 shadow-md">
              <CardHeader>
                <CardTitle>Task Details</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Action</span>
                  <span className="font-semibold">{getActionLabel(task.action)}</span>
                </div>
                <Separator />
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Status</span>
                  <Badge className={getStatusBadge(task.status)}>
                    {task.status}
                  </Badge>
                </div>
                <Separator />
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Attempts</span>
                  <span>{task.attempts}</span>
                </div>
                <Separator />
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Created</span>
                  <span>{dayjs(task.created_at).format('DD MMM YYYY HH:mm')}</span>
                </div>
                <Separator />
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Updated</span>
                  <span>{dayjs(task.updated_at).format('DD MMM YYYY HH:mm')}</span>
                </div>
                {task.error && (
                  <>
                    <Separator />
                    <div>
                      <span className="text-gray-600 dark:text-gray-400 block mb-2">Error:</span>
                      <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded p-3">
                        <p className="text-red-800 dark:text-red-200 text-sm">{task.error}</p>
                      </div>
                    </div>
                  </>
                )}
              </CardContent>
            </Card>

            {task.server && (
              <Card className="bg-white dark:bg-gray-800 shadow-md mt-6">
                <CardHeader>
                  <CardTitle>Server Information</CardTitle>
                </CardHeader>
                <CardContent className="space-y-3">
                  <div className="flex justify-between">
                    <span className="text-gray-600 dark:text-gray-400">Server Name</span>
                    <span className="font-semibold">{task.server.name}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-600 dark:text-gray-400">Type</span>
                    <span>{task.server.type}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-600 dark:text-gray-400">Endpoint</span>
                    <span className="font-mono text-sm">{task.server.endpoint}</span>
                  </div>
                  <div className="pt-2">
                    <Link href={route('admin.servers.show', task.server.id)}>
                      <Button variant="outline" className="w-full">
                        <Eye className="w-4 h-4 mr-2" />
                        View Server
                      </Button>
                    </Link>
                  </div>
                </CardContent>
              </Card>
            )}

            {task.subscription && (
              <Card className="bg-white dark:bg-gray-800 shadow-md mt-6">
                <CardHeader>
                  <CardTitle>Subscription Information</CardTitle>
                </CardHeader>
                <CardContent className="space-y-3">
                  <div className="flex justify-between">
                    <span className="text-gray-600 dark:text-gray-400">Product</span>
                    <span className="font-semibold">{task.subscription.product.name}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-600 dark:text-gray-400">Plan</span>
                    <span>{task.subscription.plan.code}</span>
                  </div>
                  {task.subscription.customer && (
                    <>
                      <Separator />
                      <div className="flex justify-between">
                        <span className="text-gray-600 dark:text-gray-400">Customer</span>
                        <span>{task.subscription.customer.name} ({task.subscription.customer.email})</span>
                      </div>
                    </>
                  )}
                  <div className="pt-2">
                    <Link href={route('admin.subscriptions.show', task.subscription.id)}>
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
                {task.server && (
                  <Link href={route('admin.servers.show', task.server.id)} className="block">
                    <Button variant="outline" className="w-full">
                      View Server
                    </Button>
                  </Link>
                )}
                {task.subscription && (
                  <Link href={route('admin.subscriptions.show', task.subscription.id)} className="block">
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

