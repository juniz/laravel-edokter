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
  id?: string;
  name: string;
  slug: string;
  type: string;
  status: string;
  metadata?: any;
}

interface ProductFormProps {
  product?: Product;
}

export default function ProductForm({ product }: ProductFormProps) {
  const isEdit = !!product;

  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Products', href: '/admin/products' },
    { title: isEdit ? 'Edit Product' : 'Create Product', href: '#' },
  ];

  const { data, setData, post, put, processing, errors } = useForm({
    name: product?.name || '',
    slug: product?.slug || '',
    type: product?.type || 'hosting_shared',
    status: product?.status || 'draft',
    metadata: product?.metadata || {},
  });

  const submit: FormEventHandler = (e) => {
    e.preventDefault();
    if (isEdit && product?.id) {
      put(route('admin.products.update', product.id));
    } else {
      post(route('admin.products.store'));
    }
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={isEdit ? 'Edit Product' : 'Create Product'} />
      <div className="flex flex-col gap-6 p-4">
        <div>
          <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
            {isEdit ? 'Edit Product' : 'Create Product'}
          </h1>
          <p className="text-gray-600 dark:text-gray-400 mt-2">
            {isEdit ? 'Update product information' : 'Add a new product to the catalog'}
          </p>
        </div>

        <Card className="bg-white dark:bg-gray-800 shadow-md">
          <CardHeader>
            <CardTitle>Product Information</CardTitle>
          </CardHeader>
          <CardContent>
            <form onSubmit={submit} className="space-y-4">
              <div>
                <Label htmlFor="name">Product Name</Label>
                <Input
                  id="name"
                  value={data.name}
                  onChange={(e) => setData('name', e.target.value)}
                  className="mt-1"
                  placeholder="e.g., Shared Hosting"
                />
                <InputError message={errors.name} className="mt-2" />
              </div>

              <div>
                <Label htmlFor="slug">Slug</Label>
                <Input
                  id="slug"
                  value={data.slug}
                  onChange={(e) => setData('slug', e.target.value)}
                  className="mt-1"
                  placeholder="e.g., shared-hosting"
                />
                <InputError message={errors.slug} className="mt-2" />
              </div>

              <div>
                <Label htmlFor="type">Type</Label>
                <Select value={data.type} onValueChange={(value) => setData('type', value)}>
                  <SelectTrigger className="mt-1">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="hosting_shared">Shared Hosting</SelectItem>
                    <SelectItem value="vps">VPS</SelectItem>
                    <SelectItem value="addon">Addon</SelectItem>
                    <SelectItem value="domain">Domain</SelectItem>
                  </SelectContent>
                </Select>
                <InputError message={errors.type} className="mt-2" />
              </div>

              <div>
                <Label htmlFor="status">Status</Label>
                <Select value={data.status} onValueChange={(value) => setData('status', value)}>
                  <SelectTrigger className="mt-1">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="active">Active</SelectItem>
                    <SelectItem value="draft">Draft</SelectItem>
                    <SelectItem value="archived">Archived</SelectItem>
                  </SelectContent>
                </Select>
                <InputError message={errors.status} className="mt-2" />
              </div>

              <div className="flex gap-2">
                <Button type="submit" disabled={processing}>
                  {processing ? 'Saving...' : isEdit ? 'Update Product' : 'Create Product'}
                </Button>
                <Link href={route('admin.products.index')}>
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

