import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/input-error';
import { Separator } from '@/components/ui/separator';
import { Link as LinkIcon, Plus, Save, Trash2 } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Site Footer', href: '/settings/site-footer' }];

type FooterLinkItem = {
    label: string;
    href: string;
};

type SiteFooterSettings = {
    description?: string | null;
    quick_links_title: string;
    quick_links: FooterLinkItem[];
    support_links_title: string;
    support_links: FooterLinkItem[];
};

interface Props {
    settings: SiteFooterSettings;
}

export default function SiteFooter({ settings }: Props) {
    const { data, setData, put, processing, errors } = useForm<SiteFooterSettings>({
        description: settings.description ?? '',
        quick_links_title: settings.quick_links_title ?? 'Produk',
        quick_links: settings.quick_links ?? [],
        support_links_title: settings.support_links_title ?? 'Dukungan',
        support_links: settings.support_links ?? [],
    });

    const addQuickLink = () => {
        setData('quick_links', [...data.quick_links, { label: '', href: '' }]);
    };

    const removeQuickLink = (index: number) => {
        setData('quick_links', data.quick_links.filter((_, i) => i !== index));
    };

    const updateQuickLink = (index: number, patch: Partial<FooterLinkItem>) => {
        setData(
            'quick_links',
            data.quick_links.map((item, i) => (i === index ? { ...item, ...patch } : item)),
        );
    };

    const addSupportLink = () => {
        setData('support_links', [...data.support_links, { label: '', href: '' }]);
    };

    const removeSupportLink = (index: number) => {
        setData('support_links', data.support_links.filter((_, i) => i !== index));
    };

    const updateSupportLink = (index: number, patch: Partial<FooterLinkItem>) => {
        setData(
            'support_links',
            data.support_links.map((item, i) => (i === index ? { ...item, ...patch } : item)),
        );
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('settings.site-footer.update'), { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs} title="Site Footer">
            <Head title="Site Footer" />
            <div className="flex-1 p-4 md:p-6">
                <div className="max-w-4xl mx-auto space-y-6">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Pengaturan Footer</h1>
                        <p className="text-muted-foreground mt-2">
                            Kelola deskripsi, quick links, dan link dukungan pada footer website
                        </p>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <LinkIcon className="h-5 w-5" />
                                Konten Footer
                            </CardTitle>
                            <CardDescription>Perubahan ini akan tampil di halaman publik (Guest)</CardDescription>
                        </CardHeader>
                        <Separator />
                        <CardContent className="pt-6">
                            <form onSubmit={handleSubmit} className="space-y-8">
                                <div className="space-y-2">
                                    <Label htmlFor="description">Deskripsi</Label>
                                    <Textarea
                                        id="description"
                                        value={data.description ?? ''}
                                        onChange={(e) => setData('description', e.target.value)}
                                        placeholder="Tuliskan deskripsi singkat untuk footer..."
                                    />
                                    <InputError message={errors.description} />
                                </div>

                                <Separator />

                                <div className="space-y-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="quick_links_title">Judul Kolom Quick Links</Label>
                                        <Input
                                            id="quick_links_title"
                                            value={data.quick_links_title}
                                            onChange={(e) => setData('quick_links_title', e.target.value)}
                                            placeholder="Produk"
                                        />
                                        <InputError message={errors.quick_links_title} />
                                    </div>

                                    <div className="space-y-3">
                                        <div className="flex items-center justify-between">
                                            <h3 className="text-sm font-semibold">Daftar Link</h3>
                                            <Button type="button" variant="outline" size="sm" onClick={addQuickLink} disabled={processing}>
                                                <Plus className="h-4 w-4 mr-2" />
                                                Tambah Link
                                            </Button>
                                        </div>

                                        {data.quick_links.length === 0 ? (
                                            <div className="text-sm text-muted-foreground">Belum ada quick link.</div>
                                        ) : (
                                            <div className="space-y-3">
                                                {data.quick_links.map((item, index) => (
                                                    <div key={index} className="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                                                        <div className="md:col-span-4 space-y-2">
                                                            <Label>Label</Label>
                                                            <Input
                                                                value={item.label}
                                                                onChange={(e) => updateQuickLink(index, { label: e.target.value })}
                                                                placeholder="Shared Hosting"
                                                            />
                                                            <InputError message={(errors as any)[`quick_links.${index}.label`]} />
                                                        </div>
                                                        <div className="md:col-span-7 space-y-2">
                                                            <Label>URL</Label>
                                                            <Input
                                                                value={item.href}
                                                                onChange={(e) => updateQuickLink(index, { href: e.target.value })}
                                                                placeholder="/catalog atau https://contoh.com"
                                                            />
                                                            <InputError message={(errors as any)[`quick_links.${index}.href`]} />
                                                        </div>
                                                        <div className="md:col-span-1 pt-7">
                                                            <Button
                                                                type="button"
                                                                variant="ghost"
                                                                size="icon"
                                                                onClick={() => removeQuickLink(index)}
                                                                disabled={processing}
                                                            >
                                                                <Trash2 className="h-4 w-4" />
                                                            </Button>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        )}
                                    </div>
                                </div>

                                <Separator />

                                <div className="space-y-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="support_links_title">Judul Kolom Dukungan</Label>
                                        <Input
                                            id="support_links_title"
                                            value={data.support_links_title}
                                            onChange={(e) => setData('support_links_title', e.target.value)}
                                            placeholder="Dukungan"
                                        />
                                        <InputError message={errors.support_links_title} />
                                    </div>

                                    <div className="space-y-3">
                                        <div className="flex items-center justify-between">
                                            <h3 className="text-sm font-semibold">Daftar Link</h3>
                                            <Button type="button" variant="outline" size="sm" onClick={addSupportLink} disabled={processing}>
                                                <Plus className="h-4 w-4 mr-2" />
                                                Tambah Link
                                            </Button>
                                        </div>

                                        {data.support_links.length === 0 ? (
                                            <div className="text-sm text-muted-foreground">Belum ada link dukungan.</div>
                                        ) : (
                                            <div className="space-y-3">
                                                {data.support_links.map((item, index) => (
                                                    <div key={index} className="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                                                        <div className="md:col-span-4 space-y-2">
                                                            <Label>Label</Label>
                                                            <Input
                                                                value={item.label}
                                                                onChange={(e) => updateSupportLink(index, { label: e.target.value })}
                                                                placeholder="Client Area"
                                                            />
                                                            <InputError message={(errors as any)[`support_links.${index}.label`]} />
                                                        </div>
                                                        <div className="md:col-span-7 space-y-2">
                                                            <Label>URL</Label>
                                                            <Input
                                                                value={item.href}
                                                                onChange={(e) => updateSupportLink(index, { href: e.target.value })}
                                                                placeholder="/login atau https://contoh.com"
                                                            />
                                                            <InputError message={(errors as any)[`support_links.${index}.href`]} />
                                                        </div>
                                                        <div className="md:col-span-1 pt-7">
                                                            <Button
                                                                type="button"
                                                                variant="ghost"
                                                                size="icon"
                                                                onClick={() => removeSupportLink(index)}
                                                                disabled={processing}
                                                            >
                                                                <Trash2 className="h-4 w-4" />
                                                            </Button>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        )}
                                    </div>
                                </div>

                                <div className="flex justify-end gap-3">
                                    <Button type="submit" variant="gradient" disabled={processing}>
                                        <Save className="h-4 w-4 mr-2" />
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

