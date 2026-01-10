import * as React from 'react';
import { cn } from '@/lib/utils';
import { LucideIcon } from 'lucide-react';

export interface StatCardProps {
    title: string;
    value: string | number;
    description?: string;
    icon?: LucideIcon;
    trend?: {
        value: number;
        type: 'up' | 'down' | 'neutral';
    };
    variant?: 'default' | 'gradient' | 'gradient-purple' | 'gradient-teal' | 'gradient-orange';
    className?: string;
}

export function StatCard({
    title,
    value,
    description,
    icon: Icon,
    trend,
    variant = 'default',
    className,
}: StatCardProps) {
    const gradientClasses = {
        default: 'from-[var(--gradient-start)] to-[var(--gradient-end)]',
        gradient: 'from-[var(--gradient-start)] to-[var(--gradient-end)]',
        'gradient-purple': 'from-[var(--accent-purple)] to-[var(--accent-pink)]',
        'gradient-teal': 'from-[var(--accent-teal)] to-[var(--accent-cyan)]',
        'gradient-orange': 'from-[var(--accent-orange)] to-amber-500',
    };

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
                'relative overflow-hidden rounded-xl border bg-card p-6 shadow-sm transition-all duration-300 hover:shadow-md',
                className
            )}
        >
            {/* Decorative gradient blob */}
            <div
                className={cn(
                    'absolute -right-8 -top-8 h-24 w-24 rounded-full bg-gradient-to-br opacity-10 blur-xl',
                    gradientClasses[variant]
                )}
            />

            <div className="relative flex items-start justify-between">
                <div className="space-y-2">
                    <p className="text-sm font-medium text-muted-foreground">{title}</p>
                    <p className="text-3xl font-bold tracking-tight">
                        {typeof value === 'number'
                            ? new Intl.NumberFormat('id-ID').format(value)
                            : value}
                    </p>
                    {description && (
                        <p className="text-sm text-muted-foreground">{description}</p>
                    )}
                    {trend && (
                        <div className="flex items-center gap-1">
                            <span
                                className={cn(
                                    'text-sm font-medium',
                                    trend.type === 'up' && 'text-emerald-500',
                                    trend.type === 'down' && 'text-red-500',
                                    trend.type === 'neutral' && 'text-muted-foreground'
                                )}
                            >
                                {trend.type === 'up' && '↑'}
                                {trend.type === 'down' && '↓'}
                                {trend.value}%
                            </span>
                            <span className="text-sm text-muted-foreground">vs bulan lalu</span>
                        </div>
                    )}
                </div>

                {Icon && (
                    <div
                        className={cn(
                            'flex h-12 w-12 items-center justify-center rounded-xl text-white shadow-lg',
                            iconBgClasses[variant]
                        )}
                    >
                        <Icon className="h-6 w-6" />
                    </div>
                )}
            </div>
        </div>
    );
}
