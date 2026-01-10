import * as React from 'react';
import { cva, type VariantProps } from 'class-variance-authority';

import { cn } from '@/lib/utils';

const badgeVariants = cva(
    'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
    {
        variants: {
            variant: {
                default:
                    'border-transparent bg-primary text-primary-foreground shadow-sm hover:bg-primary/80',
                secondary:
                    'border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80',
                destructive:
                    'border-transparent bg-destructive text-destructive-foreground shadow-sm hover:bg-destructive/80',
                outline: 'text-foreground border-border',
                // Premium status variants with gradients
                success:
                    'border-transparent bg-gradient-to-r from-emerald-500 to-green-500 text-white shadow-sm',
                warning:
                    'border-transparent bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-sm',
                error:
                    'border-transparent bg-gradient-to-r from-red-500 to-rose-500 text-white shadow-sm',
                info:
                    'border-transparent bg-gradient-to-r from-[var(--gradient-start)] to-[var(--gradient-end)] text-white shadow-sm',
                // Softer variants
                'success-soft':
                    'border-transparent bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                'warning-soft':
                    'border-transparent bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                'error-soft':
                    'border-transparent bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                'info-soft':
                    'border-transparent bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                // Premium variants
                gradient:
                    'border-transparent bg-gradient-to-r from-[var(--gradient-start)] to-[var(--gradient-end)] text-white shadow-sm',
                'gradient-purple':
                    'border-transparent bg-gradient-to-r from-[var(--accent-purple)] to-[var(--accent-pink)] text-white shadow-sm',
                'gradient-teal':
                    'border-transparent bg-gradient-to-r from-[var(--accent-teal)] to-[var(--accent-cyan)] text-white shadow-sm',
                // Glow variant with pulse
                glow:
                    'border-transparent bg-gradient-to-r from-[var(--gradient-start)] to-[var(--gradient-end)] text-white shadow-sm animate-pulse',
                // Status indicator (small dot)
                dot:
                    'border-transparent bg-transparent text-foreground gap-1.5 pl-0',
            },
        },
        defaultVariants: {
            variant: 'default',
        },
    }
);

export interface BadgeProps
    extends React.HTMLAttributes<HTMLDivElement>,
        VariantProps<typeof badgeVariants> {
    dot?: boolean;
    dotColor?: 'success' | 'warning' | 'error' | 'info' | 'default';
}

const dotColors = {
    success: 'bg-emerald-500',
    warning: 'bg-amber-500',
    error: 'bg-red-500',
    info: 'bg-blue-500',
    default: 'bg-gray-500',
};

function Badge({ className, variant, dot, dotColor = 'default', children, ...props }: BadgeProps) {
    return (
        <div className={cn(badgeVariants({ variant }), className)} {...props}>
            {(variant === 'dot' || dot) && (
                <span className={cn('w-2 h-2 rounded-full', dotColors[dotColor])} />
            )}
            {children}
        </div>
    );
}

export { Badge, badgeVariants };
