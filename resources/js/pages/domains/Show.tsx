import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { type BreadcrumbItem } from '@/types';
import { ArrowLeft, Globe, CheckCircle2, Clock, XCircle } from 'lucide-react';
import dayjs from 'dayjs';

// Determine breadcrumbs based on route
const getBreadcrumbs = (): BreadcrumbItem[] => {
  const path = window.location.pathname;
  if (path.startsWith('/customer/domains')) {
    return [
      {
        title: 'My Domains',
        href: '/customer/domains',
      },
    ];
  }
  return [
    {
      title: 'Domain Management',
      href: '/admin/domains',
    },
  ];
};

const breadcrumbs = getBreadcrumbs();

interface Domain {
  id: string;
  name: string;
  status: 'active' | 'pending' | 'expired';
  customer_id: string;
  customer?: {
    id: string;
    name: string;
    email: string;
  };
  rdash_domain_id?: number | null;
  rdash_sync_status?: 'pending' | 'synced' | 'failed' | null;
  rdash_synced_at?: string | null;
  rdash_verification_status?: number | null;
  rdash_required_document?: boolean;
  auto_renew?: boolean;
  created_at: string;
  updated_at: string;
}

interface RdashDomain {
  id: number;
  name: string;
  customer_id: number;
  status: number;
  verification_status: number;
  required_document: number;
  expired_at?: string;
  created_at?: string;
  nameservers?: string[];
}

interface Props {
  domain: Domain;
  rdashDomain?: RdashDomain;
}

function getStatusBadge(status: string) {
  const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    active: 'default',
    pending: 'secondary',
    expired: 'destructive',
  };

  return (
    <Badge variant={variants[status] || 'outline'}>
      {status.charAt(0).toUpperCase() + status.slice(1)}
    </Badge>
  );
}

function getRdashStatusLabel(status: number): string {
  const statusMap: Record<number, string> = {
    0: 'Pending',
    1: 'Active',
    2: 'Expired',
    3: 'Pending Delete',
    4: 'Deleted',
    5: 'Pending Transfer',
    6: 'Transferred Away',
    7: 'Suspended',
    8: 'Rejected',
  };
  return statusMap[status] || 'Unknown';
}

function getVerificationStatusLabel(status: number): string {
  const statusMap: Record<number, string> = {
    0: 'Waiting',
    1: 'Verifying',
    2: 'Document Validating',
    3: 'Active',
  };
  return statusMap[status] || 'Unknown';
}

export default function DomainShow({ domain, rdashDomain }: Props) {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`Domain: ${domain.name}`} />
      <div className="p-4 md:p-6 space-y-6">
        <div className="flex items-center gap-4">
          <Link href={window.location.pathname.startsWith('/customer') ? '/customer/domains' : '/admin/domains'}>
            <Button variant="ghost" size="sm">
              <ArrowLeft className="w-4 h-4 mr-2" />
              Back
            </Button>
          </Link>
          <div className="flex-1">
            <div className="flex items-center gap-3">
              <Globe className="h-6 w-6 text-primary" />
              <h1 className="text-3xl font-bold tracking-tight">{domain.name}</h1>
              {getStatusBadge(domain.status)}
            </div>
            <p className="text-muted-foreground mt-1">Domain details and RDASH information</p>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {/* Domain Information */}
          <Card>
            <CardHeader>
              <CardTitle>Domain Information</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div>
                <p className="text-sm text-muted-foreground">Domain Name</p>
                <p className="font-semibold">{domain.name}</p>
              </div>

              <Separator />

              <div>
                <p className="text-sm text-muted-foreground">Status</p>
                <div className="mt-1">{getStatusBadge(domain.status)}</div>
              </div>

              {domain.customer && (
                <>
                  <Separator />
                  <div>
                    <p className="text-sm text-muted-foreground">Customer</p>
                    <p className="font-semibold">{domain.customer.name}</p>
                    <p className="text-sm text-muted-foreground">{domain.customer.email}</p>
                  </div>
                </>
              )}

              <Separator />

              <div>
                <p className="text-sm text-muted-foreground">Auto Renew</p>
                <div className="mt-1">
                  {domain.auto_renew ? (
                    <Badge variant="default">Enabled</Badge>
                  ) : (
                    <Badge variant="secondary">Disabled</Badge>
                  )}
                </div>
              </div>

              <Separator />

              <div>
                <p className="text-sm text-muted-foreground">Created At</p>
                <p className="font-semibold">{dayjs(domain.created_at).format('DD MMMM YYYY HH:mm')}</p>
              </div>
            </CardContent>
          </Card>

          {/* RDASH Integration */}
          <Card>
            <CardHeader>
              <CardTitle>RDASH Integration</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {domain.rdash_domain_id ? (
                <>
                  <div>
                    <p className="text-sm text-muted-foreground">RDASH Domain ID</p>
                    <p className="font-semibold">{domain.rdash_domain_id}</p>
                  </div>

                  <Separator />

                  <div>
                    <p className="text-sm text-muted-foreground">Sync Status</p>
                    <div className="mt-1">
                      {domain.rdash_sync_status === 'synced' && (
                        <Badge variant="default" className="bg-green-600">
                          <CheckCircle2 className="w-3 h-3 mr-1" />
                          Synced
                        </Badge>
                      )}
                      {domain.rdash_sync_status === 'pending' && (
                        <Badge variant="secondary" className="bg-yellow-600">
                          <Clock className="w-3 h-3 mr-1" />
                          Pending
                        </Badge>
                      )}
                      {domain.rdash_sync_status === 'failed' && (
                        <Badge variant="destructive">
                          <XCircle className="w-3 h-3 mr-1" />
                          Failed
                        </Badge>
                      )}
                    </div>
                  </div>

                  {domain.rdash_synced_at && (
                    <>
                      <Separator />
                      <div>
                        <p className="text-sm text-muted-foreground">Last Synced</p>
                        <p className="font-semibold">
                          {dayjs(domain.rdash_synced_at).format('DD MMMM YYYY HH:mm')}
                        </p>
                      </div>
                    </>
                  )}

                  {rdashDomain && (
                    <>
                      <Separator />
                      <div>
                        <p className="text-sm text-muted-foreground">RDASH Status</p>
                        <p className="font-semibold">
                          {getRdashStatusLabel(rdashDomain.status)}
                        </p>
                      </div>

                      <Separator />
                      <div>
                        <p className="text-sm text-muted-foreground">Verification Status</p>
                        <p className="font-semibold">
                          {getVerificationStatusLabel(rdashDomain.verification_status)}
                        </p>
                      </div>

                      {rdashDomain.expired_at && (
                        <>
                          <Separator />
                          <div>
                            <p className="text-sm text-muted-foreground">Expires At</p>
                            <p className="font-semibold">
                              {dayjs(rdashDomain.expired_at).format('DD MMMM YYYY')}
                            </p>
                          </div>
                        </>
                      )}

                      {rdashDomain.nameservers && rdashDomain.nameservers.length > 0 && (
                        <>
                          <Separator />
                          <div>
                            <p className="text-sm text-muted-foreground mb-2">Nameservers</p>
                            <ul className="space-y-1">
                              {rdashDomain.nameservers.map((ns, index) => (
                                <li key={index} className="text-sm font-mono">
                                  {ns}
                                </li>
                              ))}
                            </ul>
                          </div>
                        </>
                      )}
                    </>
                  )}
                </>
              ) : (
                <div className="text-center py-8">
                  <p className="text-muted-foreground">Domain not synced to RDASH</p>
                </div>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </AppLayout>
  );
}

