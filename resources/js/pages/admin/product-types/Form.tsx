import React, { FormEventHandler, useMemo, useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/input-error';
import { ArrowLeft, Save, Shapes } from 'lucide-react';

type ProductTypeStatus = 'active' | 'draft' | 'archived';

interface ProductType {
  id: string;
  slug: string;
  name: string;
  status: ProductTypeStatus;
  icon: string | null;
  display_order: number;
  metadata: Record<string, unknown> | null;
}

interface FormData {
  name: string;
  slug: string;
  status: ProductTypeStatus;
  icon: string;
  display_order: number;
  metadata: Record<string, unknown> | null;
}

interface Props {
  type?: ProductType;
}

export default function ProductTypeForm({ type }: Props) {
  const isEdit = Boolean(type?.id);

  const breadcrumbs: BreadcrumbItem[] = useMemo(
    () => [
      { title: 'Product Types', href: '/admin/product-types' },
      { title: isEdit ? 'Edit' : 'Create', href: '#' },
    ],
    [isEdit]
  );

  const initialMetadataJson = type?.metadata ? JSON.stringify(type.metadata, null, 2) : '';
  const [metadataJson, setMetadataJson] = useState<string>(initialMetadataJson);

  const { data, setData, post, put, processing, errors, setError, clearErrors } = useForm<FormData>({
    name: type?.name ?? '',
    slug: type?.slug ?? '',
    status: type?.status ?? 'active',
    icon: type?.icon ?? '',
    display_order: type?.display_order ?? 0,
    metadata: type?.metadata ?? null,
  });

  const submit: FormEventHandler = (e) => {
    e.preventDefault();

    clearErrors('metadata');

    const trimmed = metadataJson.trim();
    let parsed: Record<string, unknown> | null = null;

    if (trimmed !== '') {
      try {
        parsed = JSON.parse(trimmed) as Record<string, unknown>;
      } catch {
        setError('metadata', 'Metadata harus berupa JSON yang valid.');
        return;
      }
    }

    setData('metadata', parsed && Object.keys(parsed).length > 0 ? parsed : null);

    if (isEdit && type?.id) {
      put(route('admin.product-types.update', type.id));
    } else {
      post(route('admin.product-types.store'));
    }
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={isEdit ? 'Edit Product Type' : 'Create Product Type'} />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
              <Shapes className="w-8 h-8" />
              {isEdit ? 'Edit Tipe Produk' : 'Tambah Tipe Produk'}
            </h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">
              {isEdit ? 'Perbarui konfigurasi tipe produk.' : 'Buat tipe produk baru.'}
            </p>
          </div>
          <Link href={route('admin.product-types.index')}>
            <Button variant="outline">
              <ArrowLeft className="w-4 h-4 mr-2" />
              Kembali
            </Button>
          </Link>
        </div>

        <Card className="bg-white dark:bg-gray-800 shadow-md">
          <CardHeader>
            <CardTitle>Informasi Tipe</CardTitle>
          </CardHeader>
          <CardContent>
            <form onSubmit={submit} className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="name">Nama</Label>
                  <Input
                    id="name"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    className="mt-1"
                    placeholder="Contoh: Shared Hosting"
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
                    placeholder="Contoh: hosting_shared"
                  />
                  <InputError message={errors.slug} className="mt-2" />
                </div>

                <div>
                  <Label>Status</Label>
                  <Select value={data.status} onValueChange={(value) => setData('status', value as ProductTypeStatus)}>
                    <SelectTrigger className="mt-1">
                      <SelectValue placeholder="Pilih status" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="active">active</SelectItem>
                      <SelectItem value="draft">draft</SelectItem>
                      <SelectItem value="archived">archived</SelectItem>
                    </SelectContent>
                  </Select>
                  <InputError message={errors.status} className="mt-2" />
                </div>

                <div>
                  <Label htmlFor="display_order">Urutan Tampil</Label>
                  <Input
                    id="display_order"
                    type="number"
                    value={data.display_order}
                    onChange={(e) => setData('display_order', Number(e.target.value || 0))}
                    className="mt-1"
                  />
                  <InputError message={errors.display_order} className="mt-2" />
                </div>

                <div className="md:col-span-2">
                  <Label htmlFor="icon">Icon (Lucide)</Label>
                  <Input
                    id="icon"
                    value={data.icon}
                    onChange={(e) => setData('icon', e.target.value)}
                    className="mt-1"
                    placeholder="Contoh: Server, HardDrive, Globe, LayoutGrid"
                  />
                  <InputError message={errors.icon} className="mt-2" />
                </div>

                <div className="md:col-span-2">
                  <Label htmlFor="metadata">Metadata (JSON)</Label>
                  <Textarea
                    id="metadata"
                    value={metadataJson}
                    onChange={(e) => setMetadataJson(e.target.value)}
                    className="mt-1 font-mono text-sm"
                    rows={8}
                    placeholder='Contoh: {"color":"text-blue-600","bgColor":"from-blue-500 to-cyan-500","gradient":"from-blue-500/10 to-cyan-500/10"}'
                  />
                  <InputError message={errors.metadata} className="mt-2" />
                </div>
              </div>

              <div className="flex justify-end gap-2 pt-2">
                <Button type="submit" disabled={processing}>
                  <Save className="w-4 h-4 mr-2" />
                  {processing ? 'Menyimpan...' : 'Simpan'}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}

