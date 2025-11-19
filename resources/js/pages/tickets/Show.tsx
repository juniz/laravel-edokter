import React, { FormEventHandler } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import InputError from '@/components/input-error';
import { Separator } from '@/components/ui/separator';
import dayjs from 'dayjs';

interface TicketReply {
  id: string;
  message: string;
  created_at: string;
  user?: {
    name: string;
  };
  customer?: {
    name: string;
  };
}

interface Ticket {
  id: string;
  subject: string;
  status: string;
  priority: string;
  created_at: string;
  replies: TicketReply[];
}

interface TicketShowProps {
  ticket: Ticket;
}

export default function TicketShow({ ticket }: TicketShowProps) {
  const { data, setData, post, processing, errors, reset } = useForm({
    message: '',
  });

  const submit: FormEventHandler = (e) => {
    e.preventDefault();
    post(route('customer.tickets.reply', ticket.id), {
      preserveScroll: true,
      onSuccess: () => reset('message'),
    });
  };

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

  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Support Tickets', href: '/customer/tickets' },
    { title: ticket.subject, href: route('customer.tickets.show', ticket.id) },
  ];

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={ticket.subject} />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold">{ticket.subject}</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">
              Created {dayjs(ticket.created_at).format('DD MMMM YYYY HH:mm')}
            </p>
          </div>
          <div className="flex gap-2">
            <Badge className={getStatusBadge(ticket.status)}>
              {ticket.status.toUpperCase()}
            </Badge>
            <Badge className={getPriorityBadge(ticket.priority)}>
              {ticket.priority.toUpperCase()}
            </Badge>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-2 space-y-4">
            {ticket.replies.map((reply) => (
              <Card key={reply.id} className="bg-white dark:bg-gray-800 shadow-md">
                <CardHeader>
                  <div className="flex justify-between items-center">
                    <CardTitle className="text-sm">
                      {reply.user?.name || reply.customer?.name || 'Customer'}
                    </CardTitle>
                    <span className="text-xs text-gray-600 dark:text-gray-400">
                      {dayjs(reply.created_at).format('DD MMM YYYY HH:mm')}
                    </span>
                  </div>
                </CardHeader>
                <CardContent>
                  <p className="whitespace-pre-wrap">{reply.message}</p>
                </CardContent>
              </Card>
            ))}

            {ticket.status !== 'closed' && (
              <Card className="bg-white dark:bg-gray-800 shadow-md">
                <CardHeader>
                  <CardTitle>Reply</CardTitle>
                </CardHeader>
                <CardContent>
                  <form onSubmit={submit} className="space-y-4">
                    <div>
                      <Label htmlFor="message">Your Message</Label>
                      <Textarea
                        id="message"
                        value={data.message}
                        onChange={(e) => setData('message', e.target.value)}
                        className="mt-1"
                        rows={6}
                        placeholder="Type your reply..."
                      />
                      <InputError message={errors.message} className="mt-2" />
                    </div>
                    <Button type="submit" disabled={processing}>
                      {processing ? 'Sending...' : 'Send Reply'}
                    </Button>
                  </form>
                </CardContent>
              </Card>
            )}
          </div>

          <div>
            <Card className="bg-white dark:bg-gray-800 shadow-md">
              <CardHeader>
                <CardTitle>Ticket Information</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Status</span>
                  <Badge className={getStatusBadge(ticket.status)}>{ticket.status}</Badge>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Priority</span>
                  <Badge className={getPriorityBadge(ticket.priority)}>{ticket.priority}</Badge>
                </div>
                <Separator />
                <div className="text-sm text-gray-600 dark:text-gray-400">
                  <p>Created: {dayjs(ticket.created_at).format('DD MMM YYYY')}</p>
                  <p>Replies: {ticket.replies.length}</p>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}

