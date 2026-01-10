import React, { useState } from "react";
import { Head, Link } from "@inertiajs/react";
import GuestLayout from "@/layouts/guest-layout";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
	Server,
	HardDrive,
	Globe,
	Package,
	ArrowLeft,
	Check,
	Shield,
	Clock,
	Zap,
	Headphones,
	LogIn,
	Cpu,
	Database,
	Lock,
	MousePointerClick,
} from "lucide-react";

interface Plan {
	id: string;
	code: string;
	billing_cycle: string;
	price_cents: number;
	currency: string;
	trial_days?: number;
	setup_fee_cents: number;
}

interface ProductFeature {
	id: string;
	key: string;
	value: string;
	label?: string;
	unit?: string;
	display_order?: number;
}

interface Product {
	id: string;
	name: string;
	slug: string;
	type: string;
	status: string;
	metadata?: {
		description?: string;
		features?: string[];
		popular?: boolean;
		starting_price?: number;
	};
	features?: ProductFeature[];
}

interface GuestShowProps {
	product: Product;
	plans: Plan[];
	companyName?: string;
	companyLogo?: string;
}

const typeIcons: Record<string, React.ElementType> = {
	hosting_shared: HardDrive,
	vps: Server,
	addon: Package,
	domain: Globe,
};

const typeLabels: Record<string, string> = {
	hosting_shared: "Shared Hosting",
	vps: "VPS Server",
	addon: "Add-on",
	domain: "Domain",
};

function formatPrice(cents: number) {
	return new Intl.NumberFormat("id-ID", {
		style: "currency",
		currency: "IDR",
		minimumFractionDigits: 0,
	}).format(cents);
}

function formatPriceCompact(cents: number) {
	const value = cents;
	if (value >= 1000000) {
		return `${(value / 1000000).toFixed(value % 1000000 === 0 ? 0 : 1)}jt`;
	}
	if (value >= 1000) {
		return `${(value / 1000).toFixed(0)}rb`;
	}
	return value.toString();
}

export default function GuestShow({
	product,
	plans,
	companyName = "AbaHost",
	companyLogo,
}: GuestShowProps) {
	const [selectedPlan, setSelectedPlan] = useState<Plan | null>(
		plans.length > 0 ? plans[Math.floor(plans.length / 2)] : null
	);
	const [hoveredPlan, setHoveredPlan] = useState<string | null>(null);

	const TypeIcon = typeIcons[product.type] || Package;
	const typeLabel = typeLabels[product.type] || product.type;

	const highlights = [
		{ icon: Shield, text: "SSL Certificate", subtext: "Gratis" },
		{ icon: Clock, text: "Uptime", subtext: "99.9%" },
		{ icon: Zap, text: "Storage", subtext: "NVMe SSD" },
		{ icon: Headphones, text: "Support", subtext: "24/7" },
	];

	return (
		<GuestLayout showHeader companyName={companyName} companyLogo={companyLogo}>
			<Head title={`${product.name} — ${typeLabel}`} />

			{/* Minimal Hero */}
			<section className="relative">
				<div className="absolute inset-0 bg-gradient-to-b from-muted/40 to-background pointer-events-none" />

				<div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-16 relative">
					{/* Breadcrumb */}
					<nav className="flex items-center gap-2 text-sm mb-12">
						<Link
							href={route("catalog.guest")}
							className="text-muted-foreground hover:text-foreground transition-colors"
						>
							Layanan
						</Link>
						<span className="text-muted-foreground/50">/</span>
						<span className="text-muted-foreground">{typeLabel}</span>
						<span className="text-muted-foreground/50">/</span>
						<span className="font-medium">{product.name}</span>
					</nav>

					<div className="grid lg:grid-cols-5 gap-12 lg:gap-16">
						{/* Left: Product Info - 3 columns */}
						<div className="lg:col-span-3 space-y-10">
							{/* Product Header */}
							<div>
								<div className="flex items-center gap-3 mb-4">
									<div className="h-11 w-11 rounded-xl bg-foreground/5 border flex items-center justify-center">
										<TypeIcon className="h-5 w-5 text-foreground/70" />
									</div>
									<Badge
										variant="outline"
										className="font-normal text-xs tracking-wide uppercase"
									>
										{typeLabel}
									</Badge>
								</div>

								<h1 className="text-4xl sm:text-5xl font-semibold tracking-tight mb-4">
									{product.name}
								</h1>

								{product.metadata?.description && (
									<p className="text-lg text-muted-foreground leading-relaxed max-w-xl">
										{product.metadata.description}
									</p>
								)}
							</div>

							{/* Product Features (CPU, RAM, Bandwidth, etc) */}
							{product.features && product.features.length > 0 && (
								<div className="mb-8">
									<h2 className="text-sm font-medium text-muted-foreground uppercase tracking-wider mb-4">
										Spesifikasi
									</h2>
									<div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
										{product.features.map((feature) => (
											<div
												key={feature.id}
												className="p-3 rounded-lg border bg-muted/30"
											>
												<p className="text-xs text-muted-foreground mb-1">
													{feature.label || feature.key}
												</p>
												<p className="text-base font-semibold">
													{feature.value}
													{feature.unit && (
														<span className="text-sm text-muted-foreground font-normal ml-1">
															{feature.unit}
														</span>
													)}
												</p>
											</div>
										))}
									</div>
								</div>
							)}

							{/* Highlights - Horizontal */}
							<div className="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8">
								{highlights.map((item, i) => {
									const Icon = item.icon;
									return (
										<div
											key={i}
											className="group p-4 rounded-xl border bg-background hover:border-foreground/20 transition-colors"
										>
											<Icon className="h-5 w-5 mb-3 text-muted-foreground group-hover:text-foreground transition-colors" />
											<p className="text-xs text-muted-foreground">
												{item.text}
											</p>
											<p className="font-semibold">{item.subtext}</p>
										</div>
									);
								})}
							</div>

							{/* Metadata Features (descriptive features) */}
							{product.metadata?.features &&
								product.metadata.features.length > 0 && (
									<div>
										<h2 className="text-sm font-medium text-muted-foreground uppercase tracking-wider mb-6">
											Termasuk dalam paket
										</h2>
										<div className="grid sm:grid-cols-2 gap-x-8 gap-y-4">
											{product.metadata.features.map((feature, idx) => (
												<div key={idx} className="flex items-start gap-3 group">
													<div className="h-5 w-5 rounded-full bg-emerald-500/10 flex items-center justify-center flex-shrink-0 mt-0.5">
														<Check className="h-3 w-3 text-emerald-600" />
													</div>
													<span className="text-sm text-muted-foreground group-hover:text-foreground transition-colors">
														{feature}
													</span>
												</div>
											))}
										</div>
									</div>
								)}

							{/* Trust Section */}
							<div className="pt-8 border-t">
								<div className="flex flex-wrap items-center gap-6 text-sm text-muted-foreground">
									<div className="flex items-center gap-2">
										<Lock className="h-4 w-4" />
										<span>Pembayaran Aman</span>
									</div>
									<div className="flex items-center gap-2">
										<Database className="h-4 w-4" />
										<span>Backup Otomatis</span>
									</div>
									<div className="flex items-center gap-2">
										<Cpu className="h-4 w-4" />
										<span>Server Indonesia</span>
									</div>
								</div>
							</div>
						</div>

						{/* Right: Pricing Card - 2 columns */}
						<div className="lg:col-span-2">
							<div className="lg:sticky lg:top-24">
								<div className="rounded-2xl border bg-background shadow-sm overflow-hidden">
									{/* Card Header */}
									<div className="p-6 border-b bg-muted/30">
										<div className="flex items-center justify-between mb-1">
											<span className="text-sm text-muted-foreground">
												Mulai dari
											</span>
											{plans.length > 1 && (
												<span className="text-xs text-muted-foreground">
													{plans.length} paket tersedia
												</span>
											)}
										</div>
										<div className="flex items-baseline gap-1">
											<span className="text-3xl font-bold">
												{selectedPlan
													? formatPrice(selectedPlan.price_cents)
													: "—"}
											</span>
											<span className="text-muted-foreground text-sm">
												/{selectedPlan?.billing_cycle || "bulan"}
											</span>
										</div>
									</div>

									{/* Plan Selector */}
									{plans.length > 1 && (
										<div className="p-4 border-b">
											<div className="grid grid-cols-3 gap-2">
												{plans.map((plan) => {
													const isSelected = selectedPlan?.id === plan.id;
													const isHovered = hoveredPlan === plan.id;

													return (
														<button
															key={plan.id}
															onClick={() => setSelectedPlan(plan)}
															onMouseEnter={() => setHoveredPlan(plan.id)}
															onMouseLeave={() => setHoveredPlan(null)}
															className={`relative p-3 rounded-lg border-2 transition-all text-left ${
																isSelected
																	? "border-foreground bg-foreground/5"
																	: isHovered
																	? "border-muted-foreground/30"
																	: "border-transparent bg-muted/50"
															}`}
														>
															{plan.trial_days && plan.trial_days > 0 && (
																<div className="absolute -top-2 -right-2">
																	<span className="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-500 text-white">
																		Trial
																	</span>
																</div>
															)}
															<p className="font-medium text-sm truncate">
																{plan.code}
															</p>
															<p className="text-xs text-muted-foreground truncate">
																{formatPriceCompact(plan.price_cents)}
															</p>
														</button>
													);
												})}
											</div>
										</div>
									)}

									{/* Selected Plan Details */}
									{selectedPlan && (
										<div className="p-6 space-y-4">
											<div className="space-y-2 text-sm">
												<div className="flex justify-between">
													<span className="text-muted-foreground">Paket</span>
													<span className="font-medium">
														{selectedPlan.code}
													</span>
												</div>
												<div className="flex justify-between">
													<span className="text-muted-foreground">Periode</span>
													<span>{selectedPlan.billing_cycle}</span>
												</div>
												{selectedPlan.setup_fee_cents > 0 && (
													<div className="flex justify-between">
														<span className="text-muted-foreground">
															Biaya Setup
														</span>
														<span>
															{formatPrice(selectedPlan.setup_fee_cents)}
														</span>
													</div>
												)}
												<div className="pt-2 border-t flex justify-between font-medium">
													<span>Total</span>
													<span>
														{formatPrice(
															selectedPlan.price_cents +
																(selectedPlan.setup_fee_cents || 0)
														)}
													</span>
												</div>
											</div>

											<Link href={route("register")} className="block">
												<Button
													className="w-full h-12 text-base font-medium"
													variant="gradient"
												>
													<MousePointerClick className="h-4 w-4 mr-2" />
													Pesan Sekarang
												</Button>
											</Link>

											<p className="text-xs text-center text-muted-foreground">
												Daftar gratis untuk melanjutkan pemesanan
											</p>
										</div>
									)}

									{/* Login Link */}
									<div className="p-4 bg-muted/30 border-t">
										<Link
											href={route("login")}
											className="flex items-center justify-center gap-2 text-sm text-muted-foreground hover:text-foreground transition-colors"
										>
											<LogIn className="h-4 w-4" />
											Sudah punya akun? Masuk
										</Link>
									</div>
								</div>

								{/* Company Badge */}
								{companyLogo && (
									<div className="mt-6 flex items-center justify-center gap-2 text-xs text-muted-foreground">
										<span>Dipersembahkan oleh</span>
										<img
											src={companyLogo}
											alt={companyName}
											className="h-5 w-auto opacity-60"
										/>
									</div>
								)}
							</div>
						</div>
					</div>
				</div>
			</section>

			{/* Bottom CTA */}
			<section className="border-t bg-muted/20">
				<div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
					<div className="flex flex-col sm:flex-row items-center justify-between gap-6">
						<div>
							<h2 className="text-xl font-semibold mb-1">
								Butuh bantuan memilih paket?
							</h2>
							<p className="text-muted-foreground">
								Tim kami siap membantu 24/7
							</p>
						</div>
						<div className="flex items-center gap-3">
							<Link href={route("catalog.guest")}>
								<Button variant="outline" size="lg">
									<ArrowLeft className="h-4 w-4 mr-2" />
									Lihat Semua Layanan
								</Button>
							</Link>
						</div>
					</div>
				</div>
			</section>

			{/* Minimal Footer Note */}
			<section className="border-t">
				<div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
					<div className="flex flex-wrap items-center justify-center gap-x-8 gap-y-2 text-xs text-muted-foreground">
						<span>
							© {new Date().getFullYear()} {companyName}
						</span>
						<span className="hidden sm:inline">•</span>
						<span>Aktivasi Instan</span>
						<span className="hidden sm:inline">•</span>
						<span>Garansi 30 Hari</span>
						<span className="hidden sm:inline">•</span>
						<span>Server Indonesia</span>
					</div>
				</div>
			</section>
		</GuestLayout>
	);
}
