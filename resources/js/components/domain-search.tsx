import * as React from 'react';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { Search, Loader2, Check, X, ShoppingCart, Globe } from 'lucide-react';

export interface TldOption {
    tld: string;
    price: number;
    popular?: boolean;
}

export interface DomainAvailability {
    domain: string;
    available: boolean;
    price?: number;
    premium?: boolean;
}

export interface DomainSearchProps {
    tlds?: TldOption[];
    onSearch?: (domain: string) => Promise<DomainAvailability[]>;
    onAddToCart?: (domain: string, price: number) => void;
    placeholder?: string;
    className?: string;
}

const defaultTlds: TldOption[] = [
    { tld: '.com', price: 149000, popular: true },
    { tld: '.id', price: 250000, popular: true },
    { tld: '.co.id', price: 150000, popular: true },
    { tld: '.net', price: 165000 },
    { tld: '.org', price: 165000 },
    { tld: '.info', price: 99000 },
    { tld: '.xyz', price: 49000 },
    { tld: '.online', price: 59000 },
];

export function DomainSearch({
    tlds = defaultTlds,
    onSearch,
    onAddToCart,
    placeholder = 'Cari nama domain impian Anda...',
    className,
}: DomainSearchProps) {
    const [query, setQuery] = React.useState('');
    const [isSearching, setIsSearching] = React.useState(false);
    const [results, setResults] = React.useState<DomainAvailability[]>([]);
    const [hasSearched, setHasSearched] = React.useState(false);

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(amount);
    };

    const handleSearch = async () => {
        if (!query.trim()) return;

        setIsSearching(true);
        setHasSearched(true);

        // Clean domain name
        let cleanDomain = query.toLowerCase().trim();
        // Remove protocol and www
        cleanDomain = cleanDomain.replace(/^(https?:\/\/)?(www\.)?/, '');
        // Remove trailing slashes
        cleanDomain = cleanDomain.replace(/\/$/, '');
        // Get just the domain name without any existing TLD
        const parts = cleanDomain.split('.');
        const baseDomain = parts[0];

        if (onSearch) {
            try {
                const searchResults = await onSearch(baseDomain);
                setResults(searchResults);
            } catch {
                // Fallback to mock results
                setResults(
                    tlds.map((tld) => ({
                        domain: baseDomain + tld.tld,
                        available: Math.random() > 0.3,
                        price: tld.price,
                    }))
                );
            }
        } else {
            // Mock search results
            await new Promise((resolve) => setTimeout(resolve, 1000));
            setResults(
                tlds.map((tld) => ({
                    domain: baseDomain + tld.tld,
                    available: Math.random() > 0.3,
                    price: tld.price,
                }))
            );
        }

        setIsSearching(false);
    };

    const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'Enter') {
            handleSearch();
        }
    };

    return (
        <div className={cn('w-full space-y-6', className)}>
            {/* Search Input */}
            <div className="relative">
                <div className="relative flex items-center">
                    <div className="relative flex-1">
                        <Globe className="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground" />
                        <input
                            type="text"
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            onKeyDown={handleKeyDown}
                            placeholder={placeholder}
                            className={cn(
                                'w-full h-14 pl-12 pr-4 rounded-l-xl border-2 border-r-0',
                                'bg-background text-foreground placeholder:text-muted-foreground',
                                'text-lg font-medium outline-none transition-all duration-200',
                                'border-border focus:border-primary focus:ring-4 focus:ring-primary/10',
                                'dark:bg-card'
                            )}
                        />
                    </div>
                    <Button
                        variant="gradient"
                        size="xl"
                        onClick={handleSearch}
                        disabled={isSearching || !query.trim()}
                        className="h-14 rounded-l-none rounded-r-xl px-8"
                    >
                        {isSearching ? (
                            <Loader2 className="h-5 w-5 animate-spin" />
                        ) : (
                            <Search className="h-5 w-5" />
                        )}
                        <span className="hidden sm:inline ml-2">Cari Domain</span>
                    </Button>
                </div>

                {/* Popular TLDs */}
                <div className="flex flex-wrap gap-2 mt-3">
                    <span className="text-sm text-muted-foreground">Populer:</span>
                    {tlds
                        .filter((tld) => tld.popular)
                        .map((tld) => (
                            <button
                                key={tld.tld}
                                onClick={() => setQuery(query.split('.')[0] + tld.tld)}
                                className={cn(
                                    'px-3 py-1 text-sm rounded-full border transition-all duration-200',
                                    'hover:border-primary hover:bg-primary/5 hover:text-primary',
                                    'bg-background'
                                )}
                            >
                                {tld.tld}
                                <span className="ml-1 text-xs text-muted-foreground">
                                    {formatCurrency(tld.price)}
                                </span>
                            </button>
                        ))}
                </div>
            </div>

            {/* Search Results */}
            {hasSearched && (
                <div className="space-y-3">
                    <h3 className="text-lg font-semibold">Hasil Pencarian</h3>
                    
                    {isSearching ? (
                        <div className="flex items-center justify-center py-12">
                            <Loader2 className="h-8 w-8 animate-spin text-primary" />
                            <span className="ml-3 text-muted-foreground">
                                Mencari ketersediaan domain...
                            </span>
                        </div>
                    ) : (
                        <div className="space-y-2">
                            {results.map((result) => (
                                <div
                                    key={result.domain}
                                    className={cn(
                                        'flex items-center justify-between p-4 rounded-xl border transition-all duration-200',
                                        result.available
                                            ? 'bg-emerald-50/50 border-emerald-200 dark:bg-emerald-900/10 dark:border-emerald-900/30'
                                            : 'bg-muted/50 border-border'
                                    )}
                                >
                                    <div className="flex items-center gap-3">
                                        <div
                                            className={cn(
                                                'flex h-8 w-8 items-center justify-center rounded-full',
                                                result.available
                                                    ? 'bg-emerald-500 text-white'
                                                    : 'bg-muted-foreground/20 text-muted-foreground'
                                            )}
                                        >
                                            {result.available ? (
                                                <Check className="h-4 w-4" />
                                            ) : (
                                                <X className="h-4 w-4" />
                                            )}
                                        </div>
                                        <div>
                                            <p className="font-semibold">{result.domain}</p>
                                            <p className="text-sm text-muted-foreground">
                                                {result.available ? 'Tersedia' : 'Tidak tersedia'}
                                                {result.premium && (
                                                    <span className="ml-2 text-amber-600 font-medium">
                                                        Premium
                                                    </span>
                                                )}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="flex items-center gap-4">
                                        {result.available && result.price && (
                                            <>
                                                <div className="text-right">
                                                    <p className="font-bold text-lg">
                                                        {formatCurrency(result.price)}
                                                    </p>
                                                    <p className="text-xs text-muted-foreground">/tahun</p>
                                                </div>
                                                <Button
                                                    variant="gradient"
                                                    size="sm"
                                                    onClick={() => onAddToCart?.(result.domain, result.price!)}
                                                >
                                                    <ShoppingCart className="h-4 w-4 mr-1" />
                                                    Pilih
                                                </Button>
                                            </>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
