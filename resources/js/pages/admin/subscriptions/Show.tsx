import React, { useState } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import {
  AlertDialog,
  AlertDialogTrigger,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogFooter,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogCancel,
  AlertDialogAction,
} from '@/components/ui/alert-dialog';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import {
  ExternalLink,
  Loader2,
  CheckCircle2,
  XCircle,
  PauseCircle,
  PlayCircle,
  Receipt,
  Server,
  CreditCard,
  Calendar,
  RefreshCw,
  FileText,
} from 'lucide-react';
import dayjs from 'dayjs';

interface Payment {
  id: string;
  provider: string;
  amount_cents: number;
  status: string;
  paid_at?: string;
}

interface Invoice {
  id: string;
  number: string;
  status: string;
  total_cents: number;
  currency: string;
  due_at?: string;
  payments?: Payment[];
}

interface SubscriptionCycle {
  id: string;
  cycle_no: number;
  period_start: string;
  period_end: string;
  invoice?: Invoice;
}

interface PanelAccount {
  id: string;
  username: string;
  domain: string;
  status: string;
  last_sync_at?: string;
  server?: {
    id: string;
    name: string;
    type: string;
    endpoint: string;
  };
}

interface Subscription {
  id: string;
  status: string;
  product: {
    name: string;
  };
  plan: {
    code: string;
    billing_cycle: string;
  } | null;
  start_at: string;
  end_at?: string;
  next_renewal_at?: string;
  auto_renew: boolean;
  provisioning_status: string;
  customer?: {
    name: string;
    email: string;
  };
  cycles?: SubscriptionCycle[];
  panel_account?: PanelAccount[];
}

interface SubscriptionShowProps {
  subscription: Subscription;
}

export default function AdminSubscriptionShow({ subscription }: SubscriptionShowProps) {
  const { post, processing } = useForm();
  const [panelActionLoading, setPanelActionLoading] = useState<string | null>(null);
  const [panelStatus, setPanelStatus] = useState<string | null>(
    subscription.panel_account?.[0]?.status || null
  );
  const [updatingStatus, setUpdatingStatus] = useState<string | null>(null);

  const handleCancel = () => {
    post(route('customer.subscriptions.cancel', subscription.id));
  };

  const handleUpdateStatus = async (status: string) => {
    setUpdatingStatus(status);
    try {
      const response = await fetch(route('admin.subscriptions.update-status', subscription.id), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') || '',
        },
        body: JSON.stringify({ status }),
      });
      const data = await response.json();
      if (data.success) {
        router.reload({ only: ['subscription'] });
      } else {
        alert(data.message || 'Gagal mengubah status subscription');
      }
    } catch {
      alert('Gagal mengubah status subscription');
    } finally {
      setUpdatingStatus(null);
    }
  };

  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Subscriptions', href: '/admin/subscriptions' },
    { title: subscription.product.name, href: route('admin.subscriptions.show', subscription.id) },
  ];

  const getStatusBadge = (status: string) => {
    const colors: Record<string, string> = {
      trialing: 'bg-blue-500',
      active: 'bg-green-500',
      past_due: 'bg-yellow-500',
      suspended: 'bg-orange-500',
      cancelled: 'bg-red-500',
      terminated: 'bg-gray-500',
    };
    return colors[status] || 'bg-gray-500';
  };

  const getInvoiceStatusBadge = (status: string) => {
    const config: Record<string, { color: string; label: string }> = {
      paid: { color: 'bg-green-500', label: 'PAID' },
      unpaid: { color: 'bg-yellow-500', label: 'UNPAID' },
      overdue: { color: 'bg-red-500', label: 'OVERDUE' },
      cancelled: { color: 'bg-gray-500', label: 'CANCELLED' },
    };
    return config[status] || { color: 'bg-gray-500', label: status.toUpperCase() };
  };

  const getPanelStatusBadge = (status: string) => {
    const config: Record<string, { color: string; icon: React.ReactNode }> = {
      active: { color: 'bg-green-500', icon: <CheckCircle2 className="w-3 h-3" /> },
      suspended: { color: 'bg-orange-500', icon: <PauseCircle className="w-3 h-3" /> },
      terminated: { color: 'bg-red-500', icon: <XCircle className="w-3 h-3" /> },
    };
    return config[status] || { color: 'bg-gray-500', icon: null };
  };

  const formatCurrency = (cents: number, currency: string = 'IDR') => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: currency,
      minimumFractionDigits: 0,
    }).format(cents / 100);
  };

  const handleLoginToPanel = async () => {
    setPanelActionLoading('login');
    try {
      const response = await fetch(route('admin.subscriptions.panel-login', subscription.id));
      const data = await response.json();
      if (data.success && data.login_url) {
        window.open(data.login_url, '_blank');
      } else {
        alert(data.message || 'Gagal mendapatkan URL login');
      }
    } catch {
      alert('Gagal mendapatkan URL login');
    } finally {
      setPanelActionLoading(null);
    }
  };

  const handleSuspendPanel = async () => {
    setPanelActionLoading('suspend');
    try {
      const response = await fetch(route('admin.subscriptions.suspend', subscription.id), {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
      });
      const data = await response.json();
      if (data.success) {
        setPanelStatus('suspended');
        router.reload({ only: ['subscription'] });
      } else {
        alert(data.message || 'Gagal suspend account');
      }
    } catch {
      alert('Gagal suspend account');
    } finally {
      setPanelActionLoading(null);
    }
  };

  const handleUnsuspendPanel = async () => {
    setPanelActionLoading('unsuspend');
    try {
      const response = await fetch(route('admin.subscriptions.unsuspend', subscription.id), {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
      });
      const data = await response.json();
      if (data.success) {
        setPanelStatus('active');
        router.reload({ only: ['subscription'] });
      } else {
        alert(data.message || 'Gagal mengaktifkan account');
      }
    } catch {
      alert('Gagal mengaktifkan account');
    } finally {
      setPanelActionLoading(null);
    }
  };

  // Get panel account (first one)
  const panelAccount = subscription.panel_account?.[0];
  const currentPanelStatus = panelStatus || panelAccount?.status;

  // Get paid cycles (cycles with paid invoice)
  const paidCycles = subscription.cycles?.filter(
    (cycle) => cycle.invoice?.status === 'paid'
  ) || [];

  // Get all cycles sorted by cycle_no
  const allCycles = [...(subscription.cycles || [])].sort((a, b) => b.cycle_no - a.cycle_no);

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={subscription.product.name} />
      <div className="flex flex-col gap-6 p-4">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold">{subscription.product.name}</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">
              Plan: {subscription.plan?.code ?? 'Default'}
            </p>
            {subscription.customer && (
              <p className="text-gray-600 dark:text-gray-400">
                Customer: {subscription.customer.name} ({subscription.customer.email})
              </p>
            )}
          </div>
          <Badge className={getStatusBadge(subscription.status)}>
            {subscription.status.toUpperCase()}
          </Badge>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Main Content - Left Side */}
          <div className="lg:col-span-2 space-y-6">
            {/* Subscription Details */}
            <Card className="bg-white dark:bg-gray-800 shadow-md">
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Calendar className="w-5 h-5" />
                  Subscription Details
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Status</span>
                  <Badge className={getStatusBadge(subscription.status)}>{subscription.status}</Badge>
                </div>
                <div className="flex flex-wrap gap-2">
                  <Button
                    size="sm"
                    variant="outline"
                    disabled={updatingStatus !== null}
                    onClick={() => handleUpdateStatus('active')}
                  >
                    {updatingStatus === 'active' ? (
                      <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                    ) : (
                      <CheckCircle2 className="w-4 h-4 mr-2" />
                    )}
                    Set Active
                  </Button>
                  <AlertDialog>
                    <AlertDialogTrigger asChild>
                      <Button
                        size="sm"
                        variant="outline"
                        className="border-orange-500 text-orange-500 hover:bg-orange-50 dark:hover:bg-orange-900/20"
                        disabled={updatingStatus !== null}
                      >
                        {updatingStatus === 'suspended' ? (
                          <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                        ) : (
                          <PauseCircle className="w-4 h-4 mr-2" />
                        )}
                        Set Suspended
                      </Button>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                      <AlertDialogHeader>
                        <AlertDialogTitle>Suspend subscription?</AlertDialogTitle>
                        <AlertDialogDescription>
                          Subscription akan diubah statusnya menjadi suspended. Akses layanan
                          customer bisa ikut dibatasi sesuai konfigurasi panel.
                        </AlertDialogDescription>
                      </AlertDialogHeader>
                      <AlertDialogFooter>
                        <AlertDialogCancel>Batal</AlertDialogCancel>
                        <AlertDialogAction onClick={() => handleUpdateStatus('suspended')}>
                          Ya, suspend
                        </AlertDialogAction>
                      </AlertDialogFooter>
                    </AlertDialogContent>
                  </AlertDialog>
                  <AlertDialog>
                    <AlertDialogTrigger asChild>
                      <Button
                        size="sm"
                        variant="outline"
                        className="border-red-500 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20"
                        disabled={updatingStatus !== null}
                      >
                        {updatingStatus === 'cancelled' ? (
                          <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                        ) : (
                          <XCircle className="w-4 h-4 mr-2" />
                        )}
                        Set Cancelled
                      </Button>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                      <AlertDialogHeader>
                        <AlertDialogTitle>Batalkan subscription?</AlertDialogTitle>
                        <AlertDialogDescription>
                          Subscription akan dibatalkan dan auto renew dimatikan. Layanan dapat
                          tetap aktif sampai akhir periode saat ini.
                        </AlertDialogDescription>
                      </AlertDialogHeader>
                      <AlertDialogFooter>
                        <AlertDialogCancel>Batal</AlertDialogCancel>
                        <AlertDialogAction onClick={() => handleUpdateStatus('cancelled')}>
                          Ya, batalkan
                        </AlertDialogAction>
                      </AlertDialogFooter>
                    </AlertDialogContent>
                  </AlertDialog>
                  <AlertDialog>
                    <AlertDialogTrigger asChild>
                      <Button
                        size="sm"
                        variant="outline"
                        className="border-gray-500 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-900/40"
                        disabled={updatingStatus !== null}
                      >
                        {updatingStatus === 'terminated' ? (
                          <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                        ) : (
                          <XCircle className="w-4 h-4 mr-2" />
                        )}
                        Set Terminated
                      </Button>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                      <AlertDialogHeader>
                        <AlertDialogTitle>Terminate subscription?</AlertDialogTitle>
                        <AlertDialogDescription>
                          Subscription akan dihentikan permanen dan auto renew dimatikan. Pastikan
                          data penting sudah dicadangkan sebelum melanjutkan.
                        </AlertDialogDescription>
                      </AlertDialogHeader>
                      <AlertDialogFooter>
                        <AlertDialogCancel>Batal</AlertDialogCancel>
                        <AlertDialogAction onClick={() => handleUpdateStatus('terminated')}>
                          Ya, terminate
                        </AlertDialogAction>
                      </AlertDialogFooter>
                    </AlertDialogContent>
                  </AlertDialog>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Plan</span>
                  <span className="font-semibold">{subscription.plan?.code ?? 'Default'}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Billing Cycle</span>
                  <span>{subscription.plan?.billing_cycle ?? '-'}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Started</span>
                  <span>{dayjs(subscription.start_at).format('DD MMM YYYY')}</span>
                </div>
                {subscription.end_at && (
                  <div className="flex justify-between">
                    <span className="text-gray-600 dark:text-gray-400">Ends</span>
                    <span>{dayjs(subscription.end_at).format('DD MMM YYYY')}</span>
                  </div>
                )}
                {subscription.next_renewal_at && (
                  <div className="flex justify-between">
                    <span className="text-gray-600 dark:text-gray-400">Next Renewal</span>
                    <span>{dayjs(subscription.next_renewal_at).format('DD MMM YYYY')}</span>
                  </div>
                )}
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Auto Renew</span>
                  <Badge variant={subscription.auto_renew ? 'default' : 'outline'}>
                    {subscription.auto_renew ? 'Yes' : 'No'}
                  </Badge>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600 dark:text-gray-400">Provisioning Status</span>
                  <Badge>{subscription.provisioning_status}</Badge>
                </div>
              </CardContent>
            </Card>

            {/* Panel Account Management */}
            {panelAccount && (
              <Card className="bg-white dark:bg-gray-800 shadow-md">
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <Server className="w-5 h-5" />
                    Panel Account Management
                  </CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <p className="text-sm text-gray-500 dark:text-gray-400">Username</p>
                      <p className="font-semibold">{panelAccount.username}</p>
                    </div>
                    <div>
                      <p className="text-sm text-gray-500 dark:text-gray-400">Domain</p>
                      <p className="font-semibold">{panelAccount.domain}</p>
                    </div>
                    <div>
                      <p className="text-sm text-gray-500 dark:text-gray-400">Status</p>
                      <Badge className={`${getPanelStatusBadge(currentPanelStatus || 'active').color} flex items-center gap-1 w-fit`}>
                        {getPanelStatusBadge(currentPanelStatus || 'active').icon}
                        {(currentPanelStatus || 'active').toUpperCase()}
                      </Badge>
                    </div>
                    {panelAccount.server && (
                      <div>
                        <p className="text-sm text-gray-500 dark:text-gray-400">Server</p>
                        <p className="font-semibold">{panelAccount.server.name}</p>
                      </div>
                    )}
                  </div>

                  {panelAccount.last_sync_at && (
                    <div className="text-sm text-gray-500 dark:text-gray-400">
                      Last Sync: {dayjs(panelAccount.last_sync_at).format('DD MMM YYYY HH:mm')}
                    </div>
                  )}

                  <Separator />

                  {/* Panel Actions */}
                  <div className="flex flex-wrap gap-2">
                    <Button
                      onClick={handleLoginToPanel}
                      disabled={panelActionLoading !== null}
                      className="bg-blue-600 hover:bg-blue-700"
                    >
                      {panelActionLoading === 'login' ? (
                        <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                      ) : (
                        <ExternalLink className="w-4 h-4 mr-2" />
                      )}
                      Login ke Panel
                    </Button>

                    {currentPanelStatus === 'active' ? (
                      <AlertDialog>
                        <AlertDialogTrigger asChild>
                          <Button
                            variant="outline"
                            className="border-orange-500 text-orange-500 hover:bg-orange-50 dark:hover:bg-orange-900/20"
                            disabled={panelActionLoading !== null}
                          >
                            {panelActionLoading === 'suspend' ? (
                              <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                            ) : (
                              <PauseCircle className="w-4 h-4 mr-2" />
                            )}
                            Suspend Account
                          </Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                          <AlertDialogHeader>
                            <AlertDialogTitle>Suspend Panel Account?</AlertDialogTitle>
                            <AlertDialogDescription>
                              Account akan di-suspend dan user tidak bisa mengakses panel.
                              Semua website tetap ada tapi tidak bisa diakses publik.
                            </AlertDialogDescription>
                          </AlertDialogHeader>
                          <AlertDialogFooter>
                            <AlertDialogCancel>Batal</AlertDialogCancel>
                            <AlertDialogAction onClick={handleSuspendPanel}>
                              Ya, Suspend
                            </AlertDialogAction>
                          </AlertDialogFooter>
                        </AlertDialogContent>
                      </AlertDialog>
                    ) : currentPanelStatus === 'suspended' ? (
                      <Button
                        onClick={handleUnsuspendPanel}
                        variant="outline"
                        className="border-green-500 text-green-500 hover:bg-green-50 dark:hover:bg-green-900/20"
                        disabled={panelActionLoading !== null}
                      >
                        {panelActionLoading === 'unsuspend' ? (
                          <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                        ) : (
                          <PlayCircle className="w-4 h-4 mr-2" />
                        )}
                        Aktifkan Kembali
                      </Button>
                    ) : null}

                    {panelAccount.server && (
                      <Link href={route('admin.panel-accounts.show', panelAccount.id)}>
                        <Button variant="outline">
                          View Panel Account
                        </Button>
                      </Link>
                    )}
                  </div>
                </CardContent>
              </Card>
            )}

            {/* Billing History */}
            <Card className="bg-white dark:bg-gray-800 shadow-md">
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Receipt className="w-5 h-5" />
                  Billing History
                </CardTitle>
              </CardHeader>
              <CardContent>
                {allCycles.length === 0 ? (
                  <div className="text-center py-8 text-gray-500 dark:text-gray-400">
                    <CreditCard className="w-12 h-12 mx-auto mb-4 opacity-50" />
                    <p>Belum ada billing history</p>
                  </div>
                ) : (
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Cycle</TableHead>
                        <TableHead>Period</TableHead>
                        <TableHead>Invoice</TableHead>
                        <TableHead>Amount</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead className="text-right">Action</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {allCycles.map((cycle) => {
                        const invoiceStatus = getInvoiceStatusBadge(cycle.invoice?.status || 'unpaid');
                        return (
                          <TableRow key={cycle.id}>
                            <TableCell className="font-medium">#{cycle.cycle_no}</TableCell>
                            <TableCell>
                              <div className="text-sm">
                                <div>{dayjs(cycle.period_start).format('DD MMM YYYY')}</div>
                                <div className="text-gray-500">to {dayjs(cycle.period_end).format('DD MMM YYYY')}</div>
                              </div>
                            </TableCell>
                            <TableCell>
                              {cycle.invoice ? (
                                <span className="font-mono text-sm">{cycle.invoice.number}</span>
                              ) : (
                                <span className="text-gray-400">-</span>
                              )}
                            </TableCell>
                            <TableCell>
                              {cycle.invoice ? (
                                formatCurrency(cycle.invoice.total_cents, cycle.invoice.currency)
                              ) : (
                                <span className="text-gray-400">-</span>
                              )}
                            </TableCell>
                            <TableCell>
                              <Badge className={invoiceStatus.color}>
                                {invoiceStatus.label}
                              </Badge>
                            </TableCell>
                            <TableCell className="text-right">
                              {cycle.invoice && (
                                <Link href={route('admin.invoices.show', cycle.invoice.id)}>
                                  <Button variant="ghost" size="sm">
                                    <FileText className="w-4 h-4" />
                                  </Button>
                                </Link>
                              )}
                            </TableCell>
                          </TableRow>
                        );
                      })}
                    </TableBody>
                  </Table>
                )}
              </CardContent>
            </Card>
          </div>

          {/* Right Side - Actions & Summary */}
          <div className="space-y-6">
            {/* Quick Stats */}
            {paidCycles.length > 0 && (
              <Card className="bg-gradient-to-br from-green-500 to-green-600 text-white shadow-lg">
                <CardContent className="p-6">
                  <div className="flex items-center gap-3 mb-4">
                    <CheckCircle2 className="w-8 h-8" />
                    <div>
                      <p className="text-sm opacity-80">Total Paid</p>
                      <p className="text-2xl font-bold">
                        {formatCurrency(
                          paidCycles.reduce((sum, cycle) => sum + (cycle.invoice?.total_cents || 0), 0),
                          paidCycles[0]?.invoice?.currency
                        )}
                      </p>
                    </div>
                  </div>
                  <p className="text-sm opacity-80">
                    {paidCycles.length} pembayaran berhasil
                  </p>
                </CardContent>
              </Card>
            )}

            {/* Actions Card */}
            <Card className="bg-white dark:bg-gray-800 shadow-md">
              <CardHeader>
                <CardTitle>Actions</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                {subscription.status === 'active' && (
                  <AlertDialog>
                    <AlertDialogTrigger asChild>
                      <Button variant="destructive" className="w-full">Cancel Subscription</Button>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                      <AlertDialogHeader>
                        <AlertDialogTitle>Cancel Subscription?</AlertDialogTitle>
                        <AlertDialogDescription>
                          Apakah Anda yakin ingin membatalkan subscription ini?
                          Subscription akan tetap aktif hingga tanggal akhir periode.
                        </AlertDialogDescription>
                      </AlertDialogHeader>
                      <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction
                          onClick={handleCancel}
                          disabled={processing}
                        >
                          Yes, Cancel
                        </AlertDialogAction>
                      </AlertDialogFooter>
                    </AlertDialogContent>
                  </AlertDialog>
                )}

                <Button
                  variant="outline"
                  className="w-full"
                  onClick={() => router.reload()}
                >
                  <RefreshCw className="w-4 h-4 mr-2" />
                  Refresh Data
                </Button>

                {panelAccount?.server && (
                  <Link href={route('admin.servers.show', panelAccount.server.id)} className="block">
                    <Button variant="outline" className="w-full">
                      <Server className="w-4 h-4 mr-2" />
                      View Server
                    </Button>
                  </Link>
                )}
              </CardContent>
            </Card>

            {/* Customer Info */}
            {subscription.customer && (
              <Card className="bg-white dark:bg-gray-800 shadow-md">
                <CardHeader>
                  <CardTitle>Customer</CardTitle>
                </CardHeader>
                <CardContent className="space-y-2">
                  <p className="font-semibold">{subscription.customer.name}</p>
                  <p className="text-sm text-gray-500 dark:text-gray-400">{subscription.customer.email}</p>
                </CardContent>
              </Card>
            )}
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
