import { Slot } from '@radix-ui/react-slot';
import { cva, type VariantProps } from 'class-variance-authority';
import * as React from 'react';

import { cn } from '@/lib/utils';

const buttonVariants = cva(
    'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-all duration-200 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0',
    {
        variants: {
            variant: {
                default: 'bg-primary text-primary-foreground shadow-md hover:bg-primary/90 hover:shadow-lg hover:-translate-y-0.5',
                destructive: 'bg-destructive text-destructive-foreground shadow-md hover:bg-destructive/90 hover:shadow-lg hover:-translate-y-0.5',
                outline: 'border border-input bg-background hover:bg-accent hover:text-accent-foreground hover:border-primary/50',
                secondary: 'bg-secondary text-secondary-foreground shadow-sm hover:bg-secondary/80',
                ghost: 'hover:bg-accent hover:text-accent-foreground',
                link: 'text-primary underline-offset-4 hover:underline',
                // Premium variants
                gradient: 'bg-gradient-to-r from-[var(--gradient-start)] to-[var(--gradient-end)] text-white shadow-md hover:shadow-xl hover:shadow-primary/25 hover:-translate-y-0.5 active:translate-y-0',
                'gradient-purple': 'bg-gradient-to-r from-[var(--accent-purple)] to-[var(--accent-pink)] text-white shadow-md hover:shadow-xl hover:shadow-purple-500/25 hover:-translate-y-0.5 active:translate-y-0',
                'gradient-teal': 'bg-gradient-to-r from-[var(--accent-teal)] to-[var(--accent-cyan)] text-white shadow-md hover:shadow-xl hover:shadow-teal-500/25 hover:-translate-y-0.5 active:translate-y-0',
                glow: 'bg-primary text-primary-foreground shadow-md hover:shadow-[0_0_20px_rgba(37,99,235,0.5)] hover:-translate-y-0.5 active:translate-y-0',
                glass: 'bg-white/10 backdrop-blur-md border border-white/20 text-foreground hover:bg-white/20 dark:bg-white/5 dark:hover:bg-white/10',
                success: 'bg-gradient-to-r from-[var(--success)] to-emerald-500 text-white shadow-md hover:shadow-lg hover:shadow-green-500/25 hover:-translate-y-0.5',
                warning: 'bg-gradient-to-r from-[var(--warning)] to-[var(--accent-orange)] text-white shadow-md hover:shadow-lg hover:shadow-orange-500/25 hover:-translate-y-0.5',
            },
            size: {
                default: 'h-10 px-4 py-2',
                sm: 'h-9 rounded-md px-3 text-xs',
                lg: 'h-12 rounded-lg px-8 text-base',
                xl: 'h-14 rounded-xl px-10 text-lg font-semibold',
                icon: 'h-10 w-10',
            },
        },
        defaultVariants: {
            variant: 'default',
            size: 'default',
        },
    },
);

export interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement>, VariantProps<typeof buttonVariants> {
    asChild?: boolean;
    loading?: boolean;
}

const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
    ({ className, variant, size, asChild = false, loading = false, children, disabled, ...props }, ref) => {
        const Comp = asChild ? Slot : 'button';
        return (
            <Comp
                className={cn(buttonVariants({ variant, size, className }))}
                ref={ref}
                disabled={disabled || loading}
                {...props}
            >
                {loading ? (
                    <>
                        <svg
                            className="animate-spin -ml-1 mr-2 h-4 w-4"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                className="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                strokeWidth="4"
                            />
                            <path
                                className="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            />
                        </svg>
                        {children}
                    </>
                ) : (
                    children
                )}
            </Comp>
        );
    }
);
Button.displayName = 'Button';

export { Button, buttonVariants };
