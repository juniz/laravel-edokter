import * as React from 'react';

import { cn } from '@/lib/utils';
import { cva, type VariantProps } from 'class-variance-authority';

const cardVariants = cva(
    'rounded-xl text-card-foreground transition-all duration-300',
    {
        variants: {
            variant: {
                default: 'bg-card border shadow-sm hover:shadow-md',
                elevated: 'bg-card border shadow-lg hover:shadow-xl',
                glass: 'bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-white/20 dark:border-white/10',
                gradient: 'bg-card border-0 relative before:absolute before:inset-0 before:rounded-xl before:p-[1px] before:bg-gradient-to-br before:from-[var(--gradient-start)] before:to-[var(--gradient-end)] before:-z-10 before:content-[""]',
                premium: 'bg-card border border-border hover:border-primary/50 shadow-sm hover:shadow-xl hover:shadow-primary/5',
                interactive: 'bg-card border shadow-sm hover:shadow-lg hover:-translate-y-1 cursor-pointer',
                stat: 'bg-card border shadow-sm overflow-hidden relative before:absolute before:top-0 before:right-0 before:w-24 before:h-24 before:bg-gradient-to-br before:from-[var(--gradient-start)] before:to-[var(--gradient-end)] before:opacity-10 before:rounded-bl-full',
                featured: 'bg-card border-2 border-primary shadow-lg shadow-primary/10',
            },
        },
        defaultVariants: {
            variant: 'default',
        },
    }
);

export interface CardProps extends React.HTMLAttributes<HTMLDivElement>, VariantProps<typeof cardVariants> {}

const Card = React.forwardRef<HTMLDivElement, CardProps>(({ className, variant, ...props }, ref) => (
    <div ref={ref} className={cn(cardVariants({ variant, className }))} {...props} />
));
Card.displayName = 'Card';

const CardHeader = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(({ className, ...props }, ref) => (
    <div ref={ref} className={cn('flex flex-col space-y-1.5 p-6', className)} {...props} />
));
CardHeader.displayName = 'CardHeader';

const CardTitle = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(({ className, ...props }, ref) => (
    <div ref={ref} className={cn('text-xl font-semibold leading-none tracking-tight', className)} {...props} />
));
CardTitle.displayName = 'CardTitle';

const CardDescription = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(({ className, ...props }, ref) => (
    <div ref={ref} className={cn('text-sm text-muted-foreground', className)} {...props} />
));
CardDescription.displayName = 'CardDescription';

const CardContent = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(({ className, ...props }, ref) => (
    <div ref={ref} className={cn('p-6 pt-0', className)} {...props} />
));
CardContent.displayName = 'CardContent';

const CardFooter = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(({ className, ...props }, ref) => (
    <div ref={ref} className={cn('flex items-center p-6 pt-0', className)} {...props} />
));
CardFooter.displayName = 'CardFooter';

export { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle, cardVariants };
