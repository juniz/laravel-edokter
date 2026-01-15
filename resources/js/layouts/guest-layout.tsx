import React from "react";
import { Link } from "@inertiajs/react";
import { Button } from "@/components/ui/button";
import { LogIn, UserPlus, Menu, X } from "lucide-react";

interface GuestLayoutProps {
	children: React.ReactNode;
	showHeader?: boolean;
	companyName?: string;
	companyLogo?: string;
}

export default function GuestLayout({
	children,
	showHeader = true,
	companyName = "AbaHost",
	companyLogo,
}: GuestLayoutProps) {
	const [mobileMenuOpen, setMobileMenuOpen] = React.useState(false);

	return (
		<div className="min-h-screen bg-background">
			{/* Header */}
			{showHeader && (
				<header className="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
					<div className="max-w-7xl mx-auto flex h-16 items-center justify-between px-4 md:px-6 lg:px-8">
						{/* Logo */}
						<Link href="/" className="flex items-center gap-2">
							{companyLogo ? (
								<>
									<img
										src={companyLogo}
										alt={companyName}
										className="h-8 w-auto"
									/>
									<span className="font-bold text-xl hidden sm:inline-block">
										{companyName}
									</span>
								</>
							) : (
								<div className="flex items-center gap-2">
									<div className="h-8 w-8 rounded-lg bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)] flex items-center justify-center">
										<span className="text-white font-bold text-sm">A</span>
									</div>
									<span className="font-bold text-xl hidden sm:inline-block">
										{companyName}
									</span>
								</div>
							)}
						</Link>

						{/* Desktop Navigation */}
						{/* <nav className="hidden md:flex items-center gap-6">
							<Link
								href={route("catalog.guest")}
								className="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors"
							>
								Layanan
							</Link>
							<a
								href="#features"
								className="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors"
							>
								Fitur
							</a>
							<a
								href="#pricing"
								className="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors"
							>
								Harga
							</a>
						</nav> */}

						{/* Desktop CTA */}
						<div className="hidden md:flex items-center gap-3">
							<Link href={route("login")}>
								<Button variant="ghost" size="sm">
									<LogIn className="h-4 w-4 mr-2" />
									Masuk
								</Button>
							</Link>
							<Link href={route("register")}>
								<Button variant="gradient" size="sm">
									<UserPlus className="h-4 w-4 mr-2" />
									Daftar
								</Button>
							</Link>
						</div>

						{/* Mobile Menu Button */}
						<button
							className="md:hidden p-2 rounded-lg hover:bg-muted"
							onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
						>
							{mobileMenuOpen ? (
								<X className="h-5 w-5" />
							) : (
								<Menu className="h-5 w-5" />
							)}
						</button>
					</div>

					{/* Mobile Menu */}
					{mobileMenuOpen && (
						<div className="md:hidden border-t bg-background">
							<nav className="max-w-7xl mx-auto px-4 py-4 space-y-3">
								{/* <Link
									href={route("catalog.guest")}
									className="block text-sm font-medium text-muted-foreground hover:text-foreground transition-colors py-2"
									onClick={() => setMobileMenuOpen(false)}
								>
									Layanan
								</Link>
								<a
									href="#features"
									className="block text-sm font-medium text-muted-foreground hover:text-foreground transition-colors py-2"
									onClick={() => setMobileMenuOpen(false)}
								>
									Fitur
								</a>
								<a
									href="#pricing"
									className="block text-sm font-medium text-muted-foreground hover:text-foreground transition-colors py-2"
									onClick={() => setMobileMenuOpen(false)}
								>
									Harga
								</a> */}
								<div className="pt-4 border-t space-y-2">
									<Link href={route("login")} className="block">
										<Button variant="outline" className="w-full" size="sm">
											<LogIn className="h-4 w-4 mr-2" />
											Masuk
										</Button>
									</Link>
									<Link href={route("register")} className="block">
										<Button variant="gradient" className="w-full" size="sm">
											<UserPlus className="h-4 w-4 mr-2" />
											Daftar Sekarang
										</Button>
									</Link>
								</div>
							</nav>
						</div>
					)}
				</header>
			)}

			{/* Main Content */}
			<main>{children}</main>

			{/* Footer */}
			<footer className="border-t bg-muted/30">
				<div className="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 py-12">
					<div className="grid grid-cols-1 md:grid-cols-4 gap-8">
						{/* Company Info */}
						<div className="md:col-span-2">
							<div className="flex items-center gap-2 mb-4">
								{/* <div className="h-8 w-8 rounded-lg bg-gradient-to-br from-[var(--gradient-start)] to-[var(--gradient-end)] flex items-center justify-center">
                  <span className="text-white font-bold text-sm">A</span>
                </div> */}
								<span className="font-bold text-xl">{companyName}</span>
							</div>
							<p className="text-sm text-muted-foreground max-w-sm">
								Solusi hosting terbaik untuk bisnis Anda. Performa tinggi,
								keamanan terjamin, dan dukungan 24/7.
							</p>
						</div>

						{/* Quick Links */}
						<div>
							<h3 className="font-semibold mb-4">Layanan</h3>
							<ul className="space-y-2 text-sm text-muted-foreground">
								<li>
									<Link
										href={route("catalog.guest")}
										className="hover:text-foreground transition-colors"
									>
										Shared Hosting
									</Link>
								</li>
								<li>
									<Link
										href={route("catalog.guest")}
										className="hover:text-foreground transition-colors"
									>
										VPS
									</Link>
								</li>
								<li>
									<Link
										href={route("catalog.guest")}
										className="hover:text-foreground transition-colors"
									>
										Domain
									</Link>
								</li>
							</ul>
						</div>

						{/* Support */}
						<div>
							<h3 className="font-semibold mb-4">Dukungan</h3>
							<ul className="space-y-2 text-sm text-muted-foreground">
								<li>
									<Link
										href={route("login")}
										className="hover:text-foreground transition-colors"
									>
										Client Area
									</Link>
								</li>
								<li>
									<a
										href="#"
										className="hover:text-foreground transition-colors"
									>
										Knowledge Base
									</a>
								</li>
								<li>
									<a
										href="#"
										className="hover:text-foreground transition-colors"
									>
										Hubungi Kami
									</a>
								</li>
							</ul>
						</div>
					</div>

					<div className="border-t mt-8 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
						<p className="text-sm text-muted-foreground">
							© {new Date().getFullYear()} {companyName}. All rights reserved.
						</p>
						<div className="flex items-center gap-4 text-sm text-muted-foreground">
							<a href="#" className="hover:text-foreground transition-colors">
								Kebijakan Privasi
							</a>
							<a href="#" className="hover:text-foreground transition-colors">
								Syarat & Ketentuan
							</a>
						</div>
					</div>
				</div>
			</footer>
		</div>
	);
}
