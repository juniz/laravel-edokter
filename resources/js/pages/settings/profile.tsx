import { type BreadcrumbItem, type SharedData } from '@/types';
import { Transition } from '@headlessui/react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';

import DeleteUser from '@/components/delete-user';
import HeadingSmall from '@/components/heading-small';
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
import SettingsLayout from '@/layouts/settings/layout';

const breadcrumbs: BreadcrumbItem[] = [
	{
		title: 'Profile settings',
		href: '/settings/profile',
	},
];

interface CustomerData {
	organization?: string;
	phone?: string;
	street_1?: string;
	street_2?: string;
	city?: string;
	state?: string;
	country_code?: string;
	postal_code?: string;
	fax?: string;
}

export default function Profile({
	mustVerifyEmail,
	status,
	customer,
}: {
	mustVerifyEmail: boolean;
	status?: string;
	customer?: CustomerData | null;
}) {
	const { auth } = usePage<SharedData>().props;

	const countryCodes = [
		{ code: 'ID', name: 'Indonesia' },
		{ code: 'MY', name: 'Malaysia' },
		{ code: 'SG', name: 'Singapore' },
		{ code: 'TH', name: 'Thailand' },
		{ code: 'PH', name: 'Philippines' },
		{ code: 'VN', name: 'Vietnam' },
	];

	const { data, setData, patch, errors, processing, recentlySuccessful } = useForm({
		name: auth.user.name,
		email: auth.user.email,
		organization: customer?.organization || '',
		phone: customer?.phone || '',
		street_1: customer?.street_1 || '',
		street_2: customer?.street_2 || '',
		city: customer?.city || '',
		state: customer?.state || '',
		country_code: customer?.country_code || 'ID',
		postal_code: customer?.postal_code || '',
		fax: customer?.fax || '',
	});

	const submit: FormEventHandler = (e) => {
		e.preventDefault();

		patch(route('profile.update'));
	};

	const translateError = (errorKey: string, errorMessage: string | undefined): string => {
		if (!errorMessage) {
			return '';
		}

		const fieldTranslations: Record<string, string> = {
			name: 'Nama',
			email: 'Email',
			organization: 'Organisasi',
			phone: 'Nomor Telepon',
			street_1: 'Alamat Jalan',
			street_2: 'Alamat Jalan 2',
			city: 'Kota',
			state: 'Provinsi',
			country_code: 'Kode Negara',
			postal_code: 'Kode Pos',
			fax: 'Fax',
		};

		const fieldName = fieldTranslations[errorKey] || errorKey.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase());
		let translatedMessage = errorMessage;

		const errorPatterns: Array<{ pattern: RegExp; replacement: string }> = [
			{ pattern: /the (.+?) field must be a string/gi, replacement: `${fieldName} harus berupa teks` },
			{ pattern: /the (.+?) field is required/gi, replacement: `${fieldName} wajib diisi` },
			{ pattern: /the (.+?) must be a valid email/gi, replacement: `${fieldName} harus berupa email yang valid` },
			{ pattern: /the (.+?) has already been taken/gi, replacement: `${fieldName} sudah digunakan` },
			{ pattern: /must be at least (\d+) characters/gi, replacement: 'minimal $1 karakter' },
			{ pattern: /must not be greater than (\d+) characters/gi, replacement: 'maksimal $1 karakter' },
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
		<AppLayout breadcrumbs={breadcrumbs}>
			<Head title="Profile settings" />

			<SettingsLayout>
				<div className="space-y-6">
					<form onSubmit={submit} className="space-y-6">
						{/* Account Information Card */}
						<Card>
							<CardHeader>
								<CardTitle>Informasi Akun</CardTitle>
								<CardDescription>Perbarui nama dan alamat email Anda</CardDescription>
							</CardHeader>
							<CardContent className="space-y-6">
								<div className="grid gap-2">
									<Label htmlFor="name">Nama Lengkap *</Label>
									<Input
										id="name"
										className="mt-1 block w-full"
										value={data.name}
										onChange={(e) => setData('name', e.target.value)}
										required
										autoComplete="name"
										placeholder="Nama lengkap"
									/>
									<InputError className="mt-2" message={translateError('name', errors.name)} />
								</div>

								<div className="grid gap-2">
									<Label htmlFor="email">Alamat Email *</Label>
									<Input
										id="email"
										type="email"
										className="mt-1 block w-full"
										value={data.email}
										onChange={(e) => setData('email', e.target.value)}
										required
										autoComplete="username"
										placeholder="email@contoh.com"
									/>
									<InputError className="mt-2" message={translateError('email', errors.email)} />
								</div>

								{mustVerifyEmail && auth.user.email_verified_at === null && (
									<div>
										<p className="mt-2 text-sm text-muted-foreground">
											Alamat email Anda belum diverifikasi.
											<Link
												href={route('verification.send')}
												method="post"
												as="button"
												className="ml-1 rounded-md text-sm text-primary underline hover:text-primary/80 focus:ring-2 focus:ring-offset-2 focus:outline-hidden"
											>
												Klik di sini untuk mengirim ulang email verifikasi.
											</Link>
										</p>

										{status === 'verification-link-sent' && (
											<div className="mt-2 text-sm font-medium text-green-600">
												Tautan verifikasi baru telah dikirim ke alamat email Anda.
											</div>
										)}
									</div>
								)}
							</CardContent>
						</Card>

						{/* Billing Information Card */}
						<Card>
							<CardHeader>
								<CardTitle>Informasi Penagihan</CardTitle>
								<CardDescription>
									Perbarui informasi penagihan dan alamat Anda untuk faktur dan langganan
								</CardDescription>
							</CardHeader>
							<CardContent className="space-y-6">
								<div className="grid gap-2">
									<Label htmlFor="organization">Nama Organisasi/Perusahaan</Label>
									<Input
										id="organization"
										type="text"
										value={data.organization}
										onChange={(e) => setData('organization', e.target.value)}
										placeholder="Nama organisasi atau perusahaan"
									/>
									<InputError
										className="mt-2"
										message={translateError('organization', errors.organization)}
									/>
								</div>

								<div className="grid gap-2">
									<Label htmlFor="phone">Nomor Telepon</Label>
									<Input
										id="phone"
										type="tel"
										value={data.phone}
										onChange={(e) => setData('phone', e.target.value)}
										placeholder="081234567890"
									/>
									<InputError className="mt-2" message={translateError('phone', errors.phone)} />
								</div>

								<div className="grid gap-2">
									<Label htmlFor="street_1">Alamat Jalan *</Label>
									<Input
										id="street_1"
										type="text"
										value={data.street_1}
										onChange={(e) => setData('street_1', e.target.value)}
										placeholder="Jl. Contoh No. 123"
									/>
									<InputError
										className="mt-2"
										message={translateError('street_1', errors.street_1)}
									/>
								</div>

								<div className="grid gap-2">
									<Label htmlFor="street_2">Alamat Jalan 2 (Opsional)</Label>
									<Input
										id="street_2"
										type="text"
										value={data.street_2}
										onChange={(e) => setData('street_2', e.target.value)}
										placeholder="Apartemen, Unit, dll."
									/>
									<InputError
										className="mt-2"
										message={translateError('street_2', errors.street_2)}
									/>
								</div>

								<div className="grid grid-cols-1 md:grid-cols-2 gap-4">
									<div className="grid gap-2">
										<Label htmlFor="city">Kota *</Label>
										<Input
											id="city"
											type="text"
											value={data.city}
											onChange={(e) => setData('city', e.target.value)}
											placeholder="Jakarta"
										/>
										<InputError className="mt-2" message={translateError('city', errors.city)} />
									</div>

									<div className="grid gap-2">
										<Label htmlFor="state">Provinsi (Opsional)</Label>
										<Input
											id="state"
											type="text"
											value={data.state}
											onChange={(e) => setData('state', e.target.value)}
											placeholder="DKI Jakarta"
										/>
										<InputError className="mt-2" message={translateError('state', errors.state)} />
									</div>
								</div>

								<div className="grid grid-cols-1 md:grid-cols-2 gap-4">
									<div className="grid gap-2">
										<Label htmlFor="country_code">Kode Negara *</Label>
										<Select
											value={data.country_code}
											onValueChange={(value) => setData('country_code', value)}
											disabled={processing}
										>
											<SelectTrigger id="country_code">
												<SelectValue placeholder="Pilih negara" />
											</SelectTrigger>
											<SelectContent>
												{countryCodes.map((country) => (
													<SelectItem key={country.code} value={country.code}>
														{country.name} ({country.code})
													</SelectItem>
												))}
											</SelectContent>
										</Select>
										<InputError
											className="mt-2"
											message={translateError('country_code', errors.country_code)}
										/>
									</div>

									<div className="grid gap-2">
										<Label htmlFor="postal_code">Kode Pos *</Label>
										<Input
											id="postal_code"
											type="text"
											value={data.postal_code}
											onChange={(e) => setData('postal_code', e.target.value)}
											placeholder="12345"
										/>
										<InputError
											className="mt-2"
											message={translateError('postal_code', errors.postal_code)}
										/>
									</div>
								</div>

								<div className="grid gap-2">
									<Label htmlFor="fax">Fax (Opsional)</Label>
									<Input
										id="fax"
										type="text"
										value={data.fax}
										onChange={(e) => setData('fax', e.target.value)}
										placeholder="02112345678"
									/>
									<InputError className="mt-2" message={translateError('fax', errors.fax)} />
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

					<DeleteUser />
				</div>
			</SettingsLayout>
		</AppLayout>
	);
}
