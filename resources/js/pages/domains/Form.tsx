import React, { useState, useEffect } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { ArrowLeft, Check, Search, Globe, CreditCard } from 'lucide-react';
import axios from 'axios';

// Partial Components
import DomainSearch, { DomainResult } from '@/components/DomainSearch';
import Step2Config from './partials/Step2Config';
import Step3Checkout from './partials/Step3Checkout';

// Determine breadcrumbs based on route
const getBreadcrumbs = (): BreadcrumbItem[] => {
  const path = window.location.pathname;
  if (path.startsWith('/customer/domains')) {
    return [
      { title: 'Domain Saya', href: '/customer/domains' },
      { title: 'Daftarkan Domain', href: '/customer/domains/create' },
    ];
  }
  return [
    { title: 'Manajemen Domain', href: '/admin/domains' },
    { title: 'Daftarkan Domain', href: '/admin/domains/create' },
  ];
};

const breadcrumbs = getBreadcrumbs();

interface Customer {
  id: string;
  name: string;
  email: string;
  rdash_customer_id: number;
}

interface PageProps {
  auth: {
    user: {
      name: string;
      email: string;
    };
  };
  [key: string]: any;
}

interface Props {
  customers: Customer[];
  billingSettings?: { // Optional because it comes from backend
     pph_rate: number;
     application_fee: number;
  };
}

export default function DomainForm({ customers, billingSettings }: Props) {
  const { auth } = usePage<PageProps>().props;

  // Set customer_id otomatis jika customer sendiri
  const isCustomer = window.location.pathname.startsWith('/customer');
  const defaultCustomerId = isCustomer && customers.length > 0 ? customers[0].id : '';

  // Step State
  const [step, setStep] = useState(1);
  const totalSteps = 3;

  const { data, setData, post, processing, errors } = useForm({
    name: '',
    period: 1,
    customer_id: defaultCustomerId,
    nameserver: ['', '', '', '', ''],
    buy_whois_protection: false,
    include_premium_domains: false,
    registrant_contact_id: null as number | null,
    auto_renew: false,
    payment_method: 'bca_va',
  });

   // Ensure at least 2 NS are visible initially if empty
   useEffect(() => {
       const hasContent = data.nameserver.some(n => n.trim() !== '');
       if (!hasContent && data.nameserver[0] === '' && data.nameserver[1] === '') {
            // Already in default state, do nothing
       }
   }, []);


  const [availabilityCheck, setAvailabilityCheck] = useState<{
    checking: boolean;
    available: boolean | null;
    message: string;
    connectionError: boolean;
    error: string | null;
  }>({
    checking: false,
    available: null,
    message: '',
    connectionError: false,
    error: null,
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
  const extractExtension = (domainName: string): string => {
    const parts = domainName.split('.');
    if (parts.length < 2) return '';
    const indonesianExtensions = ['co', 'web', 'biz', 'my', 'or', 'ac', 'sch'];
    const lastPart = parts[parts.length - 1];
    const secondLast = parts.length >= 2 ? parts[parts.length - 2] : '';

    if (lastPart === 'id' && indonesianExtensions.includes(secondLast)) {
      return '.' + secondLast + '.' + lastPart;
    }
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
        setDomainPrice(null);
      }
    } catch (error: any) {
      console.error('Error fetching domain price:', error);
      setDomainPrice(null);
    } finally {
      setLoadingPrice(false);
    }
  };

  const handleCheckAvailability = async (e?: React.FormEvent) => {
    if (e) e.preventDefault();
    if (!data.name) return;

    setAvailabilityCheck({ 
        checking: true, 
        available: null, 
        message: '', 
        connectionError: false,
        error: null 
    });

    try {
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
        
        let isAvailable = false;
        let displayMessage = '';
        
        if (message === 'available') {
          isAvailable = true;
          displayMessage = 'Domain Tersedia';
        } else if (message === 'in use' || message.includes('in use')) {
          isAvailable = false;
          displayMessage = 'Domain Tidak Tersedia';
        } else {
          isAvailable = availability.available === true || availability.available === 1;
          displayMessage = isAvailable ? 'Domain Tersedia' : 'Domain Tidak Tersedia';
        }

        setAvailabilityCheck({
          checking: false,
          available: isAvailable,
          message: displayMessage,
          connectionError: false,
          error: null,
        });
      }
    } catch (error: any) {
      const isConnectionError = 
        !error.response || 
        error.code === 'ECONNABORTED' || 
        error.code === 'ERR_NETWORK' ||
        (error.response?.status >= 500);

      if (isConnectionError) {
        setAvailabilityCheck({
          checking: false,
          available: null,
          message: '',
          connectionError: true,
          error: 'Koneksi sedang bermasalah, silahkan coba beberapa saat lagi',
        });
      } else {
        setAvailabilityCheck({
          checking: false,
          available: false,
          message: '',
          connectionError: false,
          error: error.response?.data?.message || 'Terjadi kesalahan saat mengecek domain',
        });
      }
    }
  };

  const handleNext = () => {
      setStep(prev => Math.min(prev + 1, totalSteps));
  };

  const handlePrev = () => {
      setStep(prev => Math.max(prev - 1, 1));
  };


  // Fetch domain price when domain name changes and is available
  useEffect(() => {
    if (step === 1 && data.name && availabilityCheck.available === true) {
      const extension = extractExtension(data.name);
      if (extension) {
        fetchDomainPrice(extension);
      }
    } else if (!data.name || availabilityCheck.available === false) {
      setDomainPrice(null);
    }
     
  }, [data.name, availabilityCheck.available, step]);

  // Translate errors helper
  const translateError = (errorKey: string, errorMessage: string): string => {
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

    if (translatedMessage.includes('required')) translatedMessage = `${fieldName} wajib diisi`;

    return translatedMessage;
  };

// Declare global Snap type
declare global {
  interface Window {
    snap: any;
  }
}

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    const basePath = window.location.pathname.startsWith('/customer') ? '/customer/domains' : '/admin/domains';
    
    // Filter empty nameservers before submission
    const currentNameservers = data.nameserver;
    const filteredNameservers = currentNameservers.filter((ns: string) => ns.trim() !== '');

    // Manually set processing state as we are using axios
    const formPayload = {
        ...data,
        nameserver: filteredNameservers,
    };

    try {
        // We use axios directly to handle the JSON response for Snap Token
        // Instead of Inertia's post which handles redirects automatically
        // but makes it harder to intercept the token and open popup without leaving page
        const response = await axios.post(basePath, formPayload, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (response.data.success) {
            const { snap_token, invoice_url, redirect_url } = response.data;
            
            if (snap_token && window.snap) {
                window.snap.pay(snap_token, {
                    onSuccess: function(result: any) {
                        // Payment success, redirect to invoice
                        window.location.href = invoice_url;
                    },
                    onPending: function(result: any) {
                        // Payment pending, redirect to invoice
                        window.location.href = invoice_url;
                    },
                    onError: function(result: any) {
                        // Payment error, redirect to invoice (it will show unpaid)
                        window.location.href = invoice_url;
                    },
                    onClose: function() {
                        // Customer closed the popup without finishing the payment
                        // Redirect to invoice page so they can pay later
                        window.location.href = invoice_url;
                    }
                });
            } else if (redirect_url) {
                // If no snap token but there is a redirect url (e.g. other gateways)
                window.location.href = redirect_url;
            } else {
                // Default fallback
                window.location.href = invoice_url;
            }
        }
    } catch (error: any) {
        console.error("Submission error:", error);
        // Handle validation errors if they come back as 422
        // We might need to map them to setErrors if we want to show inline errors
        // For now, simpler error handling or fallback to inertia behavior if complex
    }
  };

  // Determine current customer for display
  const currentCustomer = isCustomer 
    ? { name: auth?.user?.name, email: auth?.user?.email } 
    : customers.find(c => c.id === data.customer_id);

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Daftarkan Domain Baru" />
      <div className="container max-w-5xl mx-auto py-8 px-4">
        
        {/* Header */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 className="text-3xl font-bold tracking-tight">Daftarkan Domain</h1>
                <p className="text-muted-foreground mt-1">
                    Mulai perjalanan digital Anda dengan domain pilihan.
                </p>
            </div>
            
             <Link href={window.location.pathname.startsWith('/customer') ? '/customer/domains' : '/admin/domains'}>
                <Button variant="outline" size="sm">
                <ArrowLeft className="w-4 h-4 mr-2" />
                Kembali ke List
                </Button>
            </Link>
        </div>

        {/* Stepper Indicator - Redesigned */}
        <div className="mb-12">
            <div className="flex items-start justify-between relative max-w-4xl mx-auto">
                {/* Connecting Lines Container */}
                <div className="absolute top-[24px] left-[70px] right-[70px] h-1 z-0">
                    {/* Background Line */}
                    <div className="absolute top-0 left-0 w-full h-full bg-slate-200 rounded-full" />
                    {/* Active Line */}
                    <div 
                        className="absolute top-0 left-0 h-full bg-green-500 transition-all duration-500 rounded-full"
                        style={{ width: `${((Math.max(step - 1, 0)) / (totalSteps - 1)) * 100}%` }}
                    />
                </div>

                {[
                    { id: 1, title: 'Pencarian Domain', icon: Search },
                    { id: 2, title: 'Konfigurasi', icon: Globe },
                    { id: 3, title: 'Pembayaran', icon: CreditCard },
                ].map((s, index) => {
                    const isCompleted = step > s.id;
                    const isCurrent = step === s.id;
                    const isPending = step < s.id;
                    const isClickable = step > s.id;

                    return (
                        <div 
                            key={s.id} 
                            className={`flex flex-col items-center px-4 z-10 min-w-[140px] ${isClickable ? 'cursor-pointer group' : ''}`}
                            onClick={() => isClickable && setStep(s.id)}
                        >
                            {/* Icon Circle */}
                            <div className={`
                                w-12 h-12 rounded-full flex items-center justify-center transition-all duration-300
                                ${isCompleted ? 'bg-green-500 text-white shadow-md shadow-green-200 group-hover:bg-green-600' : ''}
                                ${isCurrent ? 'bg-primary text-primary-foreground ring-4 ring-primary/20 scale-110 shadow-lg' : ''}
                                ${isPending ? 'bg-white border-2 border-gray-200 text-gray-400 bg-background' : ''}
                            `}>
                                {isCompleted ? (
                                    <Check className="w-6 h-6" />
                                ) : (
                                    <s.icon className="w-5 h-5" />
                                )}
                            </div>

                            {/* Labels */}
                            <div className="mt-4 flex flex-col items-center text-center space-y-1">
                                <span className="text-[10px] uppercase tracking-wider font-semibold text-muted-foreground">
                                    STEP {s.id}
                                </span>
                                <span className={`text-sm font-bold ${isCurrent ? 'text-foreground' : 'text-muted-foreground transition-colors group-hover:text-foreground'}`}>
                                    {s.title}
                                </span>
                                
                                {/* Status Badge */}
                                <div className={`
                                    mt-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-medium
                                    ${isCompleted ? 'bg-green-100 text-green-700' : ''}
                                    ${isCurrent ? 'bg-primary/10 text-primary' : ''}
                                    ${isPending ? 'bg-gray-100 text-gray-500 border border-gray-200' : ''}
                                `}>
                                    {isCompleted ? 'Completed' : isCurrent ? 'In Progress' : 'Pending'}
                                </div>
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>

        {/* Validation Errors Global */}
        {((errors as any).error) && (
             <div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 animate-pulse">
               <p className="text-sm text-red-700 font-medium">{(errors as any).error}</p>
             </div>
        )}

        {/* Steps Content */}
        <div className="min-h-[400px]">
            {step === 1 && (
                <div className="animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <div className="bg-white dark:bg-gray-900 rounded-xl p-6 shadow-sm border mb-6">
                        <div className="mb-6">
                            <h2 className="text-xl font-semibold">Cari Domain Impianmu</h2>
                            <p className="text-sm text-muted-foreground">
                                Mulai dengan mencari nama domain yang ingin Anda daftarkan.
                            </p>
                        </div>
                        <DomainSearch 
                            onAddDomain={(result: DomainResult) => {
                                setData('name', result.domain);
                                setAvailabilityCheck(prev => ({ 
                                    ...prev, 
                                    available: true,
                                    checking: false,
                                    error: null 
                                }));
                                // Small delay to allow state to settle or visual feedback
                                setTimeout(() => {
                                    handleNext();
                                }, 100);
                            }}
                            selectedDomains={data.name ? [{ domain: data.name, available: true }] : []}
                        />
                    </div>
                </div>
            )}

            {step === 2 && (
                <Step2Config 
                    data={data}
                    setData={setData}
                    customers={customers}
                    domainPrice={domainPrice}
                    loadingPrice={loadingPrice}
                    errors={errors}
                    translateError={translateError}
                    onNext={handleNext}
                    onPrev={handlePrev}
                />
            )}

            {step === 3 && (
                 <Step3Checkout 
                    data={data}
                    domainPrice={domainPrice}
                    processing={processing}
                    onSubmit={handleSubmit}
                    onPrev={handlePrev}
                    customer={currentCustomer}
                    billingSettings={billingSettings}
                    setData={setData}
                 />
            )}
        </div>

      </div>
    </AppLayout>
  );
}
