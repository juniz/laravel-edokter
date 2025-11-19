import React, { FormEventHandler } from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/input-error';
import dayjs from 'dayjs';

interface TicketReply {
  id: string;
  message: string;
  created_at: string;
  user?: {
    id: number;
    name: string;
    email: string;
  };
  customer?: {
    id: string;
    name: string;
    email: string;
  };
}

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
  replies: TicketReply[];
}

interface TicketShowProps {
  ticket: Ticket;
}

export default function AdminTicketShow({ ticket }: TicketShowProps) {
  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Tickets', href: '/admin/tickets' },
    { title: ticket.subject, href: route('admin.tickets.show', ticket.id) },
  ];

  const { data: statusData, setData: setStatusData, put: updateStatus, processing: statusProcessing } = useForm({
    status: ticket.status,
    priority: ticket.priority,
  });

  const { data: replyData, setData: setReplyData, post: reply, processing: replyProcessing, reset: resetReply, errors } = useForm({
    message: '',
  });

  const handleStatusUpdate: FormEventHandler = (e) => {
    e.preventDefault();
    updateStatus(route('admin.tickets.update', ticket.id), {
      preserveScroll: true,
    });
  };

  const handleReply: FormEventHandler = (e) => {
    e.preventDefault();
    reply(route('admin.tickets.reply', ticket.id), {
      preserveScroll: true,
      onSuccess: () => resetReply('message'),
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

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`Ticket: ${ticket.subject}`} />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">Ticket: {ticket.subject}</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">
              Opened on {dayjs(ticket.created_at).format('DD MMMM YYYY HH:mm')}
            </p>
            {ticket.customer && (
              <p className="text-gray-600 dark:text-gray-400">
                Customer: {ticket.customer.name} ({ticket.customer.email})
              </p>
            )}
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
          <div className="lg:col-span-2">
            <Card className="bg-white dark:bg-gray-800 shadow-md">
              <CardHeader>
                <CardTitle>Conversation</CardTitle>
              </CardHeader>
              <CardContent className="space-y-6">
                {ticket.replies.map((reply) => (
                  <div key={reply.id} className="border-b pb-4 last:border-b-0 last:pb-0">
                    <div className="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400 mb-2">
                      <div className="flex items-center gap-2">
                        {reply.user ? (
                          <>
                            <span className="font-semibold text-primary">Agent: {reply.user.name}</span>
                            <Badge variant="outline" className="text-xs">Staff</Badge>
                          </>
                        ) : (
                          <>
                            <span>Customer: {reply.customer?.name || 'N/A'}</span>
                            <Badge variant="outline" className="text-xs">Customer</Badge>
                          </>
                        )}
                      </div>
                      <span>{dayjs(reply.created_at).format('DD MMM YYYY HH:mm')}</span>
                    </div>
                    <div className="mt-2 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                      <p className="text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{reply.message}</p>
                    </div>
                  </div>
                ))}

                <Separator />

                <form onSubmit={handleReply} className="space-y-4">
                  <div>
                    <Label htmlFor="message">Your Reply</Label>
                    <Textarea
                      id="message"
                      value={replyData.message}
                      onChange={(e) => setReplyData('message', e.target.value)}
                      className="mt-1"
                      rows={4}
                      placeholder="Type your reply here..."
                    />
                    <InputError message={errors.message} className="mt-2" />
                  </div>
                  <Button type="submit" disabled={replyProcessing}>
                    {replyProcessing ? 'Sending...' : 'Send Reply'}
                  </Button>
                </form>
              </CardContent>
            </Card>
          </div>

          <div className="space-y-6">
            <Card className="bg-white dark:bg-gray-800 shadow-md">
              <CardHeader>
                <CardTitle>Ticket Details</CardTitle>
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
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Created</span>
                  <span>{dayjs(ticket.created_at).format('DD MMM YYYY HH:mm')}</span>
                </div>
                {ticket.customer && (
                  <>
                    <Separator />
                    <div>
                      <span className="text-gray-600 dark:text-gray-400 block mb-1">Customer</span>
                      <p className="font-semibold">{ticket.customer.name}</p>
                      <p className="text-sm text-gray-600 dark:text-gray-400">{ticket.customer.email}</p>
                    </div>
                  </>
                )}
              </CardContent>
            </Card>

            <Card className="bg-white dark:bg-gray-800 shadow-md">
              <CardHeader>
                <CardTitle>Update Ticket</CardTitle>
              </CardHeader>
              <CardContent>
                <form onSubmit={handleStatusUpdate} className="space-y-4">
                  <div>
                    <Label htmlFor="status">Status</Label>
                    <Select value={statusData.status} onValueChange={(value) => setStatusData('status', value)}>
                      <SelectTrigger className="mt-1">
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="open">Open</SelectItem>
                        <SelectItem value="pending">Pending</SelectItem>
                        <SelectItem value="solved">Solved</SelectItem>
                        <SelectItem value="closed">Closed</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div>
                    <Label htmlFor="priority">Priority</Label>
                    <Select value={statusData.priority} onValueChange={(value) => setStatusData('priority', value)}>
                      <SelectTrigger className="mt-1">
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="low">Low</SelectItem>
                        <SelectItem value="normal">Normal</SelectItem>
                        <SelectItem value="high">High</SelectItem>
                        <SelectItem value="urgent">Urgent</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <Button type="submit" disabled={statusProcessing} className="w-full">
                    {statusProcessing ? 'Updating...' : 'Update Ticket'}
                  </Button>
                </form>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}




