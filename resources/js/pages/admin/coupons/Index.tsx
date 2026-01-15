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
    AlertDialogTrigger,
    AlertDialogContent,
    AlertDialogHeader,
    AlertDialogFooter,
    AlertDialogTitle,
    AlertDialogDescription,
    AlertDialogCancel,
    AlertDialogAction,
} from '@/components/ui/alert-dialog';
import {
    Tag,
    Plus,
    Edit,
    Trash2,
    Search,
    Percent,
    DollarSign,
    Calendar,
    Package,
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Coupons', href: '/admin/coupons' },
];

interface Coupon {
    id: string;
    code: string;
    type: 'percent' | 'fixed';
    value: number;
    max_uses: number | null;
    used_count: number;
    valid_from: string | null;
    valid_until: string | null;
    applicable_product_ids: string[] | null;
    created_at: string;
}

interface CouponsProps {
    coupons: {
        data: Coupon[];
        links: any;
        meta: any;
    };
    filters?: {
        search?: string;
    };
}

export default function CouponsIndex({ coupons, filters }: CouponsProps) {
    const [search, setSearch] = useState(filters?.search || '');
    const [deleteId, setDeleteId] = useState<string | null>(null);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(route('admin.coupons.index'), { search }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleDelete = (id: string) => {
        router.delete(route('admin.coupons.destroy', id), {
            preserveScroll: true,
            onSuccess: () => setDeleteId(null),
        });
    };

    const formatDate = (date: string | null) => {
        if (!date) return 'Tidak ada batas';
        return new Date(date).toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    const isExpired = (coupon: Coupon) => {
        if (!coupon.valid_until) return false;
        return new Date(coupon.valid_until) < new Date();
    };

    const isActive = (coupon: Coupon) => {
        if (isExpired(coupon)) return false;
        if (coupon.max_uses && coupon.used_count >= coupon.max_uses) return false;
        if (coupon.valid_from && new Date(coupon.valid_from) > new Date()) return false;
        return true;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Coupons" />
            <div className="flex flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <Tag className="w-8 h-8" />
                            Kode Promo
                        </h1>
                        <p className="text-gray-600 dark:text-gray-400 mt-2">
                            Kelola kode promo dan diskon untuk pelanggan
                        </p>
                    </div>
                    <Link href={route('admin.coupons.create')}>
                        <Button>
                            <Plus className="w-4 h-4 mr-2" />
                            Tambah Kupon
                        </Button>
                    </Link>
                </div>

                {/* Search */}
                <Card className="bg-white dark:bg-gray-800 shadow-md">
                    <CardContent className="p-4">
                        <form onSubmit={handleSearch} className="flex gap-2">
                            <div className="flex-1 relative">
                                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                                <Input
                                    type="text"
                                    placeholder="Cari kode promo..."
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

                {/* Coupons List */}
                {coupons.data.length === 0 ? (
                    <Card className="bg-white dark:bg-gray-800 shadow-md">
                        <CardContent className="p-12 text-center">
                            <Tag className="w-16 h-16 mx-auto mb-4 text-gray-400" />
                            <h3 className="text-lg font-semibold mb-2">Tidak Ada Kupon</h3>
                            <p className="text-gray-600 dark:text-gray-400 mb-6">
                                Belum ada kode promo yang dibuat. Buat kupon pertama Anda sekarang.
                            </p>
                            <Link href={route('admin.coupons.create')}>
                                <Button>
                                    <Plus className="w-4 h-4 mr-2" />
                                    Tambah Kupon Pertama
                                </Button>
                            </Link>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        {coupons.data.map((coupon) => (
                            <Card
                                key={coupon.id}
                                className={`bg-white dark:bg-gray-800 shadow-md ${
                                    !isActive(coupon) ? 'opacity-60' : ''
                                }`}
                            >
                                <CardContent className="p-5">
                                    <div className="flex items-start justify-between mb-3">
                                        <div className="flex-1">
                                            <div className="flex items-center gap-2 mb-2">
                                                <h3 className="font-bold text-lg">{coupon.code}</h3>
                                                {isActive(coupon) ? (
                                                    <Badge variant="success-soft">Aktif</Badge>
                                                ) : (
                                                    <Badge variant="destructive">Tidak Aktif</Badge>
                                                )}
                                            </div>
                                            <div className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                                {coupon.type === 'percent' ? (
                                                    <>
                                                        <Percent className="h-4 w-4" />
                                                        <span className="font-semibold text-primary">
                                                            {coupon.value}% Diskon
                                                        </span>
                                                    </>
                                                ) : (
                                                    <>
                                                        <DollarSign className="h-4 w-4" />
                                                        <span className="font-semibold text-primary">
                                                            Rp {coupon.value.toLocaleString('id-ID')}
                                                        </span>
                                                    </>
                                                )}
                                            </div>
                                        </div>
                                    </div>

                                    <div className="space-y-2 text-sm mb-4">
                                        {coupon.max_uses && (
                                            <div className="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                                <Package className="h-4 w-4" />
                                                <span>
                                                    Digunakan: {coupon.used_count} / {coupon.max_uses}
                                                </span>
                                            </div>
                                        )}
                                        <div className="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                            <Calendar className="h-4 w-4" />
                                            <span>
                                                Berlaku: {formatDate(coupon.valid_from)} - {formatDate(coupon.valid_until)}
                                            </span>
                                        </div>
                                        {coupon.applicable_product_ids && coupon.applicable_product_ids.length > 0 && (
                                            <div className="text-gray-600 dark:text-gray-400">
                                                <span className="font-medium">
                                                    {coupon.applicable_product_ids.length} produk berlaku
                                                </span>
                                            </div>
                                        )}
                                    </div>

                                    <div className="flex gap-2 pt-3 border-t">
                                        <Link href={route('admin.coupons.edit', coupon.id)} className="flex-1">
                                            <Button variant="outline" size="sm" className="w-full">
                                                <Edit className="w-4 h-4 mr-1" />
                                                Edit
                                            </Button>
                                        </Link>
                                        <AlertDialog>
                                            <AlertDialogTrigger asChild>
                                                <Button
                                                    variant="destructive"
                                                    size="sm"
                                                    onClick={() => setDeleteId(coupon.id)}
                                                >
                                                    <Trash2 className="w-4 h-4" />
                                                </Button>
                                            </AlertDialogTrigger>
                                            <AlertDialogContent>
                                                <AlertDialogHeader>
                                                    <AlertDialogTitle>Hapus Kupon?</AlertDialogTitle>
                                                    <AlertDialogDescription>
                                                        Apakah Anda yakin ingin menghapus kupon <strong>{coupon.code}</strong>?
                                                        Tindakan ini tidak dapat dibatalkan.
                                                    </AlertDialogDescription>
                                                </AlertDialogHeader>
                                                <AlertDialogFooter>
                                                    <AlertDialogCancel onClick={() => setDeleteId(null)}>
                                                        Batal
                                                    </AlertDialogCancel>
                                                    <AlertDialogAction
                                                        onClick={() => handleDelete(coupon.id)}
                                                        className="bg-red-600 hover:bg-red-700"
                                                    >
                                                        Hapus
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
                {coupons.links && coupons.links.length > 3 && (
                    <div className="flex justify-center gap-2">
                        {coupons.links.map((link: any, index: number) => (
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
