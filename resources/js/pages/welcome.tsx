import { Head, Link, usePage } from '@inertiajs/react';
import { type SharedData } from '@/types';
import { useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import { PricingCard, type PricingFeature } from '@/components/ui/pricing-card';
import { FeatureCard } from '@/components/ui/feature-card';
import { DomainSearch } from '@/components/domain-search';
import {
    Server,
    Shield,
    Zap,
    Clock,
    Headphones,
    Database,
    Globe,
    Lock,
    ArrowRight,
    Star,
    ChevronRight,
    Cloud,
    Cpu,
    HardDrive,
} from 'lucide-react';

// Pricing plans data
const pricingPlans = [
    {
        name: 'Starter',
        description: 'Untuk website personal & blog',
        price: 29000,
        period: '/bulan',
        features: [
            { text: '1 Website', included: true },
            { text: '5 GB SSD Storage', included: true },
            { text: 'Unlimited Bandwidth', included: true },
            { text: 'Free SSL Certificate', included: true },
            { text: '1 Email Account', included: true },
            { text: 'Daily Backup', included: false },
            { text: 'Priority Support', included: false },
        ] as PricingFeature[],
    },
    {
        name: 'Business',
        description: 'Untuk UMKM & toko online',
        price: 79000,
        period: '/bulan',
        featured: true,
        features: [
            { text: '10 Website', included: true, highlight: true },
            { text: '25 GB NVMe SSD', included: true, highlight: true },
            { text: 'Unlimited Bandwidth', included: true },
            { text: 'Free SSL Certificate', included: true },
            { text: 'Unlimited Email', included: true },
            { text: 'Daily Backup', included: true },
            { text: 'Priority Support', included: true, highlight: true },
        ] as PricingFeature[],
    },
    {
        name: 'Enterprise',
        description: 'Untuk bisnis skala besar',
        price: 199000,
        period: '/bulan',
        features: [
            { text: 'Unlimited Website', included: true },
            { text: '100 GB NVMe SSD', included: true },
            { text: 'Unlimited Bandwidth', included: true },
            { text: 'Free SSL Wildcard', included: true },
            { text: 'Unlimited Email', included: true },
            { text: 'Daily Backup + Restore', included: true },
            { text: '24/7 Dedicated Support', included: true },
        ] as PricingFeature[],
    },
];

// Features data
const features = [
    {
        title: 'Uptime 99.9%',
        description: 'Jaminan server selalu online dengan monitoring 24/7 dan redundansi tinggi.',
        icon: Clock,
        variant: 'gradient' as const,
    },
    {
        title: 'NVMe SSD Storage',
        description: 'Performa storage hingga 10x lebih cepat dari SSD biasa.',
        icon: Zap,
        variant: 'gradient-orange' as const,
    },
    {
        title: 'Free SSL Certificate',
        description: 'Sertifikat SSL gratis untuk keamanan website Anda.',
        icon: Lock,
        variant: 'gradient-teal' as const,
    },
    {
        title: 'LiteSpeed Web Server',
        description: 'Web server tercepat untuk performa website optimal.',
        icon: Server,
        variant: 'gradient-purple' as const,
    },
    {
        title: 'Daily Backup',
        description: 'Backup otomatis setiap hari dengan restore 1-click.',
        icon: Database,
        variant: 'gradient' as const,
    },
    {
        title: 'Support 24/7',
        description: 'Tim support siap membantu kapanpun Anda butuhkan.',
        icon: Headphones,
        variant: 'gradient-orange' as const,
    },
];

// Testimonials data
const testimonials = [
    {
        name: 'Ahmad Santoso',
        role: 'CEO, TechStartup.id',
        content: 'Hosting tercepat yang pernah saya gunakan. Website kami load dalam hitungan milidetik!',
        avatar: 'AS',
        rating: 5,
    },
    {
        name: 'Dewi Lestari',
        role: 'Owner, TokoOnline.com',
        content: 'Support yang sangat responsif dan helpful. Masalah langsung solved dalam hitungan menit.',
        avatar: 'DL',
        rating: 5,
    },
    {
        name: 'Budi Prakoso',
        role: 'Developer, DigitalAgency',
        content: 'Fitur yang lengkap dengan harga yang sangat kompetitif. Recommended untuk semua developer!',
        avatar: 'BP',
        rating: 5,
    },
];

export default function Welcome() {
    const { auth, setting } = usePage<SharedData>().props;
    const [billingPeriod, setBillingPeriod] = useState<'monthly' | 'yearly'>('monthly');

    const primaryColor = setting?.warna || '#0ea5e9';

    useEffect(() => {
        document.documentElement.style.setProperty('--primary', primaryColor);
        document.documentElement.style.setProperty('--color-primary', primaryColor);
    }, [primaryColor]);

    return (
        <>
            <Head title="Welcome - Premium Hosting Indonesia" />

            {/* Navigation */}
            <nav className="fixed top-0 left-0 right-0 z-50 glass border-b border-white/10">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center justify-between h-16">
                        <div className="flex items-center gap-2">
                            <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)] flex items-center justify-center">
                                <Server className="h-5 w-5 text-white" />
                            </div>
                            <span className="text-xl font-bold">AbaHost</span>
                        </div>

                        <div className="hidden md:flex items-center gap-8">
                            <a href="#features" className="text-sm font-medium hover:text-primary transition-colors">
                                Fitur
                            </a>
                            <a href="#pricing" className="text-sm font-medium hover:text-primary transition-colors">
                                Harga
                            </a>
                            <a href="#testimonials" className="text-sm font-medium hover:text-primary transition-colors">
                                Testimoni
                            </a>
                        </div>

                        <div className="flex items-center gap-3">
                            {auth.user ? (
                                <Button variant="gradient" asChild>
                                    <Link href="/dashboard">
                                        Dashboard
                                        <ArrowRight className="ml-2 h-4 w-4" />
                                    </Link>
                                </Button>
                            ) : (
                                <>
                                    <Button variant="ghost" asChild>
                                        <Link href="/login">Masuk</Link>
                                    </Button>
                                    <Button variant="gradient" asChild>
                                        <Link href="/register">Daftar</Link>
                                    </Button>
                                </>
                            )}
                        </div>
                    </div>
                </div>
            </nav>

            {/* Hero Section */}
            <section className="relative min-h-screen flex items-center pt-16 overflow-hidden">
                {/* Background Elements */}
                <div className="absolute inset-0 bg-gradient-to-br from-background via-background to-primary/5" />
                
                {/* Animated gradient blobs */}
                <div className="absolute top-20 left-10 w-72 h-72 bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)] rounded-full opacity-10 blur-3xl animate-pulse" />
                <div className="absolute bottom-20 right-10 w-96 h-96 bg-gradient-to-br from-[var(--accent-purple)] to-[var(--accent-pink)] rounded-full opacity-10 blur-3xl animate-pulse" style={{ animationDelay: '1s' }} />
                <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-to-br from-[var(--accent-teal)] to-[var(--accent-cyan)] rounded-full opacity-5 blur-3xl" />

                {/* Grid pattern overlay */}
                <div className="absolute inset-0 bg-[linear-gradient(rgba(0,0,0,0.02)_1px,transparent_1px),linear-gradient(90deg,rgba(0,0,0,0.02)_1px,transparent_1px)] bg-[size:60px_60px]" />

                <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
                    <div className="text-center space-y-8 max-w-4xl mx-auto">
                        {/* Badge */}
                        <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 border border-primary/20">
                            <Zap className="h-4 w-4 text-[var(--gradient-start)]" />
                            <span className="text-sm font-medium">Hosting Tercepat di Indonesia</span>
                        </div>

                        {/* Heading */}
                        <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold tracking-tight">
                            Hosting Premium untuk{' '}
                            <span className="text-gradient-primary">Website Impian</span> Anda
                        </h1>

                        <p className="text-lg md:text-xl text-muted-foreground max-w-2xl mx-auto">
                            Performa tinggi, keamanan maksimal, dan dukungan 24/7. Mulai bangun website Anda
                            dengan hosting terpercaya dari kami.
                        </p>

                        {/* Domain Search */}
                        <div className="max-w-2xl mx-auto pt-4">
                            <DomainSearch
                                placeholder="Cari domain untuk website Anda..."
                                onAddToCart={(domain, price) => {
                                    console.log('Added to cart:', domain, price);
                                }}
                            />
                        </div>

                        {/* Trust indicators */}
                        <div className="flex flex-wrap items-center justify-center gap-6 pt-8 text-sm text-muted-foreground">
                            <div className="flex items-center gap-2">
                                <div className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" />
                                <span>99.9% Uptime</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <Shield className="h-4 w-4 text-emerald-500" />
                                <span>SSL Gratis</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <Headphones className="h-4 w-4 text-primary" />
                                <span>Support 24/7</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <Globe className="h-4 w-4 text-primary" />
                                <span>10,000+ Pelanggan</span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Scroll indicator */}
                <div className="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
                    <ChevronRight className="h-6 w-6 rotate-90 text-muted-foreground" />
                </div>
            </section>

            {/* Features Section */}
            <section id="features" className="py-20 lg:py-32 bg-muted/30">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center space-y-4 mb-16">
                        <h2 className="text-3xl md:text-4xl font-bold">
                            Mengapa Memilih <span className="text-gradient-primary">AbaHost</span>?
                        </h2>
                        <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
                            Kami menyediakan fitur terbaik untuk memastikan website Anda berjalan optimal
                        </p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {features.map((feature, index) => (
                            <FeatureCard
                                key={index}
                                title={feature.title}
                                description={feature.description}
                                icon={feature.icon}
                                variant={feature.variant}
                            />
                        ))}
                    </div>
                </div>
            </section>

            {/* Pricing Section */}
            <section id="pricing" className="py-20 lg:py-32">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center space-y-4 mb-16">
                        <h2 className="text-3xl md:text-4xl font-bold">
                            Paket Hosting <span className="text-gradient-primary">Terbaik</span>
                        </h2>
                        <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
                            Pilih paket yang sesuai dengan kebutuhan Anda
                        </p>

                        {/* Billing toggle */}
                        <div className="flex items-center justify-center gap-4 pt-4">
                            <button
                                onClick={() => setBillingPeriod('monthly')}
                                className={`px-4 py-2 text-sm font-medium rounded-lg transition-all ${
                                    billingPeriod === 'monthly'
                                        ? 'bg-primary text-primary-foreground shadow-md'
                                        : 'text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                Bulanan
                            </button>
                            <button
                                onClick={() => setBillingPeriod('yearly')}
                                className={`px-4 py-2 text-sm font-medium rounded-lg transition-all flex items-center gap-2 ${
                                    billingPeriod === 'yearly'
                                        ? 'bg-primary text-primary-foreground shadow-md'
                                        : 'text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                Tahunan
                                <span className="px-2 py-0.5 text-xs rounded-full bg-emerald-500 text-white">
                                    Hemat 20%
                                </span>
                            </button>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 items-start">
                        {pricingPlans.map((plan, index) => (
                            <PricingCard
                                key={index}
                                name={plan.name}
                                description={plan.description}
                                price={billingPeriod === 'yearly' ? Math.round(plan.price * 12 * 0.8) : plan.price}
                                period={billingPeriod === 'yearly' ? '/tahun' : '/bulan'}
                                features={plan.features}
                                featured={plan.featured}
                                buttonText="Mulai Sekarang"
                                onSelect={() => console.log('Selected:', plan.name)}
                            />
                        ))}
                    </div>
                </div>
            </section>

            {/* Testimonials Section */}
            <section id="testimonials" className="py-20 lg:py-32 bg-muted/30">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center space-y-4 mb-16">
                        <h2 className="text-3xl md:text-4xl font-bold">
                            Apa Kata <span className="text-gradient-primary">Pelanggan</span> Kami?
                        </h2>
                        <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
                            Ribuan pelanggan sudah merasakan layanan terbaik dari kami
                        </p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {testimonials.map((testimonial, index) => (
                            <div
                                key={index}
                                className="relative bg-card rounded-xl border p-6 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1"
                            >
                                {/* Quote mark */}
                                <div className="absolute -top-3 left-6 text-4xl text-primary/20">"</div>

                                {/* Stars */}
                                <div className="flex gap-1 mb-4">
                                    {[...Array(testimonial.rating)].map((_, i) => (
                                        <Star
                                            key={i}
                                            className="h-4 w-4 fill-amber-400 text-amber-400"
                                        />
                                    ))}
                                </div>

                                <p className="text-muted-foreground mb-6">"{testimonial.content}"</p>

                                <div className="flex items-center gap-3">
                                    <div className="w-10 h-10 rounded-full bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)] flex items-center justify-center text-white font-semibold text-sm">
                                        {testimonial.avatar}
                                    </div>
                                    <div>
                                        <p className="font-semibold text-sm">{testimonial.name}</p>
                                        <p className="text-xs text-muted-foreground">{testimonial.role}</p>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* CTA Section */}
            <section className="py-20 lg:py-32">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)] p-8 md:p-16 text-white">
                        {/* Background pattern */}
                        <div className="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:40px_40px]" />
                        
                        {/* Floating elements */}
                        <div className="absolute top-10 right-10 w-20 h-20 rounded-full bg-white/10 blur-xl animate-pulse" />
                        <div className="absolute bottom-10 left-10 w-32 h-32 rounded-full bg-white/10 blur-xl animate-pulse" style={{ animationDelay: '1s' }} />

                        <div className="relative text-center space-y-6 max-w-2xl mx-auto">
                            <h2 className="text-3xl md:text-4xl font-bold">
                                Siap Memulai Website Anda?
                            </h2>
                            <p className="text-lg text-white/80">
                                Daftar sekarang dan dapatkan diskon 50% untuk 3 bulan pertama.
                                Tidak ada risiko dengan jaminan uang kembali 30 hari.
                            </p>
                            <div className="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                                <Button
                                    size="xl"
                                    variant="secondary"
                                    className="bg-white text-primary hover:bg-white/90 shadow-xl"
                                    asChild
                                >
                                    <Link href="/register">
                                        Mulai Sekarang
                                        <ArrowRight className="ml-2 h-5 w-5" />
                                    </Link>
                                </Button>
                                <Button
                                    size="xl"
                                    variant="outline"
                                    className="border-white/30 text-white hover:bg-white/10"
                                    asChild
                                >
                                    <Link href="/catalog">Lihat Semua Paket</Link>
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Footer */}
            <footer className="py-12 border-t">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-8 mb-12">
                        <div className="col-span-2 md:col-span-1">
                            <div className="flex items-center gap-2 mb-4">
                                <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)] flex items-center justify-center">
                                    <Server className="h-5 w-5 text-white" />
                                </div>
                                <span className="text-xl font-bold">AbaHost</span>
                            </div>
                            <p className="text-sm text-muted-foreground">
                                Premium hosting terpercaya untuk website Indonesia.
                            </p>
                        </div>

                        <div>
                            <h4 className="font-semibold mb-4">Produk</h4>
                            <ul className="space-y-2 text-sm text-muted-foreground">
                                <li><a href="#" className="hover:text-primary transition-colors">Shared Hosting</a></li>
                                <li><a href="#" className="hover:text-primary transition-colors">VPS Hosting</a></li>
                                <li><a href="#" className="hover:text-primary transition-colors">Domain</a></li>
                                <li><a href="#" className="hover:text-primary transition-colors">SSL Certificate</a></li>
                            </ul>
                        </div>

                        <div>
                            <h4 className="font-semibold mb-4">Perusahaan</h4>
                            <ul className="space-y-2 text-sm text-muted-foreground">
                                <li><a href="#" className="hover:text-primary transition-colors">Tentang Kami</a></li>
                                <li><a href="#" className="hover:text-primary transition-colors">Blog</a></li>
                                <li><a href="#" className="hover:text-primary transition-colors">Karir</a></li>
                                <li><a href="#" className="hover:text-primary transition-colors">Hubungi Kami</a></li>
                            </ul>
                        </div>

                        <div>
                            <h4 className="font-semibold mb-4">Bantuan</h4>
                            <ul className="space-y-2 text-sm text-muted-foreground">
                                <li><a href="#" className="hover:text-primary transition-colors">Panduan</a></li>
                                <li><a href="#" className="hover:text-primary transition-colors">FAQ</a></li>
                                <li><a href="#" className="hover:text-primary transition-colors">Status Server</a></li>
                                <li><a href="#" className="hover:text-primary transition-colors">Support</a></li>
                            </ul>
                        </div>
                    </div>

                    <div className="flex flex-col md:flex-row items-center justify-between pt-8 border-t text-sm text-muted-foreground">
                        <p>© 2024 AbaHost. All rights reserved.</p>
                        <div className="flex gap-6 mt-4 md:mt-0">
                            <a href="#" className="hover:text-primary transition-colors">Kebijakan Privasi</a>
                            <a href="#" className="hover:text-primary transition-colors">Syarat & Ketentuan</a>
                        </div>
                    </div>
                </div>
            </footer>
        </>
    );
}