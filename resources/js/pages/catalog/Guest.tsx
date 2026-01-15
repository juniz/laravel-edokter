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
												<ul className="space-y-4">
													{product.metadata?.features &&
														product.metadata.features.map((feature, idx) => (
															<li key={idx} className="flex items-start gap-3">
																<div className="mt-1 h-5 w-5 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
																	<Check className="h-3 w-3 text-primary" />
																</div>
																<span className="text-sm text-muted-foreground">
																	{feature}
																</span>
															</li>
														))}
												</ul>
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
