import React, { FormEventHandler, useState } from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/input-error';
import { Plus, Trash2 } from 'lucide-react';

interface ProductFeature {
  id?: string;
  key: string;
  value: string;
  label?: string;
  unit?: string;
  display_order?: number;
}

interface Product {
  id?: string;
  name: string;
  slug: string;
  type: string;
  status: string;
  metadata?: any;
  features?: ProductFeature[];
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

  const [features, setFeatures] = useState<ProductFeature[]>(
    product?.features && product.features.length > 0
      ? product.features.map((f) => ({
          key: f.key,
          value: f.value,
          label: f.label || '',
          unit: f.unit || '',
          display_order: f.display_order || 0,
        }))
      : []
  );

  const [metadataFeatures, setMetadataFeatures] = useState<string[]>(
    product?.metadata?.features && Array.isArray(product.metadata.features)
      ? product.metadata.features
      : []
  );

  const { data, setData, post, put, processing, errors } = useForm({
    name: product?.name || '',
    slug: product?.slug || '',
    type: product?.type || 'hosting_shared',
    status: product?.status || 'draft',
    metadata: {
      description: product?.metadata?.description || '',
      features: metadataFeatures,
      popular: product?.metadata?.popular || false,
      starting_price: product?.metadata?.starting_price || null,
    },
    features: features,
  });

  const updateMetadata = (field: string, value: any) => {
    setData('metadata', {
      ...data.metadata,
      [field]: value,
    });
  };

  const addMetadataFeature = () => {
    const updatedFeatures = [...metadataFeatures, ''];
    setMetadataFeatures(updatedFeatures);
    updateMetadata('features', updatedFeatures);
  };

  const removeMetadataFeature = (index: number) => {
    const updatedFeatures = metadataFeatures.filter((_, i) => i !== index);
    setMetadataFeatures(updatedFeatures);
    updateMetadata('features', updatedFeatures);
  };

  const updateMetadataFeature = (index: number, value: string) => {
    const updatedFeatures = metadataFeatures.map((f, i) => (i === index ? value : f));
    setMetadataFeatures(updatedFeatures);
    updateMetadata('features', updatedFeatures);
  };

  const addFeature = () => {
    const newFeature: ProductFeature = {
      key: '',
      value: '',
      label: '',
      unit: '',
      display_order: features.length,
    };
    const updatedFeatures = [...features, newFeature];
    setFeatures(updatedFeatures);
    setData('features', updatedFeatures);
  };

  const removeFeature = (index: number) => {
    const updatedFeatures = features.filter((_, i) => i !== index);
    setFeatures(updatedFeatures);
    setData('features', updatedFeatures);
  };

  const updateFeature = (index: number, field: keyof ProductFeature, value: string | number) => {
    const updatedFeatures = features.map((f, i) =>
      i === index ? { ...f, [field]: value } : f
    );
    setFeatures(updatedFeatures);
    setData('features', updatedFeatures);
  };

  const submit: FormEventHandler = (e) => {
    e.preventDefault();
    
    // Clean up metadata - remove empty fields before submit
    const cleanedMetadata: any = {};
    if (data.metadata?.description && data.metadata.description.trim()) {
      cleanedMetadata.description = data.metadata.description.trim();
    }
    if (data.metadata?.features && Array.isArray(data.metadata.features) && data.metadata.features.length > 0) {
      // Filter out empty features
      const nonEmptyFeatures = data.metadata.features.filter((f: string) => f && f.trim());
      if (nonEmptyFeatures.length > 0) {
        cleanedMetadata.features = nonEmptyFeatures;
      }
    }
    if (data.metadata?.popular) {
      cleanedMetadata.popular = true;
    }
    if (data.metadata?.starting_price && data.metadata.starting_price > 0) {
      cleanedMetadata.starting_price = parseInt(String(data.metadata.starting_price));
    }

    // Update metadata in form data
    setData('metadata', Object.keys(cleanedMetadata).length > 0 ? cleanedMetadata : null);

    // Submit using transform to ensure cleaned metadata is sent
    if (isEdit && product?.id) {
      put(route('admin.products.update', product.id), {
        transform: (data) => ({
          ...data,
          metadata: Object.keys(cleanedMetadata).length > 0 ? cleanedMetadata : null,
        }),
      });
    } else {
      post(route('admin.products.store'), {
        transform: (data) => ({
          ...data,
          metadata: Object.keys(cleanedMetadata).length > 0 ? cleanedMetadata : null,
        }),
      });
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

              {/* Metadata Section */}
              <div className="pt-4 border-t">
                <div className="mb-4">
                  <Label className="text-base font-semibold">Metadata</Label>
                  <p className="text-sm text-muted-foreground mt-1">
                    Informasi tambahan untuk produk (deskripsi, fitur deskriptif, dll)
                  </p>
                </div>

                <div className="space-y-4">
                  {/* Description */}
                  <div>
                    <Label htmlFor="metadata_description">Description</Label>
                    <Textarea
                      id="metadata_description"
                      value={data.metadata?.description || ''}
                      onChange={(e) => updateMetadata('description', e.target.value)}
                      className="mt-1"
                      placeholder="Deskripsi produk yang akan ditampilkan di halaman catalog"
                      rows={4}
                    />
                    <InputError message={errors['metadata.description']} className="mt-2" />
                  </div>

                  {/* Popular Checkbox */}
                  <div className="flex items-center space-x-2">
                    <Checkbox
                      id="metadata_popular"
                      checked={data.metadata?.popular || false}
                      onCheckedChange={(checked) => updateMetadata('popular', checked)}
                    />
                    <Label htmlFor="metadata_popular" className="cursor-pointer">
                      Tandai sebagai produk populer
                    </Label>
                  </div>

                  {/* Starting Price */}
                  <div>
                    <Label htmlFor="metadata_starting_price">Starting Price (Cents)</Label>
                    <Input
                      id="metadata_starting_price"
                      type="number"
                      value={data.metadata?.starting_price || ''}
                      onChange={(e) => updateMetadata('starting_price', e.target.value ? parseInt(e.target.value) : null)}
                      className="mt-1"
                      placeholder="e.g., 50000 (untuk Rp 50.000)"
                      min="0"
                    />
                    <p className="text-xs text-muted-foreground mt-1">
                      Harga mulai yang ditampilkan di catalog (dalam cents)
                    </p>
                    <InputError message={errors['metadata.starting_price']} className="mt-2" />
                  </div>

                  {/* Metadata Features (descriptive features) */}
                  <div>
                    <div className="flex items-center justify-between mb-2">
                      <Label className="text-sm font-semibold">Fitur Deskriptif</Label>
                      <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={addMetadataFeature}
                      >
                        <Plus className="h-4 w-4 mr-2" />
                        Tambah Fitur
                      </Button>
                    </div>
                    <p className="text-xs text-muted-foreground mb-3">
                      Fitur-fitur yang akan ditampilkan sebagai daftar bullet di halaman catalog
                    </p>

                    {metadataFeatures.length === 0 ? (
                      <div className="text-center py-4 border border-dashed rounded-lg">
                        <p className="text-sm text-muted-foreground">
                          Belum ada fitur deskriptif. Klik "Tambah Fitur" untuk menambahkan.
                        </p>
                      </div>
                    ) : (
                      <div className="space-y-2">
                        {metadataFeatures.map((feature, index) => (
                          <div key={index} className="flex gap-2">
                            <Input
                              value={feature}
                              onChange={(e) => updateMetadataFeature(index, e.target.value)}
                              placeholder="e.g., SSL Gratis untuk semua domain"
                              className="flex-1"
                            />
                            <Button
                              type="button"
                              variant="ghost"
                              size="icon"
                              onClick={() => removeMetadataFeature(index)}
                              className="text-destructive hover:text-destructive"
                            >
                              <Trash2 className="h-4 w-4" />
                            </Button>
                          </div>
                        ))}
                      </div>
                    )}

                    {errors['metadata.features'] && (
                      <InputError message={errors['metadata.features'] as string} className="mt-2" />
                    )}
                  </div>
                </div>

                {errors.metadata && (
                  <InputError message={errors.metadata as string} className="mt-2" />
                )}
              </div>

              {/* Features Section */}
              <div className="pt-4 border-t">
                <div className="flex items-center justify-between mb-4">
                  <div>
                    <Label className="text-base font-semibold">Product Features</Label>
                    <p className="text-sm text-muted-foreground mt-1">
                      Tambahkan spesifikasi produk seperti CPU, RAM, Bandwidth, dll
                    </p>
                  </div>
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={addFeature}
                  >
                    <Plus className="h-4 w-4 mr-2" />
                    Tambah Feature
                  </Button>
                </div>

                {features.length === 0 ? (
                  <div className="text-center py-8 border border-dashed rounded-lg">
                    <p className="text-sm text-muted-foreground">
                      Belum ada feature. Klik "Tambah Feature" untuk menambahkan.
                    </p>
                  </div>
                ) : (
                  <div className="space-y-4">
                    {features.map((feature, index) => (
                      <div
                        key={index}
                        className="grid grid-cols-1 md:grid-cols-12 gap-3 p-4 border rounded-lg bg-muted/30"
                      >
                        <div className="md:col-span-3">
                          <Label className="text-xs">Key</Label>
                          <Input
                            value={feature.key}
                            onChange={(e) => updateFeature(index, 'key', e.target.value)}
                            placeholder="e.g., cpu"
                            className="mt-1"
                          />
                          {errors[`features.${index}.key`] && (
                            <InputError message={errors[`features.${index}.key`]} className="mt-1" />
                          )}
                        </div>

                        <div className="md:col-span-2">
                          <Label className="text-xs">Label</Label>
                          <Input
                            value={feature.label || ''}
                            onChange={(e) => updateFeature(index, 'label', e.target.value)}
                            placeholder="e.g., CPU"
                            className="mt-1"
                          />
                        </div>

                        <div className="md:col-span-3">
                          <Label className="text-xs">Value</Label>
                          <Input
                            value={feature.value}
                            onChange={(e) => updateFeature(index, 'value', e.target.value)}
                            placeholder="e.g., 2"
                            className="mt-1"
                          />
                          {errors[`features.${index}.value`] && (
                            <InputError message={errors[`features.${index}.value`]} className="mt-1" />
                          )}
                        </div>

                        <div className="md:col-span-2">
                          <Label className="text-xs">Unit</Label>
                          <Input
                            value={feature.unit || ''}
                            onChange={(e) => updateFeature(index, 'unit', e.target.value)}
                            placeholder="e.g., core"
                            className="mt-1"
                          />
                        </div>

                        <div className="md:col-span-1">
                          <Label className="text-xs">Order</Label>
                          <Input
                            type="number"
                            value={feature.display_order || index}
                            onChange={(e) => updateFeature(index, 'display_order', parseInt(e.target.value) || index)}
                            className="mt-1"
                            min="0"
                          />
                        </div>

                        <div className="md:col-span-1 flex items-end">
                          <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            onClick={() => removeFeature(index)}
                            className="text-destructive hover:text-destructive"
                          >
                            <Trash2 className="h-4 w-4" />
                          </Button>
                        </div>
                      </div>
                    ))}
                  </div>
                )}

                {errors.features && (
                  <InputError message={errors.features as string} className="mt-2" />
                )}
              </div>

              <div className="flex gap-2 pt-4">
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

