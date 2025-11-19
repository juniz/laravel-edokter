import React, { FormEventHandler } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/input-error';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Support Tickets', href: '/customer/tickets' },
  { title: 'Create Ticket', href: '#' },
];

export default function TicketForm() {
  const { data, setData, post, processing, errors } = useForm({
    subject: '',
    priority: 'normal',
    message: '',
  });

  const submit: FormEventHandler = (e) => {
    e.preventDefault();
    post(route('customer.tickets.store'));
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Create Ticket" />
      <div className="flex flex-col gap-6 p-4">
        <div>
          <h1 className="text-3xl font-bold text-gray-900 dark:text-white">Create Support Ticket</h1>
          <p className="text-gray-600 dark:text-gray-400 mt-2">Buat tiket baru untuk mendapatkan bantuan</p>
        </div>

        <Card className="bg-white dark:bg-gray-800 shadow-md">
          <CardHeader>
            <CardTitle>Ticket Information</CardTitle>
          </CardHeader>
          <CardContent>
            <form onSubmit={submit} className="space-y-4">
              <div>
                <Label htmlFor="subject">Subject</Label>
                <Input
                  id="subject"
                  value={data.subject}
                  onChange={(e) => setData('subject', e.target.value)}
                  className="mt-1"
                  placeholder="Brief description of your issue"
                />
                <InputError message={errors.subject} className="mt-2" />
              </div>

              <div>
                <Label htmlFor="priority">Priority</Label>
                <Select value={data.priority} onValueChange={(value) => setData('priority', value)}>
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
                <InputError message={errors.priority} className="mt-2" />
              </div>

              <div>
                <Label htmlFor="message">Message</Label>
                <Textarea
                  id="message"
                  value={data.message}
                  onChange={(e) => setData('message', e.target.value)}
                  className="mt-1"
                  rows={6}
                  placeholder="Describe your issue in detail..."
                />
                <InputError message={errors.message} className="mt-2" />
              </div>

              <div className="flex gap-2">
                <Button type="submit" disabled={processing}>
                  {processing ? 'Creating...' : 'Create Ticket'}
                </Button>
                <Button type="button" variant="outline" onClick={() => window.history.back()}>
                  Cancel
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}

