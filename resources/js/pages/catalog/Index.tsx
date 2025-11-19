import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Catalog', href: '/catalog' },
];

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
}

interface CatalogProps {
  products: Product[];
}

export default function Catalog({ products }: CatalogProps) {
  const getTypeBadgeColor = (type: string) => {
    switch (type) {
      case 'hosting_shared':
        return 'bg-blue-500';
      case 'vps':
        return 'bg-purple-500';
      case 'addon':
        return 'bg-green-500';
      case 'domain':
        return 'bg-orange-500';
      default:
        return 'bg-gray-500';
    }
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Catalog" />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">Product Catalog</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">Pilih paket hosting yang sesuai kebutuhan Anda</p>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {products.map((product) => (
            <Card key={product.id} className="bg-white dark:bg-gray-800 shadow-md hover:shadow-lg transition-shadow">
              <CardHeader>
                <div className="flex items-start justify-between">
                  <div>
                    <CardTitle className="text-xl mb-2">{product.name}</CardTitle>
                    <Badge className={getTypeBadgeColor(product.type)}>
                      {product.type.replace('_', ' ').toUpperCase()}
                    </Badge>
                  </div>
                </div>
                {product.metadata?.description && (
                  <CardDescription className="mt-2">
                    {product.metadata.description}
                  </CardDescription>
                )}
              </CardHeader>
              <CardContent>
                {product.metadata?.features && (
                  <ul className="space-y-2 mb-4">
                    {product.metadata.features.slice(0, 3).map((feature, idx) => (
                      <li key={idx} className="flex items-center text-sm text-gray-600 dark:text-gray-400">
                        <span className="mr-2">✓</span>
                        {feature}
                      </li>
                    ))}
                  </ul>
                )}
                <Link href={route('catalog.show', product.slug)}>
                  <Button className="w-full">Lihat Detail</Button>
                </Link>
              </CardContent>
            </Card>
          ))}
        </div>

        {products.length === 0 && (
          <Card className="bg-white dark:bg-gray-800 shadow-md">
            <CardContent className="p-8 text-center">
              <p className="text-gray-600 dark:text-gray-400">Tidak ada produk tersedia saat ini.</p>
            </CardContent>
          </Card>
        )}
      </div>
    </AppLayout>
  );
}

