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

interface Plan {
  id: string;
  code: string;
  billing_cycle: string;
  price_cents: number;
  currency: string;
  setup_fee_cents: number;
  trial_days?: number;
}

interface Product {
  id: string;
  name: string;
  slug: string;
  type: string;
  status: string;
  metadata?: any;
  created_at: string;
  updated_at: string;
  plans?: Plan[];
}

interface ProductShowProps {
  product: Product;
}

export default function ProductShow({ product }: ProductShowProps) {
  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Products', href: '/admin/products' },
    { title: product.name, href: route('admin.products.show', product.id) },
  ];

  const getStatusBadge = (status: string) => {
    const colors: Record<string, string> = {
      active: 'bg-green-500',
      draft: 'bg-yellow-500',
      archived: 'bg-gray-500',
    };
    return colors[status] || 'bg-gray-500';
  };

  const getTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
      hosting_shared: 'Shared Hosting',
      vps: 'VPS',
      addon: 'Addon',
      domain: 'Domain',
    };
    return labels[type] || type;
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
      <Head title={product.name} />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">{product.name}</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">/{product.slug}</p>
          </div>
          <div className="flex gap-2">
            <Badge className={getStatusBadge(product.status)}>
              {product.status.toUpperCase()}
            </Badge>
            <Link href={route('admin.products.edit', product.id)}>
              <Button variant="outline">
                <Edit className="w-4 h-4 mr-2" />
                Edit
              </Button>
            </Link>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-2">
            <Card className="bg-white dark:bg-gray-800 shadow-md">
              <CardHeader>
                <CardTitle>Product Details</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Type</span>
                  <span className="font-semibold">{getTypeLabel(product.type)}</span>
                </div>
                <Separator />
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Status</span>
                  <Badge className={getStatusBadge(product.status)}>
                    {product.status}
                  </Badge>
                </div>
                <Separator />
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Created</span>
                  <span>{dayjs(product.created_at).format('DD MMM YYYY HH:mm')}</span>
                </div>
                <Separator />
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Updated</span>
                  <span>{dayjs(product.updated_at).format('DD MMM YYYY HH:mm')}</span>
                </div>
              </CardContent>
            </Card>

            {product.plans && product.plans.length > 0 && (
              <Card className="bg-white dark:bg-gray-800 shadow-md mt-6">
                <CardHeader>
                  <CardTitle>Plans ({product.plans.length})</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-4">
                    {product.plans.map((plan) => (
                      <div key={plan.id} className="flex justify-between items-center p-4 border rounded-lg">
                        <div>
                          <h4 className="font-semibold">{plan.code}</h4>
                          <p className="text-sm text-gray-600 dark:text-gray-400">
                            {plan.billing_cycle}
                            {plan.trial_days && ` • ${plan.trial_days} days trial`}
                          </p>
                        </div>
                        <div className="text-right">
                          <p className="font-semibold">{formatPrice(plan.price_cents, plan.currency)}</p>
                          {plan.setup_fee_cents > 0 && (
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                              Setup: {formatPrice(plan.setup_fee_cents, plan.currency)}
                            </p>
                          )}
                        </div>
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
                <Link href={route('admin.products.edit', product.id)} className="block">
                  <Button variant="outline" className="w-full">
                    <Edit className="w-4 h-4 mr-2" />
                    Edit Product
                  </Button>
                </Link>
                <Link href={route('admin.plans.index', { product_id: product.id })} className="block">
                  <Button variant="outline" className="w-full">
                    <Package className="w-4 h-4 mr-2" />
                    Manage Plans
                  </Button>
                </Link>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}

