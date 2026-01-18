import React from "react";
import { Head, Link } from "@inertiajs/react";
import GuestLayout from "@/layouts/guest-layout";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent } from "@/components/ui/card";
import {
	Server,
	HardDrive,
	Globe,
	Package,
	ArrowRight,
	Check,
	Tag,
	Lock,
	Mail,
	HardDriveIcon,
	Cpu,
	MemoryStick,
	Wifi,
	Folder,
	Database,
	Cloud,
	Activity,
	CheckCircle2,
	Star,
} from "lucide-react";

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
	price_cents: number;
	currency: string;
	annual_discount_percent?: number;
	metadata?: {
		description?: string;
		features?: string[];
		popular?: boolean;
	};
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
	}).format(amount); // Amount is in raw IDR
}

// Map feature key/label to appropriate icon
function getFeatureIcon(key: string, label?: string): React.ComponentType<{ className?: string }> {
	const searchText = (label || key).toLowerCase();
	
	// Website/Domain related
	if (searchText.includes('website') || searchText.includes('domain') || searchText.includes('site')) {
		return Globe;
	}
	
	// SSL/Security
	if (searchText.includes('ssl') || searchText.includes('certificate') || searchText.includes('security')) {
		return Lock;
	}
	
	// Email
	if (searchText.includes('email') || searchText.includes('mail')) {
		return Mail;
	}
	
	// Storage/Disk
	if (searchText.includes('storage') || searchText.includes('disk') || searchText.includes('space') || searchText.includes('gb')) {
		return HardDriveIcon;
	}
	
	// CPU
	if (searchText.includes('cpu') || searchText.includes('core') || searchText.includes('processor')) {
		return Cpu;
	}
	
	// RAM/Memory
	if (searchText.includes('ram') || searchText.includes('memory')) {
		return MemoryStick;
	}
	
	// Bandwidth/Network
	if (searchText.includes('bandwidth') || searchText.includes('transfer') || searchText.includes('network') || searchText.includes('mbps')) {
		return Wifi;
	}
	
	// Backup
	if (searchText.includes('backup')) {
		return Folder;
	}
	
	// Database
	if (searchText.includes('database') || searchText.includes('db') || searchText.includes('mysql')) {
		return Database;
	}
	
	// Cloud/CDN
	if (searchText.includes('cloud') || searchText.includes('cdn')) {
		return Cloud;
	}
	
	// Performance/Monitoring
	if (searchText.includes('uptime') || searchText.includes('monitoring') || searchText.includes('performance')) {
		return Activity;
	}
	
	// Default icons based on common patterns
	if (searchText.includes('free') || searchText.includes('gratis')) {
		return Star;
	}
	
	// Default fallback
	return CheckCircle2;
}

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

	// Get all products for the main display
	const displayFeatured = products;

	return (
		<GuestLayout showHeader companyName={companyName} companyLogo={companyLogo}>
			<Head title="Layanan Hosting Terbaik" />

			{/* Products Section (Pricing) - Langsung tampilkan produk */}
			{displayFeatured.length > 0 && (
				<section
					id="pricing"
					className="py-12 md:py-16 relative overflow-hidden"
				>
					{/* Background decoration */}
					<div className="absolute top-1/2 left-0 w-full h-full bg-gradient-to-b from-transparent to-muted/20 -z-10" />

					<div className="max-w-7xl mx-auto px-4 md:px-6 lg:px-8">
						<div className="text-center max-w-2xl mx-auto mb-8 md:mb-12">
							<h1 className="text-3xl md:text-4xl lg:text-5xl font-bold mb-3 text-foreground">
								Pilihan Paket <span className="text-primary">Hosting</span>
							</h1>
							<p className="text-muted-foreground text-base md:text-lg">
								Transparan, tanpa biaya tersembunyi. Pilih sesuai kebutuhan
								Anda.
							</p>
						</div>

						<div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
							{displayFeatured.map((product) => {
								const isPopular = product.metadata?.popular;
								const annualDiscountPercent =
									product.annual_discount_percent ?? 0;
								const hasAnnualDiscount = annualDiscountPercent > 0;

								// Calculate annual savings
								const monthlyPrice = product.price_cents;
								const annualPriceWithoutDiscount = monthlyPrice * 12;
								const annualDiscountAmount = Math.round(
									annualPriceWithoutDiscount * (annualDiscountPercent / 100)
								);
								const annualPriceWithDiscount =
									annualPriceWithoutDiscount - annualDiscountAmount;

								return (
									<Card
										key={product.id}
										className={`relative overflow-hidden transition-all duration-300 hover:shadow-2xl flex flex-col ${
											isPopular
												? "border-2 border-primary shadow-xl scale-105 z-10"
												: "border hover:border-primary/50"
										}`}
									>
										{/* Popular Badge */}
										{isPopular && (
											<div className="absolute top-0 left-0 z-20">
												<div className="relative">
													<div className="absolute -left-12 top-6 w-40 bg-primary text-primary-foreground text-xs font-bold py-1.5 text-center transform -rotate-45 shadow-md">
														POPULER
													</div>
												</div>
											</div>
										)}

										{/* Annual Discount Badge */}
										{hasAnnualDiscount && (
											<div className="absolute top-0 right-0 z-20">
												<div className="bg-gradient-to-r from-emerald-500 to-green-500 text-white text-xs font-bold px-3 py-1.5 rounded-bl-lg shadow-md">
													<Tag className="h-3 w-3 inline mr-1" />
													Hemat {annualDiscountPercent}%
												</div>
											</div>
										)}

										<CardContent className="p-8 flex flex-col h-full">
											{/* Header */}
											<div className="text-center mb-8 border-b border-border/50 pb-8">
												<h3 className="font-bold text-2xl mb-2">
													{product.name}
												</h3>

												{/* Price */}
												<div className="mb-3">
													<div className="flex items-baseline justify-center gap-1 mb-1">
														<span className="font-bold text-3xl text-primary">
															{formatPrice(product.price_cents)
																.replace("Rp", "")
																.trim()}
														</span>
														<span className="text-sm text-muted-foreground">
															/bln
														</span>
													</div>

													{/* Annual Price with Discount */}
													{hasAnnualDiscount && (
														<div className="mt-2 space-y-1">
															<div className="flex items-center justify-center gap-2">
																<span className="text-xs text-muted-foreground line-through">
																	{formatPrice(annualPriceWithoutDiscount)
																		.replace("Rp", "")
																		.trim()}
																	/tahun
																</span>
																<span className="text-xs font-semibold text-emerald-600">
																	Hemat{" "}
																	{formatPrice(annualDiscountAmount)
																		.replace("Rp", "")
																		.trim()}
																</span>
															</div>
															<div className="flex items-baseline justify-center gap-1">
																<span className="font-bold text-xl text-emerald-600">
																	{formatPrice(annualPriceWithDiscount)
																		.replace("Rp", "")
																		.trim()}
																</span>
																<span className="text-xs text-muted-foreground">
																	/tahun
																</span>
															</div>
														</div>
													)}
												</div>

												<p className="text-sm text-muted-foreground line-clamp-2">
													{product.metadata?.description}
												</p>
											</div>

											{/* Features */}
											<div className="flex-1 mb-8">
												{(() => {
													const hasProductFeatures = product.features && product.features.length > 0;
													const hasMetadataFeatures = product.metadata?.features && product.metadata.features.length > 0;
													
													if (!hasProductFeatures && !hasMetadataFeatures) {
														return null;
													}
													
													return (
														<div className="space-y-4">
															{/* Product Features from Database */}
															{hasProductFeatures && (
																<ul className="space-y-3">
																	{product.features!.map((feature) => {
																		const displayLabel = feature.label || feature.key;
																		const displayValue = feature.value + (feature.unit ? ` ${feature.unit}` : '');
																		const IconComponent = getFeatureIcon(feature.key, feature.label);
																		return (
																			<li key={feature.id} className="flex items-start gap-3">
																				<div className="mt-0.5 h-5 w-5 flex items-center justify-center flex-shrink-0">
																					<IconComponent className="h-4 w-4 text-primary" />
																				</div>
																				<div className="flex-1 min-w-0">
																					<p className="text-sm font-semibold text-foreground">
																						{displayValue ? `${displayLabel}: ${displayValue}` : displayLabel}
																					</p>
																				</div>
																			</li>
																		);
																	})}
																</ul>
															)}
															
															{/* Separator between sections */}
															{hasProductFeatures && hasMetadataFeatures && (
																<div className="pt-2 pb-2 border-t border-muted/50">
																	<p className="text-xs font-medium text-muted-foreground uppercase tracking-wider text-center">
																		Fitur Tambahan
																	</p>
																</div>
															)}
															
															{/* Metadata Features */}
															{hasMetadataFeatures && (
																<ul className="space-y-3">
																	{product.metadata!.features!.map((feature, idx) => {
																		const parts = feature.split(':');
																		const label = parts[0] || feature;
																		const value = parts.slice(1).join(':').trim() || '';
																		const IconComponent = getFeatureIcon(label, label);
																		return (
																			<li key={`metadata-${idx}`} className="flex items-start gap-3">
																				<div className="mt-0.5 h-5 w-5 flex items-center justify-center flex-shrink-0">
																					<IconComponent className="h-4 w-4 text-primary" />
																				</div>
																				<div className="flex-1 min-w-0">
																					<p className="text-sm text-muted-foreground">
																						{value ? `${label}: ${value}` : label}
																					</p>
																				</div>
																			</li>
																		);
																	})}
																</ul>
															)}
														</div>
													);
												})()}
											</div>

											{/* CTA */}
											<Link
												href={route("catalog.guest.checkout", product.slug)}
												className="mt-auto"
											>
												<Button
													className={`w-full h-12 font-semibold text-base transition-all duration-200 ${
														isPopular
															? "shadow-lg hover:shadow-xl hover:scale-[1.02]"
															: ""
													}`}
													variant={isPopular ? "default" : "outline"}
												>
													Pilih Paket
												</Button>
											</Link>
										</CardContent>
									</Card>
								);
							})}
						</div>
					</div>
				</section>
			)}
		</GuestLayout>
	);
}
