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
  { title: 'Support Tickets', href: '/customer/tickets' },
];

interface Ticket {
  id: string;
  subject: string;
  status: string;
  priority: string;
  created_at: string;
  updated_at: string;
}

interface TicketsProps {
  tickets: Ticket[];
}

export default function Tickets({ tickets }: TicketsProps) {
  const getStatusBadge = (status: string) => {
    const colors: Record<string, string> = {
      open: 'bg-blue-500',
      pending: 'bg-yellow-500',
      solved: 'bg-green-500',
      closed: 'bg-gray-500',
    };
    return colors[status] || 'bg-gray-500';
  };

  const getPriorityBadge = (priority: string) => {
    const colors: Record<string, string> = {
      low: 'bg-gray-500',
      normal: 'bg-blue-500',
      high: 'bg-orange-500',
      urgent: 'bg-red-500',
    };
    return colors[priority] || 'bg-gray-500';
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Support Tickets" />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">Support Tickets</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">Kelola tiket dukungan Anda</p>
          </div>
          <Link href={route('customer.tickets.create')}>
            <Button>+ New Ticket</Button>
          </Link>
        </div>

        {tickets.length === 0 ? (
          <Card className="bg-white dark:bg-gray-800 shadow-md">
            <CardContent className="p-8 text-center">
              <p className="text-gray-600 dark:text-gray-400">Belum ada ticket.</p>
              <Link href={route('customer.tickets.create')}>
                <Button className="mt-4">Create Ticket</Button>
              </Link>
            </CardContent>
          </Card>
        ) : (
          <div className="space-y-4">
            {tickets.map((ticket) => (
              <Card key={ticket.id} className="bg-white dark:bg-gray-800 shadow-md hover:shadow-lg transition-shadow">
                <CardContent className="p-6">
                  <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div className="flex-1">
                      <div className="flex items-center gap-3 mb-2">
                        <Link href={route('customer.tickets.show', ticket.id)}>
                          <h3 className="text-lg font-semibold hover:underline">{ticket.subject}</h3>
                        </Link>
                        <Badge className={getStatusBadge(ticket.status)}>
                          {ticket.status.toUpperCase()}
                        </Badge>
                        <Badge className={getPriorityBadge(ticket.priority)}>
                          {ticket.priority.toUpperCase()}
                        </Badge>
                      </div>
                      <div className="text-sm text-gray-600 dark:text-gray-400">
                        Created {dayjs(ticket.created_at).fromNow()}
                      </div>
                    </div>
                    <div>
                      <Link href={route('customer.tickets.show', ticket.id)}>
                        <Button variant="outline">View</Button>
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

