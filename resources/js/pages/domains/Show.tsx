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
        title: 'Domain Saya',
        href: '/customer/domains',
      },
    ];
  }
  return [
    {
      title: 'Manajemen Domain',
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

  const labels: Record<string, string> = {
    active: 'Aktif',
    pending: 'Menunggu',
    expired: 'Kedaluwarsa',
  };

  return (
    <Badge variant={variants[status] || 'outline'}>
      {labels[status] || status.charAt(0).toUpperCase() + status.slice(1)}
    </Badge>
  );
}

function getRdashStatusLabel(status: number): string {
  const statusMap: Record<number, string> = {
    0: 'Menunggu',
    1: 'Aktif',
    2: 'Kedaluwarsa',
    3: 'Menunggu Penghapusan',
    4: 'Terhapus',
    5: 'Menunggu Transfer',
    6: 'Ditransfer',
    7: 'Ditangguhkan',
    8: 'Ditolak',
  };
  return statusMap[status] || 'Tidak Diketahui';
}

function getVerificationStatusLabel(status: number): string {
  const statusMap: Record<number, string> = {
    0: 'Menunggu',
    1: 'Memverifikasi',
    2: 'Memvalidasi Dokumen',
    3: 'Aktif',
  };
  return statusMap[status] || 'Tidak Diketahui';
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
              Kembali
            </Button>
          </Link>
          <div className="flex-1">
            <div className="flex items-center gap-3">
              <Globe className="h-6 w-6 text-primary" />
              <h1 className="text-3xl font-bold tracking-tight">{domain.name}</h1>
              {getStatusBadge(domain.status)}
            </div>
            <p className="text-muted-foreground mt-1">Detail domain dan informasi integrasi RDASH</p>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {/* Domain Information */}
          <Card>
            <CardHeader>
              <CardTitle>Informasi Domain</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div>
                <p className="text-sm text-muted-foreground">Nama Domain</p>
                <p className="font-semibold text-lg">{domain.name}</p>
              </div>

              <Separator />

              <div>
                <p className="text-sm text-muted-foreground mb-1">Status Domain</p>
                <div className="mt-1">{getStatusBadge(domain.status)}</div>
                <p className="text-xs text-muted-foreground mt-1">
                  {domain.status === 'active' && 'Domain aktif dan dapat digunakan'}
                  {domain.status === 'pending' && 'Domain sedang dalam proses aktivasi'}
                  {domain.status === 'expired' && 'Domain telah kedaluwarsa dan perlu diperpanjang'}
                </p>
              </div>

              {domain.customer && (
                <>
                  <Separator />
                  <div>
                    <p className="text-sm text-muted-foreground mb-1">Pemilik Domain</p>
                    <p className="font-semibold">{domain.customer.name}</p>
                    <p className="text-sm text-muted-foreground">{domain.customer.email}</p>
                  </div>
                </>
              )}

              <Separator />

              <div>
                <p className="text-sm text-muted-foreground mb-1">Perpanjangan Otomatis</p>
                <div className="mt-1">
                  {domain.auto_renew ? (
                    <Badge variant="default">Diaktifkan</Badge>
                  ) : (
                    <Badge variant="secondary">Dinonaktifkan</Badge>
                  )}
                </div>
                <p className="text-xs text-muted-foreground mt-1">
                  {domain.auto_renew 
                    ? 'Domain akan diperpanjang otomatis sebelum masa berlaku habis'
                    : 'Domain tidak akan diperpanjang otomatis, pastikan untuk memperpanjang manual'}
                </p>
              </div>

              <Separator />

              <div>
                <p className="text-sm text-muted-foreground mb-1">Tanggal Pendaftaran</p>
                <p className="font-semibold">{dayjs(domain.created_at).format('DD MMMM YYYY HH:mm')}</p>
                <p className="text-xs text-muted-foreground mt-1">
                  Terdaftar {dayjs(domain.created_at).fromNow()}
                </p>
              </div>
            </CardContent>
          </Card>

          {/* RDASH Integration */}
          <Card>
            <CardHeader>
              <CardTitle>Integrasi RDASH</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {domain.rdash_domain_id ? (
                <>
                  <div>
                    <p className="text-sm text-muted-foreground mb-1">ID Domain RDASH</p>
                    <p className="font-semibold font-mono">{domain.rdash_domain_id}</p>
                    <p className="text-xs text-muted-foreground mt-1">
                      ID unik domain di sistem RDASH
                    </p>
                  </div>

                  <Separator />

                  <div>
                    <p className="text-sm text-muted-foreground mb-1">Status Sinkronisasi</p>
                    <div className="mt-1">
                      {domain.rdash_sync_status === 'synced' && (
                        <Badge variant="default" className="bg-green-600">
                          <CheckCircle2 className="w-3 h-3 mr-1" />
                          Tersinkronisasi
                        </Badge>
                      )}
                      {domain.rdash_sync_status === 'pending' && (
                        <Badge variant="secondary" className="bg-yellow-600">
                          <Clock className="w-3 h-3 mr-1" />
                          Menunggu
                        </Badge>
                      )}
                      {domain.rdash_sync_status === 'failed' && (
                        <Badge variant="destructive">
                          <XCircle className="w-3 h-3 mr-1" />
                          Gagal
                        </Badge>
                      )}
                    </div>
                    <p className="text-xs text-muted-foreground mt-1">
                      {domain.rdash_sync_status === 'synced' && 'Domain berhasil tersinkronisasi dengan sistem RDASH'}
                      {domain.rdash_sync_status === 'pending' && 'Domain sedang menunggu proses sinkronisasi'}
                      {domain.rdash_sync_status === 'failed' && 'Sinkronisasi domain gagal, silakan hubungi support'}
                    </p>
                  </div>

                  {domain.rdash_synced_at && (
                    <>
                      <Separator />
                      <div>
                        <p className="text-sm text-muted-foreground mb-1">Terakhir Disinkronisasi</p>
                        <p className="font-semibold">
                          {dayjs(domain.rdash_synced_at).format('DD MMMM YYYY HH:mm')}
                        </p>
                        <p className="text-xs text-muted-foreground mt-1">
                          {dayjs(domain.rdash_synced_at).fromNow()}
                        </p>
                      </div>
                    </>
                  )}

                  {rdashDomain && (
                    <>
                      <Separator />
                      <div>
                        <p className="text-sm text-muted-foreground mb-1">Status di RDASH</p>
                        <p className="font-semibold">
                          {getRdashStatusLabel(rdashDomain.status)}
                        </p>
                        <p className="text-xs text-muted-foreground mt-1">
                          Status domain di sistem RDASH
                        </p>
                      </div>

                      <Separator />
                      <div>
                        <p className="text-sm text-muted-foreground mb-1">Status Verifikasi</p>
                        <p className="font-semibold">
                          {getVerificationStatusLabel(rdashDomain.verification_status)}
                        </p>
                        <p className="text-xs text-muted-foreground mt-1">
                          {rdashDomain.verification_status === 0 && 'Menunggu proses verifikasi dimulai'}
                          {rdashDomain.verification_status === 1 && 'Domain sedang dalam proses verifikasi'}
                          {rdashDomain.verification_status === 2 && 'Dokumen domain sedang divalidasi'}
                          {rdashDomain.verification_status === 3 && 'Domain telah terverifikasi dan aktif'}
                        </p>
                      </div>

                      {rdashDomain.expired_at && (
                        <>
                          <Separator />
                          <div>
                            <p className="text-sm text-muted-foreground mb-1">Tanggal Kedaluwarsa</p>
                            <p className="font-semibold">
                              {dayjs(rdashDomain.expired_at).format('DD MMMM YYYY')}
                            </p>
                            <p className="text-xs text-muted-foreground mt-1">
                              Domain akan kedaluwarsa dalam {dayjs(rdashDomain.expired_at).fromNow()}
                            </p>
                          </div>
                        </>
                      )}

                      {rdashDomain.nameservers && rdashDomain.nameservers.length > 0 && (
                        <>
                          <Separator />
                          <div>
                            <p className="text-sm text-muted-foreground mb-2">Nameserver</p>
                            <p className="text-xs text-muted-foreground mb-2">
                              Nameserver yang digunakan untuk mengarahkan domain ke server hosting
                            </p>
                            <ul className="space-y-1">
                              {rdashDomain.nameservers.map((ns, index) => (
                                <li key={index} className="text-sm font-mono bg-muted p-2 rounded">
                                  {ns}
                                </li>
                              ))}
                            </ul>
                          </div>
                        </>
                      )}

                      {domain.rdash_required_document && (
                        <>
                          <Separator />
                          <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <p className="text-sm font-medium text-yellow-800 mb-1">
                              ⚠️ Dokumen Diperlukan
                            </p>
                            <p className="text-xs text-yellow-700">
                              Domain ini memerlukan dokumen verifikasi tambahan. Silakan unggah dokumen yang diperlukan untuk menyelesaikan proses verifikasi.
                            </p>
                          </div>
                        </>
                      )}
                    </>
                  )}
                </>
              ) : (
                <div className="text-center py-8">
                  <Globe className="h-12 w-12 mx-auto text-muted-foreground mb-3" />
                  <p className="text-muted-foreground font-medium">Domain Belum Tersinkronisasi</p>
                  <p className="text-xs text-muted-foreground mt-1">
                    Domain ini belum terhubung dengan sistem RDASH. Silakan hubungi administrator untuk bantuan.
                  </p>
                </div>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </AppLayout>
  );
}

