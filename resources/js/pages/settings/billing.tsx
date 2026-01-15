import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/input-error';
import { Separator } from '@/components/ui/separator';
import { Percent, Receipt, Tag } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Billing Settings', href: '/settings/billing' },
];

interface BillingSettings {
    pph_rate: number;
    annual_discount_rate: number;
}

interface Props {
    settings: BillingSettings;
}

export default function Billing({ settings }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        pph_rate: settings.pph_rate,
        annual_discount_rate: settings.annual_discount_rate,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('settings.billing.update'), {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Billing Settings" />
            <div className="flex-1 p-4 md:p-6">
                <div className="max-w-3xl mx-auto space-y-6">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Pengaturan Billing</h1>
                        <p className="text-muted-foreground mt-2">
                            Kelola pengaturan PPH, diskon tahunan, dan tarif lainnya
                        </p>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Receipt className="h-5 w-5" />
                                Pengaturan Pajak & Diskon
                            </CardTitle>
                            <CardDescription>
                                Konfigurasi tarif PPH dan diskon tahunan untuk semua transaksi
                            </CardDescription>
                        </CardHeader>
                        <Separator />
                        <CardContent className="pt-6">
                            <form onSubmit={handleSubmit} className="space-y-6">
                                {/* PPH Rate */}
                                <div className="space-y-2">
                                    <Label htmlFor="pph_rate" className="flex items-center gap-2">
                                        <Percent className="h-4 w-4" />
                                        Tarif PPH (Pajak Penghasilan)
                                    </Label>
                                    <div className="relative">
                                        <Input
                                            id="pph_rate"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="1"
                                            value={data.pph_rate}
                                            onChange={(e) => setData('pph_rate', parseFloat(e.target.value) || 0)}
                                            className="pr-8"
                                            placeholder="0.11"
                                        />
                                        <span className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground">
                                            %
                                        </span>
                                    </div>
                                    <p className="text-sm text-muted-foreground">
                                        Tarif PPH yang akan dikenakan pada setiap transaksi. Default: 11% (sesuai regulasi Indonesia untuk jasa digital)
                                    </p>
                                    <p className="text-sm font-medium">
                                        Nilai saat ini: <span className="text-primary">{(data.pph_rate * 100).toFixed(1)}%</span>
                                    </p>
                                    <InputError message={errors.pph_rate} />
                                </div>

                                <Separator />

                                {/* Annual Discount Rate */}
                                <div className="space-y-2">
                                    <Label htmlFor="annual_discount_rate" className="flex items-center gap-2">
                                        <Tag className="h-4 w-4" />
                                        Diskon Tahun-an Default
                                    </Label>
                                    <div className="relative">
                                        <Input
                                            id="annual_discount_rate"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="1"
                                            value={data.annual_discount_rate}
                                            onChange={(e) => setData('annual_discount_rate', parseFloat(e.target.value) || 0)}
                                            className="pr-8"
                                            placeholder="0.20"
                                        />
                                        <span className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground">
                                            %
                                        </span>
                                    </div>
                                    <p className="text-sm text-muted-foreground">
                                        Diskon default yang diberikan untuk pembelian tahunan jika tidak ada plan tahunan spesifik. Default: 20% (setara dengan 2 bulan gratis)
                                    </p>
                                    <p className="text-sm font-medium">
                                        Nilai saat ini: <span className="text-primary">{(data.annual_discount_rate * 100).toFixed(1)}%</span>
                                    </p>
                                    <InputError message={errors.annual_discount_rate} />
                                </div>

                                <Separator />

                                {/* Info Box */}
                                <div className="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                    <h4 className="font-semibold text-sm mb-2 text-blue-900 dark:text-blue-100">
                                        Catatan Penting:
                                    </h4>
                                    <ul className="text-sm text-blue-800 dark:text-blue-200 space-y-1 list-disc list-inside">
                                        <li>Perubahan pengaturan ini akan mempengaruhi semua transaksi baru</li>
                                        <li>PPH dihitung dari subtotal setelah dikurangi diskon</li>
                                        <li>Diskon tahunan akan otomatis diterapkan jika customer memilih plan tahunan</li>
                                    </ul>
                                </div>

                                <div className="flex justify-end gap-3">
                                    <Button
                                        type="submit"
                                        variant="gradient"
                                        disabled={processing}
                                    >
                                        {processing ? 'Menyimpan...' : 'Simpan Pengaturan'}
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
