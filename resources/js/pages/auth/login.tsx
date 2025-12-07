import { Head, useForm } from "@inertiajs/react";
import { LoaderCircle } from "lucide-react";
import { FormEventHandler } from "react";

import InputError from "@/components/input-error";
import TextLink from "@/components/text-link";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import AuthLayout from "@/layouts/auth-layout";

interface LoginProps {
	status?: string;
	canResetPassword: boolean;
}

export default function Login({ status, canResetPassword }: LoginProps) {
	const { data, setData, post, processing, errors, reset } = useForm<{
		email: string;
		password: string;
		remember: boolean;
	}>({
		email: "",
		password: "",
		remember: false,
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
			email: "Email",
			password: "Kata Sandi",
			remember: "Ingat Saya",
		};

		const fieldName =
			fieldTranslations[errorKey] ||
			errorKey.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
		let translatedMessage = errorMessage;

		// Pattern matching untuk error messages umum Laravel
		const errorPatterns: Array<{ pattern: RegExp; replacement: string }> = [
			// "The field must be a string"
			{
				pattern: /the (.+?) field must be a string/gi,
				replacement: `${fieldName} harus berupa teks`,
			},
			// "The field must be an integer"
			{
				pattern: /the (.+?) field must be an integer/gi,
				replacement: `${fieldName} harus berupa angka`,
			},
			// "The field must be an array"
			{
				pattern: /the (.+?) field must be an array/gi,
				replacement: `${fieldName} harus berupa array`,
			},
			// "The field must be a boolean"
			{
				pattern: /the (.+?) field must be a boolean/gi,
				replacement: `${fieldName} harus berupa true atau false`,
			},
			// "The field is required"
			{
				pattern: /the (.+?) field is required/gi,
				replacement: `${fieldName} wajib diisi`,
			},
			// "The field may not be greater than X"
			{
				pattern: /the (.+?) field may not be greater than (\d+)/gi,
				replacement: `${fieldName} tidak boleh lebih dari $2`,
			},
			// "The field may not be less than X"
			{
				pattern: /the (.+?) field may not be less than (\d+)/gi,
				replacement: `${fieldName} tidak boleh kurang dari $2`,
			},
			// "The field must not be greater than X characters"
			{
				pattern: /the (.+?) field must not be greater than (\d+) characters/gi,
				replacement: `${fieldName} tidak boleh lebih dari $2 karakter`,
			},
			// "The field must be at least X characters"
			{
				pattern: /the (.+?) field must be at least (\d+) characters/gi,
				replacement: `${fieldName} minimal $2 karakter`,
			},
			// "The selected field is invalid"
			{
				pattern: /the selected (.+?) is invalid/gi,
				replacement: `${fieldName} yang dipilih tidak valid`,
			},
			// "The field does not exist"
			{
				pattern: /the (.+?) does not exist/gi,
				replacement: `${fieldName} tidak ditemukan`,
			},
			// "The field must be a valid email"
			{
				pattern: /the (.+?) must be a valid email/gi,
				replacement: `${fieldName} harus berupa email yang valid`,
			},
			// Generic "must be" patterns
			{ pattern: /must be a string/gi, replacement: "harus berupa teks" },
			{ pattern: /must be an integer/gi, replacement: "harus berupa angka" },
			{ pattern: /must be an array/gi, replacement: "harus berupa array" },
			{
				pattern: /must be a boolean/gi,
				replacement: "harus berupa true atau false",
			},
			{ pattern: /is required/gi, replacement: "wajib diisi" },
			{
				pattern: /must not be greater than/gi,
				replacement: "tidak boleh lebih dari",
			},
			{
				pattern: /must not be less than/gi,
				replacement: "tidak boleh kurang dari",
			},
			// Auth specific messages
			{
				pattern: /these credentials do not match our records/gi,
				replacement: "Email atau kata sandi tidak sesuai",
			},
			{
				pattern: /too many login attempts/gi,
				replacement: "Terlalu banyak percobaan login",
			},
		];

		// Apply pattern replacements
		errorPatterns.forEach(({ pattern, replacement }) => {
			translatedMessage = translatedMessage.replace(pattern, replacement);
		});

		// Jika masih mengandung "the field", ganti dengan field name
		if (
			translatedMessage.includes("the field") ||
			translatedMessage.includes("The field")
		) {
			translatedMessage = translatedMessage.replace(/the field/gi, fieldName);
		}

		// Clean up: remove "field" jika masih ada
		translatedMessage = translatedMessage.replace(/\bfield\b/gi, "").trim();

		return translatedMessage;
	};

	const submit: FormEventHandler = (e) => {
		e.preventDefault();
		post(route("login"), {
			onFinish: () => reset("password"),
		});
	};

	return (
		<AuthLayout
			title="Masuk ke akun Anda"
			description="Masukkan email dan kata sandi Anda di bawah untuk masuk"
		>
			<Head title="Masuk" />

			<form className="flex flex-col gap-6" onSubmit={submit}>
				<div className="grid gap-6">
					<div className="grid gap-2">
						<Label htmlFor="email">Alamat Email</Label>
						<Input
							id="email"
							type="email"
							required
							autoFocus
							tabIndex={1}
							autoComplete="email"
							value={data.email}
							onChange={(e) => setData("email", e.target.value)}
							placeholder="email@contoh.com"
						/>
						<InputError message={translateError("email", errors.email)} />
					</div>

					<div className="grid gap-2">
						<div className="flex items-center">
							<Label htmlFor="password">Kata Sandi</Label>
							{canResetPassword && (
								<TextLink
									href={route("password.request")}
									className="ml-auto text-sm"
									tabIndex={5}
								>
									Lupa kata sandi?
								</TextLink>
							)}
						</div>
						<Input
							id="password"
							type="password"
							required
							tabIndex={2}
							autoComplete="current-password"
							value={data.password}
							onChange={(e) => setData("password", e.target.value)}
							placeholder="Kata Sandi"
						/>
						<InputError message={translateError("password", errors.password)} />
					</div>

					<div className="flex items-center space-x-3">
						<Checkbox
							id="remember"
							name="remember"
							tabIndex={3}
							checked={data.remember}
							onCheckedChange={(checked) =>
								setData("remember", checked as boolean)
							}
						/>
						<Label htmlFor="remember">Ingat Saya</Label>
					</div>

					<Button
						type="submit"
						className="mt-4 w-full"
						tabIndex={4}
						disabled={processing}
					>
						{processing && (
							<LoaderCircle className="h-4 w-4 animate-spin mr-2" />
						)}
						Masuk
					</Button>
				</div>

				<div className="text-muted-foreground text-center text-sm">
					Belum punya akun?{" "}
					<TextLink href={route("register")} tabIndex={5}>
						Daftar
					</TextLink>
				</div>
			</form>

			{status && (
				<div className="mb-4 text-center text-sm font-medium text-green-600">
					{status}
				</div>
			)}
		</AuthLayout>
	);
}
