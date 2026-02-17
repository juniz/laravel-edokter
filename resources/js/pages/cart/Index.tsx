import React, { useState } from 'react';
import { Head, Link, useForm, router, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { PaymentMethodModal } from '@/components/payment-method-modal';
import {
  ShoppingCart,
  Trash2,
  Plus,
  Minus,
  Wallet,
  Building2,
  ArrowLeft,
  ShoppingBag,
} from 'lucide-react';

interface CartItem {
  id: string;
  product: {
    id: string;
    name: string;
    slug: string;
  };
  plan: {
    id: string;
    code: string;
  } | null;
  qty: number;
  unit_price_cents: number;
  total_cents: number;
  meta: Record<string, unknown>;
}

interface Cart {
  id: string;
  currency: string;
  totals: {
    subtotal: number;
    setup_fee?: number;
    discount: number;
    tax: number;
    total: number;
  };
  items: CartItem[];
}

interface CartIndexProps {
  cart: Cart;
}

export default function CartIndex({ cart }: CartIndexProps) {
  const { props } = usePage();
  const paymentGateway =
    (props?.paymentGateway as { manual_only?: boolean }) ?? {};
  const manualOnly = paymentGateway.manual_only === true;

  const [isCheckoutModalOpen, setIsCheckoutModalOpen] = useState(false);
  const [selectedPaymentMethod, setSelectedPaymentMethod] = useState('bca_va');
  const [updatingItemId, setUpdatingItemId] = useState<string | null>(null);

  const checkoutForm = useForm({
    payment_method: 'bca_va',
  });

  const formatPrice = (cents: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: cart.currency || 'IDR',
      minimumFractionDigits: 0,
    }).format(cents);
  };

  const handleUpdateQty = (itemId: string, newQty: number) => {
    if (newQty < 1) {
      return;
    }

    setUpdatingItemId(itemId);
    router.put(route('customer.cart.update', itemId), { qty: newQty }, {
      preserveScroll: true,
      onFinish: () => setUpdatingItemId(null),
    });
  };

  const handleRemoveItem = (itemId: string) => {
    if (confirm('Apakah Anda yakin ingin menghapus item ini dari cart?')) {
      router.delete(route('customer.cart.remove', itemId), {
        preserveScroll: true,
      });
    }
  };

  const handleClearCart = () => {
    if (confirm('Apakah Anda yakin ingin mengosongkan cart?')) {
      router.post(route('customer.cart.clear'), {}, {
        preserveScroll: true,
      });
    }
  };

  const handleCheckout = (e: React.FormEvent) => {
    e.preventDefault();

    checkoutForm.setData('payment_method', selectedPaymentMethod);
    checkoutForm.post(route('customer.cart.checkout'), {
      preserveScroll: true,
      onSuccess: () => {
        setIsCheckoutModalOpen(false);
      },
    });
  };

  const handleCheckoutManual = () => {
    checkoutForm.setData('payment_method', 'manual');
    checkoutForm.post(route('customer.cart.checkout'), {
      preserveScroll: true,
    });
  };

  const getPaymentLogo = (method: string): string | null => {
    const logos: Record<string, string> = {
      // Bank
      bca_va: '/images/payment/bank/bca.png',
      bni_va: '/images/payment/bank/bni.png',
      bri_va: '/images/payment/bank/bri.png',
      mandiri_va: '/images/payment/bank/mandiri.png',
      permata_va: '/images/payment/bank/permata.png',
      // E-Wallet
      gopay: '/images/payment/wallet/gopay.png',
      shopeepay: '/images/payment/wallet/shopeepay.png',
      dana: '/images/payment/wallet/dana.png',
      ovo: '/images/payment/wallet/ovo.png',
      linkaja: '/images/payment/wallet/linkaja.png',
    };
    return logos[method] || null;
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
  ];

  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Catalog', href: '/catalog' },
    { title: 'Cart', href: route('customer.cart.index') },
  ];

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Shopping Cart" />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
              <ShoppingCart className="w-8 h-8" />
              Shopping Cart
            </h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">
              {cart.items.length} item{cart.items.length !== 1 ? 's' : ''} di cart
            </p>
          </div>
          {cart.items.length > 0 && (
            <Button variant="outline" onClick={handleClearCart}>
              <Trash2 className="w-4 h-4 mr-2" />
              Kosongkan Cart
            </Button>
          )}
        </div>

        {cart.items.length === 0 ? (
          <Card className="bg-white dark:bg-gray-800">
            <CardContent className="p-12 text-center">
              <ShoppingBag className="w-16 h-16 mx-auto mb-4 text-gray-400" />
              <h3 className="text-xl font-semibold mb-2">Cart Anda Kosong</h3>
              <p className="text-gray-600 dark:text-gray-400 mb-6">
                Tambahkan produk ke cart untuk melanjutkan pembelian
              </p>
              <Link href={route('catalog.index')}>
                <Button>
                  <ArrowLeft className="w-4 h-4 mr-2" />
                  Kembali ke Catalog
                </Button>
              </Link>
            </CardContent>
          </Card>
        ) : (
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {/* Cart Items */}
            <div className="lg:col-span-2 space-y-4">
              {cart.items.map((item) => (
                <Card key={item.id} className="bg-white dark:bg-gray-800">
                  <CardContent className="p-6">
                    <div className="flex gap-4">
                      <div className="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center flex-shrink-0">
                        <ShoppingBag className="w-10 h-10 text-white" />
                      </div>
                      <div className="flex-1">
                        <h3 className="font-semibold text-lg text-gray-900 dark:text-white">
                          {item.product.name}
                        </h3>
                        {item.plan && (
                          <>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">
                              Paket: {item.plan.code}
                            </p>
                          </>
                        )}
                        <div className="mt-3 flex items-center gap-4">
                          <div className="flex items-center gap-2">
                            <Button
                              variant="outline"
                              size="sm"
                              onClick={() => handleUpdateQty(item.id, item.qty - 1)}
                              disabled={updatingItemId === item.id || item.qty <= 1}
                            >
                              <Minus className="w-4 h-4" />
                            </Button>
                            <span className="w-12 text-center font-medium">{item.qty}</span>
                            <Button
                              variant="outline"
                              size="sm"
                              onClick={() => handleUpdateQty(item.id, item.qty + 1)}
                              disabled={updatingItemId === item.id}
                            >
                              <Plus className="w-4 h-4" />
                            </Button>
                          </div>
                          <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => handleRemoveItem(item.id)}
                            className="text-red-600 hover:text-red-700"
                          >
                            <Trash2 className="w-4 h-4 mr-1" />
                            Hapus
                          </Button>
                        </div>
                      </div>
                      <div className="text-right">
                        <div className="text-2xl font-bold text-gray-900 dark:text-white">
                          {formatPrice(item.total_cents)}
                        </div>
                        <div className="text-sm text-gray-600 dark:text-gray-400">
                          {formatPrice(item.unit_price_cents)} / item
                        </div>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>

            {/* Order Summary */}
            <div className="lg:col-span-1">
              <Card className="bg-gray-50 dark:bg-gray-900/50 sticky top-6">
                <CardHeader>
                  <CardTitle className="text-lg">Ringkasan Pesanan</CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="space-y-2">
                    <div className="flex justify-between text-sm">
                      <span className="text-gray-600 dark:text-gray-400">Subtotal</span>
                      <span className="font-medium">{formatPrice(cart.totals.subtotal)}</span>
                    </div>
                    {cart.totals.setup_fee && cart.totals.setup_fee > 0 && (
                      <div className="flex justify-between text-sm">
                        <span className="text-gray-600 dark:text-gray-400">Biaya Setup</span>
                        <span className="font-medium">{formatPrice(cart.totals.setup_fee)}</span>
                      </div>
                    )}
                    {cart.totals.discount > 0 && (
                      <div className="flex justify-between text-sm">
                        <span className="text-gray-600 dark:text-gray-400">Diskon</span>
                        <span className="font-medium text-green-600">
                          -{formatPrice(cart.totals.discount)}
                        </span>
                      </div>
                    )}
                    {cart.totals.tax > 0 && (
                      <div className="flex justify-between text-sm">
                        <span className="text-gray-600 dark:text-gray-400">Pajak</span>
                        <span className="font-medium">{formatPrice(cart.totals.tax)}</span>
                      </div>
                    )}
                  </div>

                  <Separator />

                  <div className="flex justify-between items-center">
                    <span className="text-lg font-semibold">Total</span>
                    <span className="text-2xl font-bold text-blue-600 dark:text-blue-400">
                      {formatPrice(cart.totals.total)}
                    </span>
                  </div>

                  <Button
                    className="w-full"
                    size="lg"
                    onClick={() => {
                      if (manualOnly) {
                        handleCheckoutManual();
                        return;
                      }
                      setIsCheckoutModalOpen(true);
                    }}
                  >
                    <Wallet className="w-4 h-4 mr-2" />
                    Checkout
                  </Button>

                  <Link href={route('catalog.index')}>
                    <Button variant="outline" className="w-full">
                      <ArrowLeft className="w-4 h-4 mr-2" />
                      Lanjutkan Belanja
                    </Button>
                  </Link>
                </CardContent>
              </Card>
            </div>
          </div>
        )}

        {/* Checkout Modal */}
        {!manualOnly ? (
          <PaymentMethodModal
            open={isCheckoutModalOpen}
            onOpenChange={setIsCheckoutModalOpen}
            selectedPaymentMethod={selectedPaymentMethod}
            onPaymentMethodChange={setSelectedPaymentMethod}
            paymentMethods={paymentMethods}
            totalAmount={formatPrice(cart.totals.total)}
            onSubmit={handleCheckout}
            processing={checkoutForm.processing}
            errors={checkoutForm.errors as { payment_method?: string; error?: string }}
            getPaymentLogo={getPaymentLogo}
          />
        ) : null}
      </div>
    </AppLayout>
  );
}
