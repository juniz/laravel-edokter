import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';

interface Plan {
  id: string;
  code: string;
  billing_cycle: string;
  price_cents: number;
  currency: string;
  trial_days?: number;
  setup_fee_cents: number;
}

interface Product {
  id: string;
  name: string;
  slug: string;
  type: string;
  status: string;
  metadata?: {
    description?: string;
    features?: string[];
  };
  plans?: Plan[];
}

interface CatalogShowProps {
  product: Product;
  plans: Plan[];
}

export default function CatalogShow({ product, plans }: CatalogShowProps) {
  const { post, processing } = useForm({
    product_id: product.id,
    plan_id: '',
  });

  const formatPrice = (cents: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(cents);
  };

  const handleAddToCart = (planId: string) => {
    post(route('cart.add'), {
      data: {
        ...post.data,
        plan_id: planId,
      },
    });
  };

  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Catalog', href: '/catalog' },
    { title: product.name, href: route('catalog.show', product.slug) },
  ];

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={product.name} />
      <div className="flex flex-col gap-6 p-4">
        <div>
          <h1 className="text-3xl font-bold text-gray-900 dark:text-white">{product.name}</h1>
          {product.metadata?.description && (
            <p className="text-gray-600 dark:text-gray-400 mt-2">{product.metadata.description}</p>
          )}
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Product Info */}
          <div className="lg:col-span-2">
            <Card className="bg-white dark:bg-gray-800 shadow-md">
              <CardHeader>
                <CardTitle>Product Details</CardTitle>
              </CardHeader>
              <CardContent>
                {product.metadata?.features && (
                  <div>
                    <h3 className="font-semibold mb-3">Features:</h3>
                    <ul className="space-y-2">
                      {product.metadata.features.map((feature, idx) => (
                        <li key={idx} className="flex items-center text-sm">
                          <span className="mr-2 text-green-500">✓</span>
                          {feature}
                        </li>
                      ))}
                    </ul>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>

          {/* Plans */}
          <div className="space-y-4">
            <h2 className="text-xl font-semibold">Pilih Paket</h2>
            {plans.map((plan) => (
              <Card key={plan.id} className="bg-white dark:bg-gray-800 shadow-md hover:shadow-lg transition-shadow">
                <CardHeader>
                  <CardTitle className="text-lg">{plan.code}</CardTitle>
                  <CardDescription>
                    Billing Cycle: {plan.billing_cycle}
                    {plan.trial_days && ` • ${plan.trial_days} days trial`}
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="mb-4">
                    <div className="text-3xl font-bold">
                      {formatPrice(plan.price_cents)}
                    </div>
                    {plan.setup_fee_cents > 0 && (
                      <div className="text-sm text-gray-600 dark:text-gray-400">
                        Setup Fee: {formatPrice(plan.setup_fee_cents)}
                      </div>
                    )}
                  </div>
                  <Button
                    className="w-full"
                    onClick={() => handleAddToCart(plan.id)}
                    disabled={processing}
                  >
                    Pilih Paket
                  </Button>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </div>
    </AppLayout>
  );
}

