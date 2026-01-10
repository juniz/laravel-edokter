import * as React from 'react';
import { cn } from '@/lib/utils';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from './card';
import { Button } from './button';
import { Check } from 'lucide-react';

export interface PricingFeature {
    text: string;
    included: boolean;
    highlight?: boolean;
}

export interface PricingCardProps {
    name: string;
    description?: string;
    price: number | string;
    currency?: string;
    period?: string;
    features: PricingFeature[];
    buttonText?: string;
    buttonVariant?: 'default' | 'gradient' | 'gradient-purple' | 'outline';
    featured?: boolean;
    badge?: string;
    onSelect?: () => void;
    className?: string;
    disabled?: boolean;
}

export function PricingCard({
    name,
    description,
    price,
    currency = 'Rp',
    period = '/bulan',
    features,
    buttonText = 'Pilih Paket',
    buttonVariant = 'default',
    featured = false,
    badge,
    onSelect,
    className,
    disabled = false,
}: PricingCardProps) {
    return (
        <Card
            variant={featured ? 'featured' : 'premium'}
            className={cn(
                'relative flex flex-col h-full',
                featured && 'scale-105 z-10',
                className
            )}
        >
            {/* Featured/Popular badge */}
            {(featured || badge) && (
                <div className="absolute -top-3 left-1/2 transform -translate-x-1/2">
                    <span className="inline-flex items-center px-4 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-[var(--gradient-start)] to-[var(--gradient-end)] text-white shadow-lg">
                        {badge || 'POPULER'}
                    </span>
                </div>
            )}

            <CardHeader className={cn('text-center', featured && 'pt-8')}>
                <CardTitle className="text-lg font-bold">{name}</CardTitle>
                {description && (
                    <CardDescription className="text-sm">{description}</CardDescription>
                )}
                
                {/* Price */}
                <div className="mt-4">
                    <div className="flex items-baseline justify-center gap-1">
                        <span className="text-sm text-muted-foreground">{currency}</span>
                        <span className={cn(
                            'font-bold tracking-tight',
                            featured ? 'text-4xl text-gradient-primary' : 'text-3xl'
                        )}>
                            {typeof price === 'number' 
                                ? new Intl.NumberFormat('id-ID').format(price)
                                : price
                            }
                        </span>
                    </div>
                    <span className="text-sm text-muted-foreground">{period}</span>
                </div>
            </CardHeader>

            <CardContent className="flex-1">
                <ul className="space-y-3">
                    {features.map((feature, index) => (
                        <li
                            key={index}
                            className={cn(
                                'flex items-start gap-3 text-sm',
                                !feature.included && 'text-muted-foreground line-through'
                            )}
                        >
                            <div className={cn(
                                'mt-0.5 flex-shrink-0 rounded-full p-0.5',
                                feature.included 
                                    ? feature.highlight
                                        ? 'bg-gradient-to-r from-[var(--gradient-start)] to-[var(--gradient-end)]'
                                        : 'bg-emerald-500'
                                    : 'bg-muted'
                            )}>
                                <Check className="h-3 w-3 text-white" />
                            </div>
                            <span className={cn(
                                feature.highlight && 'font-medium text-foreground'
                            )}>
                                {feature.text}
                            </span>
                        </li>
                    ))}
                </ul>
            </CardContent>

            <CardFooter className="pt-4">
                <Button
                    variant={featured ? 'gradient' : buttonVariant}
                    size="lg"
                    className="w-full"
                    onClick={onSelect}
                    disabled={disabled}
                >
                    {buttonText}
                </Button>
            </CardFooter>
        </Card>
    );
}
