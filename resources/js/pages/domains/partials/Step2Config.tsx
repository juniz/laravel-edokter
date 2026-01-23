import React, { useEffect, useState } from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { 
  Select, 
  SelectContent, 
  SelectItem, 
  SelectTrigger, 
  SelectValue 
} from '@/components/ui/select';
import { Trash2, Plus, ArrowRight, ArrowLeft } from 'lucide-react';

interface Customer {
  id: string;
  name: string;
  email: string;
}

interface Step2ConfigProps {
  data: any;
  setData: (key: string, value: any) => void;
  customers: Customer[];
  domainPrice: {
    registration: Record<string, number | string>;
    currency: string;
  } | null;
  loadingPrice: boolean;
  errors: any;
  translateError: (key: string, msg: string) => string;
  onNext: () => void;
  onPrev: () => void;
}

export default function Step2Config({
  data,
  setData,
  customers,
  domainPrice,
  loadingPrice,
  errors,
  translateError,
  onNext,
  onPrev
}: Step2ConfigProps) {
  
  const handleAddNameserver = () => {
    if (data.nameserver.length < 5) {
      setData('nameserver', [...data.nameserver, '']);
    }
  };

  const handleRemoveNameserver = (index: number) => {
    if (data.nameserver.length > 2) {
      const newNameservers = data.nameserver.filter((_, i) => i !== index);
      setData('nameserver', newNameservers);
    }
  };

  const formatCurrency = (amount: number | string) => {
    const num = typeof amount === 'string' ? parseFloat(amount) : amount;
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: domainPrice?.currency || 'IDR',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(num);
  };

  const isCustomer = window.location.pathname.startsWith('/customer');

  return (
    <div className="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
      <Card>
        <CardContent className="p-6 space-y-6">
          <div className="space-y-2">
            <h2 className="text-xl font-semibold">Konfigurasi Domain</h2>
            <p className="text-sm text-muted-foreground">
              Sesuaikan pengaturan domain Anda sebelum melanjutkan.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-1 gap-6">
            {/* Customer Selection (Admin Only) */}
            {!isCustomer && (
              <div className="space-y-2">
                <Label htmlFor="customer_id">Customer *</Label>
                <Select
                  value={data.customer_id}
                  onValueChange={(value) => setData('customer_id', value)}
                >
                  <SelectTrigger id="customer_id" className={errors.customer_id ? 'border-red-500' : ''}>
                    <SelectValue placeholder="Pilih Customer" />
                  </SelectTrigger>
                  <SelectContent>
                    {customers.map((customer) => (
                      <SelectItem key={customer.id} value={customer.id}>
                        {customer.name} ({customer.email})
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.customer_id && (
                  <p className="text-xs text-red-500 mt-1">{translateError('customer_id', errors.customer_id)}</p>
                )}
              </div>
            )}

            {/* Registration Period */}
            <div className="space-y-2">
              <Label>Periode Pendaftaran *</Label>
              <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                {Array.from({ length: 10 }, (_, i) => i + 1).map((year) => {
                  const regPrice = domainPrice?.registration?.[year];
                  const promoPrice = domainPrice?.promo_registration?.registration?.[year];
                  
                  // Use promo price if available, otherwise regular price
                  // If neither exists directly, calculate from 1 year (fallback)
                  // Note: Promos usually apply to specific years (often just 1st year), so fallback calculation might be tricky.
                  // For now, if no specific year price, fall back to 1 year regular * years.
                  
                  const finalPrice = promoPrice 
                      ? promoPrice 
                      : (regPrice ? regPrice : (domainPrice?.registration?.[1] ? Number(domainPrice.registration[1]) * year : null));

                  const originalPrice = promoPrice ? (regPrice || Number(domainPrice?.registration?.[1]) * year) : null;

                  return (
                    <div
                      key={year}
                      onClick={() => setData('period', year)}
                      className={`
                        cursor-pointer rounded-lg border-2 p-3 text-center transition-all hover:border-primary/50 relative overflow-hidden
                        ${data.period === year 
                          ? 'border-primary bg-primary/5 shadow-sm' 
                          : 'border-muted bg-card'
                        }
                      `}
                    >
                      {promoPrice && (
                          <div className="absolute top-0 right-0 bg-red-500 text-white text-[10px] px-1.5 py-0.5 rounded-bl-lg font-bold">
                              PROMO
                          </div>
                      )}
                      
                      <div className="text-sm font-semibold">{year} Year(s)</div>
                      <div className="flex flex-col items-center mt-1">
                          {loadingPrice ? (
                              <span className="text-xs text-muted-foreground">...</span>
                          ) : (
                              <>
                                {originalPrice && (
                                    <span className="text-[10px] text-muted-foreground line-through decoration-red-500/50">
                                        {formatCurrency(originalPrice)}
                                    </span>
                                )}
                                <span className={`text-xs ${promoPrice ? 'font-bold text-red-600' : 'text-muted-foreground'}`}>
                                    {finalPrice ? formatCurrency(finalPrice) : '-'}
                                </span>
                              </>
                          )}
                      </div>
                    </div>
                  );
                })}
              </div>
              {errors.period && (
                <p className="text-xs text-red-500 mt-1">{translateError('period', errors.period)}</p>
              )}
            </div>
          </div>

          {/* Nameservers */}
          <div className="space-y-3 pt-4 border-t">
            <div className="flex items-center justify-between">
              <Label>Nameservers</Label>
              <Button
                type="button"
                variant="outline"
                size="sm"
                onClick={handleAddNameserver}
                disabled={data.nameserver.length >= 5}
              >
                <Plus className="h-3 w-3 mr-1" />
                Tambah Nameserver
              </Button>
            </div>
            <div className="grid gap-3">
              {data.nameserver.map((ns: string, index: number) => (
                <div key={index} className="flex gap-2">
                  <div className="relative flex-1">
                    <Input
                      placeholder={`ns${index + 1}.nameserver.com`}
                      value={ns}
                      onChange={(e) => {
                        const newNs = [...data.nameserver];
                        newNs[index] = e.target.value;
                        setData('nameserver', newNs);
                      }}
                      className={errors[`nameserver.${index}`] ? 'border-red-500' : ''}
                    />
                    <span className="absolute right-3 top-2.5 text-xs text-muted-foreground pointer-events-none">
                      NS{index + 1}
                    </span>
                  </div>
                  {index >= 2 && (
                    <Button
                      type="button"
                      variant="ghost"
                      size="icon"
                      onClick={() => handleRemoveNameserver(index)}
                      className="text-red-500 hover:text-red-700 hover:bg-red-50"
                    >
                      <Trash2 className="h-4 w-4" />
                    </Button>
                  )}
                </div>
              ))}
            </div>
          </div>

          {/* Additional Options */}
          {/* <div className="space-y-4 pt-4 border-t">
            <h3 className="text-sm font-medium">Opsi Tambahan</h3>
            
            <div className="flex items-start space-x-2">
              <Checkbox
                id="buy_whois_protection"
                checked={data.buy_whois_protection}
                onCheckedChange={(checked) => setData('buy_whois_protection', checked === true)}
              />
              <div className="flex-1">
                <Label htmlFor="buy_whois_protection" className="cursor-pointer font-medium">
                  Beli Perlindungan WHOIS
                </Label>
                <p className="text-xs text-muted-foreground mt-1">
                  Menyembunyikan informasi kontak Anda dari database WHOIS publik.
                </p>
              </div>
            </div>

            <div className="flex items-start space-x-2">
              <Checkbox
                id="auto_renew"
                checked={data.auto_renew}
                onCheckedChange={(checked) => setData('auto_renew', checked === true)}
              />
              <div className="flex-1">
                <Label htmlFor="auto_renew" className="cursor-pointer font-medium">
                  Perpanjangan Otomatis
                </Label>
                <p className="text-xs text-muted-foreground mt-1">
                  Mencegah domain kedaluwarsa secara tidak sengaja.
                </p>
              </div>
            </div>
          </div> */}

          <div className="flex justify-between pt-6 border-t">
             <Button variant="outline" onClick={onPrev}>
               <ArrowLeft className="mr-2 h-4 w-4" /> Kembali
             </Button>
             <Button onClick={onNext} disabled={!data.customer_id && !isCustomer}>
               Lanjut ke Pembayaran <ArrowRight className="ml-2 h-4 w-4" />
             </Button>
          </div>

        </CardContent>
      </Card>
    </div>
  );
}
