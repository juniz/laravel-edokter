import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem } from '@/types';
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
import { List, Plus, Edit, Trash2, Eye } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Plans', href: '/admin/plans' },
];

interface Plan {
  id: string;
  code: string;
  billing_cycle: string;
  price_cents: number;
  currency: string;
  setup_fee_cents: number;
  trial_days?: number;
  product?: {
    name: string;
  };
}

interface PlansProps {
  plans: {
    data: Plan[];
    links: any;
    meta: any;
  };
  products?: Array<{ id: string; name: string }>;
  filters?: {
    product_id?: string;
  };
}

export default function PlansIndex({ plans, products, filters }: PlansProps) {
  const handleDelete = (id: string) => {
    router.delete(`/admin/plans/${id}`);
  };

  const formatPrice = (cents: number, currency: string = 'IDR') => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: currency,
      minimumFractionDigits: 0,
    }).format(cents);
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Plans" />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">Plans</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">Kelola paket hosting</p>
          </div>
          <Link href={route('admin.plans.create')}>
            <Button>
              <Plus className="w-4 h-4 mr-2" />
              Add Plan
            </Button>
          </Link>
        </div>

        {products && products.length > 0 && (
          <Card className="bg-white dark:bg-gray-800 shadow-md">
            <CardContent className="p-4">
              <div className="flex gap-2 flex-wrap">
                <Link href={route('admin.plans.index')}>
                  <Button variant={!filters?.product_id ? 'default' : 'outline'} size="sm">
                    All Products
                  </Button>
                </Link>
                {products.map((product) => (
                  <Link key={product.id} href={route('admin.plans.index', { product_id: product.id })}>
                    <Button variant={filters?.product_id === product.id ? 'default' : 'outline'} size="sm">
                      {product.name}
                    </Button>
                  </Link>
                ))}
              </div>
            </CardContent>
          </Card>
        )}

        {plans.data.length === 0 ? (
          <Card className="bg-white dark:bg-gray-800 shadow-md">
            <CardContent className="p-8 text-center">
              <List className="w-12 h-12 mx-auto mb-4 text-gray-400" />
              <p className="text-gray-600 dark:text-gray-400">Belum ada plan.</p>
              <Link href={route('admin.plans.create')}>
                <Button className="mt-4">Create Plan</Button>
              </Link>
            </CardContent>
          </Card>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {plans.data.map((plan) => (
              <Card key={plan.id} className="bg-white dark:bg-gray-800 shadow-md hover:shadow-lg transition-shadow">
                <CardContent className="p-6">
                  <div className="mb-4">
                    <h3 className="text-lg font-semibold mb-1">{plan.code}</h3>
                    {plan.product && (
                      <p className="text-sm text-gray-600 dark:text-gray-400">{plan.product.name}</p>
                    )}
                  </div>
                  
                  <div className="space-y-2 mb-4">
                    <div className="flex items-center justify-between text-sm">
                      <span className="text-gray-600 dark:text-gray-400">Price:</span>
                      <span className="font-semibold text-lg">{formatPrice(plan.price_cents, plan.currency)}</span>
                    </div>
                    <div className="flex items-center justify-between text-sm">
                      <span className="text-gray-600 dark:text-gray-400">Cycle:</span>
                      <span className="font-medium">{plan.billing_cycle}</span>
                    </div>
                    {plan.setup_fee_cents > 0 && (
                      <div className="flex items-center justify-between text-sm">
                        <span className="text-gray-600 dark:text-gray-400">Setup Fee:</span>
                        <span>{formatPrice(plan.setup_fee_cents, plan.currency)}</span>
                      </div>
                    )}
                    {plan.trial_days && (
                      <div className="flex items-center justify-between text-sm">
                        <span className="text-gray-600 dark:text-gray-400">Trial:</span>
                        <span>{plan.trial_days} days</span>
                      </div>
                    )}
                  </div>

                  <div className="flex gap-2 pt-4 border-t">
                    <Link href={route('admin.plans.show', plan.id)} className="flex-1">
                      <Button variant="outline" className="w-full" size="sm">
                        <Eye className="w-4 h-4 mr-1" />
                        View
                      </Button>
                    </Link>
                    <Link href={route('admin.plans.edit', plan.id)} className="flex-1">
                      <Button variant="outline" className="w-full" size="sm">
                        <Edit className="w-4 h-4 mr-1" />
                        Edit
                      </Button>
                    </Link>
                    <AlertDialog>
                      <AlertDialogTrigger asChild>
                        <Button variant="outline" size="sm" className="text-red-600 hover:text-red-700">
                          <Trash2 className="w-4 h-4" />
                        </Button>
                      </AlertDialogTrigger>
                      <AlertDialogContent>
                        <AlertDialogHeader>
                          <AlertDialogTitle>Delete Plan?</AlertDialogTitle>
                          <AlertDialogDescription>
                            Are you sure you want to delete "{plan.code}"? This action cannot be undone.
                          </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                          <AlertDialogCancel>Cancel</AlertDialogCancel>
                          <AlertDialogAction
                            onClick={() => handleDelete(plan.id)}
                            className="bg-red-600 hover:bg-red-700"
                          >
                            Delete
                          </AlertDialogAction>
                        </AlertDialogFooter>
                      </AlertDialogContent>
                    </AlertDialog>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}

        {/* Pagination */}
        {plans.links && plans.links.length > 3 && (
          <div className="flex justify-center gap-2">
            {plans.links.map((link: any, index: number) => (
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

