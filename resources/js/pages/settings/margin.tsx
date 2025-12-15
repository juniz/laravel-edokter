import { type BreadcrumbItem } from '@/types';
import { Transition } from '@headlessui/react';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
	Select,
	SelectContent,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from '@/components/ui/select';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';

const breadcrumbs: BreadcrumbItem[] = [
	{
		title: 'Margin Keuntungan',
		href: '/settings/margin',
	},
];

interface MarginSettings {
	domain_margin_type: 'percentage' | 'fixed';
	domain_margin_value: number;
	ssh_margin_type: 'percentage' | 'fixed';
	ssh_margin_value: number;
}

export default function Margin({
	settings,
}: {
	settings: MarginSettings;
}) {
	const { data, setData, put, errors, processing, recentlySuccessful } = useForm({
		domain_margin_type: settings?.domain_margin_type || 'percentage',
		domain_margin_value: settings?.domain_margin_value || 0,
		ssh_margin_type: settings?.ssh_margin_type || 'percentage',
		ssh_margin_value: settings?.ssh_margin_value || 0,
	});

	const submit: FormEventHandler = (e) => {
		e.preventDefault();
		put('/settings/margin');
	};

	const translateError = (errorKey: string, errorMessage: string | undefined): string => {
		if (!errorMessage) {
			return '';
		}

		const fieldTranslations: Record<string, string> = {
			domain_margin_type: 'Tipe Margin Domain',
			domain_margin_value: 'Nilai Margin Domain',
			ssh_margin_type: 'Tipe Margin SSH',
			ssh_margin_value: 'Nilai Margin SSH',
		};

		const fieldName = fieldTranslations[errorKey] || errorKey.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase());
		let translatedMessage = errorMessage;

		const errorPatterns: Array<{ pattern: RegExp; replacement: string }> = [
			{ pattern: /the (.+?) field must be a string/gi, replacement: `${fieldName} harus berupa teks` },
			{ pattern: /the (.+?) field is required/gi, replacement: `${fieldName} wajib diisi` },
			{ pattern: /must be at least (\d+)/gi, replacement: 'minimal $1' },
			{ pattern: /must not be greater than (\d+)/gi, replacement: 'maksimal $1' },
			{ pattern: /must be (\d+) characters/gi, replacement: 'harus $1 karakter' },
			{ pattern: /must match the required format/gi, replacement: 'format tidak valid' },
		];

		errorPatterns.forEach(({ pattern, replacement }) => {
			translatedMessage = translatedMessage.replace(pattern, replacement);
		});

		if (translatedMessage.includes('the field') || translatedMessage.includes('The field')) {
			translatedMessage = translatedMessage.replace(/the field/gi, fieldName);
		}

		translatedMessage = translatedMessage.replace(/\bfield\b/gi, '').trim();

		return translatedMessage;
	};

	return (
		<AppLayout breadcrumbs={breadcrumbs} title="Margin Keuntungan">
			<Head title="Margin Keuntungan" />
			<div className="flex-1 p-4 md:p-6">
				<Card className="max-w-3xl mx-auto">
					<CardHeader>
						<CardTitle className="text-2xl font-bold tracking-tight">Margin Keuntungan</CardTitle>
						<CardDescription>
							Atur margin keuntungan untuk pembelian domain dan SSH melalui pihak ketiga (RDASH)
						</CardDescription>
					</CardHeader>
					<CardContent className="pt-6">
						<form onSubmit={submit} className="space-y-6">
							{/* Domain Margin Settings Card */}
							<Card>
							<CardHeader>
								<CardTitle>Margin Keuntungan Domain</CardTitle>
								<CardDescription>
									Atur margin keuntungan untuk pembelian domain melalui pihak ketiga (RDASH)
								</CardDescription>
							</CardHeader>
							<CardContent className="space-y-6">
								<div className="grid gap-2">
									<Label htmlFor="domain_margin_type">Tipe Margin *</Label>
									<Select
										value={data.domain_margin_type}
										onValueChange={(value: 'percentage' | 'fixed') => setData('domain_margin_type', value)}
										disabled={processing}
									>
										<SelectTrigger id="domain_margin_type">
											<SelectValue placeholder="Pilih tipe margin" />
										</SelectTrigger>
										<SelectContent>
											<SelectItem value="percentage">Persentase (%)</SelectItem>
											<SelectItem value="fixed">Tetap (Rp)</SelectItem>
										</SelectContent>
									</Select>
									<InputError
										className="mt-2"
										message={translateError('domain_margin_type', errors.domain_margin_type)}
									/>
									<p className="text-sm text-muted-foreground">
										{data.domain_margin_type === 'percentage'
											? 'Margin akan dihitung sebagai persentase dari harga dasar'
											: 'Margin akan ditambahkan sebagai nilai tetap'}
									</p>
								</div>

								<div className="grid gap-2">
									<Label htmlFor="domain_margin_value">
										Nilai Margin *{' '}
										{data.domain_margin_type === 'percentage' ? '(%)' : '(Rp)'}
									</Label>
									<Input
										id="domain_margin_value"
										type="number"
										step={data.domain_margin_type === 'percentage' ? '0.01' : '1'}
										min="0"
										value={data.domain_margin_value}
										onChange={(e) => setData('domain_margin_value', parseFloat(e.target.value) || 0)}
										required
										placeholder={data.domain_margin_type === 'percentage' ? '10' : '10000'}
									/>
									<InputError
										className="mt-2"
										message={translateError('domain_margin_value', errors.domain_margin_value)}
									/>
									{data.domain_margin_type === 'percentage' && data.domain_margin_value > 0 && (
										<p className="text-sm text-muted-foreground">
											Contoh: Harga dasar Rp 100.000 dengan margin {data.domain_margin_value}% = Rp{' '}
											{(100000 * (1 + data.domain_margin_value / 100)).toLocaleString('id-ID')}
										</p>
									)}
									{data.domain_margin_type === 'fixed' && data.domain_margin_value > 0 && (
										<p className="text-sm text-muted-foreground">
											Contoh: Harga dasar Rp 100.000 + margin Rp{' '}
											{data.domain_margin_value.toLocaleString('id-ID')} = Rp{' '}
											{(100000 + data.domain_margin_value).toLocaleString('id-ID')}
										</p>
									)}
								</div>
							</CardContent>
						</Card>

						{/* SSH Margin Settings Card */}
						<Card>
							<CardHeader>
								<CardTitle>Margin Keuntungan SSH</CardTitle>
								<CardDescription>
									Atur margin keuntungan untuk pembelian SSH melalui pihak ketiga (RDASH)
								</CardDescription>
							</CardHeader>
							<CardContent className="space-y-6">
								<div className="grid gap-2">
									<Label htmlFor="ssh_margin_type">Tipe Margin *</Label>
									<Select
										value={data.ssh_margin_type}
										onValueChange={(value: 'percentage' | 'fixed') => setData('ssh_margin_type', value)}
										disabled={processing}
									>
										<SelectTrigger id="ssh_margin_type">
											<SelectValue placeholder="Pilih tipe margin" />
										</SelectTrigger>
										<SelectContent>
											<SelectItem value="percentage">Persentase (%)</SelectItem>
											<SelectItem value="fixed">Tetap (Rp)</SelectItem>
										</SelectContent>
									</Select>
									<InputError
										className="mt-2"
										message={translateError('ssh_margin_type', errors.ssh_margin_type)}
									/>
									<p className="text-sm text-muted-foreground">
										{data.ssh_margin_type === 'percentage'
											? 'Margin akan dihitung sebagai persentase dari harga dasar'
											: 'Margin akan ditambahkan sebagai nilai tetap'}
									</p>
								</div>

								<div className="grid gap-2">
									<Label htmlFor="ssh_margin_value">
										Nilai Margin *{' '}
										{data.ssh_margin_type === 'percentage' ? '(%)' : '(Rp)'}
									</Label>
									<Input
										id="ssh_margin_value"
										type="number"
										step={data.ssh_margin_type === 'percentage' ? '0.01' : '1'}
										min="0"
										value={data.ssh_margin_value}
										onChange={(e) => setData('ssh_margin_value', parseFloat(e.target.value) || 0)}
										required
										placeholder={data.ssh_margin_type === 'percentage' ? '10' : '10000'}
									/>
									<InputError
										className="mt-2"
										message={translateError('ssh_margin_value', errors.ssh_margin_value)}
									/>
									{data.ssh_margin_type === 'percentage' && data.ssh_margin_value > 0 && (
										<p className="text-sm text-muted-foreground">
											Contoh: Harga dasar Rp 100.000 dengan margin {data.ssh_margin_value}% = Rp{' '}
											{(100000 * (1 + data.ssh_margin_value / 100)).toLocaleString('id-ID')}
										</p>
									)}
									{data.ssh_margin_type === 'fixed' && data.ssh_margin_value > 0 && (
										<p className="text-sm text-muted-foreground">
											Contoh: Harga dasar Rp 100.000 + margin Rp{' '}
											{data.ssh_margin_value.toLocaleString('id-ID')} = Rp{' '}
											{(100000 + data.ssh_margin_value).toLocaleString('id-ID')}
										</p>
									)}
								</div>
							</CardContent>
						</Card>

							<div className="flex items-center gap-4">
								<Button type="submit" disabled={processing}>
									{processing ? 'Menyimpan...' : 'Simpan Perubahan'}
								</Button>

								<Transition
									show={recentlySuccessful}
									enter="transition ease-in-out"
									enterFrom="opacity-0"
									leave="transition ease-in-out"
									leaveTo="opacity-0"
								>
									<p className="text-sm text-muted-foreground">Disimpan</p>
								</Transition>
							</div>
						</form>
					</CardContent>
				</Card>
			</div>
		</AppLayout>
	);
}

