import React, { useState } from 'react';
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
import { type BreadcrumbItem } from '@/types';
import { ArrowLeft, Globe, Search } from 'lucide-react';
import axios from 'axios';

// Determine breadcrumbs based on route
const getBreadcrumbs = (): BreadcrumbItem[] => {
  const path = window.location.pathname;
  if (path.startsWith('/customer/domains')) {
    return [
      {
        title: 'My Domains',
        href: '/customer/domains',
      },
      {
        title: 'Register Domain',
        href: '/customer/domains/create',
      },
    ];
  }
  return [
    {
      title: 'Domain Management',
      href: '/admin/domains',
    },
    {
      title: 'Register Domain',
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
  });

  const [availabilityCheck, setAvailabilityCheck] = useState<{
    checking: boolean;
    available: boolean | null;
    message: string;
  }>({
    checking: false,
    available: null,
    message: '',
  });

  const handleCheckAvailability = async () => {
    if (!data.name) {
      return;
    }

    setAvailabilityCheck({ checking: true, available: null, message: '' });

    try {
      const response = await axios.post('/admin/domains/check-availability', {
        domain: data.name,
        include_premium_domains: data.include_premium_domains,
      });

      if (response.data.success) {
        const availability = response.data.data;
        setAvailabilityCheck({
          checking: false,
          available: availability.available,
          message: availability.message,
        });
      }
    } catch (error: any) {
      setAvailabilityCheck({
        checking: false,
        available: false,
        message: error.response?.data?.message || 'Error checking availability',
      });
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

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const basePath = window.location.pathname.startsWith('/customer') ? '/customer/domains' : '/admin/domains';
    post(basePath);
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Register Domain" />
      <div className="p-4 md:p-6 space-y-6">
        <div className="flex items-center gap-4">
          <Link href={window.location.pathname.startsWith('/customer') ? '/customer/domains' : '/admin/domains'}>
            <Button variant="ghost" size="sm">
              <ArrowLeft className="w-4 h-4 mr-2" />
              Back
            </Button>
          </Link>
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Register Domain</h1>
            <p className="text-muted-foreground mt-1">Register a new domain via RDASH API</p>
          </div>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Domain Information</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {/* Domain Name */}
              <div className="space-y-2">
                <Label htmlFor="name">Domain Name *</Label>
                <div className="flex gap-2">
                  <Input
                    id="name"
                    placeholder="example.com"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    className={errors.name ? 'border-red-500' : ''}
                  />
                  <Button
                    type="button"
                    variant="outline"
                    onClick={handleCheckAvailability}
                    disabled={!data.name || availabilityCheck.checking}
                  >
                    <Search className="w-4 h-4 mr-2" />
                    {availabilityCheck.checking ? 'Checking...' : 'Check Availability'}
                  </Button>
                </div>
                {errors.name && <p className="text-sm text-red-500">{errors.name}</p>}
                {availabilityCheck.available !== null && (
                  <div
                    className={`text-sm p-2 rounded ${
                      availabilityCheck.available
                        ? 'bg-green-50 text-green-700 border border-green-200'
                        : 'bg-red-50 text-red-700 border border-red-200'
                    }`}
                  >
                    {availabilityCheck.message}
                  </div>
                )}
              </div>

              {/* Period */}
              <div className="space-y-2">
                <Label htmlFor="period">Registration Period (Years) *</Label>
                <Select
                  value={data.period.toString()}
                  onValueChange={(value) => setData('period', parseInt(value))}
                >
                  <SelectTrigger id="period">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    {[1, 2, 3, 4, 5, 6, 7, 8, 9, 10].map((year) => (
                      <SelectItem key={year} value={year.toString()}>
                        {year} {year === 1 ? 'Year' : 'Years'}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.period && <p className="text-sm text-red-500">{errors.period}</p>}
              </div>

              {/* Customer - hanya untuk admin */}
              {!window.location.pathname.startsWith('/customer') && (
                <div className="space-y-2">
                  <Label htmlFor="customer_id">Customer *</Label>
                  <Select
                    value={data.customer_id}
                    onValueChange={(value) => setData('customer_id', value)}
                  >
                    <SelectTrigger id="customer_id">
                      <SelectValue placeholder="Select customer" />
                    </SelectTrigger>
                    <SelectContent>
                      {customers.map((customer) => (
                        <SelectItem key={customer.id} value={customer.id}>
                          {customer.name} ({customer.email}) - RDASH ID: {customer.rdash_customer_id}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  {errors.customer_id && (
                    <p className="text-sm text-red-500">{errors.customer_id}</p>
                  )}
                  {customers.length === 0 && (
                    <p className="text-sm text-yellow-600">
                      No customers synced to RDASH. Please sync customers first.
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
              <CardTitle>Nameservers</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {data.nameserver.map((ns, index) => (
                <div key={index} className="flex gap-2">
                  <Input
                    placeholder={`Nameserver ${index + 1}`}
                    value={ns}
                    onChange={(e) => {
                      const newNameservers = [...data.nameserver];
                      newNameservers[index] = e.target.value;
                      setData('nameserver', newNameservers);
                    }}
                  />
                  {data.nameserver.length > 2 && (
                    <Button
                      type="button"
                      variant="ghost"
                      size="sm"
                      onClick={() => handleRemoveNameserver(index)}
                    >
                      Remove
                    </Button>
                  )}
                </div>
              ))}
              {data.nameserver.length < 5 && (
                <Button type="button" variant="outline" onClick={handleAddNameserver}>
                  Add Nameserver
                </Button>
              )}
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Additional Options</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex items-center space-x-2">
                <Checkbox
                  id="buy_whois_protection"
                  checked={data.buy_whois_protection}
                  onCheckedChange={(checked) => setData('buy_whois_protection', checked === true)}
                />
                <Label htmlFor="buy_whois_protection" className="cursor-pointer">
                  Buy WHOIS Protection
                </Label>
              </div>

              <div className="flex items-center space-x-2">
                <Checkbox
                  id="include_premium_domains"
                  checked={data.include_premium_domains}
                  onCheckedChange={(checked) =>
                    setData('include_premium_domains', checked === true)
                  }
                />
                <Label htmlFor="include_premium_domains" className="cursor-pointer">
                  Include Premium Domains
                </Label>
              </div>

              <div className="flex items-center space-x-2">
                <Checkbox
                  id="auto_renew"
                  checked={data.auto_renew}
                  onCheckedChange={(checked) => setData('auto_renew', checked === true)}
                />
                <Label htmlFor="auto_renew" className="cursor-pointer">
                  Auto Renew
                </Label>
              </div>
            </CardContent>
          </Card>

          <div className="flex justify-end gap-4">
            <Link href={window.location.pathname.startsWith('/customer') ? '/customer/domains' : '/admin/domains'}>
              <Button type="button" variant="outline" disabled={processing}>
                Cancel
              </Button>
            </Link>
            <Button type="submit" disabled={processing || !availabilityCheck.available}>
              {processing ? 'Registering...' : 'Register Domain'}
            </Button>
          </div>
        </form>
      </div>
    </AppLayout>
  );
}

