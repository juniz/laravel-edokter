import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/input-error';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { CreditCard, ShieldCheck } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Payment Gateway', href: '/settings/payment-gateway' }];

interface PaymentGatewaySettings {
	default_gateway: 'manual' | 'midtrans' | 'xendit' | 'tripay';
	midtrans_enabled: boolean;
	midtrans_is_production: boolean;
	midtrans_verify_webhook_signature: boolean;
}

interface Props {
	settings: PaymentGatewaySettings;
	env: {
		midtrans_has_server_key: boolean;
		midtrans_has_client_key: boolean;
	};
}

export default function PaymentGateway({ settings, env }: Props) {
	const { data, setData, put, processing, errors } = useForm<PaymentGatewaySettings>({
		default_gateway: settings.default_gateway ?? 'manual',
		midtrans_enabled: settings.midtrans_enabled ?? true,
		midtrans_is_production: settings.midtrans_is_production ?? false,
		midtrans_verify_webhook_signature:
			settings.midtrans_verify_webhook_signature ?? true,
	});

	const handleSubmit = (e: React.FormEvent) => {
		e.preventDefault();
		put(route('settings.payment-gateway.update'), { preserveScroll: true });
	};

	const isMidtransKeyReady = env.midtrans_has_server_key && env.midtrans_has_client_key;

	return (
		<AppLayout breadcrumbs={breadcrumbs}>
			<Head title="Payment Gateway" />
			<div className="flex-1 p-4 md:p-6">
				<div className="max-w-3xl mx-auto space-y-6">
					<div>
						<h1 className="text-3xl font-bold tracking-tight">Payment Gateway</h1>
						<p className="text-muted-foreground mt-2">
							Atur gateway pembayaran yang aktif dan mode Midtrans
						</p>
					</div>

					<Card>
						<CardHeader>
							<CardTitle className="flex items-center gap-2">
								<CreditCard className="h-5 w-5" />
								Konfigurasi Gateway
							</CardTitle>
							<CardDescription>
								Default gateway dipakai untuk checkout dan pembuatan pembayaran
							</CardDescription>
						</CardHeader>
						<Separator />
						<CardContent className="pt-6">
							<form onSubmit={handleSubmit} className="space-y-6">
								<div className="space-y-2">
									<Label>Default Gateway</Label>
									<Select
										value={data.default_gateway}
										onValueChange={(value) =>
											setData(
												'default_gateway',
												value as PaymentGatewaySettings['default_gateway']
											)
										}
									>
										<SelectTrigger>
											<SelectValue />
										</SelectTrigger>
										<SelectContent>
											<SelectItem value="manual">Manual Transfer</SelectItem>
											<SelectItem value="midtrans">Midtrans</SelectItem>
											<SelectItem value="xendit">Xendit</SelectItem>
											<SelectItem value="tripay">Tripay</SelectItem>
										</SelectContent>
									</Select>
									<InputError message={errors.default_gateway} />
								</div>

								<Separator />

								<div className="space-y-4">
									<div className="flex items-start space-x-2">
										<Checkbox
											id="midtrans_enabled"
											checked={data.midtrans_enabled}
											onCheckedChange={(checked) =>
												setData('midtrans_enabled', checked === true)
											}
										/>
										<div className="flex-1">
											<Label htmlFor="midtrans_enabled" className="cursor-pointer">
												Aktifkan Midtrans
											</Label>
											<div className="text-xs text-muted-foreground mt-1 space-y-1">
												<div>
													Server Key: {env.midtrans_has_server_key ? 'Terpasang' : 'Belum'}
												</div>
												<div>
													Client Key: {env.midtrans_has_client_key ? 'Terpasang' : 'Belum'}
												</div>
											</div>
											{!isMidtransKeyReady ? (
												<div className="text-xs text-amber-600 dark:text-amber-400 mt-2">
													Midtrans tidak akan bisa dipakai sebelum Server Key dan Client Key
													terpasang di environment.
												</div>
											) : null}
										</div>
									</div>
									<InputError message={errors.midtrans_enabled} />

									<div className="flex items-start space-x-2">
										<Checkbox
											id="midtrans_is_production"
											checked={data.midtrans_is_production}
											onCheckedChange={(checked) =>
												setData('midtrans_is_production', checked === true)
											}
										/>
										<div className="flex-1">
											<Label
												htmlFor="midtrans_is_production"
												className="cursor-pointer"
											>
												Mode Production
											</Label>
											<p className="text-xs text-muted-foreground mt-1">
												Nonaktifkan untuk Sandbox (uji coba)
											</p>
										</div>
									</div>
									<InputError message={errors.midtrans_is_production} />

									<div className="flex items-start space-x-2">
										<Checkbox
											id="midtrans_verify_webhook_signature"
											checked={data.midtrans_verify_webhook_signature}
											onCheckedChange={(checked) =>
												setData('midtrans_verify_webhook_signature', checked === true)
											}
										/>
										<div className="flex-1">
											<Label
												htmlFor="midtrans_verify_webhook_signature"
												className="cursor-pointer flex items-center gap-2"
											>
												<ShieldCheck className="h-4 w-4" />
												Verifikasi Signature Webhook
											</Label>
											<p className="text-xs text-muted-foreground mt-1">
												Disarankan tetap aktif untuk keamanan
											</p>
										</div>
									</div>
									<InputError message={errors.midtrans_verify_webhook_signature} />
								</div>

								<div className="flex justify-end">
									<Button type="submit" disabled={processing}>
										Simpan
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

