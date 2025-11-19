import React from "react";
import { useForm, Link } from "@inertiajs/react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import AppLayout from "@/layouts/app-layout";
import { Head } from "@inertiajs/react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Separator } from "@/components/ui/separator";
import { BreadcrumbItem } from "@/types";
import {
	Select,
	SelectContent,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from "@/components/ui/select";
import { Checkbox } from "@/components/ui/checkbox";

interface Role {
	id: number;
	name: string;
}

interface User {
	id?: number;
	name: string;
	email: string;
	roles?: string[];
}

interface Props {
	user?: User;
	roles: Role[];
	currentRoles?: string[];
}

export default function UserForm({ user, roles, currentRoles }: Props) {
	const isEdit = !!user;

	const { data, setData, post, put, processing, errors } = useForm({
		name: user?.name || "",
		email: user?.email || "",
		password: "",
		roles: currentRoles || [],
		create_rdash_customer: false,
		sync_rdash: false,
		rdash_customer: {
			organization: "",
			street_1: "",
			street_2: "",
			city: "",
			state: "",
			country_code: "ID",
			postal_code: "",
			phone: "",
			fax: "",
		},
	});

	const handleSubmit = (e: React.FormEvent) => {
		e.preventDefault();
		isEdit ? put(`/users/${user?.id}`) : post("/users");
	};

	const breadcrumbs: BreadcrumbItem[] = [
		{ title: "User Management", href: "/users" },
		{ title: isEdit ? "Edit User" : "Create User", href: "#" },
	];

	return (
		<AppLayout breadcrumbs={breadcrumbs}>
			<Head title={isEdit ? "Edit User" : "Create User"} />
			<div className="flex-1 p-4 md:p-6">
				<Card className="max-w-3xl mx-auto">
					<CardHeader className="pb-3">
						<CardTitle className="text-2xl font-bold tracking-tight">
							{isEdit ? "Edit User" : "Create New User"}
						</CardTitle>
						<p className="text-sm text-muted-foreground">
							{isEdit
								? "Update user data and roles"
								: "Enter user data and set roles"}
						</p>
					</CardHeader>

					<Separator />

					<CardContent className="pt-5">
						<form onSubmit={handleSubmit} className="space-y-8">
							<div className="space-y-4">
								{/* Name */}
								<div>
									<Label htmlFor="name" className="mb-2 block">
										Name
									</Label>
									<Input
										id="name"
										placeholder="Full name"
										value={data.name}
										onChange={(e) => setData("name", e.target.value)}
										className={errors.name ? "border-red-500" : ""}
									/>
									{errors.name && (
										<p className="text-sm text-red-500 mt-2">{errors.name}</p>
									)}
								</div>

								{/* Email */}
								<div>
									<Label htmlFor="email" className="mb-2 block">
										Email
									</Label>
									<Input
										id="email"
										placeholder="Email address"
										value={data.email}
										onChange={(e) => setData("email", e.target.value)}
										className={errors.email ? "border-red-500" : ""}
									/>
									{errors.email && (
										<p className="text-sm text-red-500 mt-2">{errors.email}</p>
									)}
								</div>

								{/* Password */}
								<div>
									<Label htmlFor="password" className="mb-2 block">
										Password {isEdit ? "(Optional)" : ""}
									</Label>
									<Input
										id="password"
										type="password"
										placeholder="••••••••"
										value={data.password}
										onChange={(e) => setData("password", e.target.value)}
										className={errors.password ? "border-red-500" : ""}
									/>
									{errors.password && (
										<p className="text-sm text-red-500 mt-2">
											{errors.password}
										</p>
									)}
								</div>

								{/* Roles */}
								<div>
									<Label className="mb-3 block">Roles</Label>
									<div className="space-y-3 border rounded-lg p-4">
										{roles.map((role) => (
											<div
												key={role.id}
												className="flex items-center space-x-2"
											>
												<Checkbox
													id={`role-${role.id}`}
													checked={data.roles.includes(role.name)}
													onCheckedChange={(checked) => {
														if (checked) {
															setData("roles", [...data.roles, role.name]);
														} else {
															setData(
																"roles",
																data.roles.filter((r) => r !== role.name)
															);
														}
													}}
												/>
												<Label
													htmlFor={`role-${role.id}`}
													className="text-sm font-normal cursor-pointer"
												>
													{role.name}
												</Label>
											</div>
										))}
									</div>
									{errors.roles && (
										<p className="text-sm text-red-500 mt-2">{errors.roles}</p>
									)}
								</div>

								{/* RDASH Integration */}
								<div className="space-y-3 border rounded-lg p-4 bg-muted/50">
									<Label className="text-base font-semibold">
										RDASH Integration
									</Label>
									<div className="space-y-3">
										{!isEdit ? (
											<>
												<div className="flex items-center space-x-2">
													<Checkbox
														id="create_rdash_customer"
														checked={data.create_rdash_customer}
														onCheckedChange={(checked) =>
															setData("create_rdash_customer", checked === true)
														}
													/>
													<Label
														htmlFor="create_rdash_customer"
														className="text-sm font-normal cursor-pointer"
													>
														Create customer in RDASH
													</Label>
												</div>
												{data.create_rdash_customer && (
													<div className="mt-4 space-y-4 pl-6 border-l-2">
														<p className="text-xs text-muted-foreground font-medium">
															RDASH Customer Information
														</p>

														<div>
															<Label
																htmlFor="rdash_organization"
																className="mb-2 block text-sm"
															>
																Organization *
															</Label>
															<Input
																id="rdash_organization"
																placeholder="Organization name"
																value={data.rdash_customer.organization}
																onChange={(e) =>
																	setData("rdash_customer", {
																		...data.rdash_customer,
																		organization: e.target.value,
																	})
																}
															/>
														</div>

														<div>
															<Label
																htmlFor="rdash_street_1"
																className="mb-2 block text-sm"
															>
																Street 1 *
															</Label>
															<Input
																id="rdash_street_1"
																placeholder="Street address"
																value={data.rdash_customer.street_1}
																onChange={(e) =>
																	setData("rdash_customer", {
																		...data.rdash_customer,
																		street_1: e.target.value,
																	})
																}
															/>
														</div>

														<div>
															<Label
																htmlFor="rdash_street_2"
																className="mb-2 block text-sm"
															>
																Street 2
															</Label>
															<Input
																id="rdash_street_2"
																placeholder="Street address 2 (optional)"
																value={data.rdash_customer.street_2}
																onChange={(e) =>
																	setData("rdash_customer", {
																		...data.rdash_customer,
																		street_2: e.target.value,
																	})
																}
															/>
														</div>

														<div className="grid grid-cols-2 gap-4">
															<div>
																<Label
																	htmlFor="rdash_city"
																	className="mb-2 block text-sm"
																>
																	City *
																</Label>
																<Input
																	id="rdash_city"
																	placeholder="City"
																	value={data.rdash_customer.city}
																	onChange={(e) =>
																		setData("rdash_customer", {
																			...data.rdash_customer,
																			city: e.target.value,
																		})
																	}
																/>
															</div>
															<div>
																<Label
																	htmlFor="rdash_state"
																	className="mb-2 block text-sm"
																>
																	State
																</Label>
																<Input
																	id="rdash_state"
																	placeholder="State (optional)"
																	value={data.rdash_customer.state}
																	onChange={(e) =>
																		setData("rdash_customer", {
																			...data.rdash_customer,
																			state: e.target.value,
																		})
																	}
																/>
															</div>
														</div>

														<div className="grid grid-cols-2 gap-4">
															<div>
																<Label
																	htmlFor="rdash_country_code"
																	className="mb-2 block text-sm"
																>
																	Country Code *
																</Label>
																<Input
																	id="rdash_country_code"
																	placeholder="ID"
																	maxLength={2}
																	value={data.rdash_customer.country_code}
																	onChange={(e) =>
																		setData("rdash_customer", {
																			...data.rdash_customer,
																			country_code: e.target.value.toUpperCase(),
																		})
																	}
																/>
																<p className="text-xs text-muted-foreground mt-1">
																	ISO 3166-1 alpha-2 (e.g., ID, US, SG)
																</p>
															</div>
															<div>
																<Label
																	htmlFor="rdash_postal_code"
																	className="mb-2 block text-sm"
																>
																	Postal Code *
																</Label>
																<Input
																	id="rdash_postal_code"
																	placeholder="Postal code"
																	value={data.rdash_customer.postal_code}
																	onChange={(e) =>
																		setData("rdash_customer", {
																			...data.rdash_customer,
																			postal_code: e.target.value,
																		})
																	}
																/>
															</div>
														</div>

														<div className="grid grid-cols-2 gap-4">
															<div>
																<Label
																	htmlFor="rdash_phone"
																	className="mb-2 block text-sm"
																>
																	Phone *
																</Label>
																<Input
																	id="rdash_phone"
																	placeholder="081234567890"
																	value={data.rdash_customer.phone}
																	onChange={(e) =>
																		setData("rdash_customer", {
																			...data.rdash_customer,
																			phone: e.target.value,
																		})
																	}
																/>
																<p className="text-xs text-muted-foreground mt-1">
																	Min 9, Max 20 characters
																</p>
															</div>
															<div>
																<Label
																	htmlFor="rdash_fax"
																	className="mb-2 block text-sm"
																>
																	Fax
																</Label>
																<Input
																	id="rdash_fax"
																	placeholder="Fax number (optional)"
																	value={data.rdash_customer.fax}
																	onChange={(e) =>
																		setData("rdash_customer", {
																			...data.rdash_customer,
																			fax: e.target.value,
																		})
																	}
																/>
															</div>
														</div>
													</div>
												)}
											</>
										) : (
											<div className="flex items-center space-x-2">
												<Checkbox
													id="sync_rdash"
													checked={data.sync_rdash}
													onCheckedChange={(checked) =>
														setData("sync_rdash", checked === true)
													}
												/>
												<Label
													htmlFor="sync_rdash"
													className="text-sm font-normal cursor-pointer"
												>
													Sync to RDASH after update
												</Label>
											</div>
										)}
										<p className="text-xs text-muted-foreground">
											{!isEdit
												? "Create customer profile in RDASH API for domain management. Fields marked with * are required by RDASH API."
												: "Update customer data in RDASH API"}
										</p>
									</div>
								</div>
							</div>

							<Separator />

							<div className="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-2">
								<Link href="/users" className="w-full sm:w-auto">
									<Button type="button" variant="secondary" className="w-full">
										Back
									</Button>
								</Link>
								<Button
									type="submit"
									disabled={processing}
									className="w-full sm:w-auto"
								>
									{processing ? (
										<span className="animate-pulse">Saving...</span>
									) : isEdit ? (
										"Save Changes"
									) : (
										"Create User"
									)}
								</Button>
							</div>
						</form>
					</CardContent>
				</Card>
			</div>
		</AppLayout>
	);
}
