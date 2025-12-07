import React, { useState, useEffect } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { type BreadcrumbItem } from '@/types';
import { ArrowLeft, Globe, Search, Loader2, ChevronDown, CreditCard } from 'lucide-react';
import axios from 'axios';

// Determine breadcrumbs based on route
const getBreadcrumbs = (): BreadcrumbItem[] => {
  const path = window.location.pathname;
  if (path.startsWith('/customer/domains')) {
    return [
      {
        title: 'Domain Saya',
        href: '/customer/domains',
      },
      {
        title: 'Daftarkan Domain',
        href: '/customer/domains/create',
      },
    ];
  }
  return [
    {
      title: 'Manajemen Domain',
      href: '/admin/domains',
    },
    {
      title: 'Daftarkan Domain',
      href: '/admin/domains/create',
    },
  ];
};

const breadcrumbs = getBreadcrumbs();

interface Customer {
  id: string;
  name: string;
  email: string;
  rdash_customer_id: number;
}

interface Props {
  customers: Customer[];
}

// Helper function untuk format currency tanpa Rp prefix (hanya angka)
function formatCurrencyNumber(amount: number | string | undefined | null): string {
  if (!amount || amount === '' || amount === '0' || amount === '0.00') {
    return '0';
  }

  const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;

  if (isNaN(numAmount) || numAmount <= 0) {
    return '0';
  }

  return new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(numAmount);
}

export default function DomainForm({ customers }: Props) {
  // Set customer_id otomatis jika customer sendiri
  const isCustomer = window.location.pathname.startsWith('/customer');
  const defaultCustomerId = isCustomer && customers.length > 0 ? customers[0].id : '';

  const { data, setData, post, processing, errors } = useForm({
    name: '',
    period: 1,
    customer_id: defaultCustomerId,
    nameserver: ['', ''],
    buy_whois_protection: false,
    include_premium_domains: false,
    registrant_contact_id: null as number | null,
    auto_renew: false,
    payment_method: 'credit_card',
  });

  const [availabilityCheck, setAvailabilityCheck] = useState<{
    checking: boolean;
    available: boolean | null;
    message: string;
    connectionError: boolean;
  }>({
    checking: false,
    available: null,
    message: '',
    connectionError: false,
  });

  const [domainPrice, setDomainPrice] = useState<{
    registration: Record<string, number | string>;
    promo_registration?: {
      registration: Record<string, string>;
    } | null;
    currency: string;
  } | null>(null);
  const [loadingPrice, setLoadingPrice] = useState(false);

  // Extract extension from domain name
  // Handles domains like: example.com -> .com, example.co.id -> .co.id, abahweb.id -> .id
  const extractExtension = (domainName: string): string => {
    const parts = domainName.split('.');
    if (parts.length < 2) {
      return '';
    }

    // Indonesian domain extensions (2 parts)
    const indonesianExtensions = ['co', 'web', 'biz', 'my', 'or', 'ac', 'sch'];
    const lastPart = parts[parts.length - 1];
    const secondLast = parts.length >= 2 ? parts[parts.length - 2] : '';

    // Check if it's an Indonesian domain extension (e.g., .co.id, .web.id)
    if (lastPart === 'id' && indonesianExtensions.includes(secondLast)) {
      return '.' + secondLast + '.' + lastPart;
    }

    // Standard extension (single part, e.g., .com, .net, .id)
    return '.' + lastPart;
  };

  // Fetch domain price by extension
  const fetchDomainPrice = async (extension: string) => {
    if (!extension) {
      setDomainPrice(null);
      return;
    }

    setLoadingPrice(true);
    try {
      const basePath = window.location.pathname.startsWith('/customer')
        ? '/customer/domain-prices'
        : '/admin/domain-prices';

      const response = await axios.get(`${basePath}/by-extension`, {
        params: { extension },
      });

      if (response.data.success && response.data.data) {
        setDomainPrice({
          registration: response.data.data.registration || {},
          promo_registration: response.data.data.promo_registration || null,
          currency: response.data.data.currency || 'IDR',
        });
      } else {
        console.warn('Price not found for extension:', extension, response.data);
        setDomainPrice(null);
      }
    } catch (error: any) {
      console.error('Error fetching domain price:', error);
      // Don't show error to user, just silently fail
      // The UI will show "Silakan cek ketersediaan domain terlebih dahulu"
      setDomainPrice(null);
    } finally {
      setLoadingPrice(false);
    }
  };

  const handleCheckAvailability = async () => {
    if (!data.name) {
      return;
    }

    setAvailabilityCheck({ checking: true, available: null, message: '', connectionError: false });

    try {
      // Gunakan route yang sesuai dengan path saat ini
      const checkAvailabilityRoute = window.location.pathname.startsWith('/customer')
        ? '/customer/domains/check-availability'
        : '/admin/domains/check-availability';

      const response = await axios.post(checkAvailabilityRoute, {
        domain: data.name,
        include_premium_domains: data.include_premium_domains,
      });

      if (response.data.success) {
        const availability = response.data.data;
        const message = availability.message?.toLowerCase() || '';
        
        // Tentukan status berdasarkan message
        let isAvailable = false;
        let displayMessage = '';
        
        if (message === 'available') {
          isAvailable = true;
          displayMessage = 'Domain Tersedia';
        } else if (message === 'in use' || message.includes('in use')) {
          isAvailable = false;
          displayMessage = 'Domain Tidak Tersedia';
        } else {
          // Fallback: gunakan available flag dari API
          isAvailable = availability.available === true || availability.available === 1;
          displayMessage = isAvailable ? 'Domain Tersedia' : 'Domain Tidak Tersedia';
        }

        setAvailabilityCheck({
          checking: false,
          available: isAvailable,
          message: displayMessage,
          connectionError: false,
        });
      }
    } catch (error: any) {
      // Cek apakah ini error koneksi (network error, timeout, atau 500+)
      const isConnectionError = 
        !error.response || 
        error.code === 'ECONNABORTED' || 
        error.code === 'ERR_NETWORK' ||
        (error.response?.status >= 500);

      if (isConnectionError) {
        setAvailabilityCheck({
          checking: false,
          available: null,
          message: 'Koneksi sedang bermasalah, silahkan coba beberapa saat lagi',
          connectionError: true,
        });
      } else {
        // Error lainnya (misalnya validasi)
        setAvailabilityCheck({
          checking: false,
          available: false,
          message: 'Domain Tidak Tersedia',
          connectionError: false,
        });
      }
    }
  };

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

  // Fetch domain price when domain name changes and is available
  useEffect(() => {
    if (data.name && availabilityCheck.available === true) {
      const extension = extractExtension(data.name);
      if (extension) {
        fetchDomainPrice(extension);
      }
    } else if (!data.name || availabilityCheck.available === false) {
      setDomainPrice(null);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [data.name, availabilityCheck.available]);

  // Fungsi untuk menerjemahkan error message ke bahasa Indonesia
  const translateError = (errorKey: string, errorMessage: string): string => {
    // Terjemahkan berdasarkan key field
    const fieldTranslations: Record<string, string> = {
      'name': 'Nama Domain',
      'period': 'Periode Pendaftaran',
      'customer_id': 'Customer',
      'nameserver': 'Nameserver',
      'registrant_contact_id': 'Registrant Contact ID',
      'buy_whois_protection': 'Perlindungan WHOIS',
      'include_premium_domains': 'Domain Premium',
      'auto_renew': 'Perpanjangan Otomatis',
      'payment_method': 'Metode Pembayaran',
    };

    const fieldName = fieldTranslations[errorKey] || errorKey.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    let translatedMessage = errorMessage;

    // Pattern matching untuk error messages umum Laravel
    const errorPatterns: Array<{ pattern: RegExp; replacement: string }> = [
      // "The field must be a string"
      { pattern: /the (.+?) field must be a string/gi, replacement: `${fieldName} harus berupa teks` },
      // "The field must be an integer"
      { pattern: /the (.+?) field must be an integer/gi, replacement: `${fieldName} harus berupa angka` },
      // "The field must be an array"
      { pattern: /the (.+?) field must be an array/gi, replacement: `${fieldName} harus berupa array` },
      // "The field must be a boolean"
      { pattern: /the (.+?) field must be a boolean/gi, replacement: `${fieldName} harus berupa true atau false` },
      // "The field is required"
      { pattern: /the (.+?) field is required/gi, replacement: `${fieldName} wajib diisi` },
      // "The field may not be greater than X"
      { pattern: /the (.+?) field may not be greater than (\d+)/gi, replacement: `${fieldName} tidak boleh lebih dari $2` },
      // "The field may not be less than X"
      { pattern: /the (.+?) field may not be less than (\d+)/gi, replacement: `${fieldName} tidak boleh kurang dari $2` },
      // "The field must not be greater than X characters"
      { pattern: /the (.+?) field must not be greater than (\d+) characters/gi, replacement: `${fieldName} tidak boleh lebih dari $2 karakter` },
      // "The field must be at least X characters"
      { pattern: /the (.+?) field must be at least (\d+) characters/gi, replacement: `${fieldName} minimal $2 karakter` },
      // "The selected field is invalid"
      { pattern: /the selected (.+?) is invalid/gi, replacement: `${fieldName} yang dipilih tidak valid` },
      // "The field does not exist"
      { pattern: /the (.+?) does not exist/gi, replacement: `${fieldName} tidak ditemukan` },
      // Generic "must be" patterns
      { pattern: /must be a string/gi, replacement: 'harus berupa teks' },
      { pattern: /must be an integer/gi, replacement: 'harus berupa angka' },
      { pattern: /must be an array/gi, replacement: 'harus berupa array' },
      { pattern: /must be a boolean/gi, replacement: 'harus berupa true atau false' },
      { pattern: /is required/gi, replacement: 'wajib diisi' },
      { pattern: /must not be greater than/gi, replacement: 'tidak boleh lebih dari' },
      { pattern: /must not be less than/gi, replacement: 'tidak boleh kurang dari' },
    ];

    // Apply pattern replacements
    errorPatterns.forEach(({ pattern, replacement }) => {
      translatedMessage = translatedMessage.replace(pattern, replacement);
    });

    // Jika masih mengandung "the field", ganti dengan field name
    if (translatedMessage.includes('the field') || translatedMessage.includes('The field')) {
      translatedMessage = translatedMessage.replace(/the field/gi, fieldName);
    }

    // Clean up: remove "field" jika masih ada
    translatedMessage = translatedMessage.replace(/\bfield\b/gi, '').trim();

    return translatedMessage;
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const basePath = window.location.pathname.startsWith('/customer') ? '/customer/domains' : '/admin/domains';
    post(basePath);
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Daftarkan Domain Baru" />
      <div className="p-4 md:p-6 space-y-6">
        <div className="flex items-center gap-4">
          <Link href={window.location.pathname.startsWith('/customer') ? '/customer/domains' : '/admin/domains'}>
            <Button variant="ghost" size="sm">
              <ArrowLeft className="w-4 h-4 mr-2" />
              Kembali
            </Button>
          </Link>
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Daftarkan Domain Baru</h1>
            <p className="text-muted-foreground mt-1">Daftarkan domain baru melalui sistem RDASH. Pastikan domain tersedia sebelum mendaftarkan.</p>
          </div>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Error Summary */}
          {(errors.error || Object.keys(errors).filter(key => key !== 'error').length > 0) && (
            <div className="bg-red-50 border border-red-200 rounded-lg p-4">
              <h3 className="text-sm font-semibold text-red-800 mb-2">Terdapat kesalahan dalam form:</h3>
              <ul className="list-disc list-inside space-y-1 text-sm text-red-700">
                {errors.error && <li>{errors.error}</li>}
                {errors.name && <li><strong>Nama Domain:</strong> {translateError('name', errors.name)}</li>}
                {errors.period && <li><strong>Periode Pendaftaran:</strong> {translateError('period', errors.period)}</li>}
                {errors.customer_id && <li><strong>Customer:</strong> {translateError('customer_id', errors.customer_id)}</li>}
                {Object.keys(errors).map((key) => {
                  if (key.startsWith('nameserver')) {
                    const index = key.match(/\[?(\d+)\]?/)?.[1] || '';
                    const fieldName = index ? `Nameserver #${parseInt(index) + 1}` : 'Nameserver';
                    return <li key={key}><strong>{fieldName}:</strong> {translateError('nameserver', errors[key])}</li>;
                  }
                  if (key === 'registrant_contact_id') {
                    return <li key={key}><strong>Registrant Contact ID:</strong> {translateError('registrant_contact_id', errors[key])}</li>;
                  }
                  if (!['error', 'name', 'period', 'customer_id'].includes(key)) {
                    const fieldName = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    return <li key={key}><strong>{fieldName}:</strong> {translateError(key, errors[key])}</li>;
                  }
                  return null;
                })}
              </ul>
            </div>
          )}

          <Card>
            <CardHeader>
              <CardTitle>Informasi Domain</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {/* Domain Name */}
              <div className="space-y-2">
                <Label htmlFor="name">Nama Domain *</Label>
                <p className="text-xs text-muted-foreground mb-2">
                  Masukkan nama domain yang ingin Anda daftarkan (contoh: contoh.com)
                </p>
                <div className="flex gap-2 relative">
                  <div className="flex-1 relative">
                    <Input
                      id="name"
                      placeholder="contoh.com"
                      value={data.name}
                      onChange={(e) => {
                        setData('name', e.target.value);
                        // Reset availability check saat user mengetik
                        if (availabilityCheck.available !== null || availabilityCheck.connectionError) {
                          setAvailabilityCheck({ checking: false, available: null, message: '', connectionError: false });
                        }
                      }}
                      className={
                        errors.name 
                          ? 'border-red-500' 
                          : availabilityCheck.checking
                          ? 'border-blue-400 focus:border-blue-500 focus:ring-blue-500 animate-pulse'
                          : availabilityCheck.available === true
                          ? 'border-green-500 focus:border-green-500 focus:ring-green-500'
                          : availabilityCheck.available === false
                          ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                          : ''
                      }
                      disabled={availabilityCheck.checking}
                    />
                    {availabilityCheck.checking && (
                      <div className="absolute right-3 top-1/2 -translate-y-1/2">
                        <Loader2 className="h-4 w-4 animate-spin text-blue-500" />
                      </div>
                    )}
                  </div>
                  <Button
                    type="button"
                    variant="outline"
                    onClick={handleCheckAvailability}
                    disabled={!data.name || availabilityCheck.checking}
                    className={availabilityCheck.checking ? 'min-w-[140px]' : ''}
                  >
                    {availabilityCheck.checking ? (
                      <>
                        <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                        <span className="animate-pulse">Memeriksa...</span>
                      </>
                    ) : (
                      <>
                        <Search className="w-4 h-4 mr-2" />
                        Cek Ketersediaan
                      </>
                    )}
                  </Button>
                </div>
                {errors.name && <p className="text-sm text-red-500">{translateError('name', errors.name)}</p>}
                {availabilityCheck.checking && (
                  <div className="text-sm p-3 rounded bg-blue-50 text-blue-700 border border-blue-200 animate-pulse">
                    <div className="flex items-center gap-2">
                      <Loader2 className="w-4 h-4 animate-spin" />
                      <strong>Sedang memeriksa ketersediaan domain...</strong>
                    </div>
                    <p className="text-xs mt-1 opacity-75">Mohon tunggu sebentar</p>
                  </div>
                )}
                {availabilityCheck.connectionError && (
                  <div className="text-sm p-3 rounded bg-yellow-50 text-yellow-700 border border-yellow-200">
                    <strong>⚠️ {availabilityCheck.message}</strong>
                  </div>
                )}
                {!availabilityCheck.checking && !availabilityCheck.connectionError && availabilityCheck.available !== null && (
                  <div
                    className={`text-sm p-3 rounded animate-in fade-in slide-in-from-top-2 duration-300 ${
                      availabilityCheck.available
                        ? 'bg-green-50 text-green-700 border border-green-200'
                        : 'bg-red-50 text-red-700 border border-red-200'
                    }`}
                  >
                    <strong>{availabilityCheck.available ? '✓ ' : '✗ '}{availabilityCheck.message}</strong>
                  </div>
                )}
              </div>

              {/* Period */}
              <div className="space-y-2">
                <Label htmlFor="period">DOMAIN PERIOD *</Label>
                {loadingPrice && (
                  <div className="flex items-center gap-2 text-sm text-muted-foreground mb-2">
                    <Loader2 className="w-4 h-4 animate-spin" />
                    <span>Memuat harga domain...</span>
                  </div>
                )}
                {!loadingPrice && !domainPrice && availabilityCheck.available === true && (
                  <p className="text-xs text-muted-foreground mb-2">
                    Harga domain sedang dimuat...
                  </p>
                )}
                {!loadingPrice && domainPrice && (
                  <div className="flex gap-2 overflow-x-auto pb-2 scrollbar-thin">
                    {Array.from({ length: 10 }, (_, i) => i + 1).map((year) => {
                      const yearKey = year.toString();
                      const normalPrice = domainPrice.registration?.[yearKey] || domainPrice.registration?.[year];
                      const promoPrice = domainPrice.promo_registration?.registration?.[yearKey] || domainPrice.promo_registration?.registration?.[year];
                      const hasPromo = promoPrice && promoPrice !== '' && promoPrice !== null && promoPrice !== undefined;
                      const isSelected = data.period === year;

                      return (
                        <button
                          key={year}
                          type="button"
                          onClick={() => setData('period', year)}
                          className={`
                            flex-shrink-0 min-w-[110px] p-4 rounded-lg border-2 transition-all cursor-pointer
                            ${isSelected
                              ? 'border-red-500 bg-card shadow-md'
                              : 'border-border bg-card hover:border-primary/50 hover:bg-muted/50'
                            }
                            ${errors.period ? 'border-red-500' : ''}
                          `}
                        >
                          <div className="text-center space-y-1.5">
                            <div className="text-sm font-medium text-foreground">
                              {year} Year{year > 1 ? '(s)' : '(s)'}
                            </div>
                            <div className="text-xs font-bold">
                              {hasPromo ? (
                                <div className="space-y-0.5">
                                  <div className="text-red-600 dark:text-red-400">
                                    Rp{formatCurrencyNumber(promoPrice)}
                                  </div>
                                  <div className="text-muted-foreground line-through text-[10px] font-normal">
                                    Rp{formatCurrencyNumber(normalPrice)}
                                  </div>
                                </div>
                              ) : normalPrice ? (
                                <div className="text-foreground">
                                  Rp{formatCurrencyNumber(normalPrice)}
                                </div>
                              ) : (
                                <div className="text-muted-foreground font-normal">-</div>
                              )}
                            </div>
                          </div>
                        </button>
                      );
                    })}
                  </div>
                )}
                {!loadingPrice && !domainPrice && availabilityCheck.available !== true && (
                  <div className="text-sm text-muted-foreground p-3 bg-muted rounded-lg">
                    Silakan cek ketersediaan domain terlebih dahulu untuk melihat harga
                  </div>
                )}
                {errors.period && <p className="text-sm text-red-500 mt-1">{translateError('period', errors.period)}</p>}
              </div>

              {/* Customer - hanya untuk admin */}
              {!window.location.pathname.startsWith('/customer') && (
                <div className="space-y-2">
                  <Label htmlFor="customer_id">Customer *</Label>
                  <p className="text-xs text-muted-foreground mb-2">
                    Pilih customer yang akan menjadi pemilik domain ini
                  </p>
                  <Select
                    value={data.customer_id}
                    onValueChange={(value) => setData('customer_id', value)}
                  >
                    <SelectTrigger 
                      id="customer_id"
                      className={errors.customer_id ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''}
                    >
                      <SelectValue placeholder="Pilih customer" />
                    </SelectTrigger>
                    <SelectContent>
                      {customers.map((customer) => (
                        <SelectItem key={customer.id} value={customer.id}>
                          {customer.name} ({customer.email}) - ID RDASH: {customer.rdash_customer_id}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  {errors.customer_id && (
                    <p className="text-sm text-red-500 mt-1">{translateError('customer_id', errors.customer_id)}</p>
                  )}
                  {customers.length === 0 && (
                    <p className="text-sm text-yellow-600 bg-yellow-50 p-2 rounded border border-yellow-200">
                      Tidak ada customer yang tersinkronisasi dengan RDASH. Silakan sinkronkan customer terlebih dahulu.
                    </p>
                  )}
                </div>
              )}
              
              {/* Hidden input untuk customer jika customer sendiri */}
              {window.location.pathname.startsWith('/customer') && customers.length > 0 && (
                <input type="hidden" value={customers[0].id} />
              )}
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Nameserver</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <p className="text-sm text-muted-foreground mb-2">
                Nameserver mengarahkan domain ke server hosting Anda. Minimal 2 nameserver diperlukan. Kosongkan jika ingin menggunakan nameserver default dari registrar.
              </p>
              {/* Nameserver Array Error (jika ada error untuk array secara keseluruhan) */}
              {errors.nameserver && typeof errors.nameserver === 'string' && (
                <div className="p-3 rounded bg-red-50 text-red-700 border border-red-200">
                  <p className="text-sm font-medium">{translateError('nameserver', errors.nameserver)}</p>
                </div>
              )}
              {data.nameserver.map((ns, index) => {
                const nameserverError = errors[`nameserver.${index}`] || errors[`nameserver[${index}]`];
                return (
                  <div key={index} className="space-y-1">
                    <div className="flex gap-2">
                      <Input
                        placeholder={`Nameserver ${index + 1} (contoh: ns1.example.com)`}
                        value={ns}
                        onChange={(e) => {
                          const newNameservers = [...data.nameserver];
                          newNameservers[index] = e.target.value;
                          setData('nameserver', newNameservers);
                        }}
                        className={nameserverError ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''}
                      />
                      {data.nameserver.length > 2 && (
                        <Button
                          type="button"
                          variant="ghost"
                          size="sm"
                          onClick={() => handleRemoveNameserver(index)}
                        >
                          Hapus
                        </Button>
                      )}
                    </div>
                    {nameserverError && (
                      <p className="text-sm text-red-500 ml-1">{translateError('nameserver', nameserverError)}</p>
                    )}
                  </div>
                );
              })}
              {data.nameserver.length < 5 && (
                <Button type="button" variant="outline" onClick={handleAddNameserver}>
                  Tambah Nameserver
                </Button>
              )}
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Metode Pembayaran</CardTitle>
              <p className="text-sm text-muted-foreground mt-2">
                Pilih metode pembayaran yang ingin Anda gunakan untuk pembayaran domain ini
              </p>
            </CardHeader>
            <CardContent className="space-y-4">
              {errors.payment_method && (
                <div className="p-3 rounded bg-red-50 text-red-700 border border-red-200">
                  <p className="text-sm font-medium">{translateError('payment_method', errors.payment_method)}</p>
                </div>
              )}

              {/* QRIS Section */}
              <Collapsible defaultOpen className="group space-y-3">
                <CollapsibleTrigger className="flex w-full items-center justify-between rounded-lg border bg-card p-4 hover:bg-muted/50 transition-colors">
                  <div className="flex items-center gap-3">
                    <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                      <span className="text-xl">📱</span>
                    </div>
                    <div className="text-left">
                      <h3 className="font-semibold">QRIS</h3>
                      <p className="text-xs text-muted-foreground">GoPay, OVO, DANA, ShopeePay, LinkAja, dan lainnya</p>
                    </div>
                  </div>
                  <ChevronDown className="h-5 w-5 text-muted-foreground transition-transform duration-200 group-data-[state=open]:rotate-180" />
                </CollapsibleTrigger>
                <CollapsibleContent className="space-y-2">
                  <div className="grid grid-cols-2 md:grid-cols-3 gap-3">
                    {[
                      { value: 'qris', label: 'QRIS', icon: '📱', description: 'Semua E-Wallet' },
                      { value: 'gopay', label: 'GoPay', icon: '💙', description: 'GoPay' },
                      { value: 'shopeepay', label: 'ShopeePay', icon: '🛍️', description: 'ShopeePay' },
                      { value: 'dana', label: 'DANA', icon: '💙', description: 'DANA' },
                      { value: 'ovo', label: 'OVO', icon: '💜', description: 'OVO' },
                      { value: 'linkaja', label: 'LinkAja', icon: '🔴', description: 'LinkAja' },
                    ].map((method) => (
                      <button
                        key={method.value}
                        type="button"
                        onClick={() => setData('payment_method', method.value)}
                        className={`
                          relative flex flex-col items-center justify-center p-4 rounded-lg border-2 transition-all
                          ${data.payment_method === method.value
                            ? 'border-primary bg-primary/5 shadow-md'
                            : 'border-border bg-card hover:border-primary/50 hover:bg-muted/50'
                          }
                        `}
                      >
                        <div className="text-2xl mb-2">{method.icon}</div>
                        <div className="text-sm font-medium text-center">{method.label}</div>
                        <div className="text-xs text-muted-foreground mt-1">{method.description}</div>
                        {data.payment_method === method.value && (
                          <div className="absolute top-2 right-2 h-5 w-5 rounded-full bg-primary flex items-center justify-center">
                            <div className="h-2 w-2 rounded-full bg-white" />
                          </div>
                        )}
                      </button>
                    ))}
                  </div>
                </CollapsibleContent>
              </Collapsible>

              {/* Virtual Account Section */}
              <Collapsible className="group space-y-3">
                <CollapsibleTrigger className="flex w-full items-center justify-between rounded-lg border bg-card p-4 hover:bg-muted/50 transition-colors">
                  <div className="flex items-center gap-3">
                    <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                      <span className="text-xl">🏦</span>
                    </div>
                    <div className="text-left">
                      <h3 className="font-semibold">Virtual Account</h3>
                      <p className="text-xs text-muted-foreground">Transfer Bank via Virtual Account</p>
                    </div>
                  </div>
                  <ChevronDown className="h-5 w-5 text-muted-foreground transition-transform duration-200 group-data-[state=open]:rotate-180" />
                </CollapsibleTrigger>
                <CollapsibleContent className="space-y-2">
                  <div className="grid grid-cols-2 md:grid-cols-3 gap-3">
                    {[
                      { value: 'bca_va', label: 'BCA', icon: '🏦', description: 'BCA Virtual Account' },
                      { value: 'bni_va', label: 'BNI', icon: '🏦', description: 'BNI Virtual Account' },
                      { value: 'bri_va', label: 'BRI', icon: '🏦', description: 'BRIVA' },
                      { value: 'mandiri_va', label: 'Mandiri', icon: '🏦', description: 'Mandiri VA' },
                      { value: 'permata_va', label: 'Permata', icon: '🏦', description: 'Permata VA' },
                      { value: 'bank_transfer', label: 'Bank Lain', icon: '🏦', description: 'Pilih Otomatis' },
                    ].map((method) => (
                      <button
                        key={method.value}
                        type="button"
                        onClick={() => setData('payment_method', method.value)}
                        className={`
                          relative flex flex-col items-center justify-center p-4 rounded-lg border-2 transition-all
                          ${data.payment_method === method.value
                            ? 'border-primary bg-primary/5 shadow-md'
                            : 'border-border bg-card hover:border-primary/50 hover:bg-muted/50'
                          }
                        `}
                      >
                        <div className="text-2xl mb-2">{method.icon}</div>
                        <div className="text-sm font-medium text-center">{method.label}</div>
                        <div className="text-xs text-muted-foreground mt-1 text-center">{method.description}</div>
                        {data.payment_method === method.value && (
                          <div className="absolute top-2 right-2 h-5 w-5 rounded-full bg-primary flex items-center justify-center">
                            <div className="h-2 w-2 rounded-full bg-white" />
                          </div>
                        )}
                      </button>
                    ))}
                  </div>
                </CollapsibleContent>
              </Collapsible>

              {/* Credit Card Section */}
              <Collapsible className="group space-y-3">
                <CollapsibleTrigger className="flex w-full items-center justify-between rounded-lg border bg-card p-4 hover:bg-muted/50 transition-colors">
                  <div className="flex items-center gap-3">
                    <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                      <CreditCard className="h-5 w-5" />
                    </div>
                    <div className="text-left">
                      <h3 className="font-semibold">Kartu Kredit/Debit</h3>
                      <p className="text-xs text-muted-foreground">Visa, Mastercard, JCB, Amex</p>
                    </div>
                  </div>
                  <ChevronDown className="h-5 w-5 text-muted-foreground transition-transform duration-200 group-data-[state=open]:rotate-180" />
                </CollapsibleTrigger>
                <CollapsibleContent className="space-y-2">
                  <button
                    type="button"
                    onClick={() => setData('payment_method', 'credit_card')}
                    className={`
                      w-full flex items-center justify-between p-4 rounded-lg border-2 transition-all
                      ${data.payment_method === 'credit_card'
                        ? 'border-primary bg-primary/5 shadow-md'
                        : 'border-border bg-card hover:border-primary/50 hover:bg-muted/50'
                      }
                    `}
                  >
                    <div className="flex items-center gap-3">
                      <CreditCard className="h-6 w-6" />
                      <div className="text-left">
                        <div className="font-medium">Kartu Kredit/Debit</div>
                        <div className="text-xs text-muted-foreground">Visa, Mastercard, JCB, American Express</div>
                      </div>
                    </div>
                    {data.payment_method === 'credit_card' && (
                      <div className="h-5 w-5 rounded-full bg-primary flex items-center justify-center">
                        <div className="h-2 w-2 rounded-full bg-white" />
                      </div>
                    )}
                  </button>
                </CollapsibleContent>
              </Collapsible>

              {/* Convenience Store Section */}
              <Collapsible className="group space-y-3">
                <CollapsibleTrigger className="flex w-full items-center justify-between rounded-lg border bg-card p-4 hover:bg-muted/50 transition-colors">
                  <div className="flex items-center gap-3">
                    <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                      <span className="text-xl">🏪</span>
                    </div>
                    <div className="text-left">
                      <h3 className="font-semibold">Convenience Store</h3>
                      <p className="text-xs text-muted-foreground">Indomaret, Alfamart, dan lainnya</p>
                    </div>
                  </div>
                  <ChevronDown className="h-5 w-5 text-muted-foreground transition-transform duration-200 group-data-[state=open]:rotate-180" />
                </CollapsibleTrigger>
                <CollapsibleContent className="space-y-2">
                  <div className="grid grid-cols-2 md:grid-cols-3 gap-3">
                    {[
                      { value: 'indomaret', label: 'Indomaret', icon: '🏪', description: 'Indomaret' },
                      { value: 'alfamart', label: 'Alfamart', icon: '🏪', description: 'Alfamart' },
                      { value: 'cstore', label: 'Store Lain', icon: '🏪', description: 'Pilih Otomatis' },
                    ].map((method) => (
                      <button
                        key={method.value}
                        type="button"
                        onClick={() => setData('payment_method', method.value)}
                        className={`
                          relative flex flex-col items-center justify-center p-4 rounded-lg border-2 transition-all
                          ${data.payment_method === method.value
                            ? 'border-primary bg-primary/5 shadow-md'
                            : 'border-border bg-card hover:border-primary/50 hover:bg-muted/50'
                          }
                        `}
                      >
                        <div className="text-2xl mb-2">{method.icon}</div>
                        <div className="text-sm font-medium text-center">{method.label}</div>
                        <div className="text-xs text-muted-foreground mt-1">{method.description}</div>
                        {data.payment_method === method.value && (
                          <div className="absolute top-2 right-2 h-5 w-5 rounded-full bg-primary flex items-center justify-center">
                            <div className="h-2 w-2 rounded-full bg-white" />
                          </div>
                        )}
                      </button>
                    ))}
                  </div>
                </CollapsibleContent>
              </Collapsible>

              {/* Manual Transfer (Admin Only) */}
              {!window.location.pathname.startsWith('/customer') && (
                <Collapsible className="group space-y-3">
                  <CollapsibleTrigger className="flex w-full items-center justify-between rounded-lg border bg-card p-4 hover:bg-muted/50 transition-colors">
                    <div className="flex items-center gap-3">
                      <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                        <span className="text-xl">💵</span>
                      </div>
                      <div className="text-left">
                        <h3 className="font-semibold">Transfer Manual</h3>
                        <p className="text-xs text-muted-foreground">Untuk Admin</p>
                      </div>
                    </div>
                    <ChevronDown className="h-5 w-5 text-muted-foreground transition-transform duration-200 group-data-[state=open]:rotate-180" />
                  </CollapsibleTrigger>
                  <CollapsibleContent className="space-y-2">
                    <button
                      type="button"
                      onClick={() => setData('payment_method', 'manual')}
                      className={`
                        w-full flex items-center justify-between p-4 rounded-lg border-2 transition-all
                        ${data.payment_method === 'manual'
                          ? 'border-primary bg-primary/5 shadow-md'
                          : 'border-border bg-card hover:border-primary/50 hover:bg-muted/50'
                        }
                      `}
                    >
                      <div className="flex items-center gap-3">
                        <span className="text-2xl">💵</span>
                        <div className="text-left">
                          <div className="font-medium">Transfer Manual</div>
                          <div className="text-xs text-muted-foreground">Pembayaran manual untuk admin</div>
                        </div>
                      </div>
                      {data.payment_method === 'manual' && (
                        <div className="h-5 w-5 rounded-full bg-primary flex items-center justify-center">
                          <div className="h-2 w-2 rounded-full bg-white" />
                        </div>
                      )}
                    </button>
                  </CollapsibleContent>
                </Collapsible>
              )}
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Opsi Tambahan</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
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
                    Menyembunyikan informasi kontak Anda dari database WHOIS publik untuk privasi dan keamanan
                  </p>
                </div>
              </div>

              <div className="flex items-start space-x-2">
                <Checkbox
                  id="include_premium_domains"
                  checked={data.include_premium_domains}
                  onCheckedChange={(checked) =>
                    setData('include_premium_domains', checked === true)
                  }
                />
                <div className="flex-1">
                  <Label htmlFor="include_premium_domains" className="cursor-pointer font-medium">
                    Sertakan Domain Premium
                  </Label>
                  <p className="text-xs text-muted-foreground mt-1">
                    Tampilkan domain premium dalam hasil pencarian (biasanya lebih mahal)
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
                    Domain akan diperpanjang secara otomatis sebelum masa berlaku habis, mencegah domain kedaluwarsa
                  </p>
                </div>
              </div>

              {/* Registrant Contact ID Error */}
              {errors.registrant_contact_id && (
                <div className="mt-2 p-3 rounded bg-red-50 text-red-700 border border-red-200">
                  <p className="text-sm font-medium"><strong>Registrant Contact ID:</strong> {translateError('registrant_contact_id', errors.registrant_contact_id)}</p>
                </div>
              )}
            </CardContent>
          </Card>

          <div className="flex justify-end gap-4">
            <Link href={window.location.pathname.startsWith('/customer') ? '/customer/domains' : '/admin/domains'}>
              <Button type="button" variant="outline" disabled={processing}>
                Batal
              </Button>
            </Link>
            <Button type="submit" disabled={processing || availabilityCheck.available !== true || availabilityCheck.connectionError}>
              {processing ? 'Mendaftarkan...' : 'Daftarkan Domain'}
            </Button>
          </div>
          {availabilityCheck.available === false && !availabilityCheck.connectionError && (
            <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
              <p className="text-sm text-yellow-800">
                <strong>Perhatian:</strong> Domain tidak tersedia. Silakan pilih domain lain atau coba lagi.
              </p>
            </div>
          )}
          {availabilityCheck.connectionError && (
            <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
              <p className="text-sm text-yellow-800">
                <strong>Perhatian:</strong> Terjadi masalah koneksi dengan server. Silakan coba beberapa saat lagi sebelum mendaftarkan domain.
              </p>
            </div>
          )}
          {availabilityCheck.available === null && !availabilityCheck.connectionError && (
            <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <p className="text-sm text-blue-800">
                <strong>Info:</strong> Silakan cek ketersediaan domain terlebih dahulu sebelum mendaftarkan.
              </p>
            </div>
          )}
        </form>
      </div>
    </AppLayout>
  );
}

