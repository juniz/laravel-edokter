import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import InputError from '@/components/input-error';
import { CreditCard, Wallet, Building2, Store, QrCode, ShoppingCart, Check, ArrowLeft } from 'lucide-react';

interface Plan {
  id: string;
  code: string;
  billing_cycle: string;
  price_cents: number;
  currency: string;
  trial_days?: number;
  setup_fee_cents: number;
}

interface Product {
  id: string;
  name: string;
  slug: string;
  type: string;
  status: string;
  metadata?: {
    description?: string;
    features?: string[];
  };
  plans?: Plan[];
}

interface CatalogShowProps {
  product: Product;
  plans: Plan[];
}

export default function CatalogShow({ product, plans }: CatalogShowProps) {
  const [isPaymentModalOpen, setIsPaymentModalOpen] = useState(false);
  const [selectedPlan, setSelectedPlan] = useState<Plan | null>(null);
  const [selectedPaymentMethod, setSelectedPaymentMethod] = useState('credit_card');

  const { post, processing } = useForm({
    product_id: product.id,
    plan_id: '',
  });

  const checkoutForm = useForm({
    plan_id: '',
    payment_method: 'credit_card',
  });

  const formatPrice = (cents: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(cents);
  };

  const handleAddToCart = () => {
    post(route('cart.add'), {
      preserveScroll: true,
    });
  };

  const handleOpenPaymentModal = (plan: Plan) => {
    setSelectedPlan(plan);
    setIsPaymentModalOpen(true);
  };

  const handleClosePaymentModal = () => {
    setIsPaymentModalOpen(false);
    setSelectedPlan(null);
    setSelectedPaymentMethod('credit_card');
  };

  const handleCheckout = (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedPlan) return;

    checkoutForm.setData('plan_id', selectedPlan.id);
    checkoutForm.setData('payment_method', selectedPaymentMethod);

    checkoutForm.post(route('catalog.checkout'), {
      preserveScroll: true,
      onSuccess: () => {
        setIsPaymentModalOpen(false);
      },
    });
  };

  const paymentMethods = [
    {
      category: 'Bank Transfer',
      methods: [
        { value: 'bca_va', label: 'BCA Virtual Account', icon: Building2, bank: 'BCA' },
        { value: 'bni_va', label: 'BNI Virtual Account', icon: Building2, bank: 'BNI' },
        { value: 'bri_va', label: 'BRI Virtual Account', icon: Building2, bank: 'BRI' },
        { value: 'mandiri_va', label: 'Mandiri Virtual Account', icon: Building2, bank: 'Mandiri' },
        { value: 'permata_va', label: 'Permata Virtual Account', icon: Building2, bank: 'Permata' },
      ],
    },
    {
      category: 'E-Wallet',
      methods: [
        { value: 'gopay', label: 'GoPay', icon: Wallet },
        { value: 'shopeepay', label: 'ShopeePay', icon: Wallet },
        { value: 'dana', label: 'DANA', icon: Wallet },
        { value: 'ovo', label: 'OVO', icon: Wallet },
        { value: 'linkaja', label: 'LinkAja', icon: Wallet },
      ],
    },
    {
      category: 'QR Code',
      methods: [
        { value: 'qris', label: 'QRIS', icon: QrCode },
      ],
    },
    {
      category: 'Credit Card',
      methods: [
        { value: 'credit_card', label: 'Credit Card', icon: CreditCard },
      ],
    },
    {
      category: 'Retail',
      methods: [
        { value: 'indomaret', label: 'Indomaret', icon: Store },
        { value: 'alfamart', label: 'Alfamart', icon: Store },
      ],
    },
  ];

  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Catalog', href: '/catalog' },
    { title: product.name, href: route('catalog.show', product.slug) },
  ];

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={product.name} />
      <div className="flex flex-col gap-6 p-4">
        <div>
          <h1 className="text-3xl font-bold text-gray-900 dark:text-white">{product.name}</h1>
          {product.metadata?.description && (
            <p className="text-gray-600 dark:text-gray-400 mt-2">{product.metadata.description}</p>
          )}
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Product Info */}
          <div className="lg:col-span-2">
            <Card className="bg-white dark:bg-gray-800 shadow-md">
              <CardHeader>
                <CardTitle>Product Details</CardTitle>
              </CardHeader>
              <CardContent>
                {product.metadata?.features && (
                  <div>
                    <h3 className="font-semibold mb-3">Features:</h3>
                    <ul className="space-y-2">
                      {product.metadata.features.map((feature, idx) => (
                        <li key={idx} className="flex items-center text-sm">
                          <span className="mr-2 text-green-500">✓</span>
                          {feature}
                        </li>
                      ))}
                    </ul>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>

          {/* Plans */}
          <div className="space-y-4">
            <h2 className="text-xl font-semibold">Pilih Paket</h2>
            {plans.map((plan) => (
              <Card key={plan.id} className="bg-white dark:bg-gray-800 shadow-md hover:shadow-lg transition-shadow">
                <CardHeader>
                  <CardTitle className="text-lg">{plan.code}</CardTitle>
                  <CardDescription>
                    Billing Cycle: {plan.billing_cycle}
                    {plan.trial_days && ` • ${plan.trial_days} days trial`}
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="mb-4">
                    <div className="text-3xl font-bold">
                      {formatPrice(plan.price_cents)}
                    </div>
                    {plan.setup_fee_cents > 0 && (
                      <div className="text-sm text-gray-600 dark:text-gray-400">
                        Setup Fee: {formatPrice(plan.setup_fee_cents)}
                      </div>
                    )}
                  </div>
                  <div className="flex gap-2">
                    <Button
                      className="flex-1"
                      variant="outline"
                      onClick={handleAddToCart}
                      disabled={processing}
                    >
                      Tambah ke Cart
                    </Button>
                    <Button
                      className="flex-1"
                      onClick={() => handleOpenPaymentModal(plan)}
                      disabled={processing}
                    >
                      Bayar Sekarang
                    </Button>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>

        {/* Payment Method Modal - Ecommerce Style */}
        <Dialog open={isPaymentModalOpen} onOpenChange={setIsPaymentModalOpen}>
          <DialogContent className="max-w-5xl max-h-[90vh] overflow-hidden p-0">
            <form onSubmit={handleCheckout} className="flex flex-col h-full">
              {/* Header */}
              <div className="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <DialogHeader>
                  <DialogTitle className="text-2xl font-bold flex items-center gap-2">
                    <ShoppingCart className="w-6 h-6" />
                    Checkout
                  </DialogTitle>
                  <DialogDescription className="text-base mt-1">
                    Lengkapi pembayaran Anda untuk melanjutkan
                  </DialogDescription>
                </DialogHeader>
              </div>

              {/* Content - Two Column Layout */}
              <div className="flex-1 overflow-y-auto">
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 p-6">
                  {/* Left Column - Order Summary */}
                  <div className="lg:col-span-2 space-y-4">
                    {/* Product Info */}
                    {selectedPlan && (
                      <Card className="bg-white dark:bg-gray-800">
                        <CardHeader>
                          <CardTitle className="text-lg">Ringkasan Pesanan</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                          <div className="flex gap-4">
                            <div className="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center flex-shrink-0">
                              <Building2 className="w-10 h-10 text-white" />
                            </div>
                            <div className="flex-1">
                              <h3 className="font-semibold text-lg text-gray-900 dark:text-white">
                                {product.name}
                              </h3>
                              <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Paket: {selectedPlan.code}
                              </p>
                              <Badge variant="outline" className="mt-2">
                                {selectedPlan.billing_cycle}
                              </Badge>
                              {selectedPlan.trial_days && (
                                <Badge variant="secondary" className="ml-2">
                                  {selectedPlan.trial_days} hari trial
                                </Badge>
                              )}
                            </div>
                          </div>

                          <Separator />

                          {/* Price Breakdown */}
                          <div className="space-y-3">
                            <div className="flex justify-between text-sm">
                              <span className="text-gray-600 dark:text-gray-400">Harga Paket</span>
                              <span className="font-medium">{formatPrice(selectedPlan.price_cents)}</span>
                            </div>
                            {selectedPlan.setup_fee_cents > 0 && (
                              <div className="flex justify-between text-sm">
                                <span className="text-gray-600 dark:text-gray-400">Biaya Setup</span>
                                <span className="font-medium">{formatPrice(selectedPlan.setup_fee_cents)}</span>
                              </div>
                            )}
                            <Separator />
                            <div className="flex justify-between text-lg font-bold">
                              <span>Total Pembayaran</span>
                              <span className="text-blue-600 dark:text-blue-400">
                                {formatPrice(selectedPlan.price_cents + (selectedPlan.setup_fee_cents || 0))}
                              </span>
                            </div>
                          </div>
                        </CardContent>
                      </Card>
                    )}

                    {/* Payment Methods */}
                    <Card className="bg-white dark:bg-gray-800">
                      <CardHeader>
                        <CardTitle className="text-lg">Pilih Metode Pembayaran</CardTitle>
                        <CardDescription>
                          Pilih metode pembayaran yang paling nyaman untuk Anda
                        </CardDescription>
                      </CardHeader>
                      <CardContent>
                        <RadioGroup value={selectedPaymentMethod} onValueChange={setSelectedPaymentMethod}>
                          <div className="space-y-6">
                            {paymentMethods.map((category) => (
                              <div key={category.category}>
                                <Label className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 block">
                                  {category.category}
                                </Label>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                                  {category.methods.map((method) => {
                                    const Icon = method.icon;
                                    const isSelected = selectedPaymentMethod === method.value;
                                    return (
                                      <label
                                        key={method.value}
                                        className={`relative flex items-center gap-3 p-4 border-2 rounded-lg cursor-pointer transition-all ${
                                          isSelected
                                            ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 shadow-md'
                                            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800/50'
                                        }`}
                                      >
                                        <RadioGroupItem value={method.value} id={method.value} className="mt-0" />
                                        <div className="flex items-center gap-3 flex-1">
                                          <div className={`p-2 rounded-md ${
                                            isSelected
                                              ? 'bg-blue-100 dark:bg-blue-900/40'
                                              : 'bg-gray-100 dark:bg-gray-700'
                                          }`}>
                                            <Icon className={`w-5 h-5 ${
                                              isSelected
                                                ? 'text-blue-600 dark:text-blue-400'
                                                : 'text-gray-600 dark:text-gray-400'
                                            }`} />
                                          </div>
                                          <div className="flex-1">
                                            <span className={`font-medium block ${
                                              isSelected
                                                ? 'text-blue-900 dark:text-blue-100'
                                                : 'text-gray-900 dark:text-white'
                                            }`}>
                                              {method.label}
                                            </span>
                                            {'bank' in method && method.bank && (
                                              <span className="text-xs text-gray-500 dark:text-gray-400">
                                                {method.bank}
                                              </span>
                                            )}
                                          </div>
                                        </div>
                                        {isSelected && (
                                          <div className="absolute top-2 right-2">
                                            <div className="w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center">
                                              <Check className="w-3 h-3 text-white" />
                                            </div>
                                          </div>
                                        )}
                                      </label>
                                    );
                                  })}
                                </div>
                              </div>
                            ))}
                          </div>
                        </RadioGroup>
                        <InputError message={checkoutForm.errors.payment_method} className="mt-4" />
                      </CardContent>
                    </Card>

                    {/* Error Messages */}
                    {checkoutForm.errors.plan_id && (
                      <div className="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <InputError message={checkoutForm.errors.plan_id} />
                      </div>
                    )}

                    {'error' in checkoutForm.errors && (
                      <div className="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <p className="text-sm text-red-600 dark:text-red-400">
                          {(checkoutForm.errors as Record<string, string>).error}
                        </p>
                      </div>
                    )}
                  </div>

                  {/* Right Column - Order Summary Sidebar */}
                  <div className="lg:col-span-1">
                    <Card className="bg-gray-50 dark:bg-gray-900/50 sticky top-6">
                      <CardHeader>
                        <CardTitle className="text-lg">Ringkasan</CardTitle>
                      </CardHeader>
                      <CardContent className="space-y-4">
                        {selectedPlan && (
                          <>
                            <div className="space-y-2">
                              <div className="flex justify-between text-sm">
                                <span className="text-gray-600 dark:text-gray-400">Produk</span>
                                <span className="font-medium text-right max-w-[60%] truncate">
                                  {product.name}
                                </span>
                              </div>
                              <div className="flex justify-between text-sm">
                                <span className="text-gray-600 dark:text-gray-400">Paket</span>
                                <span className="font-medium">{selectedPlan.code}</span>
                              </div>
                              <div className="flex justify-between text-sm">
                                <span className="text-gray-600 dark:text-gray-400">Durasi</span>
                                <span className="font-medium">{selectedPlan.billing_cycle}</span>
                              </div>
                            </div>

                            <Separator />

                            <div className="space-y-2">
                              <div className="flex justify-between text-sm">
                                <span className="text-gray-600 dark:text-gray-400">Subtotal</span>
                                <span className="font-medium">{formatPrice(selectedPlan.price_cents)}</span>
                              </div>
                              {selectedPlan.setup_fee_cents > 0 && (
                                <div className="flex justify-between text-sm">
                                  <span className="text-gray-600 dark:text-gray-400">Biaya Setup</span>
                                  <span className="font-medium">{formatPrice(selectedPlan.setup_fee_cents)}</span>
                                </div>
                              )}
                            </div>

                            <Separator />

                            <div className="flex justify-between items-center">
                              <span className="text-lg font-semibold">Total</span>
                              <span className="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                {formatPrice(selectedPlan.price_cents + (selectedPlan.setup_fee_cents || 0))}
                              </span>
                            </div>

                            <Button
                              type="submit"
                              className="w-full"
                              size="lg"
                              disabled={checkoutForm.processing || !selectedPlan}
                            >
                              {checkoutForm.processing ? (
                                <>
                                  <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2" />
                                  Memproses...
                                </>
                              ) : (
                                <>
                                  Bayar Sekarang
                                  <CreditCard className="w-4 h-4 ml-2" />
                                </>
                              )}
                            </Button>

                            <Button
                              type="button"
                              variant="outline"
                              className="w-full"
                              onClick={handleClosePaymentModal}
                            >
                              <ArrowLeft className="w-4 h-4 mr-2" />
                              Kembali
                            </Button>
                          </>
                        )}
                      </CardContent>
                    </Card>
                  </div>
                </div>
              </div>
            </form>
          </DialogContent>
        </Dialog>
      </div>
    </AppLayout>
  );
}

