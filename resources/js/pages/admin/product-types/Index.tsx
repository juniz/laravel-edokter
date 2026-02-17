import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Plus, Search, Shapes, Pencil, Trash2 } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Product Types', href: '/admin/product-types' }];

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

interface ProductType {
  id: string;
  slug: string;
  name: string;
  status: 'active' | 'draft' | 'archived';
  icon: string | null;
  display_order: number;
}

interface Props {
  types: {
    data: ProductType[];
    links: PaginationLink[];
    meta: PaginationMeta;
  };
  filters?: {
    search?: string | null;
  };
}

export default function ProductTypesIndex({ types, filters }: Props) {
  const [search, setSearch] = useState(filters?.search ?? '');
  const [deleteId, setDeleteId] = useState<string | null>(null);

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    router.get(
      route('admin.product-types.index'),
      { search: search || undefined },
      { preserveState: true, preserveScroll: true }
    );
  };

  const handleDelete = (id: string) => {
    router.delete(route('admin.product-types.destroy', id), {
      preserveScroll: true,
      onSuccess: () => setDeleteId(null),
    });
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Product Types" />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
              <Shapes className="w-8 h-8" />
              Tipe Produk
            </h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">
              Kelola tipe produk secara dinamis (tanpa hard code)
            </p>
          </div>
          <Link href={route('admin.product-types.create')}>
            <Button>
              <Plus className="w-4 h-4 mr-2" />
              Tambah Tipe
            </Button>
          </Link>
        </div>

        <Card className="bg-white dark:bg-gray-800 shadow-md">
          <CardContent className="p-4">
            <form onSubmit={handleSearch} className="flex gap-2">
              <div className="flex-1 relative">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <Input
                  type="text"
                  placeholder="Cari nama atau slug..."
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  className="pl-9"
                />
              </div>
              <Button type="submit" variant="outline">
                Cari
              </Button>
            </form>
          </CardContent>
        </Card>

        {types.data.length === 0 ? (
          <Card className="bg-white dark:bg-gray-800 shadow-md">
            <CardContent className="p-12 text-center">
              <Shapes className="w-16 h-16 mx-auto mb-4 text-gray-400" />
              <h3 className="text-lg font-semibold mb-2">Tidak Ada Tipe Produk</h3>
              <p className="text-gray-600 dark:text-gray-400 mb-6">
                Buat tipe produk pertama untuk mengelompokkan produk Anda.
              </p>
              <Link href={route('admin.product-types.create')}>
                <Button>
                  <Plus className="w-4 h-4 mr-2" />
                  Tambah Tipe Pertama
                </Button>
              </Link>
            </CardContent>
          </Card>
        ) : (
          <div className="grid gap-4">
            {types.data.map((type) => (
              <Card key={type.id} className="bg-white dark:bg-gray-800 shadow-md">
                <CardContent className="p-6">
                  <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div className="flex-1 min-w-0">
                      <div className="flex items-center gap-2">
                        <h3 className="text-xl font-semibold text-gray-900 dark:text-white truncate">
                          {type.name}
                        </h3>
                        <Badge
                          variant={
                            type.status === 'active'
                              ? 'default'
                              : type.status === 'draft'
                              ? 'secondary'
                              : 'outline'
                          }
                        >
                          {type.status}
                        </Badge>
                      </div>
                      <div className="mt-2 text-sm text-muted-foreground flex flex-wrap gap-x-4 gap-y-1">
                        <span>Slug: {type.slug}</span>
                        <span>Urutan: {type.display_order}</span>
                        {type.icon ? <span>Icon: {type.icon}</span> : null}
                      </div>
                    </div>

                    <div className="flex items-center gap-2">
                      <Link href={route('admin.product-types.edit', type.id)}>
                        <Button variant="outline" size="sm">
                          <Pencil className="w-4 h-4 mr-2" />
                          Edit
                        </Button>
                      </Link>

                      <AlertDialog open={deleteId === type.id} onOpenChange={(open) => setDeleteId(open ? type.id : null)}>
                        <AlertDialogTrigger asChild>
                          <Button
                            variant="outline"
                            size="sm"
                            className="text-red-600 hover:text-red-700 hover:bg-red-50"
                            onClick={() => setDeleteId(type.id)}
                          >
                            <Trash2 className="w-4 h-4 mr-2" />
                            Hapus
                          </Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                          <AlertDialogHeader>
                            <AlertDialogTitle>Hapus Tipe Produk?</AlertDialogTitle>
                            <AlertDialogDescription>
                              Tindakan ini tidak bisa dibatalkan. Pastikan tipe ini tidak sedang dipakai oleh produk.
                            </AlertDialogDescription>
                          </AlertDialogHeader>
                          <AlertDialogFooter>
                            <AlertDialogCancel onClick={() => setDeleteId(null)}>Batal</AlertDialogCancel>
                            <AlertDialogAction className="bg-red-600 hover:bg-red-700" onClick={() => handleDelete(type.id)}>
                              Hapus
                            </AlertDialogAction>
                          </AlertDialogFooter>
                        </AlertDialogContent>
                      </AlertDialog>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}

        {types.links && types.links.length > 3 && (
          <div className="flex justify-center gap-2 flex-wrap">
            {types.links.map((link, index) => (
              <Button
                key={index}
                variant={link.active ? 'default' : 'outline'}
                size="sm"
                onClick={() => link.url && router.get(link.url)}
                disabled={!link.url}
              >
                <span dangerouslySetInnerHTML={{ __html: link.label }} />
              </Button>
            ))}
          </div>
        )}
      </div>
    </AppLayout>
  );
}

