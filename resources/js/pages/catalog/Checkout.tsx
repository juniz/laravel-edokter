import { Head, Link, useForm, router, usePage } from "@inertiajs/react";
import {
	Card,
	CardContent,
	CardHeader,
	CardTitle,
	CardDescription,
} from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
	Select,
	SelectContent,
	SelectGroup,
	SelectItem,
	SelectLabel,
	SelectTrigger,
	SelectValue,
} from "@/components/ui/select";
import { Badge } from "@/components/ui/badge";
import {
	Search,
	Timer,
	ArrowLeft,
	Loader2,
	XCircle,
	Globe,
	Building2,
	ChevronRight,
} from "lucide-react";
import { useState, useEffect } from "react";
import axios from "axios";

interface ProductFeature {
	key: string;
	value: string;
	label?: string;
	unit?: string;
}

interface Product {
	id: string;
	name: string;
	slug: string;
	type: string;
	description: string;
	price_cents: number;
	currency: string;
	annual_discount_percent?: number;
	duration_1_month_enabled: boolean;
	duration_12_months_enabled: boolean;
	features: ProductFeature[];
	metadata?: {
		popular?: boolean;
		description?: string;
	};
}

interface Promo {
	code: string;
	type: "percent" | "fixed";
	value: number;
	promo_label?: string;
	valid_until?: string | null;
}

interface DomainResult {
	domain: string;
	available: boolean;
	price?: number;
	originalPrice?: number;
	discountPercent?: number;
	error?: string;
	isLoading?: boolean;
}

interface DomainPriceData {
	registration: Record<string, number | string>;
	promo_registration?: {
		registration: Record<string, string>;
	} | null;
	currency: string;
}

interface CheckoutProps {
	product: Product;
	companyName: string;
	companyLogo?: string;
	pphRate: number;
	promo?: Promo | null;
}

export default function Checkout({
	product,
	companyName,
	companyLogo,
	pphRate = 0.11,
	promo,
}: CheckoutProps) {
	const { auth } = usePage<{
		auth: { user?: { id: number; name: string; email: string } | null };
	}>().props;

	// Initialize duration based on availability
	const getInitialDuration = (): "1" | "12" => {
		// Check URL for duration param
		const urlParams = new URLSearchParams(window.location.search);
		const urlDuration = urlParams.get('duration');
		if (urlDuration === "1" || urlDuration === "12") {
			return urlDuration;
		}

		if (product.duration_1_month_enabled) {
			return "1";
		}
		if (product.duration_12_months_enabled) {
			return "12";
		}
		// Fallback to "1" if both are disabled (shouldn't happen, but safety)
		return "1";
	};

	const [duration, setDuration] = useState<"1" | "12">(getInitialDuration());
	const [domainQuery, setDomainQuery] = useState("");
	const [isSearchingDomain, setIsSearchingDomain] = useState(false);
	const [domainResults, setDomainResults] = useState<DomainResult[]>([]);
	const [showAllDomains, setShowAllDomains] = useState(false);
	const [selectedDomains, setSelectedDomains] = useState<DomainResult[]>([]);
	const [paymentMethod, setPaymentMethod] = useState<string>("");
	const INITIAL_DOMAIN_LIMIT = 6;

	const paymentMethods = [
		{ value: 'bri_va', label: 'Bank BRI', logo: '/images/payment/bank/bri.png' },
		{ value: 'bni_va', label: 'BNI', logo: '/images/payment/bank/bni.png' },
		{ value: 'mandiri_va', label: 'Mandiri', logo: '/images/payment/bank/mandiri.png' },
		{ value: 'permata_va', label: 'Permata Bank', logo: '/images/payment/bank/permata.png' },
	];

	const checkoutForm = useForm({
		product_id: product.id,
		payment_method: paymentMethod,
		duration_months: parseInt(getInitialDuration()),
		domains: [] as Array<{
			domain: string;
			price_cents: number;
			original_price_cents: number;
			discount_percent: number;
		}>,
	});

	// Update form when duration changes
	useEffect(() => {
		checkoutForm.setData("duration_months", parseInt(duration));
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [duration]);

	// Update form when selectedDomains changes
	useEffect(() => {
		const domains = selectedDomains.map((domain) => ({
			domain: domain.domain,
			price_cents: domain.price || 0,
			original_price_cents: domain.originalPrice || domain.price || 0,
			discount_percent: domain.discountPercent || 0,
		}));
		checkoutForm.setData("domains", domains);
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [selectedDomains]);

	const formatPrice = (amount: number) => {
		return new Intl.NumberFormat("id-ID", {
			style: "currency",
			currency: "IDR",
			minimumFractionDigits: 0,
			maximumFractionDigits: 0,
		}).format(amount);
	};

	// Fetch domain price by extension
	// For checkout page (guest), use API endpoint directly
	// For admin/customer pages, use their respective endpoints
	const fetchDomainPrice = async (
		extension: string
	): Promise<DomainPriceData | null> => {
		if (!extension) {
			return null;
		}

		try {
			// Check if we're on checkout page (guest) or admin/customer page
			const isCheckoutPage = window.location.pathname.startsWith("/checkout");
			const isCustomerPage = window.location.pathname.startsWith("/customer");
			const isAdminPage = window.location.pathname.startsWith("/admin");

			let url: string;
			let params: Record<string, string> = {};

			if (isCheckoutPage) {
				// Use public endpoint for guest checkout
				url = "/domain-prices/by-extension";
				params = { extension };
			} else if (isCustomerPage) {
				url = "/customer/domain-prices/by-extension";
				params = { extension };
			} else if (isAdminPage) {
				url = "/admin/domain-prices/by-extension";
				params = { extension };
			} else {
				// Default to public endpoint
				url = "/domain-prices/by-extension";
				params = { extension };
			}

			const response = await axios.get(url, { params });

			if (response.data.success && response.data.data) {
				return {
					registration: response.data.data.registration || {},
					promo_registration: response.data.data.promo_registration || null,
					currency: response.data.data.currency || "IDR",
				};
			}

			return null;
		} catch (error) {
			console.error(`Error fetching price for ${extension}:`, error);
			return null;
		}
	};

	const handleDomainSearch = async () => {
		if (!domainQuery) return;
		setIsSearchingDomain(true);

		// Split domain into name and extension
		let name = domainQuery;
		let ext = ".com";
		if (domainQuery.includes(".")) {
			const parts = domainQuery.split(".");
			name = parts[0];
			ext = "." + parts.slice(1).join(".");
		}

		const tldsToCheck = [
			ext,
			".co.id",
			".or.id",
			".id",
			".web.id",
			".sch.id",
			".ac.id",
			".ponpes.id",
			".biz.id",
			".my.id",
			".com",
		];
		// Remove duplicates (keep all, don't limit here)
		const uniqueTlds = Array.from(new Set(tldsToCheck));

		const initialResults = uniqueTlds.map((tld) => ({
			domain: name + tld,
			available: false,
			isLoading: true,
		}));

		setDomainResults(initialResults);

		try {
			// Check availability for all domains in parallel
			const availabilityPromises = uniqueTlds.map((tld) => {
				const domain = name + tld;
				return fetch(
					`/api/rdash/domains/availability/check?domain=${encodeURIComponent(
						domain
					)}`
				)
					.then((res) => res.json())
					.catch((error) => {
						console.error(`Error checking ${domain}:`, error);
						return { success: false, domain, error };
					});
			});

			const availabilityResults = await Promise.all(availabilityPromises);

			// Get unique extensions to fetch prices
			const uniqueExtensions = Array.from(new Set(uniqueTlds));

			// Fetch prices for all extensions in parallel (using same logic as Form.tsx)
			const pricePromises = uniqueExtensions.map(
				async (
					extension
				): Promise<{ extension: string; price: DomainPriceData | null }> => {
					const price = await fetchDomainPrice(extension);
					return { extension, price };
				}
			);

			const priceResults = await Promise.all(pricePromises);

			// Create a map of extension to price data
			const priceMap = new Map<string, DomainPriceData>();
			priceResults.forEach((result) => {
				if (result.price) {
					priceMap.set(result.extension, result.price);
				}
			});

			// Combine availability and price data
			const updatedResults = initialResults.map((res, index) => {
				const availabilityResult = availabilityResults[index];
				const tld = res.domain.substring(name.length);
				const priceData = priceMap.get(tld);

				if (!availabilityResult || !availabilityResult.success) {
					return {
						...res,
						available: false,
						isLoading: false,
						error: "Gagal cek",
					};
				}

				const isAvailable =
					availabilityResult.data?.available === 1 ||
					availabilityResult.data?.available === true;

				// Extract price information from price data (same logic as Form.tsx)
				let price = 0;
				let originalPrice: number | undefined;
				let discountPercent: number | undefined;

				if (priceData) {
					// Get registration price for 1 year (default period)
					const yearKey = "1";
					const normalPrice =
						priceData.registration?.[yearKey] || priceData.registration?.[1];
					const promoPrice =
						priceData.promo_registration?.registration?.[yearKey] ||
						priceData.promo_registration?.registration?.[1];

					// Convert to number if string
					const normalPriceNum =
						typeof normalPrice === "string"
							? parseFloat(normalPrice) || 0
							: normalPrice || 0;

					const promoPriceNum =
						typeof promoPrice === "string"
							? parseFloat(promoPrice) || 0
							: promoPrice || 0;

					const hasPromo =
						promoPriceNum &&
						promoPriceNum !== 0 &&
						promoPriceNum !== null &&
						promoPriceNum !== undefined;

					if (hasPromo) {
						price = promoPriceNum;
						originalPrice = normalPriceNum;
						if (originalPrice > 0) {
							discountPercent = Math.round(
								((originalPrice - price) / originalPrice) * 100
							);
						}
					} else {
						price = normalPriceNum;
					}
				}

				return {
					...res,
					available: isAvailable,
					price: isAvailable ? price : undefined,
					originalPrice: isAvailable ? originalPrice : undefined,
					discountPercent: isAvailable ? discountPercent : undefined,
					isLoading: false,
				};
			});

			setDomainResults(updatedResults);
			setShowAllDomains(false); // Reset show all when new search
		} catch (error) {
			console.error("Domain check error:", error);
			setDomainResults(
				initialResults.map((res) => ({
					...res,
					isLoading: false,
					error: "Gagal cek",
				}))
			);
			setShowAllDomains(false);
		} finally {
			setIsSearchingDomain(false);
		}
	};

	// Get displayed results based on showAllDomains state
	const displayedResults = showAllDomains
		? domainResults
		: domainResults.slice(0, INITIAL_DOMAIN_LIMIT);
	const hasMoreResults = domainResults.length > INITIAL_DOMAIN_LIMIT;

	// Handle add domain to cart
	const handleAddDomain = (domain: DomainResult) => {
		// Check if domain is already added
		if (selectedDomains.some((d) => d.domain === domain.domain)) {
			return;
		}

		// Add domain to selected domains
		setSelectedDomains([...selectedDomains, domain]);
	};

	// Handle remove domain from cart
	const handleRemoveDomain = (domain: string) => {
		setSelectedDomains(selectedDomains.filter((d) => d.domain !== domain));
	};

	// Handle checkout
	const handleCheckout = (e: React.FormEvent) => {
		e.preventDefault();

		// Check if user is logged in
		if (!auth?.user) {
			// Redirect to login with return URL
			const returnUrl = window.location.pathname + window.location.search;
			router.visit(`/login?redirect=${encodeURIComponent(returnUrl)}`);
			return;
		}

		// Check if payment method is selected
		if (!paymentMethod) {
			alert("Silakan pilih metode pembayaran terlebih dahulu");
			return;
		}

		// Prepare domain data
		const domains = selectedDomains.map((domain) => ({
			domain: domain.domain,
			price_cents: domain.price || 0,
			original_price_cents: domain.originalPrice || domain.price || 0,
			discount_percent: domain.discountPercent || 0,
		}));

		// Update form data with all fields including domains
		// Use setData to ensure all fields are updated before submit
		checkoutForm.setData({
			product_id: product.id,
			payment_method: paymentMethod,
			duration_months: parseInt(duration),
			domains: domains,
		});

		// Submit checkout
		checkoutForm.post("/catalog/checkout", {
			onSuccess: () => {
				// Backend will redirect to payment page
			},
			onError: (errors) => {
				console.error("Checkout error:", errors);
			},
		});
	};

	// Update payment method in form when it changes
	useEffect(() => {
		checkoutForm.setData("payment_method", paymentMethod);
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [paymentMethod]);

	// Calculate totals
	const monthlyPrice = product.price_cents;
	const annualDiscountPercent = product.annual_discount_percent ?? 0;

	// Calculate product subtotal with annual discount
	let productSubtotal = duration === "1" ? monthlyPrice : monthlyPrice * 12;
	let annualDiscount = 0;

	if (duration === "12" && annualDiscountPercent > 0) {
		const annualPriceWithoutDiscount = monthlyPrice * 12;
		annualDiscount = Math.round(
			annualPriceWithoutDiscount * (annualDiscountPercent / 100)
		);
		productSubtotal = annualPriceWithoutDiscount - annualDiscount;
	}

	// Calculate domain total
	const domainTotal = selectedDomains.reduce((sum, domain) => {
		return sum + (domain.price || 0);
	}, 0);

	// Calculate subtotal (product + domains)
	const subtotal = productSubtotal + domainTotal;

	// Calculate Promo (on top of annual discount)
	let promoDiscount = 0;
	if (promo) {
		if (promo.type === "percent") {
			promoDiscount = Math.round(productSubtotal * (promo.value / 100));
		} else {
			promoDiscount = promo.value;
		}
		// Ensure discount doesn't exceed product subtotal (promo only applies to product, not domains)
		if (promoDiscount > productSubtotal) promoDiscount = productSubtotal;
	}

	// Total discount = annual discount + promo discount
	const totalDiscount = annualDiscount + promoDiscount;
	const taxableAmount = subtotal - totalDiscount;
	const tax = Math.round(taxableAmount * pphRate);
	const total = taxableAmount + tax;

	// Countdown Timer Hook
	const useCountdown = (targetDate: string | null) => {
		const [timeLeft, setTimeLeft] = useState<{
			days: number;
			hours: number;
			minutes: number;
			seconds: number;
		} | null>(null);

		useEffect(() => {
			if (!targetDate) return;

			const interval = setInterval(() => {
				const now = new Date().getTime();
				const distance = new Date(targetDate).getTime() - now;

				if (distance < 0) {
					clearInterval(interval);
					setTimeLeft(null);
				} else {
					setTimeLeft({
						days: Math.floor(distance / (1000 * 60 * 60 * 24)),
						hours: Math.floor(
							(distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
						),
						minutes: Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)),
						seconds: Math.floor((distance % (1000 * 60)) / 1000),
					});
				}
			}, 1000);

			return () => clearInterval(interval);
		}, [targetDate]);

		return timeLeft;
	};

	const promoTimeLeft = useCountdown(promo?.valid_until || null);

	return (
		<div className="min-h-screen bg-gray-50/50 dark:bg-gray-950 font-sans">
			<Head title={`Checkout - ${product.name}`} />

			{/* Header */}
			<header className="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
				<div className="container mx-auto px-4 h-16 flex items-center justify-between">
					<Link
						href="/layanan"
						className="flex items-center gap-2 text-muted-foreground hover:text-primary transition-colors"
					>
						<ArrowLeft className="h-4 w-4" />
						<span>Kembali ke Katalog</span>
					</Link>
					<div className="font-bold text-xl">
						{companyLogo ? (
							<img src={companyLogo} alt={companyName} className="h-8 w-auto" />
						) : (
							<span>{companyName}</span>
						)}
					</div>
				</div>
			</header>

			<main className="container mx-auto px-4 py-8 max-w-6xl">
				<h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-8">
					Keranjang Anda
				</h1>

				<div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
					{/* Left Column - Product & Upsells */}
					<div className="lg:col-span-2 space-y-6">
						{/* Product Card */}
						<Card className="shadow-md border-0 ring-1 ring-gray-200 dark:ring-gray-800">
							<CardHeader className="pb-4">
								<div className="flex justify-between items-start">
									<div>
										<CardTitle className="text-2xl font-bold text-primary">
											{product.name}
										</CardTitle>
										<CardDescription className="mt-1">
											{product.metadata?.description ||
												"Paket hosting terbaik untuk kebutuhan Anda"}
										</CardDescription>
									</div>
									{/* <Badge variant="outline" className="px-3 py-1 bg-primary/5 text-primary border-primary/20">
                                        Server Indonesia
                                    </Badge> */}
								</div>
							</CardHeader>
							<CardContent className="space-y-6">
								{/* Duration Selection */}
								<div>
									<Label className="text-sm font-medium mb-2 block">
										Durasi
									</Label>
									<div className="flex items-center justify-between p-4 border rounded-xl bg-white dark:bg-gray-900">
										<Select
											value={duration}
											onValueChange={(val: "1" | "12") => setDuration(val)}
										>
											<SelectTrigger className="w-[180px] h-10">
												<SelectValue />
											</SelectTrigger>
											<SelectContent>
												{product.duration_1_month_enabled && (
													<SelectItem value="1">1 Bulan</SelectItem>
												)}
												{product.duration_12_months_enabled && (
													<SelectItem value="12">12 Bulan</SelectItem>
												)}
											</SelectContent>
										</Select>

										<div className="text-right">
											<div className="font-bold text-xl">
												{formatPrice(monthlyPrice)}
												<span className="text-sm text-muted-foreground font-normal">
													/bln
												</span>
											</div>
											{duration === "12" && (
												<p className="text-xs text-green-600 font-medium">
													Hemat 10% (Promo)
												</p>
											)}
										</div>
									</div>
									<p className="text-xs text-muted-foreground mt-2">
										Biaya perpanjangan {formatPrice(monthlyPrice)}/bln. Bisa
										dibatalkan kapan saja!
									</p>
								</div>

								{/* Timer / Promo Banner - Dynamic */}
								{promo && (
									<div className="bg-gray-900 text-white rounded-xl p-4 flex items-center justify-between shadow-lg relative overflow-hidden group">
										<div className="absolute inset-0 bg-gradient-to-r from-primary to-purple-600 opacity-20 group-hover:opacity-30 transition-opacity" />
										<div className="flex items-center gap-4 relative z-10">
											<div className="bg-[#ccff00] text-black font-bold px-3 py-2 rounded text-xl rotate-[-4deg]">
												%
											</div>
											<div>
												<div className="flex items-center gap-2">
													<p className="font-bold text-sm text-[#ccff00]">
														{promo.promo_label || "Promo Spesial"}
													</p>
													{promoTimeLeft && (
														<div className="flex items-center gap-1 text-[10px] bg-white/10 px-2 py-0.5 rounded-full text-white font-mono">
															<Timer className="h-3 w-3" />
															<span>
																{promoTimeLeft.days > 0 &&
																	`${promoTimeLeft.days}h `}
																{promoTimeLeft.hours
																	.toString()
																	.padStart(2, "0")}
																:
																{promoTimeLeft.minutes
																	.toString()
																	.padStart(2, "0")}
																:
																{promoTimeLeft.seconds
																	.toString()
																	.padStart(2, "0")}
															</span>
														</div>
													)}
												</div>
												<p className="text-xs text-gray-300 mt-0.5">
													{promo.type === "percent"
														? `Diskon ${promo.value}%`
														: `Potongan Rp ${promo.value.toLocaleString(
																"id-ID"
														  )}`}{" "}
													otomatis diterapkan
												</p>
											</div>
										</div>
									</div>
								)}

								{/* Upsell Tips */}
								{/* <div className="bg-orange-50 dark:bg-orange-900/10 border border-orange-100 dark:border-orange-900/20 rounded-lg p-3 text-sm text-orange-800 dark:text-orange-200 flex items-center gap-2">
                                    <span className="bg-orange-200 dark:bg-orange-800 rounded-full p-0.5">💡</span>
                                    Mau domain gratis? Pilih paket minimal 12 bulan.
                                </div> */}
							</CardContent>
						</Card>

						{/* Domain Upsell */}
						<Card className="shadow-md border-0 ring-1 ring-gray-200 dark:ring-gray-800">
							<CardHeader>
								<div className="flex items-center gap-2">
									<div className="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg text-blue-600">
										<Globe className="h-5 w-5" />
									</div>
									<div>
										<CardTitle className="text-lg">
											Tiap website butuh domain
										</CardTitle>
										<CardDescription>
											Pilih domain sekarang dan onlinekan website lebih cepat
										</CardDescription>
									</div>
								</div>
							</CardHeader>
							<CardContent>
								<div className="space-y-6">
									<div className="space-y-4">
										<Label className="text-sm font-semibold">Nama Domain</Label>
										<div className="relative">
											<Search className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground" />
											<Input
												placeholder="Cari domain impian Anda..."
												className="pl-11 h-12 text-lg border-2 focus-visible:ring-primary/20 transition-all rounded-xl"
												value={domainQuery}
												onChange={(e) => setDomainQuery(e.target.value)}
												onKeyDown={(e) =>
													e.key === "Enter" && handleDomainSearch()
												}
											/>
											{isSearchingDomain && (
												<div className="absolute right-3 top-1/2 -translate-y-1/2">
													<Loader2 className="h-5 w-5 animate-spin text-primary" />
												</div>
											)}
										</div>
									</div>

									{domainResults.length > 0 && (
										<div className="space-y-4">
											<div className="divide-y border rounded-xl overflow-hidden bg-white dark:bg-gray-950 animate-in fade-in slide-in-from-top-4 duration-500">
												{displayedResults.map((result, idx) => (
													<div
														key={idx}
														className="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-muted/30 transition-colors"
													>
														<div className="flex flex-col">
															<span
																className={`font-semibold text-lg ${
																	!result.available && !result.isLoading
																		? "text-muted-foreground"
																		: ""
																}`}
															>
																{result.domain}
															</span>
															{result.isLoading && (
																<span className="text-xs text-muted-foreground flex items-center gap-1 animate-pulse">
																	<Loader2 className="h-3 w-3 animate-spin" />
																	Memeriksa...
																</span>
															)}
														</div>

														<div className="flex items-center gap-4 ml-auto sm:ml-0">
															{!result.isLoading && (
																<>
																	{!result.available ? (
																		<Badge
																			variant="secondary"
																			className="bg-gray-100 text-gray-600 font-bold px-3 py-1 uppercase text-[10px] tracking-wider rounded-lg"
																		>
																			Tidak Tersedia
																		</Badge>
																	) : (
																		<div className="flex flex-col sm:flex-row items-end sm:items-center gap-3">
																			{result.discountPercent && (
																				<Badge className="bg-[#e8ebff] text-primary border-0 font-bold text-[10px] py-1 px-2 rounded-lg whitespace-nowrap">
																					HEMAT {result.discountPercent}%
																				</Badge>
																			)}

																			<div className="text-right">
																				<div className="flex items-center gap-2 justify-end">
																					<span className="font-bold text-gray-900 dark:text-white">
																						{formatPrice(result.price || 0)}/thn
																						pertama
																					</span>
																					{result.originalPrice && (
																						<span className="text-xs text-muted-foreground line-through decoration-muted-foreground/50">
																							{formatPrice(
																								result.originalPrice
																							)}
																						</span>
																					)}
																				</div>
																			</div>

																			<Button
																				size="sm"
																				variant="outline"
																				onClick={() => handleAddDomain(result)}
																				disabled={selectedDomains.some(
																					(d) => d.domain === result.domain
																				)}
																				className="h-9 px-4 font-bold border-2 hover:bg-primary hover:text-white hover:border-primary transition-all rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
																			>
																				{selectedDomains.some(
																					(d) => d.domain === result.domain
																				)
																					? "Ditambahkan"
																					: "Tambah"}
																			</Button>
																		</div>
																	)}
																</>
															)}
														</div>
													</div>
												))}
											</div>
											{hasMoreResults && (
												<div className="flex justify-center pt-2">
													<Button
														type="button"
														variant="outline"
														onClick={() => setShowAllDomains(!showAllDomains)}
														className="w-full sm:w-auto"
													>
														{showAllDomains
															? `Tampilkan Lebih Sedikit (${INITIAL_DOMAIN_LIMIT})`
															: `Tampilkan Semua (${domainResults.length} domain)`}
													</Button>
												</div>
											)}
										</div>
									)}
								</div>
							</CardContent>
						</Card>
					</div>

					{/* Right Column - Order Summary */}
					<div className="space-y-6">
						<Card className="shadow-lg border-0 ring-1 ring-gray-200 dark:ring-gray-800 sticky top-24">
							<CardHeader>
								<CardTitle>Daftar pesanan</CardTitle>
							</CardHeader>
							<CardContent className="space-y-6">
								<div className="space-y-4">
									{/* Product */}
									<div className="flex justify-between items-start pb-4 border-b border-dashed">
										<div>
											<p className="font-bold text-gray-900 dark:text-white">
												{product.name}
											</p>
											<p className="text-sm text-muted-foreground">
												Paket {duration === "1" ? "1 bulan" : "12 bulan"}
											</p>
										</div>
										<p className="font-medium">
											{formatPrice(productSubtotal)}
										</p>
									</div>

									{/* Selected Domains */}
									{selectedDomains.length > 0 && (
										<div className="space-y-2 pb-4 border-b border-dashed">
											<p className="text-sm font-semibold text-gray-900 dark:text-white mb-2">
												Domain ({selectedDomains.length})
											</p>
											{selectedDomains.map((domain) => (
												<div
													key={domain.domain}
													className="flex justify-between items-center group"
												>
													<div className="flex items-center gap-2 flex-1 min-w-0">
														<span className="text-sm text-gray-900 dark:text-white truncate">
															{domain.domain}
														</span>
														{domain.discountPercent && (
															<Badge className="bg-[#e8ebff] text-primary border-0 text-[9px] py-0 px-1.5">
																-{domain.discountPercent}%
															</Badge>
														)}
													</div>
													<div className="flex items-center gap-2">
														<span className="text-sm font-medium">
															{formatPrice(domain.price || 0)}
														</span>
														<Button
															type="button"
															variant="ghost"
															size="sm"
															onClick={() => handleRemoveDomain(domain.domain)}
															className="h-6 w-6 p-0 opacity-0 group-hover:opacity-100 transition-opacity"
														>
															<XCircle className="h-4 w-4 text-red-500" />
														</Button>
													</div>
												</div>
											))}
										</div>
									)}

									{/* Domain Total */}
									{selectedDomains.length > 0 && (
										<div className="flex justify-between items-center text-sm pb-2">
											<span className="text-muted-foreground">
												Subtotal Domain
											</span>
											<span className="font-medium">
												{formatPrice(domainTotal)}
											</span>
										</div>
									)}

									{annualDiscount > 0 && (
										<div className="flex justify-between items-center text-sm text-emerald-600 font-medium">
											<span className="flex items-center gap-1">
												Diskon Tahunan ({annualDiscountPercent}%)
											</span>
											<span>-{formatPrice(annualDiscount)}</span>
										</div>
									)}
									{promo && promoDiscount > 0 && (
										<div className="flex justify-between items-center text-sm text-green-600">
											<span>Diskon ({promo.code})</span>
											<span>-{formatPrice(promoDiscount)}</span>
										</div>
									)}

									<div className="flex justify-between items-center text-sm">
										<span className="text-muted-foreground">
											Pajak ({(pphRate * 100).toFixed(0)}%)
										</span>
										<span>{formatPrice(tax)}</span>
									</div>

									<p className="text-xs text-muted-foreground italic">
										(Dihitung setelah informasi penagihan)
									</p>

									<div className="flex justify-between items-center pt-4 border-t">
										<span className="font-bold text-lg">Subtotal</span>
										<span className="font-bold text-2xl text-primary">
											{formatPrice(total)}
										</span>
									</div>
								</div>

								{/* Payment Method Selection - Snap Style */}
								<div className="space-y-3 pt-4 border-t">
									<Label className="text-sm font-semibold text-gray-900 dark:text-white mb-2 block">
										Metode Pembayaran
									</Label>
									<div className="space-y-2">
										{paymentMethods.map((method) => (
											<button
												key={method.value}
												type="button"
												onClick={() => setPaymentMethod(method.value)}
												className={`w-full flex items-center justify-between p-3 bg-white dark:bg-gray-900 border rounded-xl transition-all hover:bg-gray-50 dark:hover:bg-gray-800 group ${
													paymentMethod === method.value 
														? 'border-[#4a1fb8] ring-1 ring-[#4a1fb8]' 
														: 'border-gray-100 dark:border-gray-800'
												}`}
											>
												<div className="flex items-center gap-3">
													<div className="w-10 h-6 flex items-center justify-center bg-white rounded p-1">
														<img src={method.logo} alt={method.label} className="max-h-full max-w-full object-contain" />
													</div>
													<span className="font-semibold text-sm text-gray-700 dark:text-gray-300">{method.label}</span>
												</div>
												<div className={`w-4 h-4 rounded-full border flex items-center justify-center transition-colors ${
													paymentMethod === method.value 
														? 'bg-[#4a1fb8] border-[#4a1fb8]' 
														: 'border-gray-300'
												}`}>
													{paymentMethod === method.value && (
														<div className="w-1.5 h-1.5 bg-white rounded-full" />
													)}
												</div>
											</button>
										))}
									</div>
								</div>

								<div className="pt-2">
									<button className="text-primary hover:text-primary/80 text-sm font-medium hover:underline flex items-center gap-1 transition-colors">
										Punya Kode Kupon?
									</button>
								</div>

								{!auth?.user && (
									<div className="bg-yellow-50 dark:bg-yellow-900/10 border border-yellow-200 dark:border-yellow-900/20 rounded-lg p-4 mb-4">
										<p className="text-sm text-yellow-800 dark:text-yellow-200 mb-2">
											<strong>Perhatian:</strong> Anda harus login terlebih
											dahulu untuk melanjutkan pembayaran.
										</p>
										<Link
											href={`/login?redirect=${encodeURIComponent(
												window.location.pathname + window.location.search
											)}`}
											className="text-sm font-medium text-yellow-900 dark:text-yellow-100 hover:underline"
										>
											Klik di sini untuk login
										</Link>
									</div>
								)}

								<Button
									type="button"
									onClick={handleCheckout}
									disabled={
										!auth?.user || !paymentMethod || checkoutForm.processing
									}
									className="w-full h-12 text-lg font-bold shadow-lg hover:shadow-xl hover:translate-y-[-1px] transition-all disabled:opacity-50 disabled:cursor-not-allowed"
								>
									{checkoutForm.processing ? (
										<>
											<Loader2 className="w-5 h-5 mr-2 animate-spin" />
											Memproses...
										</>
									) : (
										"Lanjutkan ke Pembayaran"
									)}
								</Button>

								{/* <div className="flex items-center justify-center gap-2 text-sm text-muted-foreground pt-2">
                                    <ShieldCheck className="h-4 w-4" />
                                    <span>Jaminan 30 hari uang kembali</span>
                                </div> */}
							</CardContent>
						</Card>
					</div>
				</div>
			</main>
		</div>
	);
}
