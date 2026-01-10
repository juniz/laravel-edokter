import React from "react";
import { Head, Link } from "@inertiajs/react";
import GuestLayout from "@/layouts/guest-layout";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
	Server,
	HardDrive,
	Globe,
	Package,
	ArrowRight,
	CheckCircle2,
	Star,
	Shield,
	Clock,
	Zap,
	Headphones,
	Database,
	Lock,
	Cpu,
	ChevronRight,
	Sparkles,
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
	plans?: Plan[];
	features?: ProductFeature[];
}

interface CatalogGuestProps {
	products: Product[];
	companyName?: string;
	companyLogo?: string;
}

function getProductTypeConfig(type: string) {
	const config: Record<
		string,
		{
			icon: React.ElementType;
			color: string;
			bgColor: string;
			label: string;
			gradient: string;
		}
	> = {
		hosting_shared: {
			icon: HardDrive,
			color: "text-blue-600",
			bgColor: "from-blue-500 to-cyan-500",
			label: "Shared Hosting",
			gradient: "from-blue-500/10 to-cyan-500/10",
		},
		vps: {
			icon: Server,
			color: "text-purple-600",
			bgColor: "from-purple-500 to-pink-500",
			label: "VPS",
			gradient: "from-purple-500/10 to-pink-500/10",
		},
		addon: {
			icon: Package,
			color: "text-emerald-600",
			bgColor: "from-emerald-500 to-green-500",
			label: "Addon",
			gradient: "from-emerald-500/10 to-green-500/10",
		},
		domain: {
			icon: Globe,
			color: "text-orange-600",
			bgColor: "from-orange-500 to-amber-500",
			label: "Domain",
			gradient: "from-orange-500/10 to-amber-500/10",
		},
	};
	return (
		config[type] || {
			icon: Package,
			color: "text-gray-600",
			bgColor: "from-gray-500 to-slate-500",
			label: type.replace("_", " ").toUpperCase(),
			gradient: "from-gray-500/10 to-slate-500/10",
		}
	);
}

function formatPrice(amount: number) {
	return new Intl.NumberFormat("id-ID", {
		style: "currency",
		currency: "IDR",
		minimumFractionDigits: 0,
	}).format(amount);
}

const features = [
	{
		icon: Shield,
		title: "SSL Gratis",
		description: "Sertifikat SSL gratis untuk semua domain Anda",
		color: "from-emerald-500 to-green-500",
	},
	{
		icon: Clock,
		title: "Uptime 99.9%",
		description: "Jaminan server online dengan SLA terjamin",
		color: "from-blue-500 to-cyan-500",
	},
	{
		icon: Zap,
		title: "NVMe SSD",
		description: "Storage super cepat, 10x lebih kencang dari HDD",
		color: "from-purple-500 to-pink-500",
	},
	{
		icon: Headphones,
		title: "Support 24/7",
		description: "Tim support siap membantu kapan saja",
		color: "from-orange-500 to-amber-500",
	},
	{
		icon: Database,
		title: "Backup Harian",
		description: "Data Anda aman dengan backup otomatis setiap hari",
		color: "from-rose-500 to-red-500",
	},
	{
		icon: Lock,
		title: "Keamanan Tinggi",
		description: "Firewall dan proteksi DDoS tingkat enterprise",
		color: "from-indigo-500 to-violet-500",
	},
];

export default function CatalogGuest({
	products,
	companyName = "AbaHost",
	companyLogo,
}: CatalogGuestProps) {
	// Group products by type
	const groupedProducts = products.reduce((acc, product) => {
		if (!acc[product.type]) {
			acc[product.type] = [];
		}
		acc[product.type].push(product);
		return acc;
	}, {} as Record<string, Product[]>);

	const productTypes = Object.keys(groupedProducts);

	// Get featured products (popular ones or first 3)
	const featuredProducts = products
		.filter((p) => p.metadata?.popular)
		.slice(0, 3);
	const displayFeatured =
		featuredProducts.length > 0 ? featuredProducts : products.slice(0, 3);

	return (
		<GuestLayout showHeader companyName={companyName} companyLogo={companyLogo}>
			<Head title="Layanan Hosting Terbaik" />

			{/* Hero Section */}
			<section className="relative overflow-hidden">
				{/* Background Pattern */}
				<div className="absolute inset-0 bg-gradient-to-br from-[var(--gradient-start)]/5 via-background to-[var(--gradient-end)]/5" />
				<div className="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0zNiAxOGMxLjY1NyAwIDMtMS4zNDMgMy0zczEuMzQzLTMgMy0zIDMgMS4zNDMgMyAzLTEuMzQzIDMtMyAzLTMtMS4zNDMtMy0zIDEuMzQzLTMgMy0zeiIgZmlsbD0iY3VycmVudENvbG9yIiBmaWxsLW9wYWNpdHk9Ii4wNSIvPjwvZz48L3N2Zz4=')] opacity-50" />

				<div className="max-w-7xl mx-auto relative px-4 md:px-6 lg:px-8 py-20 md:py-32">
					<div className="max-w-4xl mx-auto text-center">
						{/* Badge */}
						<div className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gradient-to-r from-[var(--gradient-start)]/10 to-[var(--gradient-end)]/10 border border-[var(--gradient-start)]/20 mb-6">
							<Sparkles className="h-4 w-4 text-[var(--gradient-start)]" />
							<span className="text-sm font-medium">
								Hosting Premium #1 Indonesia
							</span>
						</div>

						{/* Headline */}
						<h1 className="text-4xl md:text-5xl lg:text-6xl font-bold tracking-tight mb-6">
							Hosting <span className="text-gradient-primary">Super Cepat</span>
							<br />
							untuk Bisnis Anda
						</h1>

						<p className="text-lg md:text-xl text-muted-foreground max-w-2xl mx-auto mb-8">
							Server berkualitas tinggi dengan teknologi terbaru, performa
							optimal, dan dukungan ahli 24/7 untuk kesuksesan online Anda.
						</p>

						{/* CTA Buttons */}
						<div className="flex flex-col sm:flex-row items-center justify-center gap-4">
							<Link href="#pricing">
								<Button variant="gradient" size="lg" className="text-base px-8">
									Lihat Paket
									<ArrowRight className="h-5 w-5 ml-2" />
								</Button>
							</Link>
							<Link href={route("register")}>
								<Button variant="outline" size="lg" className="text-base px-8">
									Daftar Gratis
								</Button>
							</Link>
						</div>

						{/* Stats */}
						<div className="grid grid-cols-3 gap-8 mt-16 pt-8 border-t max-w-2xl mx-auto">
							<div>
								<p className="text-3xl md:text-4xl font-bold text-gradient-primary">
									99.9%
								</p>
								<p className="text-sm text-muted-foreground mt-1">Uptime</p>
							</div>
							<div>
								<p className="text-3xl md:text-4xl font-bold text-gradient-primary">
									24/7
								</p>
								<p className="text-sm text-muted-foreground mt-1">Support</p>
							</div>
							<div>
								<p className="text-3xl md:text-4xl font-bold text-gradient-primary">
									10K+
								</p>
								<p className="text-sm text-muted-foreground mt-1">Pelanggan</p>
							</div>
						</div>
					</div>
				</div>
			</section>

			{/* Features Section */}
			<section id="features" className="py-20 bg-muted/30">
				<div className="max-w-7xl mx-auto px-4 md:px-6 lg:px-8">
					<div className="text-center max-w-2xl mx-auto mb-12">
						<Badge variant="outline" className="mb-4">
							Keunggulan
						</Badge>
						<h2 className="text-3xl md:text-4xl font-bold mb-4">
							Mengapa Memilih{" "}
							<span className="text-gradient-primary">Kami?</span>
						</h2>
						<p className="text-muted-foreground">
							Kami menyediakan layanan hosting terbaik dengan teknologi terkini
						</p>
					</div>

					<div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
						{features.map((feature, index) => {
							const Icon = feature.icon;
							return (
								<div
									key={index}
									className="group relative p-6 rounded-2xl bg-background border hover:border-[var(--gradient-start)]/30 transition-all duration-300 hover:shadow-lg hover:shadow-[var(--gradient-start)]/5"
								>
									<div
										className={`h-12 w-12 rounded-xl bg-gradient-to-br ${feature.color} flex items-center justify-center mb-4 group-hover:scale-110 transition-transform`}
									>
										<Icon className="h-6 w-6 text-white" />
									</div>
									<h3 className="font-semibold text-lg mb-2">
										{feature.title}
									</h3>
									<p className="text-sm text-muted-foreground">
										{feature.description}
									</p>
								</div>
							);
						})}
					</div>
				</div>
			</section>

			{/* Featured Products */}
			{displayFeatured.length > 0 && (
				<section className="py-20">
					<div className="max-w-7xl mx-auto px-4 md:px-6 lg:px-8">
						<div className="text-center max-w-2xl mx-auto mb-12">
							<Badge variant="outline" className="mb-4">
								Populer
							</Badge>
							<h2 className="text-3xl md:text-4xl font-bold mb-4">
								Produk <span className="text-gradient-primary">Terlaris</span>
							</h2>
							<p className="text-muted-foreground">
								Pilihan terpopuler dari pelanggan kami
							</p>
						</div>

						<div className="grid grid-cols-1 md:grid-cols-3 gap-6">
							{displayFeatured.map((product, index) => {
								const typeConfig = getProductTypeConfig(product.type);
								const TypeIcon = typeConfig.icon;
								const isCenter = index === 1;

								return (
									<div
										key={product.id}
										className={`relative rounded-2xl border bg-background overflow-hidden transition-all duration-300 hover:shadow-xl ${
											isCenter
												? "md:scale-105 md:shadow-lg ring-2 ring-[var(--gradient-start)]/30"
												: "hover:border-[var(--gradient-start)]/30"
										}`}
									>
										{/* Popular Tag */}
										{(product.metadata?.popular || isCenter) && (
											<div className="absolute top-4 right-4">
												<Badge className="bg-gradient-to-r from-[var(--gradient-start)] to-[var(--gradient-end)] text-white border-0">
													<Star className="h-3 w-3 mr-1" />
													Populer
												</Badge>
											</div>
										)}

										{/* Header */}
										<div
											className={`p-6 bg-gradient-to-br ${typeConfig.gradient}`}
										>
											<div
												className={`h-14 w-14 rounded-xl bg-gradient-to-br ${typeConfig.bgColor} flex items-center justify-center mb-4`}
											>
												<TypeIcon className="h-7 w-7 text-white" />
											</div>
											<h3 className="font-bold text-xl mb-1">{product.name}</h3>
											<Badge variant="outline">{typeConfig.label}</Badge>
										</div>

										{/* Content */}
										<div className="p-6">
											{product.metadata?.description && (
												<p className="text-sm text-muted-foreground mb-4 line-clamp-2">
													{product.metadata.description}
												</p>
											)}

											{/* Product Features (CPU, RAM, Bandwidth, etc) */}
											{product.features && product.features.length > 0 && (
												<div className="mb-4 p-3 bg-muted/50 rounded-lg">
													<div className="grid grid-cols-2 gap-2">
														{product.features.map((feature) => (
															<div
																key={feature.id}
																className="flex items-center gap-1.5 text-xs"
															>
																<span className="text-muted-foreground font-medium">
																	{feature.label || feature.key}:
																</span>
																<span className="font-semibold">
																	{feature.value}
																	{feature.unit && ` ${feature.unit}`}
																</span>
															</div>
														))}
													</div>
												</div>
											)}

											{/* Metadata Features (descriptive features) */}
											{product.metadata?.features && (
												<ul className="space-y-2 mb-6">
													{product.metadata.features
														.slice(0, 4)
														.map((feature, idx) => (
															<li
																key={idx}
																className="flex items-start gap-2 text-sm"
															>
																<CheckCircle2 className="h-4 w-4 text-emerald-500 mt-0.5 flex-shrink-0" />
																<span className="text-muted-foreground">
																	{feature}
																</span>
															</li>
														))}
												</ul>
											)}

											{/* Price */}
											{product.metadata?.starting_price && (
												<div className="mb-4">
													<p className="text-xs text-muted-foreground">
														Mulai dari
													</p>
													<p className="text-2xl font-bold">
														{formatPrice(product.metadata.starting_price)}
														<span className="text-sm font-normal text-muted-foreground">
															/bulan
														</span>
													</p>
												</div>
											)}

											{/* CTA */}
											<Link href={route("catalog.guest.show", product.slug)}>
												<Button
													variant={isCenter ? "gradient" : "outline"}
													className="w-full"
												>
													Lihat Detail
													<ChevronRight className="h-4 w-4 ml-1" />
												</Button>
											</Link>
										</div>
									</div>
								);
							})}
						</div>
					</div>
				</section>
			)}

			{/* All Products Section */}
			<section id="pricing" className="py-20 bg-muted/30">
				<div className="max-w-7xl mx-auto px-4 md:px-6 lg:px-8">
					<div className="text-center max-w-2xl mx-auto mb-12">
						<Badge variant="outline" className="mb-4">
							Layanan
						</Badge>
						<h2 className="text-3xl md:text-4xl font-bold mb-4">
							Semua <span className="text-gradient-primary">Layanan</span> Kami
						</h2>
						<p className="text-muted-foreground">
							Temukan paket yang sesuai dengan kebutuhan Anda
						</p>
					</div>

					{productTypes.length === 0 ? (
						<div className="text-center py-12">
							<div className="h-16 w-16 rounded-2xl bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)] flex items-center justify-center mx-auto mb-4">
								<Package className="h-8 w-8 text-white" />
							</div>
							<h3 className="text-lg font-semibold mb-2">Tidak Ada Produk</h3>
							<p className="text-muted-foreground">
								Tidak ada produk tersedia saat ini. Silakan cek kembali nanti.
							</p>
						</div>
					) : (
						<div className="space-y-12">
							{productTypes.map((type) => {
								const typeConfig = getProductTypeConfig(type);
								const TypeIcon = typeConfig.icon;
								const typeProducts = groupedProducts[type];

								return (
									<div key={type}>
										{/* Category Header */}
										<div className="flex items-center gap-3 mb-6">
											<div
												className={`h-10 w-10 rounded-xl bg-gradient-to-br ${typeConfig.bgColor} flex items-center justify-center`}
											>
												<TypeIcon className="h-5 w-5 text-white" />
											</div>
											<div>
												<h3 className="font-bold text-xl">
													{typeConfig.label}
												</h3>
												<p className="text-sm text-muted-foreground">
													{typeProducts.length} paket tersedia
												</p>
											</div>
										</div>

										{/* Products Grid */}
										<div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
											{typeProducts.map((product) => {
												const isPopular = product.metadata?.popular;

												return (
													<div
														key={product.id}
														className={`group relative rounded-xl border bg-background p-5 transition-all duration-300 hover:shadow-lg hover:border-[var(--gradient-start)]/30 ${
															isPopular
																? "ring-2 ring-[var(--gradient-start)]/30"
																: ""
														}`}
													>
														{isPopular && (
															<div className="absolute -top-2 -right-2">
																<Badge className="bg-gradient-to-r from-[var(--gradient-start)] to-[var(--gradient-end)] text-white border-0 text-xs">
																	<Star className="h-3 w-3 mr-1" />
																	Populer
																</Badge>
															</div>
														)}

														<div className="flex items-start gap-3 mb-3">
															<div
																className={`h-10 w-10 rounded-lg bg-gradient-to-br ${typeConfig.bgColor} flex items-center justify-center flex-shrink-0`}
															>
																<TypeIcon className="h-5 w-5 text-white" />
															</div>
															<div className="flex-1 min-w-0">
																<h4 className="font-semibold truncate">
																	{product.name}
																</h4>
																<p className="text-xs text-muted-foreground">
																	{typeConfig.label}
																</p>
															</div>
														</div>

														{product.metadata?.description && (
															<p className="text-xs text-muted-foreground line-clamp-2 mb-3">
																{product.metadata.description}
															</p>
														)}

														{/* Product Features (CPU, RAM, Bandwidth, etc) */}
														{product.features && product.features.length > 0 && (
															<div className="mb-4">
																<div className="grid grid-cols-2 gap-2">
																	{product.features
																		.slice(0, 4)
																		.map((feature) => (
																			<div
																				key={feature.id}
																				className="flex items-center gap-1.5 text-xs"
																			>
																				<span className="text-muted-foreground font-medium">
																					{feature.label || feature.key}:
																				</span>
																				<span className="font-semibold">
																					{feature.value}
																					{feature.unit && ` ${feature.unit}`}
																				</span>
																			</div>
																		))}
																</div>
															</div>
														)}

														{/* Metadata Features (descriptive features) */}
														{product.metadata?.features &&
															product.metadata.features.length > 0 && (
																<ul className="space-y-1 mb-4">
																	{product.metadata.features
																		.slice(0, 3)
																		.map((feature, idx) => (
																			<li
																				key={idx}
																				className="flex items-start gap-1.5 text-xs"
																			>
																				<CheckCircle2 className="h-3 w-3 text-emerald-500 mt-0.5 flex-shrink-0" />
																				<span className="text-muted-foreground line-clamp-1">
																					{feature}
																				</span>
																			</li>
																		))}
																</ul>
															)}

														{/* Price & CTA */}
														<div className="pt-3 border-t">
															{product.metadata?.starting_price && (
																<div className="mb-3">
																	<p className="text-xs text-muted-foreground">
																		Mulai dari
																	</p>
																	<p className="text-lg font-bold">
																		{formatPrice(
																			product.metadata.starting_price
																		)}
																		<span className="text-xs font-normal text-muted-foreground">
																			/bln
																		</span>
																	</p>
																</div>
															)}
															<Link
																href={route("catalog.guest.show", product.slug)}
															>
																<Button
																	variant="gradient"
																	size="sm"
																	className="w-full"
																>
																	Lihat Detail
																	<ChevronRight className="h-3 w-3 ml-1" />
																</Button>
															</Link>
														</div>
													</div>
												);
											})}
										</div>
									</div>
								);
							})}
						</div>
					)}
				</div>
			</section>

			{/* CTA Section */}
			<section className="py-20">
				<div className="max-w-7xl mx-auto px-4 md:px-6 lg:px-8">
					<div className="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)] p-8 md:p-12 text-center">
						{/* Background Pattern */}
						<div className="absolute inset-0 opacity-10">
							<div className="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0zNiAxOGMxLjY1NyAwIDMtMS4zNDMgMy0zczEuMzQzLTMgMy0zIDMgMS4zNDMgMyAzLTEuMzQzIDMtMyAzLTMtMS4zNDMtMy0zIDEuMzQzLTMgMy0zeiIgZmlsbD0iI2ZmZiIgZmlsbC1vcGFjaXR5PSIuMiIvPjwvZz48L3N2Zz4=')]" />
						</div>

						<div className="relative">
							<div className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/20 mb-6">
								<Cpu className="h-4 w-4 text-white" />
								<span className="text-sm font-medium text-white">
									Siap Memulai?
								</span>
							</div>

							<h2 className="text-3xl md:text-4xl font-bold text-white mb-4">
								Mulai Perjalanan Digital Anda Sekarang
							</h2>

							<p className="text-white/80 max-w-xl mx-auto mb-8">
								Daftar sekarang dan dapatkan hosting berkualitas tinggi untuk
								website Anda. Setup cepat, performa optimal, support 24/7.
							</p>

							<div className="flex flex-col sm:flex-row items-center justify-center gap-4">
								<Link href={route("register")}>
									<Button
										size="lg"
										className="bg-white text-[var(--gradient-start)] hover:bg-white/90 text-base px-8"
									>
										Daftar Sekarang
										<ArrowRight className="h-5 w-5 ml-2" />
									</Button>
								</Link>
								<Link href={route("login")}>
									<Button
										variant="outline"
										size="lg"
										className="border-2 border-white/80 bg-white/10 backdrop-blur-sm text-white hover:bg-white/20 hover:border-white text-base px-8 font-medium"
									>
										Sudah Punya Akun?
									</Button>
								</Link>
							</div>
						</div>
					</div>
				</div>
			</section>
		</GuestLayout>
	);
}
