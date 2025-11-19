import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Settings, Eye } from 'lucide-react';
import dayjs from 'dayjs';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Provision Tasks', href: '/admin/provision-tasks' },
];

interface ProvisionTask {
  id: string;
  action: string;
  status: string;
  attempts: number;
  error?: string;
  created_at: string;
  updated_at: string;
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

interface ProvisionTasksProps {
  tasks: {
    data: ProvisionTask[];
    links: any;
    meta: any;
  };
}

export default function ProvisionTasksIndex({ tasks }: ProvisionTasksProps) {
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
      create: 'Create',
      suspend: 'Suspend',
      unsuspend: 'Unsuspend',
      terminate: 'Terminate',
      change_plan: 'Change Plan',
      reset_password: 'Reset Password',
      sync: 'Sync',
    };
    return labels[action] || action;
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Provision Tasks" />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">Provision Tasks</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">Monitor tugas provisioning</p>
          </div>
        </div>

        {tasks.data.length === 0 ? (
          <Card className="bg-white dark:bg-gray-800 shadow-md">
            <CardContent className="p-8 text-center">
              <Settings className="w-12 h-12 mx-auto mb-4 text-gray-400" />
              <p className="text-gray-600 dark:text-gray-400">Belum ada provision task.</p>
            </CardContent>
          </Card>
        ) : (
          <div className="space-y-4">
            {tasks.data.map((task) => (
              <Card key={task.id} className="bg-white dark:bg-gray-800 shadow-md hover:shadow-lg transition-shadow">
                <CardContent className="p-6">
                  <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div className="flex-1">
                      <div className="flex items-center gap-3 mb-2">
                        <h3 className="text-lg font-semibold">{getActionLabel(task.action)}</h3>
                        <Badge className={getStatusBadge(task.status)}>
                          {task.status.toUpperCase()}
                        </Badge>
                        {task.attempts > 0 && (
                          <Badge variant="outline">Attempts: {task.attempts}</Badge>
                        )}
                      </div>
                      <div className="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        {task.server && (
                          <p>Server: <span className="font-semibold">{task.server.name} ({task.server.type})</span></p>
                        )}
                        {task.subscription && (
                          <p>Subscription: <span className="font-semibold">{task.subscription.product.name} - {task.subscription.plan.code}</span></p>
                        )}
                        <p>Created: {dayjs(task.created_at).format('DD MMM YYYY HH:mm')}</p>
                        {task.error && (
                          <p className="text-red-600 font-semibold">Error: {task.error}</p>
                        )}
                      </div>
                    </div>
                    <div>
                      <Link href={route('admin.provision-tasks.show', task.id)}>
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
        {tasks.links && tasks.links.length > 3 && (
          <div className="flex justify-center gap-2">
            {tasks.links.map((link: any, index: number) => (
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

