import { Head, useForm, usePage, router } from "@inertiajs/react";
import { LoaderCircle, Check, ChevronRight, Mail } from "lucide-react";
import { FormEventHandler, useState, useEffect } from "react";

import InputError from "@/components/input-error";
import TextLink from "@/components/text-link";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
	Select,
	SelectContent,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from "@/components/ui/select";
import AuthLayout from "@/layouts/auth-layout";

export default function Register() {
	const [currentStep, setCurrentStep] = useState(1);
	const totalSteps = 3;
	const [verificationCode, setVerificationCode] = useState("");
	const [verificationError, setVerificationError] = useState("");
	const [isVerifying, setIsVerifying] = useState(false);
	const [isResending, setIsResending] = useState(false);
	const [emailSent, setEmailSent] = useState(false);
	const [resendCooldown, setResendCooldown] = useState(0);
	const [isInitialEmailSent, setIsInitialEmailSent] = useState(false);
	const { data, setData, post, processing, errors } = useForm<{
		name: string;
		email: string;
		password: string;
		password_confirmation: string;
		organization: string;
		phone: string;
		street_1: string;
		street_2?: string;
		city: string;
		state: string;
		country_code: string;
		postal_code: string;
		fax?: string;
	}>({
		name: "",
		email: "",
		password: "",
		password_confirmation: "",
		organization: "",
		phone: "",
		street_1: "",
		street_2: "",
		city: "",
		state: "",
		country_code: "ID",
		postal_code: "",
		fax: "",
	});

	// Fungsi untuk menerjemahkan error message ke bahasa Indonesia
	const translateError = (
		errorKey: string,
		errorMessage: string | undefined
	): string => {
		if (!errorMessage) {
			return "";
		}

		// Terjemahkan berdasarkan key field
		const fieldTranslations: Record<string, string> = {
			name: "Nama",
			email: "Email",
			password: "Kata Sandi",
			password_confirmation: "Konfirmasi Kata Sandi",
			organization: "Nama Organisasi/Perusahaan",
			phone: "Nomor Telepon",
			street_1: "Alamat Jalan",
			street_2: "Alamat Jalan 2",
			city: "Kota",
			state: "Provinsi",
			country_code: "Kode Negara",
			postal_code: "Kode Pos",
			fax: "Fax",
		};

		const fieldName =
			fieldTranslations[errorKey] ||
			errorKey.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
		let translatedMessage = errorMessage;

		// Pattern matching untuk error messages umum Laravel
		const errorPatterns: Array<{ pattern: RegExp; replacement: string }> = [
			{
				pattern: /the (.+?) field must be a string/gi,
				replacement: `${fieldName} harus berupa teks`,
			},
			{
				pattern: /the (.+?) field must be an integer/gi,
				replacement: `${fieldName} harus berupa angka`,
			},
			{
				pattern: /the (.+?) field is required/gi,
				replacement: `${fieldName} wajib diisi`,
			},
			{
				pattern: /the (.+?) must be a valid email/gi,
				replacement: `${fieldName} harus berupa email yang valid`,
			},
			{
				pattern: /the (.+?) has already been taken/gi,
				replacement: `${fieldName} sudah digunakan`,
			},
			{
				pattern: /the (.+?) does not match/gi,
				replacement: `${fieldName} tidak sesuai`,
			},
			{
				pattern: /must be at least (\d+) characters/gi,
				replacement: "minimal $1 karakter",
			},
			{
				pattern: /must not be greater than (\d+) characters/gi,
				replacement: "maksimal $1 karakter",
			},
			{
				pattern: /must be (\d+) characters/gi,
				replacement: "harus $1 karakter",
			},
			{
				pattern: /must match the required format/gi,
				replacement: "format tidak valid",
			},
			{ pattern: /is required/gi, replacement: "wajib diisi" },
		];

		errorPatterns.forEach(({ pattern, replacement }) => {
			translatedMessage = translatedMessage.replace(pattern, replacement);
		});

		if (
			translatedMessage.includes("the field") ||
			translatedMessage.includes("The field")
		) {
			translatedMessage = translatedMessage.replace(/the field/gi, fieldName);
		}

		translatedMessage = translatedMessage.replace(/\bfield\b/gi, "").trim();

		return translatedMessage;
	};

	// Helper function untuk memvalidasi field tidak kosong setelah trim
	const isFieldNotEmpty = (value: string | undefined | null): boolean => {
		return !!value && value.trim().length > 0;
	};

	// Helper function untuk memvalidasi format email dasar
	const isValidEmailFormat = (email: string): boolean => {
		const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		return emailRegex.test(email.trim());
	};

	// Validasi step 1 (Informasi Akun)
	const validateStep1 = (): boolean => {
		// Validasi semua field required tidak kosong
		if (!isFieldNotEmpty(data.name)) {
			return false;
		}
		if (!isFieldNotEmpty(data.email) || !isValidEmailFormat(data.email)) {
			return false;
		}
		if (!isFieldNotEmpty(data.password)) {
			return false;
		}
		if (!isFieldNotEmpty(data.password_confirmation)) {
			return false;
		}
		// Validasi password match
		if (data.password !== data.password_confirmation) {
			return false;
		}
		return true;
	};

	// Validasi step 2 (Informasi Customer RDASH)
	const validateStep2 = (): boolean => {
		// Validasi semua field required tidak kosong
		if (!isFieldNotEmpty(data.organization)) {
			return false;
		}
		if (!isFieldNotEmpty(data.phone)) {
			return false;
		}
		if (!isFieldNotEmpty(data.street_1)) {
			return false;
		}
		if (!isFieldNotEmpty(data.city)) {
			return false;
		}
		if (!isFieldNotEmpty(data.state)) {
			return false;
		}
		if (!isFieldNotEmpty(data.country_code)) {
			return false;
		}
		if (!isFieldNotEmpty(data.postal_code)) {
			return false;
		}
		return true;
	};

	const [isValidatingStep, setIsValidatingStep] = useState(false);
	const [validationErrors, setValidationErrors] = useState<
		Record<string, string>
	>({});

	const handleNext = () => {
		if (currentStep === 1 && validateStep1()) {
			setIsValidatingStep(true);
			setValidationErrors({});
			// Validate step 1 with backend using router.post
			router.post(
				route("register.validate-step1"),
				{
					name: data.name,
					email: data.email,
					password: data.password,
					password_confirmation: data.password_confirmation,
				},
				{
					preserveState: true,
					preserveScroll: true,
					onSuccess: () => {
						setCurrentStep(2);
						setIsValidatingStep(false);
						setValidationErrors({});
					},
					onError: (pageErrors) => {
						setIsValidatingStep(false);
						// Store validation errors from Inertia response
						if (pageErrors) {
							setValidationErrors(pageErrors as Record<string, string>);
						}
					},
				}
			);
		} else if (currentStep === 2 && validateStep2()) {
			// At step 2, we should submit the registration form
			// This will create PendingRegistration and send verification email
			// The submit() function will handle this via form onSubmit
			// So we don't need to do anything here, just let the form submit
			// Don't call handleNext() here, let submit() handle it
			return;
		}
	};

	const handlePrevious = () => {
		if (currentStep > 1) {
			setCurrentStep(currentStep - 1);
		}
	};

	// Get CSRF token from meta tag or use a helper function
	const getCsrfToken = (): string => {
		// Try to get from meta tag
		const metaToken = document
			.querySelector('meta[name="csrf-token"]')
			?.getAttribute("content");

		if (metaToken) {
			return metaToken;
		}

		// Fallback: try to get from cookie
		const cookies = document.cookie.split(";");
		for (const cookie of cookies) {
			const [name, value] = cookie.trim().split("=");
			if (name === "XSRF-TOKEN") {
				return decodeURIComponent(value);
			}
		}

		return "";
	};

	const handleVerifyCode = async () => {
		if (verificationCode.length !== 6) {
			setVerificationError("Kode verifikasi harus 6 digit");
			return;
		}

		setIsVerifying(true);
		setVerificationError("");

		try {
			let verifyUrl: string;
			try {
				verifyUrl = route("email-verification.verify");
			} catch {
				setVerificationError(
					"Route tidak ditemukan. Silakan refresh halaman dan coba lagi."
				);
				return;
			}

			// Fix: route() already returns full path, don't prepend origin if it starts with http
			const fullUrl = verifyUrl.startsWith("http")
				? verifyUrl
				: window.location.origin + verifyUrl;
			const csrfToken = getCsrfToken();

			// Check Service Worker status and try to bypass it
			if ("serviceWorker" in navigator) {
				const registrations = await navigator.serviceWorker.getRegistrations();

				// Aggressively unregister ALL service workers before making request
				if (registrations.length > 0) {
					for (const registration of registrations) {
						try {
							await registration.unregister();
						} catch {
							// Silently fail
						}
					}
					// Wait for unregistration to complete
					await new Promise((resolve) => setTimeout(resolve, 300));
				}
			}

			if (!csrfToken) {
				setVerificationError(
					"CSRF token tidak ditemukan. Silakan refresh halaman dan coba lagi."
				);
				return;
			}

			// Use AbortController for timeout and bypass service worker cache
			const controller = new AbortController();
			const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 seconds timeout

			const requestBody = {
				email: data.email,
				code: verificationCode,
			};

			const fetchOptions: RequestInit = {
				method: "POST",
				headers: {
					"Content-Type": "application/json",
					Accept: "application/json",
					"X-CSRF-TOKEN": getCsrfToken(),
					"X-Requested-With": "XMLHttpRequest",
					"Cache-Control": "no-cache, no-store, must-revalidate",
					Pragma: "no-cache",
					// Add header to bypass service worker
					"X-Bypass-Service-Worker": "true",
				},
				body: JSON.stringify(requestBody),
				cache: "no-store", // Bypass service worker cache
				signal: controller.signal,
				// Force bypass service worker by using different fetch mode
				credentials: "same-origin",
			};

			// Add timestamp to URL to bypass service worker cache
			const urlWithTimestamp = `${fullUrl}${
				fullUrl.includes("?") ? "&" : "?"
			}_t=${Date.now()}`;

			// Use fullUrl instead of verifyUrl to ensure correct URL
			// Try to bypass service worker by using fetch with different approach
			// Make the fetch request
			const response = await fetch(urlWithTimestamp, fetchOptions);

			clearTimeout(timeoutId);

			// Check if response is JSON
			const contentType = response.headers.get("content-type");

			if (!contentType || !contentType.includes("application/json")) {
				// Response is not JSON (likely HTML error page or offline.html)
				// Consume the response body to check if it's offline.html
				const text = await response.text();

				// Check if response is offline.html (browser offline mode)
				const isOfflineHtml =
					text.includes("offline") ||
					text.includes("You don't have an internet connection") ||
					text.includes("Boo! You don't have an internet connection") ||
					(response.url.includes("offline.html") &&
						text.includes("<!doctype html>"));

				if (isOfflineHtml) {
					// Try to unregister service worker and show helpful message
					if ("serviceWorker" in navigator) {
						try {
							const registrations = await navigator.serviceWorker.getRegistrations();
							for (const registration of registrations) {
								await registration.unregister();
							}
						} catch {
							// Silently fail
						}
					}

					setVerificationError(
						"Service Worker mengintercept request. Silakan refresh halaman (Ctrl+Shift+R atau Cmd+Shift+R) untuk me-reload tanpa cache, atau unregister Service Worker di Developer Tools > Application > Service Workers."
					);
					return;
				}

				// Provide user-friendly error messages based on status code
				let errorMessage = "Terjadi kesalahan saat memverifikasi kode.";

				if (response.status === 419) {
					errorMessage =
						"Sesi telah berakhir. Silakan refresh halaman dan coba lagi.";
				} else if (response.status === 500) {
					errorMessage =
						"Terjadi kesalahan pada server. Silakan coba lagi nanti.";
				} else if (response.status === 404) {
					errorMessage = "Endpoint tidak ditemukan. Silakan refresh halaman.";
				} else if (response.status >= 400) {
					errorMessage = `Terjadi kesalahan (${response.status}). Silakan coba lagi.`;
				}

				setVerificationError(errorMessage);
				return;
			}

			const result = await response.json();

			if (!response.ok) {
				// Handle error responses
				setVerificationError(
					result.message ||
						result.error ||
						`Terjadi kesalahan (${response.status}). Silakan coba lagi.`
				);
				return;
			}

			if (result.success) {
				// Redirect to dashboard after successful verification
				window.location.href = result.redirect || route("dashboard");
			} else {
				setVerificationError(result.message || "Kode verifikasi tidak valid");
			}
		} catch (error) {
			// Handle AbortError (timeout)
			if (error instanceof Error && error.name === "AbortError") {
				setVerificationError(
					"Request timeout. Server tidak merespons. Silakan coba lagi."
				);
				return;
			}

			// Handle network errors and offline mode
			if (error instanceof TypeError) {
				if (
					error.message.includes("fetch") ||
					error.message.includes("Failed to fetch")
				) {
					setVerificationError(
						"Tidak dapat terhubung ke server. Periksa koneksi internet Anda dan coba lagi."
					);
				} else if (error.message.includes("network")) {
					setVerificationError(
						"Koneksi jaringan bermasalah. Silakan coba lagi."
					);
				} else {
					setVerificationError(
						"Terjadi kesalahan saat memverifikasi kode. Silakan coba lagi."
					);
				}
			} else if (error instanceof Error) {
				// Check if error is related to JSON parsing (might be HTML response)
				if (
					error.message.includes("JSON") ||
					error.message.includes("Unexpected token")
				) {
					setVerificationError(
						"Tidak dapat memproses respons dari server. Silakan refresh halaman dan coba lagi."
					);
				} else {
					setVerificationError(`Terjadi kesalahan: ${error.message}`);
				}
			} else {
				setVerificationError("Terjadi kesalahan. Silakan coba lagi.");
			}
		} finally {
			setIsVerifying(false);
		}
	};

	const handleResendCode = async () => {
		setIsResending(true);
		setVerificationError("");

		try {
			// Use AbortController for timeout and bypass service worker cache
			const controller = new AbortController();
			const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 seconds timeout

			const response = await fetch(route("email-verification.resend"), {
				method: "POST",
				headers: {
					"Content-Type": "application/json",
					Accept: "application/json",
					"X-CSRF-TOKEN": getCsrfToken(),
					"X-Requested-With": "XMLHttpRequest",
					"Cache-Control": "no-cache, no-store, must-revalidate",
					Pragma: "no-cache",
				},
				body: JSON.stringify({
					email: data.email,
					name: data.name,
				}),
				cache: "no-store", // Bypass service worker cache
				signal: controller.signal,
			});

			clearTimeout(timeoutId);

			// Check if response is JSON
			const contentType = response.headers.get("content-type");
			if (!contentType || !contentType.includes("application/json")) {
				// Response is not JSON (likely HTML error page or offline.html)
				const text = await response.text();

				// Check if response is offline.html (browser offline mode)
				if (
					text.includes("offline") ||
					text.includes("You don't have an internet connection") ||
					text.includes("Boo! You don't have an internet connection")
				) {
					setVerificationError(
						"Tidak ada koneksi internet. Periksa koneksi Anda dan coba lagi."
					);
					return;
				}

				setVerificationError(
					`Gagal mengirim ulang kode: ${response.status} ${response.statusText}. Silakan coba lagi.`
				);
				return;
			}

			const result = await response.json();

			if (!response.ok) {
				// Handle error responses
				const errorMsg =
					result.message ||
					result.error ||
					`Gagal mengirim ulang kode (${response.status}). Silakan coba lagi.`;
				setVerificationError(errorMsg);

				// Show additional info if available (only in dev mode)
				if (result.error && import.meta.env.DEV) {
					// Error already shown to user
				}
				return;
			}

			if (result.success) {
				setEmailSent(true);
				setVerificationCode("");
				setVerificationError("");
				// Set cooldown 60 seconds
				setResendCooldown(60);
			} else {
				setVerificationError(
					result.message || "Gagal mengirim ulang kode verifikasi"
				);
			}
		} catch (error) {
			// Handle AbortError (timeout)
			if (error instanceof Error && error.name === "AbortError") {
				setVerificationError(
					"Request timeout. Server tidak merespons. Silakan coba lagi."
				);
				return;
			}

			// Handle network errors
			if (error instanceof TypeError) {
				if (
					error.message.includes("fetch") ||
					error.message.includes("Failed to fetch")
				) {
					setVerificationError(
						"Tidak dapat terhubung ke server. Periksa koneksi internet Anda dan coba lagi."
					);
				} else {
					setVerificationError(
						"Koneksi jaringan bermasalah. Silakan coba lagi."
					);
				}
			} else if (error instanceof Error) {
				// Check if error is related to JSON parsing (might be HTML response)
				if (
					error.message.includes("JSON") ||
					error.message.includes("Unexpected token")
				) {
					setVerificationError(
						"Tidak dapat memproses respons dari server. Silakan refresh halaman dan coba lagi."
					);
				} else {
					setVerificationError(`Terjadi kesalahan: ${error.message}`);
				}
			} else {
				setVerificationError("Terjadi kesalahan. Silakan coba lagi.");
			}
		} finally {
			setIsResending(false);
		}
	};

	const submit: FormEventHandler = (e) => {
		e.preventDefault();
		if (currentStep === 2 && validateStep2()) {
			// Submit registration form - email verification code will be sent automatically
			// This will create PendingRegistration and send verification email
			setIsValidatingStep(true);
			post(route("register"), {
				preserveScroll: true,
				onSuccess: () => {
					// Move to step 3 after successful registration
					setIsValidatingStep(false);
					setCurrentStep(3);
					setEmailSent(true);
				},
				onError: () => {
					// Handle errors if any
					setIsValidatingStep(false);
				},
				onFinish: () => {
					setIsValidatingStep(false);
					// Note: Flash message check is handled by useEffect below
				},
			});
			return;
		}
		if (currentStep === 3) {
			// Verify code
			handleVerifyCode();
			return;
		}
		if (currentStep < totalSteps) {
			handleNext();
			return;
		}
	};

	// Handle flash message and move to step 3 if registration successful
	const { props } = usePage();
	const flash = (props?.flash as { success?: string; error?: string }) ?? {};

	// Get errors from Inertia page props (for router.post validation errors)
	// Combine with validationErrors from onError callback
	const pageErrors = {
		...((props?.errors as Record<string, string>) ?? {}),
		...validationErrors,
	};

	useEffect(() => {
		if (flash.success && currentStep === 2) {
			// Registration successful, move to verification step
			// This is a fallback in case onSuccess callback doesn't fire
			setCurrentStep(3);
			setEmailSent(true);
			setIsValidatingStep(false);
		}
	}, [flash.success, currentStep]);

	// Auto-send email verification when entering step 3 (if not already sent)
	useEffect(() => {
		if (
			currentStep === 3 &&
			!isInitialEmailSent &&
			data.email &&
			data.name &&
			!emailSent
		) {
			setIsInitialEmailSent(true);
			// Email sudah dikirim oleh backend saat registrasi, jadi kita hanya set state
			// Jika email belum dikirim (misalnya karena error), kita akan coba kirim lagi
			setEmailSent(true);
		}
	}, [currentStep, isInitialEmailSent, data.email, data.name, emailSent]);

	// Cooldown timer for resend button
	useEffect(() => {
		if (resendCooldown > 0) {
			const timer = setTimeout(() => {
				setResendCooldown(resendCooldown - 1);
			}, 1000);
			return () => clearTimeout(timer);
		}
	}, [resendCooldown]);

	const countryCodes = [
		{ code: "ID", name: "Indonesia" },
		{ code: "MY", name: "Malaysia" },
		{ code: "SG", name: "Singapore" },
		{ code: "TH", name: "Thailand" },
		{ code: "PH", name: "Philippines" },
		{ code: "VN", name: "Vietnam" },
	];

	return (
		<AuthLayout
			title="Buat Akun Baru"
			description="Masukkan detail Anda di bawah untuk membuat akun"
		>
			<Head title="Daftar" />
			<form className="flex flex-col gap-6" onSubmit={submit}>
				{/* Stepper Indicator */}
				<div className="flex items-center justify-center mb-6">
					<div className="flex items-center w-full max-w-md">
						{/* Step 1 */}
						<div className="flex flex-col items-center flex-1">
							<div
								className={`flex items-center justify-center w-10 h-10 rounded-full border-2 transition-colors ${
									currentStep >= 1
										? "bg-primary text-primary-foreground border-primary"
										: "bg-background border-muted-foreground text-muted-foreground"
								}`}
							>
								{currentStep > 1 ? (
									<Check className="w-5 h-5" />
								) : (
									<span className="text-sm font-semibold">1</span>
								)}
							</div>
							<p
								className={`text-xs font-medium mt-2 hidden sm:block ${
									currentStep >= 1 ? "text-foreground" : "text-muted-foreground"
								}`}
							>
								Akun
							</p>
						</div>

						{/* Connector 1 */}
						<div
							className={`h-2 flex-1 mx-2 transition-colors rounded-full -mt-5 ${
								currentStep > 1 ? "bg-primary" : "bg-gray-300 dark:bg-gray-700"
							}`}
						/>

						{/* Step 2 */}
						<div className="flex flex-col items-center flex-1">
							<div
								className={`flex items-center justify-center w-10 h-10 rounded-full border-2 transition-colors ${
									currentStep >= 2
										? "bg-primary text-primary-foreground border-primary"
										: "bg-background border-muted-foreground text-muted-foreground"
								}`}
							>
								{currentStep > 2 ? (
									<Check className="w-5 h-5" />
								) : (
									<span className="text-sm font-semibold">2</span>
								)}
							</div>
							<p
								className={`text-xs font-medium mt-2 hidden sm:block ${
									currentStep >= 2 ? "text-foreground" : "text-muted-foreground"
								}`}
							>
								Data
							</p>
						</div>

						{/* Connector 2 */}
						<div
							className={`h-2 flex-1 mx-2 transition-colors rounded-full -mt-5 ${
								currentStep > 2 ? "bg-primary" : "bg-gray-300 dark:bg-gray-700"
							}`}
						/>

						{/* Step 3 */}
						<div className="flex flex-col items-center flex-1">
							<div
								className={`flex items-center justify-center w-10 h-10 rounded-full border-2 transition-colors ${
									currentStep >= 3
										? "bg-primary text-primary-foreground border-primary"
										: "bg-background border-muted-foreground text-muted-foreground"
								}`}
							>
								<span className="text-sm font-semibold">3</span>
							</div>
							<p
								className={`text-xs font-medium mt-2 hidden sm:block ${
									currentStep >= 3 ? "text-foreground" : "text-muted-foreground"
								}`}
							>
								Verifikasi
							</p>
						</div>
					</div>
				</div>

				<div className="grid gap-6">
					{/* Step 1: Informasi Akun */}
					{currentStep === 1 && (
						<div className="space-y-4 animate-in fade-in slide-in-from-right-4 duration-300">
							<div className="mb-4">
								<h3 className="text-lg font-semibold mb-1">Informasi Akun</h3>
								<p className="text-sm text-muted-foreground">
									Masukkan informasi akun Anda untuk membuat akun baru
								</p>
							</div>

							<div className="grid gap-2">
								<Label htmlFor="name">Nama Lengkap *</Label>
								<Input
									id="name"
									type="text"
									required
									autoFocus
									tabIndex={1}
									autoComplete="name"
									value={data.name}
									onChange={(e) => {
										setData("name", e.target.value);
										if (validationErrors.name) {
											setValidationErrors((prev) => {
												const newErrors = { ...prev };
												delete newErrors.name;
												return newErrors;
											});
										}
									}}
									disabled={processing}
									placeholder="Nama lengkap"
								/>
								<InputError
									message={translateError(
										"name",
										errors.name || pageErrors.name || validationErrors.name
									)}
								/>
							</div>

							<div className="grid gap-2">
								<Label htmlFor="email">Alamat Email *</Label>
								<Input
									id="email"
									type="email"
									required
									tabIndex={2}
									autoComplete="email"
									value={data.email}
									onChange={(e) => setData("email", e.target.value)}
									disabled={processing}
									placeholder="email@contoh.com"
								/>
								<InputError
									message={translateError(
										"email",
										errors.email || pageErrors.email || validationErrors.email
									)}
								/>
								<p className="text-xs text-muted-foreground">
									Pastikan email Anda valid dan dapat diakses untuk verifikasi
								</p>
							</div>

							<div className="grid gap-2">
								<Label htmlFor="password">Kata Sandi *</Label>
								<Input
									id="password"
									type="password"
									required
									tabIndex={3}
									autoComplete="new-password"
									value={data.password}
									onChange={(e) => setData("password", e.target.value)}
									disabled={processing}
									placeholder="Kata sandi"
								/>
								<InputError
									message={translateError(
										"password",
										errors.password ||
											pageErrors.password ||
											validationErrors.password
									)}
								/>
							</div>

							<div className="grid gap-2">
								<Label htmlFor="password_confirmation">
									Konfirmasi Kata Sandi *
								</Label>
								<Input
									id="password_confirmation"
									type="password"
									required
									tabIndex={4}
									autoComplete="new-password"
									value={data.password_confirmation}
									onChange={(e) =>
										setData("password_confirmation", e.target.value)
									}
									disabled={processing}
									placeholder="Ulangi kata sandi"
								/>
								<InputError
									message={translateError(
										"password_confirmation",
										errors.password_confirmation ||
											pageErrors.password_confirmation ||
											validationErrors.password_confirmation
									)}
								/>
							</div>
						</div>
					)}

					{/* Step 2: Informasi Customer RDASH */}
					{currentStep === 2 && (
						<div className="space-y-4 animate-in fade-in slide-in-from-right-4 duration-300">
							<div className="mb-4">
								<h3 className="text-lg font-semibold mb-1">
									Informasi Customer RDASH
								</h3>
							</div>

							<div className="grid gap-2">
								<Label htmlFor="organization">
									Nama Organisasi/Perusahaan *
								</Label>
								<Input
									id="organization"
									type="text"
									required
									tabIndex={5}
									value={data.organization}
									onChange={(e) => setData("organization", e.target.value)}
									disabled={processing}
									placeholder="Nama organisasi atau perusahaan"
								/>
								<InputError
									message={translateError(
										"organization",
										errors.organization ||
											pageErrors.organization ||
											validationErrors.organization
									)}
								/>
							</div>

							<div className="grid gap-2">
								<Label htmlFor="phone">Nomor Telepon *</Label>
								<Input
									id="phone"
									type="tel"
									required
									tabIndex={6}
									autoComplete="tel"
									value={data.phone}
									onChange={(e) => setData("phone", e.target.value)}
									disabled={processing}
									placeholder="081234567890"
								/>
								<InputError
									message={translateError(
										"phone",
										errors.phone || pageErrors.phone || validationErrors.phone
									)}
								/>
								<p className="text-xs text-muted-foreground">
									Minimal 9 karakter, maksimal 20 karakter
								</p>
							</div>

							<div className="grid gap-2">
								<Label htmlFor="street_1">Alamat Jalan *</Label>
								<Input
									id="street_1"
									type="text"
									required
									tabIndex={7}
									value={data.street_1}
									onChange={(e) => setData("street_1", e.target.value)}
									disabled={processing}
									placeholder="Jl. Contoh No. 123"
								/>
								<InputError
									message={translateError(
										"street_1",
										errors.street_1 ||
											pageErrors.street_1 ||
											validationErrors.street_1
									)}
								/>
							</div>

							<div className="grid gap-2">
								<Label htmlFor="street_2">Alamat Jalan 2 (Opsional)</Label>
								<Input
									id="street_2"
									type="text"
									tabIndex={8}
									value={data.street_2}
									onChange={(e) => setData("street_2", e.target.value)}
									disabled={processing}
									placeholder="RT/RW, Kelurahan, dll"
								/>
								<InputError
									message={translateError(
										"street_2",
										errors.street_2 ||
											pageErrors.street_2 ||
											validationErrors.street_2
									)}
								/>
							</div>

							<div className="grid grid-cols-2 gap-4">
								<div className="grid gap-2">
									<Label htmlFor="city">Kota *</Label>
									<Input
										id="city"
										type="text"
										required
										tabIndex={9}
										value={data.city}
										onChange={(e) => setData("city", e.target.value)}
										disabled={processing}
										placeholder="Jakarta"
									/>
									<InputError
										message={translateError(
											"city",
											errors.city || pageErrors.city || validationErrors.city
										)}
									/>
								</div>

								<div className="grid gap-2">
									<Label htmlFor="state">Provinsi *</Label>
									<Input
										id="state"
										type="text"
										required
										tabIndex={10}
										value={data.state}
										onChange={(e) => setData("state", e.target.value)}
										disabled={processing}
										placeholder="DKI Jakarta"
									/>
									<InputError
										message={translateError(
											"state",
											errors.state || pageErrors.state || validationErrors.state
										)}
									/>
								</div>
							</div>

							<div className="grid grid-cols-2 gap-4">
								<div className="grid gap-2">
									<Label htmlFor="country_code">Kode Negara *</Label>
									<Select
										value={data.country_code}
										onValueChange={(value) => setData("country_code", value)}
										disabled={processing}
									>
										<SelectTrigger id="country_code" tabIndex={11}>
											<SelectValue placeholder="Pilih negara" />
										</SelectTrigger>
										<SelectContent>
											{countryCodes.map((country) => (
												<SelectItem key={country.code} value={country.code}>
													{country.name} ({country.code})
												</SelectItem>
											))}
										</SelectContent>
									</Select>
									<InputError
										message={translateError(
											"country_code",
											errors.country_code ||
												pageErrors.country_code ||
												validationErrors.country_code
										)}
									/>
								</div>

								<div className="grid gap-2">
									<Label htmlFor="postal_code">Kode Pos *</Label>
									<Input
										id="postal_code"
										type="text"
										required
										tabIndex={12}
										value={data.postal_code}
										onChange={(e) => setData("postal_code", e.target.value)}
										disabled={processing}
										placeholder="12345"
									/>
									<InputError
										message={translateError(
											"postal_code",
											errors.postal_code ||
												pageErrors.postal_code ||
												validationErrors.postal_code
										)}
									/>
								</div>
							</div>

							<div className="grid gap-2">
								<Label htmlFor="fax">Fax (Opsional)</Label>
								<Input
									id="fax"
									type="text"
									tabIndex={13}
									value={data.fax}
									onChange={(e) => setData("fax", e.target.value)}
									disabled={processing}
									placeholder="02112345678"
								/>
								<InputError
									message={translateError(
										"fax",
										errors.fax || pageErrors.fax || validationErrors.fax
									)}
								/>
							</div>
						</div>
					)}

					{/* Step 3: Verifikasi Email */}
					{currentStep === 3 && (
						<div className="space-y-4 animate-in fade-in slide-in-from-right-4 duration-300">
							<div className="mb-4 text-center">
								<div className="mx-auto w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-4">
									<Mail className="w-8 h-8 text-primary" />
								</div>
								<h3 className="text-lg font-semibold mb-1">
									Verifikasi Email Anda
								</h3>
								<p className="text-sm text-muted-foreground">
									Kami telah mengirim kode verifikasi ke
								</p>
								<p className="text-sm font-medium text-foreground mt-1">
									{data.email}
								</p>
							</div>

							{emailSent && (
								<div className="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-4">
									<p className="text-sm text-green-800 dark:text-green-200">
										✓ Kode verifikasi telah dikirim ke email Anda. Silakan cek
										inbox atau folder spam.
									</p>
								</div>
							)}

							<div className="grid gap-2">
								<Label htmlFor="verification_code">Kode Verifikasi *</Label>
								<Input
									id="verification_code"
									type="text"
									required
									autoFocus
									maxLength={6}
									value={verificationCode}
									onChange={(e) => {
										const value = e.target.value.replace(/\D/g, "");
										setVerificationCode(value);
										setVerificationError("");
									}}
									disabled={isVerifying || processing}
									placeholder="000000"
									className="text-center text-2xl font-mono tracking-widest"
								/>
								{verificationError && (
									<p className="text-sm text-destructive">
										{verificationError}
									</p>
								)}
								<p className="text-xs text-muted-foreground">
									Masukkan 6 digit kode yang dikirim ke email Anda. Kode berlaku
									selama 15 menit.
								</p>
							</div>

							<div className="flex gap-2">
								<Button
									type="button"
									variant="outline"
									onClick={handleResendCode}
									disabled={isResending || processing || resendCooldown > 0}
									className="flex-1"
								>
									{isResending ? (
										<>
											<LoaderCircle className="w-4 h-4 animate-spin mr-2" />
											Mengirim...
										</>
									) : resendCooldown > 0 ? (
										`Kirim Ulang (${resendCooldown}s)`
									) : (
										"Kirim Ulang Kode"
									)}
								</Button>
							</div>
						</div>
					)}

					{/* Navigation Buttons */}
					<div className="flex gap-4 mt-6">
						{currentStep > 1 && (
							<Button
								type="button"
								variant="outline"
								onClick={handlePrevious}
								disabled={processing}
								className="flex-1"
							>
								Kembali
							</Button>
						)}
						{currentStep === 1 && (
							<Button
								type="button"
								onClick={handleNext}
								disabled={processing || isValidatingStep || !validateStep1()}
								className="flex-1"
							>
								{isValidatingStep ? (
									<>
										<LoaderCircle className="w-4 h-4 animate-spin mr-2" />
										Memvalidasi...
									</>
								) : (
									<>
										Selanjutnya
										<ChevronRight className="w-4 h-4 ml-2" />
									</>
								)}
							</Button>
						)}
						{currentStep === 2 && (
							<Button
								type="submit"
								disabled={processing || isValidatingStep || !validateStep2()}
								className="flex-1"
							>
								{processing || isValidatingStep ? (
									<>
										<LoaderCircle className="w-4 h-4 animate-spin mr-2" />
										Memproses...
									</>
								) : (
									<>
										Selanjutnya
										<ChevronRight className="w-4 h-4 ml-2" />
									</>
								)}
							</Button>
						)}
						{currentStep === 3 && (
							<Button
								type="submit"
								disabled={
									isVerifying || processing || verificationCode.length !== 6
								}
								className="flex-1"
							>
								{isVerifying || processing ? (
									<>
										<LoaderCircle className="h-4 w-4 animate-spin mr-2" />
										Memverifikasi...
									</>
								) : (
									"Verifikasi & Daftar"
								)}
							</Button>
						)}
					</div>
				</div>

				<div className="text-muted-foreground text-center text-sm">
					Sudah punya akun?{" "}
					<TextLink href={route("login")} tabIndex={15}>
						Masuk
					</TextLink>
				</div>
			</form>
		</AuthLayout>
	);
}
