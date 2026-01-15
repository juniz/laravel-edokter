import React, { FormEventHandler, useState } from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/input-error';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import { Tag, Percent, DollarSign, Calendar, Package } from 'lucide-react';

interface Product {
    id: string;
    name: string;
}

interface Coupon {
    id?: string;
    code: string;
    type: 'percent' | 'fixed';
    value: number;
    max_uses: number | null;
    valid_from: string | null;
    valid_until: string | null;
    applicable_product_ids: string[] | null;
    is_auto_apply?: boolean;
    promo_label?: string | null;
}

interface CouponFormProps {
    coupon?: Coupon;
    products: Product[];
}

export default function CouponForm({ coupon, products }: CouponFormProps) {
    const isEdit = !!coupon;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Coupons', href: '/admin/coupons' },
        { title: isEdit ? 'Edit Coupon' : 'Create Coupon', href: '#' },
    ];

    const [selectedProducts, setSelectedProducts] = useState<string[]>(
        coupon?.applicable_product_ids || []
    );

    // Helper to format date for datetime-local input (YYYY-MM-DDTHH:MM)
    const formatDateForInput = (dateString: string | null) => {
        if (!dateString) return '';
        const date = new Date(dateString);
        // Adjust for timezone to get local ISO string
        const offset = date.getTimezoneOffset() * 60000;
        const localDate = new Date(date.getTime() - offset);
        return localDate.toISOString().slice(0, 16);
    };

    const { data, setData, post, put, processing, errors, transform } = useForm<{
        code: string;
        type: 'percent' | 'fixed';
        value: number;
        max_uses: number | null;
        valid_from: string | null;
        valid_until: string | null;
        applicable_product_ids: string[] | null;
        is_auto_apply: boolean;
        promo_label: string;
    }>({
        code: coupon?.code || '',
        type: coupon?.type || 'percent',
        value: coupon?.value || 0,
        max_uses: coupon?.max_uses || null,
        valid_from: formatDateForInput(coupon?.valid_from || null),
        valid_until: formatDateForInput(coupon?.valid_until || null),
        applicable_product_ids: selectedProducts,
        is_auto_apply: coupon?.is_auto_apply || false,
        promo_label: coupon?.promo_label || '',
    });

    // Transform data to UTC before submitting
    transform((data) => ({
        ...data,
        valid_from: data.valid_from ? new Date(data.valid_from).toISOString() : null,
        valid_until: data.valid_until ? new Date(data.valid_until).toISOString() : null,
    }));

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        setData('applicable_product_ids', selectedProducts.length > 0 ? selectedProducts : null);

        if (isEdit && coupon?.id) {
            put(route('admin.coupons.update', coupon.id), {
                preserveScroll: true,
            });
        } else {
            post(route('admin.coupons.store'), {
                preserveScroll: true,
            });
        }
    };

    const handleProductToggle = (productId: string) => {
        const newSelected = selectedProducts.includes(productId)
            ? selectedProducts.filter((id) => id !== productId)
            : [...selectedProducts, productId];
        setSelectedProducts(newSelected);
        setData('applicable_product_ids', newSelected.length > 0 ? newSelected : null);
    };

    const handleSelectAll = () => {
        const newSelected = selectedProducts.length === products.length
            ? []
            : products.map((p) => p.id);
        setSelectedProducts(newSelected);
        setData('applicable_product_ids', newSelected.length > 0 ? newSelected : null);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={isEdit ? 'Edit Coupon' : 'Create Coupon'} />
            <div className="flex flex-col gap-6 p-4">
                <div>
                    <h1 className="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <Tag className="w-8 h-8" />
                        {isEdit ? 'Edit Coupon' : 'Create Coupon'}
                    </h1>
                    <p className="text-gray-600 dark:text-gray-400 mt-2">
                        {isEdit ? 'Update coupon information' : 'Create a new promotional coupon'}
                    </p>
                </div>

                <Card className="bg-white dark:bg-gray-800 shadow-md">
                    <CardHeader>
                        <CardTitle>Coupon Information</CardTitle>
                        <CardDescription>
                            Buat kode promo yang dapat digunakan pelanggan untuk mendapatkan diskon
                        </CardDescription>
                    </CardHeader>
                    <Separator />
                    <CardContent className="pt-6">
                        <form onSubmit={submit} className="space-y-6">
                            {/* Code */}
                            <div className="space-y-2">
                                <Label htmlFor="code">Kode Promo *</Label>
                                <div className="relative">
                                    <Tag className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                                    <Input
                                        id="code"
                                        value={data.code}
                                        onChange={(e) => setData('code', e.target.value.toUpperCase())}
                                        className="pl-9"
                                        placeholder="DISKON10"
                                        required
                                    />
                                </div>
                                <p className="text-sm text-muted-foreground">
                                    Kode yang akan digunakan pelanggan saat checkout
                                </p>
                                <InputError message={errors.code} />
                            </div>

                            {/* Type */}
                            <div className="space-y-2">
                                <Label htmlFor="type">Tipe Diskon *</Label>
                                <Select
                                    value={data.type}
                                    onValueChange={(value: 'percent' | 'fixed') => setData('type', value)}
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="percent">
                                            <div className="flex items-center gap-2">
                                                <Percent className="h-4 w-4" />
                                                Persentase (%)
                                            </div>
                                        </SelectItem>
                                        <SelectItem value="fixed">
                                            <div className="flex items-center gap-2">
                                                <DollarSign className="h-4 w-4" />
                                                Nominal Tetap (Rp)
                                            </div>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.type} />
                            </div>

                            {/* Value */}
                            <div className="space-y-2">
                                <Label htmlFor="value">
                                    Nilai Diskon *{' '}
                                    {data.type === 'percent' ? '(%)' : '(Rp)'}
                                </Label>
                                <Input
                                    id="value"
                                    type="number"
                                    step={data.type === 'percent' ? '0.01' : '1'}
                                    min="0"
                                    value={data.value}
                                    onChange={(e) => setData('value', parseFloat(e.target.value) || 0)}
                                    required
                                    placeholder={data.type === 'percent' ? '10' : '10000'}
                                />
                                <p className="text-sm text-muted-foreground">
                                    {data.type === 'percent' ? (
                                        <>
                                            Contoh: <strong>10</strong> = 10% diskon
                                        </>
                                    ) : (
                                        <>
                                            Contoh: <strong>10000</strong> = Rp 10.000 diskon
                                        </>
                                    )}
                                </p>
                                <InputError message={errors.value} />
                            </div>

                            <Separator />

                            {/* Max Uses */}
                            <div className="space-y-2">
                                <Label htmlFor="max_uses">Maksimal Penggunaan</Label>
                                <Input
                                    id="max_uses"
                                    type="number"
                                    min="1"
                                    value={data.max_uses || ''}
                                    onChange={(e) =>
                                        setData('max_uses', e.target.value ? parseInt(e.target.value) : null)
                                    }
                                    placeholder="Tidak terbatas"
                                />
                                <p className="text-sm text-muted-foreground">
                                    Kosongkan jika tidak ada batas penggunaan
                                </p>
                                <InputError message={errors.max_uses} />
                            </div>

                            {/* Valid From */}
                            <div className="space-y-2">
                                <Label htmlFor="valid_from" className="flex items-center gap-2">
                                    <Calendar className="h-4 w-4" />
                                    Berlaku Dari
                                </Label>
                                <Input
                                    id="valid_from"
                                    type="datetime-local"
                                    value={data.valid_from || ''}
                                    onChange={(e) => setData('valid_from', e.target.value || null)}
                                />
                                <p className="text-sm text-muted-foreground">
                                    Kosongkan jika berlaku segera
                                </p>
                                <InputError message={errors.valid_from} />
                            </div>

                            {/* Valid Until */}
                            <div className="space-y-2">
                                <Label htmlFor="valid_until" className="flex items-center gap-2">
                                    <Calendar className="h-4 w-4" />
                                    Berlaku Sampai
                                </Label>
                                <Input
                                    id="valid_until"
                                    type="datetime-local"
                                    value={data.valid_until || ''}
                                    onChange={(e) => setData('valid_until', e.target.value || null)}
                                />
                                <p className="text-sm text-muted-foreground">
                                    Kosongkan jika tidak ada batas waktu
                                </p>
                                <InputError message={errors.valid_until} />
                            </div>

                            <Separator />

                            {/* Applicable Products */}
                            <div className="space-y-2">
                                <div className="flex items-center justify-between">
                                    <Label className="flex items-center gap-2">
                                        <Package className="h-4 w-4" />
                                        Produk yang Berlaku
                                    </Label>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        onClick={handleSelectAll}
                                    >
                                        {selectedProducts.length === products.length
                                            ? 'Hapus Semua'
                                            : 'Pilih Semua'}
                                    </Button>
                                </div>
                                <p className="text-sm text-muted-foreground">
                                    Pilih produk yang dapat menggunakan kupon ini. Kosongkan jika berlaku untuk semua produk.
                                </p>
                                <div className="border rounded-lg p-4 max-h-60 overflow-y-auto space-y-2">
                                    {products.length === 0 ? (
                                        <p className="text-sm text-muted-foreground text-center py-4">
                                            Tidak ada produk tersedia
                                        </p>
                                    ) : (
                                        products.map((product) => (
                                            <div
                                                key={product.id}
                                                className="flex items-center space-x-2 p-2 hover:bg-muted rounded"
                                            >
                                                <Checkbox
                                                    id={`product-${product.id}`}
                                                    checked={selectedProducts.includes(product.id)}
                                                    onCheckedChange={() => handleProductToggle(product.id)}
                                                />
                                                <Label
                                                    htmlFor={`product-${product.id}`}
                                                    className="flex-1 cursor-pointer"
                                                >
                                                    {product.name}
                                                </Label>
                                            </div>
                                        ))
                                    )}
                                </div>
                                <InputError message={errors.applicable_product_ids} />
                            </div>

                            <Separator />

                            {/* Auto Apply Settings */}
                            <div className="space-y-4">
                                <div className="flex items-center space-x-2 border p-4 rounded-lg">
                                    <Checkbox
                                        id="is_auto_apply"
                                        checked={!!data.is_auto_apply}
                                        onCheckedChange={(checked) => setData('is_auto_apply', !!checked)}
                                    />
                                    <div className="grid gap-1.5 leading-none">
                                        <Label htmlFor="is_auto_apply" className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                            Otomatis Diterapkan pada Katalog
                                        </Label>
                                        <p className="text-sm text-muted-foreground">
                                            Jika diaktifkan, kupon ini akan otomatis memotong harga produk di halaman katalog tanpa perlu input kode manual.
                                        </p>
                                    </div>
                                </div>
                                <InputError message={errors.is_auto_apply} />

                                {data.is_auto_apply && (
                                    <div className="space-y-2 pl-4 border-l-2 border-primary/20 animate-in fade-in slide-in-from-top-2">
                                        <Label htmlFor="promo_label">Label Promosi</Label>
                                        <div className="relative">
                                            <Tag className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                                            <Input
                                                id="promo_label"
                                                value={data.promo_label || ''}
                                                onChange={(e) => setData('promo_label', e.target.value)}
                                                className="pl-9"
                                                placeholder="Promo Tahun Baru"
                                            />
                                        </div>
                                        <p className="text-sm text-muted-foreground">
                                            Teks ini akan muncul sebagai banner atau badge di produk yang mendapat diskon.
                                        </p>
                                        <InputError message={errors.promo_label} />
                                    </div>
                                )}
                            </div>



                            <Separator />

                            {/* Preview */}
                            {data.code && data.value > 0 && (
                                <div className="bg-muted rounded-lg p-4 space-y-2">
                                    <h4 className="font-semibold text-sm">Preview:</h4>
                                    <div className="flex items-center gap-2">
                                        <Badge variant="outline" className="text-lg">
                                            {data.code}
                                        </Badge>
                                        <span className="text-sm text-muted-foreground">
                                            ={' '}
                                            {data.type === 'percent' ? (
                                                <strong className="text-primary">{data.value}%</strong>
                                            ) : (
                                                <strong className="text-primary">
                                                    Rp {data.value.toLocaleString('id-ID')}
                                                </strong>
                                            )}{' '}
                                            diskon
                                        </span>
                                    </div>
                                </div>
                            )}

                            <div className="flex gap-2">
                                <Button type="submit" variant="gradient" disabled={processing}>
                                    {processing
                                        ? 'Menyimpan...'
                                        : isEdit
                                          ? 'Update Coupon'
                                          : 'Create Coupon'}
                                </Button>
                                <Link href={route('admin.coupons.index')}>
                                    <Button type="button" variant="outline">
                                        Batal
                                    </Button>
                                </Link>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
