import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
  AlertDialog,
  AlertDialogTrigger,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogFooter,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogCancel,
  AlertDialogAction,
} from '@/components/ui/alert-dialog';
import dayjs from 'dayjs';

interface Subscription {
  id: string;
  status: string;
  product: {
    name: string;
  };
  plan: {
    code: string;
    billing_cycle: string;
  } | null;
  start_at: string;
  end_at?: string;
  next_renewal_at?: string;
  auto_renew: boolean;
}

interface SubscriptionShowProps {
  subscription: Subscription;
}

export default function SubscriptionShow({ subscription }: SubscriptionShowProps) {
  const { post, processing } = useForm();

  const handleCancel = () => {
    post(route('customer.subscriptions.cancel', subscription.id));
  };

  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Subscriptions', href: '/customer/subscriptions' },
    { title: subscription.product.name, href: route('customer.subscriptions.show', subscription.id) },
  ];

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={subscription.product.name} />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold">{subscription.product.name}</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">
              Plan: {subscription.plan?.code ?? 'Default'}
            </p>
          </div>
          <Badge className={subscription.status === 'active' ? 'bg-green-500' : 'bg-yellow-500'}>
            {subscription.status.toUpperCase()}
          </Badge>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <Card className="bg-white dark:bg-gray-800 shadow-md">
            <CardHeader>
              <CardTitle>Subscription Details</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              <div className="flex justify-between">
                <span className="text-gray-600 dark:text-gray-400">Status</span>
                <Badge>{subscription.status}</Badge>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600 dark:text-gray-400">Plan</span>
                <span className="font-semibold">{subscription.plan?.code ?? 'Default'}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600 dark:text-gray-400">Billing Cycle</span>
                <span>{subscription.plan?.billing_cycle ?? '-'}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600 dark:text-gray-400">Started</span>
                <span>{dayjs(subscription.start_at).format('DD MMM YYYY')}</span>
              </div>
              {subscription.end_at && (
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Ends</span>
                  <span>{dayjs(subscription.end_at).format('DD MMM YYYY')}</span>
                </div>
              )}
              {subscription.next_renewal_at && (
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Next Renewal</span>
                  <span>{dayjs(subscription.next_renewal_at).format('DD MMM YYYY')}</span>
                </div>
              )}
              <div className="flex justify-between">
                <span className="text-gray-600 dark:text-gray-400">Auto Renew</span>
                <Badge variant={subscription.auto_renew ? 'default' : 'outline'}>
                  {subscription.auto_renew ? 'Yes' : 'No'}
                </Badge>
              </div>
            </CardContent>
          </Card>

          <Card className="bg-white dark:bg-gray-800 shadow-md">
            <CardHeader>
              <CardTitle>Actions</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              {subscription.status === 'active' && (
                <AlertDialog>
                  <AlertDialogTrigger asChild>
                    <Button variant="destructive" className="w-full">Cancel Subscription</Button>
                  </AlertDialogTrigger>
                  <AlertDialogContent>
                    <AlertDialogHeader>
                      <AlertDialogTitle>Cancel Subscription?</AlertDialogTitle>
                      <AlertDialogDescription>
                        Apakah Anda yakin ingin membatalkan subscription ini? 
                        Subscription akan tetap aktif hingga tanggal akhir periode.
                      </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                      <AlertDialogCancel>Cancel</AlertDialogCancel>
                      <AlertDialogAction
                        onClick={handleCancel}
                        disabled={processing}
                      >
                        Yes, Cancel
                      </AlertDialogAction>
                    </AlertDialogFooter>
                  </AlertDialogContent>
                </AlertDialog>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </AppLayout>
  );
}
