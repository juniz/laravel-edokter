import React from 'react';
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Separator } from "@/components/ui/separator";
import { Badge } from "@/components/ui/badge";
import { Check, ShieldCheck, Landmark, Loader2, ArrowLeft } from "lucide-react";

interface Step3CheckoutProps {
  data: any;
  domainPrice: any;
  processing: boolean;
  onSubmit: (e: React.FormEvent) => void;
  onPrev: () => void;
  customer?: { name: string; email: string };
  billingSettings?: {
      pph_rate: number;
      application_fee: number;
  };
  setData: (key: string, value: any) => void;
}

export default function Step3Checkout({
  data,
  domainPrice,
  processing,
  onSubmit,
  onPrev,
  customer,
  billingSettings,
  setData
}: Step3CheckoutProps) {
  
  // Calculate price based on period
  const getPrice = () => {
    // Check if there is a promo for this specific period
    if (domainPrice?.promo_registration?.registration?.[data.period]) {
        return Number(domainPrice.promo_registration.registration[data.period]);
    }

    if (!domainPrice?.registration) return 0;
    
    // Check exact match first
    if (domainPrice.registration[data.period]) {
        return Number(domainPrice.registration[data.period]);
    }
    
    // Fallback to 1 year * period
    if (domainPrice.registration[1]) {
        return Number(domainPrice.registration[1]) * data.period;
    }
    return 0;
  };

  const basePrice = getPrice();
  // Calculate tax and fees
  const pphRate = billingSettings?.pph_rate || 0.11;
  const applicationFee = Number(billingSettings?.application_fee || 0);
  
  const taxAmount = Math.round(basePrice * pphRate);
  
  // Assuming no tax/fees for now or handled by backend/display only
  // If there's tax, calculate here. For now, mirroring existing logic: Total = Subtotal
  const totalPrice = basePrice + taxAmount + applicationFee;

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: domainPrice?.currency || 'IDR',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(amount);
  };

  const banks = [
      { code: 'bca', name: 'BCA', image: '/images/payment/bank/bca.png' },
      { code: 'mandiri', name: 'Mandiri', image: '/images/payment/bank/mandiri.png' },
      { code: 'bni', name: 'BNI', image: '/images/payment/bank/bni.png' },
      { code: 'bri', name: 'BRI', image: '/images/payment/bank/bri.png' },
      { code: 'permata', name: 'Permata', image: '/images/payment/bank/permata.png' },
  ];

  // Set default payment method if not set
  React.useEffect(() => {
    if (!data.payment_method) {
        setData('payment_method', 'bca_va');
    }
  }, []);

  return (
    <div className="animate-in fade-in slide-in-from-right-4 duration-500">
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {/* LEFT COLUMN */}
        <div className="lg:col-span-8 space-y-6">
          
          <div className="flex items-center justify-between">
               <h2 className="text-2xl font-bold tracking-tight">Review & Pembayaran</h2>
               <Button variant="ghost" onClick={onPrev} size="sm">
                   <ArrowLeft className="mr-2 h-4 w-4" /> Edit Order
               </Button>
          </div>

          {/* 1. Account Details (Read Only) */}
          <Card className="shadow-sm border-zinc-200/60">
            <CardHeader>
              <CardTitle className="text-lg">Informasi Pendaftar</CardTitle>
            </CardHeader>
            <CardContent className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label>Nama Lengkap</Label>
                <div className="p-2 bg-muted rounded-md text-sm font-medium">
                    {customer?.name || '-'}
                </div>
              </div>
              <div className="space-y-2">
                <Label>Email</Label>
                <div className="p-2 bg-muted rounded-md text-sm font-medium">
                    {customer?.email || '-'}
                </div>
              </div>
               <div className="space-y-2">
                <Label>Domain</Label>
                <div className="p-2 bg-muted rounded-md text-sm font-medium">
                    {data.name}
                </div>
              </div>
               <div className="space-y-2">
                <Label>Periode</Label>
                <div className="p-2 bg-muted rounded-md text-sm font-medium">
                    {data.period} Tahun
                </div>
              </div>
            </CardContent>
          </Card>

          {/* 2. Payment Method (Midtrans Visual) */}
          <div className="space-y-3">
            <h3 className="text-lg font-semibold tracking-tight px-1">Metode Pembayaran ({banks.length})</h3>
            
            <div className="space-y-4">
               {/* Bank Selection */}
               <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                   {banks.map((bank) => {
                       const value = `${bank.code}_va`;
                       const isSelected = data.payment_method === value;
                       return (
                           <div 
                               key={bank.code}
                               className={`
                                   relative cursor-pointer rounded-xl border-2 p-4 transition-all hover:bg-muted/50 h-24 flex items-center justify-center
                                   ${isSelected ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'border-muted bg-card hover:border-primary/50'}
                               `}
                               onClick={() => setData('payment_method', value)}
                           >
                               <div className="flex flex-col items-center justify-center gap-2 text-center w-full h-full">
                                   <img 
                                       src={bank.image} 
                                       alt={bank.name} 
                                       className="h-8 object-contain max-w-[80%]" 
                                   />
                               </div>
                               {isSelected && (
                                   <div className="absolute top-2 right-2 h-4 w-4 rounded-full bg-primary text-primary-foreground flex items-center justify-center">
                                       <Check className="h-2.5 w-2.5" />
                                   </div>
                               )}
                           </div>
                       );
                   })}
               </div>

                {/* <div className="relative rounded-xl border border-blue-200 bg-blue-50/50 p-4 text-sm text-blue-900">
                  <div className="flex gap-3">
                    <div className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-600 text-white mt-0.5">
                      <Check className="h-3 w-3" />
                    </div>
                    <div>
                      <p className="font-medium">Pembayaran Otomatis</p>
                      <p className="text-blue-700/80 mt-1">
                        Layanan Anda akan aktif secara otomatis segera setelah pembayaran Anda dikonfirmasi oleh sistem ({data.payment_method?.replace('_', ' ').toUpperCase() || 'VA'}).
                      </p>
                    </div>
                  </div>
                </div> */}
            </div>
          </div>
        </div>

        {/* RIGHT COLUMN: Sticky Summary */}
        <div className="lg:col-span-4">
          <div className="sticky top-6">
            <Card className="shadow-lg border-zinc-200 bg-zinc-50/50 backdrop-blur-xl">
              <CardHeader className="pb-4 border-b border-zinc-100">
                <div className="flex justify-between items-center">
                  <CardTitle className="text-base">Ringkasan Pesanan</CardTitle>
                </div>
              </CardHeader>
              <CardContent className="space-y-4 pt-6 text-sm">
                <div className="flex justify-between items-start">
                  <span className="text-muted-foreground">Domain Registration<br/>
                    <span className="text-xs text-foreground font-medium">{data.name} ({data.period} Tahun)</span>
                  </span>
                  <span className="font-medium">{formatCurrency(basePrice)}</span>
                </div>
                {/* Additions if any */}
                {data.buy_whois_protection && (
                    <div className="flex justify-between">
                      <span className="text-muted-foreground">WHOIS Protection</span>
                      <span className="font-medium">Free</span> 
                    </div>
                )}
                
                {/* Tax & Fees */}
                {taxAmount > 0 && (
                     <div className="flex justify-between">
                      <span className="text-muted-foreground">PPN ({Math.round(pphRate * 100)}%)</span>
                      <span className="font-medium">{formatCurrency(taxAmount)}</span> 
                    </div>
                )}
                
                 <div className="flex justify-between">
                      <span className="text-muted-foreground">Biaya Aplikasi</span>
                      <span className="font-medium">{formatCurrency(applicationFee)}</span> 
                 </div>
                
                <Separator />
                <div className="flex justify-between items-center">
                  <span className="font-bold text-lg">Total</span>
                  <span className="font-bold text-xl text-primary">{formatCurrency(totalPrice)}</span>
                </div>
              </CardContent>
              <CardFooter className="flex-col gap-3 pt-2">
                <Button 
                  size="lg" 
                  className="w-full font-semibold text-md shadow-md" 
                  onClick={onSubmit}
                  disabled={processing}
                >
                  {processing ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : "Bayar Sekarang"}
                </Button>
                <div className="text-center text-xs text-muted-foreground flex items-center justify-center gap-1.5">
                  <ShieldCheck className="h-3.5 w-3.5 text-green-600" />
                  <span>Secured by Midtrans</span>
                </div>
              </CardFooter>
            </Card>
          </div>
        </div>

      </div>
    </div>
  );
}
