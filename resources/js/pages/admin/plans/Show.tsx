import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Edit, Package } from 'lucide-react';
import dayjs from 'dayjs';

interface PlanFeature {
  id: string;
  key: string;
  value: string;
}

interface Plan {
  id: string;
  code: string;
  billing_cycle: string;
  price_cents: number;
  currency: string;
  setup_fee_cents: number;
  trial_days?: number;
  created_at: string;
  updated_at: string;
  product?: {
    name: string;
  };
  features?: PlanFeature[];
}

interface PlanShowProps {
  plan: Plan;
}

export default function PlanShow({ plan }: PlanShowProps) {
  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Plans', href: '/admin/plans' },
    { title: plan.code, href: route('admin.plans.show', plan.id) },
  ];

  const formatPrice = (cents: number, currency: string = 'IDR') => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: currency,
      minimumFractionDigits: 0,
    }).format(cents);
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={plan.code} />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">{plan.code}</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">
              {plan.product?.name}
            </p>
          </div>
          <Link href={route('admin.plans.edit', plan.id)}>
            <Button variant="outline">
              <Edit className="w-4 h-4 mr-2" />
              Edit
            </Button>
          </Link>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-2">
            <Card className="bg-white dark:bg-gray-800 shadow-md">
              <CardHeader>
                <CardTitle>Plan Details</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Product</span>
                  <span className="font-semibold">{plan.product?.name}</span>
                </div>
                <Separator />
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Billing Cycle</span>
                  <span className="font-semibold">{plan.billing_cycle}</span>
                </div>
                <Separator />
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Price</span>
                  <span className="font-semibold text-lg">{formatPrice(plan.price_cents, plan.currency)}</span>
                </div>
                <Separator />
                {plan.setup_fee_cents > 0 && (
                  <>
                    <div className="flex justify-between">
                      <span className="text-gray-600 dark:text-gray-400">Setup Fee</span>
                      <span>{formatPrice(plan.setup_fee_cents, plan.currency)}</span>
                    </div>
                    <Separator />
                  </>
                )}
                {plan.trial_days && (
                  <>
                    <div className="flex justify-between">
                      <span className="text-gray-600 dark:text-gray-400">Trial Days</span>
                      <span>{plan.trial_days} days</span>
                    </div>
                    <Separator />
                  </>
                )}
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Created</span>
                  <span>{dayjs(plan.created_at).format('DD MMM YYYY HH:mm')}</span>
                </div>
                <Separator />
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Updated</span>
                  <span>{dayjs(plan.updated_at).format('DD MMM YYYY HH:mm')}</span>
                </div>
              </CardContent>
            </Card>

            {plan.features && plan.features.length > 0 && (
              <Card className="bg-white dark:bg-gray-800 shadow-md mt-6">
                <CardHeader>
                  <CardTitle>Plan Features</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {plan.features.map((feature) => (
                      <div key={feature.id} className="p-3 border rounded-lg">
                        <div className="font-semibold capitalize mb-1">{feature.key.replace('_', ' ')}</div>
                        <div className="text-sm text-gray-600 dark:text-gray-400">{feature.value}</div>
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
                <Link href={route('admin.plans.edit', plan.id)} className="block">
                  <Button variant="outline" className="w-full">
                    <Edit className="w-4 h-4 mr-2" />
                    Edit Plan
                  </Button>
                </Link>
                {plan.product && (
                  <Link href={route('admin.products.show', plan.product.id)} className="block">
                    <Button variant="outline" className="w-full">
                      <Package className="w-4 h-4 mr-2" />
                      View Product
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

