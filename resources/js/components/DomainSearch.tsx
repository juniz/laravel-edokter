import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Loader2, Search } from "lucide-react";
import axios from "axios";

export interface DomainResult {
    domain: string;
    available: boolean;
    price?: number;
    originalPrice?: number;
    discountPercent?: number;
    error?: string;
    isLoading?: boolean;
}

export interface DomainPriceData {
    registration: Record<string, number | string>;
    promo_registration?: {
        registration: Record<string, string>;
    } | null;
    currency: string;
}

interface DomainSearchProps {
    onAddDomain?: (domain: DomainResult) => void;
    onRemoveDomain?: (domain: string) => void;
    selectedDomains?: DomainResult[];
    className?: string;
}

export default function DomainSearch({
    onAddDomain,
    onRemoveDomain,
    selectedDomains = [],
    className = "",
}: DomainSearchProps) {
    const [domainQuery, setDomainQuery] = useState("");
    const [isSearchingDomain, setIsSearchingDomain] = useState(false);
    const [domainResults, setDomainResults] = useState<DomainResult[]>([]);
    const [showAllDomains, setShowAllDomains] = useState(false);
    const INITIAL_DOMAIN_LIMIT = 6;

    const formatPrice = (amount: number) => {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(amount);
    };

    const fetchDomainPrice = async (
        extension: string
    ): Promise<DomainPriceData | null> => {
        if (!extension) {
            return null;
        }

        try {
            // Check if we're on specific pages to determine endpoint
            const isCheckoutPage = window.location.pathname.startsWith("/checkout");
            const isCustomerPage = window.location.pathname.startsWith("/customer");
            const isAdminPage = window.location.pathname.startsWith("/admin");

            let url: string;
            const params: Record<string, string> = { extension };

            if (isCheckoutPage) {
                url = "/domain-prices/by-extension";
            } else if (isCustomerPage) {
                url = "/customer/domain-prices/by-extension";
            } else if (isAdminPage) {
                url = "/admin/domain-prices/by-extension";
            } else {
                // Default to public endpoint for catalog/show etc
                url = "/domain-prices/by-extension";
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
        // Remove duplicates
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

            // Fetch prices for all extensions in parallel
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

                let price = 0;
                let originalPrice: number | undefined;
                let discountPercent: number | undefined;

                if (priceData) {
                    const yearKey = "1";
                    const normalPrice =
                        priceData.registration?.[yearKey] || priceData.registration?.[1];
                    const promoPrice =
                        priceData.promo_registration?.registration?.[yearKey] ||
                        priceData.promo_registration?.registration?.[1];

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
            setShowAllDomains(false);
        } catch (error) {
            console.error("Domain check error:", error);
            setDomainResults(
                initialResults.map((res) => ({
                    ...res,
                    isLoading: false,
                    error: "Gagal cek",
                }))
            );
        } finally {
            setIsSearchingDomain(false);
        }
    };

    const displayedResults = showAllDomains
        ? domainResults
        : domainResults.slice(0, INITIAL_DOMAIN_LIMIT);
    
    // Unused unless we implement "Show More"
    // const hasMoreResults = domainResults.length > INITIAL_DOMAIN_LIMIT;

    return (
        <div className={`space-y-6 ${className}`}>
           <div className="space-y-4">
                {/* <Label className="text-sm font-semibold">Nama Domain</Label> */} 
                {/* ^ Removed Label to make it flexible for caller to add header or not */}
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

                                                    {(onAddDomain || onRemoveDomain) && (
                                                        <Button
                                                            size="sm"
                                                            variant="outline"
                                                            onClick={() => onAddDomain && onAddDomain(result)}
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
                                                    )}
                                                </div>
                                            )}
                                        </>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}
