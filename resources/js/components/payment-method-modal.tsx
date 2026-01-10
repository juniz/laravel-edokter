import React from 'react';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Button } from '@/components/ui/button';
import InputError from '@/components/input-error';
import { ShoppingCart, Check } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

interface PaymentMethod {
  value: string;
  label: string;
  icon: LucideIcon;
  bank?: string;
}

interface PaymentMethodCategory {
  category: string;
  methods: PaymentMethod[];
}

interface PaymentMethodModalProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  selectedPaymentMethod: string;
  onPaymentMethodChange: (value: string) => void;
  paymentMethods: PaymentMethodCategory[];
  totalAmount: string;
  onSubmit: (e: React.FormEvent) => void;
  processing?: boolean;
  errors?: {
    payment_method?: string;
    error?: string;
  };
  submitLabel?: string;
  cancelLabel?: string;
  title?: string;
  description?: string;
  getPaymentLogo?: (method: string) => string | null;
}

export function PaymentMethodModal({
  open,
  onOpenChange,
  selectedPaymentMethod,
  onPaymentMethodChange,
  paymentMethods,
  totalAmount,
  onSubmit,
  processing = false,
  errors = {},
  submitLabel = 'Lanjutkan Pembayaran',
  cancelLabel = 'Batal',
  title = 'Pilih Metode Pembayaran',
  description,
  getPaymentLogo,
}: PaymentMethodModalProps) {
  const defaultGetPaymentLogo = (method: string): string | null => {
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

  const getLogo = getPaymentLogo || defaultGetPaymentLogo;

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle className="text-2xl font-bold flex items-center gap-2">
            <ShoppingCart className="w-6 h-6" />
            {title}
          </DialogTitle>
          <DialogDescription className="text-base mt-1">
            {description || (
              <>
                Total Pembayaran: <strong>{totalAmount}</strong>
              </>
            )}
          </DialogDescription>
        </DialogHeader>
        <form onSubmit={onSubmit} className="space-y-4">
          <div>
            <Label className="mb-3 block">Metode Pembayaran</Label>
            <RadioGroup value={selectedPaymentMethod} onValueChange={onPaymentMethodChange}>
              <div className="space-y-4">
                {paymentMethods.map((category) => (
                  <div key={category.category}>
                    <Label className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 block">
                      {category.category}
                    </Label>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                      {category.methods.map((method) => {
                        const Icon = method.icon;
                        const isSelected = selectedPaymentMethod === method.value;
                        const logoPath = getLogo(method.value);
                        return (
                          <label
                            key={method.value}
                            className={`relative flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer transition-all ${
                              isSelected
                                ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 shadow-md'
                                : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800/50'
                            }`}
                          >
                            <RadioGroupItem value={method.value} id={method.value} className="sr-only" />
                            <div className={`flex items-center justify-center p-3 rounded-lg ${
                              isSelected
                                ? 'bg-white dark:bg-white/90'
                                : 'bg-white dark:bg-white/80'
                            }`}>
                              {logoPath ? (
                                <img 
                                  src={logoPath} 
                                  alt={method.label}
                                  className="h-12 w-auto object-contain"
                                />
                              ) : (
                                <Icon className={`w-8 h-8 ${
                                  isSelected
                                    ? 'text-blue-600 dark:text-blue-400'
                                    : 'text-gray-600 dark:text-gray-400'
                                }`} />
                              )}
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
            <InputError message={errors.payment_method} className="mt-4" />
          </div>

          {errors.error && (
            <div className="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
              <p className="text-sm text-red-600 dark:text-red-400">
                {errors.error}
              </p>
            </div>
          )}

          <div className="flex justify-end gap-2 pt-4">
            <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
              {cancelLabel}
            </Button>
            <Button type="submit" disabled={processing}>
              {processing ? 'Memproses...' : submitLabel}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
}
