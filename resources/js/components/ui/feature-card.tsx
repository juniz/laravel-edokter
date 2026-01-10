import * as React from 'react';
import { cn } from '@/lib/utils';
import { LucideIcon } from 'lucide-react';

export interface FeatureCardProps {
    title: string;
    description: string;
    icon: LucideIcon;
    variant?: 'default' | 'gradient' | 'gradient-purple' | 'gradient-teal' | 'gradient-orange';
    className?: string;
}

export function FeatureCard({
    title,
    description,
    icon: Icon,
    variant = 'gradient',
    className,
}: FeatureCardProps) {
    const iconBgClasses = {
        default: 'bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)]',
        gradient: 'bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)]',
        'gradient-purple': 'bg-gradient-to-br from-[var(--accent-purple)] to-[var(--accent-pink)]',
        'gradient-teal': 'bg-gradient-to-br from-[var(--accent-teal)] to-[var(--accent-cyan)]',
        'gradient-orange': 'bg-gradient-to-br from-[var(--accent-orange)] to-amber-500',
    };

    return (
        <div
            className={cn(
                'group relative overflow-hidden rounded-xl border bg-card p-6 transition-all duration-300',
                'hover:shadow-lg hover:-translate-y-1 hover:border-primary/50',
                className
            )}
        >
            {/* Hover gradient overlay */}
            <div className="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100" />

            <div className="relative space-y-4">
                {/* Icon */}
                <div
                    className={cn(
                        'inline-flex h-12 w-12 items-center justify-center rounded-xl text-white shadow-lg',
                        'transition-transform duration-300 group-hover:scale-110',
                        iconBgClasses[variant]
                    )}
                >
                    <Icon className="h-6 w-6" />
                </div>

                {/* Content */}
                <div className="space-y-2">
                    <h3 className="font-semibold text-lg tracking-tight">{title}</h3>
                    <p className="text-sm text-muted-foreground leading-relaxed">{description}</p>
                </div>

                {/* Decorative accent line */}
                <div className="absolute bottom-0 left-6 right-6 h-0.5 rounded-full bg-gradient-to-r from-transparent via-primary/20 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100" />
            </div>
        </div>
    );
}
