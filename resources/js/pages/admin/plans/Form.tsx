import React, { FormEventHandler } from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/input-error';

interface Product {
  id: string;
  name: string;
}

interface Plan {
  id?: string;
  product_id: string;
  code: string;
  billing_cycle: string;
  price_cents: number;
  currency: string;
  trial_days?: number;
  setup_fee_cents: number;
}

interface PlanFormProps {
  plan?: Plan;
  products: Product[];
}

export default function PlanForm({ plan, products }: PlanFormProps) {
  const isEdit = !!plan;

  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Plans', href: '/admin/plans' },
    { title: isEdit ? 'Edit Plan' : 'Create Plan', href: '#' },
  ];

  const { data, setData, post, put, processing, errors } = useForm({
    product_id: plan?.product_id || '',
    code: plan?.code || '',
    billing_cycle: plan?.billing_cycle || 'monthly',
    price_cents: plan?.price_cents || 0,
    currency: plan?.currency || 'IDR',
    trial_days: plan?.trial_days || null,
    setup_fee_cents: plan?.setup_fee_cents || 0,
  });

  const submit: FormEventHandler = (e) => {
    e.preventDefault();
    if (isEdit && plan?.id) {
      put(route('admin.plans.update', plan.id));
    } else {
      post(route('admin.plans.store'));
    }
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={isEdit ? 'Edit Plan' : 'Create Plan'} />
      <div className="flex flex-col gap-6 p-4">
        <div>
          <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
            {isEdit ? 'Edit Plan' : 'Create Plan'}
          </h1>
          <p className="text-gray-600 dark:text-gray-400 mt-2">
            {isEdit ? 'Update plan information' : 'Add a new plan to a product'}
          </p>
        </div>

        <Card className="bg-white dark:bg-gray-800 shadow-md">
          <CardHeader>
            <CardTitle>Plan Information</CardTitle>
          </CardHeader>
          <CardContent>
            <form onSubmit={submit} className="space-y-4">
              <div>
                <Label htmlFor="product_id">Product</Label>
                <Select value={data.product_id} onValueChange={(value) => setData('product_id', value)}>
                  <SelectTrigger className="mt-1">
                    <SelectValue placeholder="Select a product" />
                  </SelectTrigger>
                  <SelectContent>
                    {products.map((product) => (
                      <SelectItem key={product.id} value={product.id}>
                        {product.name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                <InputError message={errors.product_id} className="mt-2" />
              </div>

              <div>
                <Label htmlFor="code">Plan Code</Label>
                <Input
                  id="code"
                  value={data.code}
                  onChange={(e) => setData('code', e.target.value)}
                  className="mt-1"
                  placeholder="e.g., BASIC-1Y"
                />
                <InputError message={errors.code} className="mt-2" />
              </div>

              <div>
                <Label htmlFor="billing_cycle">Billing Cycle</Label>
                <Select value={data.billing_cycle} onValueChange={(value) => setData('billing_cycle', value)}>
                  <SelectTrigger className="mt-1">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="monthly">Monthly</SelectItem>
                    <SelectItem value="quarterly">Quarterly</SelectItem>
                    <SelectItem value="semiannually">Semiannually</SelectItem>
                    <SelectItem value="annually">Annually</SelectItem>
                    <SelectItem value="biennially">Biennially</SelectItem>
                    <SelectItem value="triennially">Triennially</SelectItem>
                  </SelectContent>
                </Select>
                <InputError message={errors.billing_cycle} className="mt-2" />
              </div>

              <div>
                <Label htmlFor="price_cents">Price (in cents)</Label>
                <Input
                  id="price_cents"
                  type="number"
                  value={data.price_cents}
                  onChange={(e) => setData('price_cents', parseInt(e.target.value) || 0)}
                  className="mt-1"
                  placeholder="500000"
                />
                <InputError message={errors.price_cents} className="mt-2" />
                <p className="text-sm text-gray-500 mt-1">
                  Example: 500000 = Rp 5.000.000
                </p>
              </div>

              <div>
                <Label htmlFor="currency">Currency</Label>
                <Input
                  id="currency"
                  value={data.currency}
                  onChange={(e) => setData('currency', e.target.value.toUpperCase())}
                  className="mt-1"
                  placeholder="IDR"
                  maxLength={3}
                />
                <InputError message={errors.currency} className="mt-2" />
              </div>

              <div>
                <Label htmlFor="setup_fee_cents">Setup Fee (in cents)</Label>
                <Input
                  id="setup_fee_cents"
                  type="number"
                  value={data.setup_fee_cents}
                  onChange={(e) => setData('setup_fee_cents', parseInt(e.target.value) || 0)}
                  className="mt-1"
                  placeholder="0"
                />
                <InputError message={errors.setup_fee_cents} className="mt-2" />
              </div>

              <div>
                <Label htmlFor="trial_days">Trial Days (optional)</Label>
                <Input
                  id="trial_days"
                  type="number"
                  value={data.trial_days || ''}
                  onChange={(e) => setData('trial_days', e.target.value ? parseInt(e.target.value) : null)}
                  className="mt-1"
                  placeholder="7"
                />
                <InputError message={errors.trial_days} className="mt-2" />
              </div>

              <div className="flex gap-2">
                <Button type="submit" disabled={processing}>
                  {processing ? 'Saving...' : isEdit ? 'Update Plan' : 'Create Plan'}
                </Button>
                <Link href={route('admin.plans.index')}>
                  <Button type="button" variant="outline">Cancel</Button>
                </Link>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}

