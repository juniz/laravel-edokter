import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { MessageSquare, Eye } from 'lucide-react';
import dayjs from 'dayjs';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Tickets', href: '/admin/tickets' },
];

interface Customer {
  id: string;
  name: string;
  email: string;
}

interface Ticket {
  id: string;
  subject: string;
  status: string;
  priority: string;
  created_at: string;
  customer?: Customer;
}

interface TicketsProps {
  tickets: {
    data: Ticket[];
    links: any;
    meta: any;
  };
  filters?: {
    status?: string;
    priority?: string;
  };
}

export default function AdminTicketsIndex({ tickets, filters }: TicketsProps) {
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

  const handleFilterChange = (key: string, value: string) => {
    const params = new URLSearchParams(window.location.search);
    if (value) {
      params.set(key, value);
    } else {
      params.delete(key);
    }
    router.get(`/admin/tickets?${params.toString()}`);
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Tickets" />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">Support Tickets</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">Kelola semua tiket dukungan</p>
          </div>
        </div>

        {/* Filters */}
        <Card className="bg-white dark:bg-gray-800 shadow-md">
          <CardContent className="p-4">
            <div className="flex gap-4">
              <div className="flex-1">
                <label className="text-sm font-medium mb-2 block">Status</label>
                <Select 
                  value={filters?.status || 'all'} 
                  onValueChange={(value) => handleFilterChange('status', value === 'all' ? '' : value)}
                >
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Status</SelectItem>
                    <SelectItem value="open">Open</SelectItem>
                    <SelectItem value="pending">Pending</SelectItem>
                    <SelectItem value="solved">Solved</SelectItem>
                    <SelectItem value="closed">Closed</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div className="flex-1">
                <label className="text-sm font-medium mb-2 block">Priority</label>
                <Select 
                  value={filters?.priority || 'all'} 
                  onValueChange={(value) => handleFilterChange('priority', value === 'all' ? '' : value)}
                >
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Priority</SelectItem>
                    <SelectItem value="low">Low</SelectItem>
                    <SelectItem value="normal">Normal</SelectItem>
                    <SelectItem value="high">High</SelectItem>
                    <SelectItem value="urgent">Urgent</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
          </CardContent>
        </Card>

        {tickets.data.length === 0 ? (
          <Card className="bg-white dark:bg-gray-800 shadow-md">
            <CardContent className="p-8 text-center">
              <MessageSquare className="w-12 h-12 mx-auto mb-4 text-gray-400" />
              <p className="text-gray-600 dark:text-gray-400">Belum ada ticket.</p>
            </CardContent>
          </Card>
        ) : (
          <div className="space-y-4">
            {tickets.data.map((ticket) => (
              <Card key={ticket.id} className="bg-white dark:bg-gray-800 shadow-md hover:shadow-lg transition-shadow">
                <CardContent className="p-6">
                  <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div className="flex-1">
                      <div className="flex items-center gap-3 mb-2">
                        <Link href={route('admin.tickets.show', ticket.id)}>
                          <h3 className="text-lg font-semibold hover:underline">{ticket.subject}</h3>
                        </Link>
                        <Badge className={getStatusBadge(ticket.status)}>
                          {ticket.status.toUpperCase()}
                        </Badge>
                        <Badge className={getPriorityBadge(ticket.priority)}>
                          {ticket.priority.toUpperCase()}
                        </Badge>
                      </div>
                      <div className="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        {ticket.customer && (
                          <p>Customer: <span className="font-semibold">{ticket.customer.name} ({ticket.customer.email})</span></p>
                        )}
                        <p>Created {dayjs(ticket.created_at).format('DD MMM YYYY HH:mm')}</p>
                      </div>
                    </div>
                    <div>
                      <Link href={route('admin.tickets.show', ticket.id)}>
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
        {tickets.links && tickets.links.length > 3 && (
          <div className="flex justify-center gap-2">
            {tickets.links.map((link: any, index: number) => (
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




