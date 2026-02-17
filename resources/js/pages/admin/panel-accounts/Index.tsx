import React, { useState, useEffect, useCallback } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import InputError from '@/components/input-error';
import { UserCircle, Eye, Plus, Users, RefreshCw, ExternalLink, Loader2, CheckCircle2, XCircle } from 'lucide-react';
import dayjs from 'dayjs';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Panel Accounts', href: '/admin/panel-accounts' },
];

interface PanelAccount {
  id: string;
  username: string;
  domain: string;
  status: string;
  last_sync_at?: string;
  server?: {
    name: string;
    type: string;
  };
  subscription?: {
    id: string;
    product: {
      name: string;
    };
    plan: {
      code: string;
    } | null;
  };
}

interface Server {
  id: string;
  name: string;
  endpoint: string;
}

interface Package {
  package_id: number;
  package_name: string;
  disk_space_quota: number;
  monthly_bandwidth_limit: number;
  max_site_limit: number;
  max_database: number;
  max_email_account?: number;
  php_start_children: number;
  php_max_children: number;
  remark?: string;
  status?: number;
  use_count?: number;
  create_time?: number;
  updated_time?: number;
}

interface Disk {
  mountpoint: string;
  device?: string;
  fstype?: string;
  total?: number;
  used?: number;
  free?: number;
  used_percent?: number;
  inodes_total?: number;
  inodes_used?: number;
  inodes_free?: number;
  inodes_used_percent?: number;
  is_default?: boolean;
  is_group_quota?: boolean;
  is_user_quota?: boolean;
  account_allocate?: number;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PanelAccountsProps {
  accounts: {
    data: PanelAccount[];
    links: PaginationLink[];
    meta: {
      current_page: number;
      last_page: number;
      per_page: number;
      total: number;
    };
  };
  aapanelServers?: Server[];
}

export default function PanelAccountsIndex({ accounts, aapanelServers = [] }: PanelAccountsProps) {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isVirtualModalOpen, setIsVirtualModalOpen] = useState(false);
  const [packages, setPackages] = useState<Package[]>([]);
  const [disks, setDisks] = useState<Disk[]>([]);
  const [loadingPackages, setLoadingPackages] = useState(false);
  const [loadingDisks, setLoadingDisks] = useState(false);
  const [connectionStatus, setConnectionStatus] = useState<'idle' | 'testing' | 'success' | 'failed'>('idle');
  const [connectionMessage, setConnectionMessage] = useState('');

  const { data, setData, post, processing, errors, reset } = useForm({
    server_id: '',
    domain: '',
    username: '',
    password: '',
    path: '',
    php_version: '82',
    port: '80',
    type_id: '0',
    description: '',
  });

  const {
    data: virtualData,
    setData: setVirtualData,
    post: postVirtual,
    processing: processingVirtual,
    errors: errorsVirtual,
    reset: resetVirtual,
  } = useForm({
    server_id: '',
    username: '',
    password: '',
    email: '',
    expire_type: 'perpetual',
    expire_date: '0000-00-00',
    package_id: 0,
    package_name: 'Default',
    mountpoint: '/',
    disk_space_quota: 0,
    monthly_bandwidth_limit: 0,
    max_site_limit: 5,
    max_database: 5,
    php_start_children: 1,
    php_max_children: 5,
    remark: '',
    automatic_dns: '0',
    domain: '',
  });

  // State untuk selected package
  const [selectedPackage, setSelectedPackage] = useState<Package | null>(null);

  // Update form data ketika package dipilih
  const updatePackageData = useCallback((pkg: Package) => {
    setVirtualData(prev => ({
      ...prev,
      package_id: pkg.package_id,
      package_name: pkg.package_name,
      disk_space_quota: pkg.disk_space_quota,
      monthly_bandwidth_limit: pkg.monthly_bandwidth_limit,
      max_site_limit: pkg.max_site_limit,
      max_database: pkg.max_database,
      php_start_children: pkg.php_start_children,
      php_max_children: pkg.php_max_children,
    }));
  }, [setVirtualData]);

  const fetchPackages = useCallback(async (serverId: string) => {
    setLoadingPackages(true);
    try {
      const response = await fetch(`/admin/panel-accounts/server-packages?server_id=${serverId}`);
      const data = await response.json();
      if (data.success) {
        setPackages(data.packages);
        // Set default package jika ada
        if (data.packages.length > 0) {
          const defaultPkg = data.packages[0];
          setSelectedPackage(defaultPkg);
          updatePackageData(defaultPkg);
        }
      }
    } catch (err) {
      console.error('Failed to fetch packages:', err);
    } finally {
      setLoadingPackages(false);
    }
  }, [updatePackageData]);

  const fetchDisks = useCallback(async (serverId: string) => {
    setLoadingDisks(true);
    try {
      const response = await fetch(`/admin/panel-accounts/server-disks?server_id=${serverId}`);
      const data = await response.json();
      if (data.success) {
        setDisks(data.disks);
        // Set default mountpoint (prioritas ke disk dengan is_default: true atau "/" )
        if (data.disks.length > 0) {
          const defaultDisk = data.disks.find((d: Disk) => d.is_default) 
            || data.disks.find((d: Disk) => d.mountpoint === '/') 
            || data.disks[0];
          setVirtualData('mountpoint', defaultDisk.mountpoint);
        }
      }
    } catch (err) {
      console.error('Failed to fetch disks:', err);
    } finally {
      setLoadingDisks(false);
    }
  }, [setVirtualData]);

  const testConnection = async (serverId: string) => {
    setConnectionStatus('testing');
    try {
      const response = await fetch(`/admin/panel-accounts/test-connection?server_id=${serverId}`);
      const data = await response.json();
      if (data.success) {
        setConnectionStatus('success');
        setConnectionMessage(data.message || 'Connection successful');
      } else {
        setConnectionStatus('failed');
        setConnectionMessage(data.message || 'Connection failed');
      }
    } catch {
      setConnectionStatus('failed');
      setConnectionMessage('Failed to test connection');
    }
  };

  // Handle package selection
  const handlePackageSelect = (packageId: string) => {
    const pkg = packages.find(p => p.package_id.toString() === packageId);
    if (pkg) {
      setSelectedPackage(pkg);
      updatePackageData(pkg);
    }
  };

  // Fetch packages dan disks ketika server dipilih
  useEffect(() => {
    if (virtualData.server_id) {
      fetchPackages(virtualData.server_id);
      fetchDisks(virtualData.server_id);
      testConnection(virtualData.server_id);
    } else {
      setPackages([]);
      setDisks([]);
      setConnectionStatus('idle');
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [virtualData.server_id]);

  // Generate random password
  const generatePassword = () => {
    const lowercase = 'abcdefghjkmnpqrstuvwxyz';
    const uppercase = 'ABCDEFGHJKMNPQRSTUVWXYZ';
    const numbers = '23456789';
    const special = '!@#$%^&*_+-=';
    const allChars = lowercase + uppercase + numbers + special;
    
    let password = '';
    // Ensure at least one of each type
    password += lowercase[Math.floor(Math.random() * lowercase.length)];
    password += uppercase[Math.floor(Math.random() * uppercase.length)];
    password += numbers[Math.floor(Math.random() * numbers.length)];
    password += special[Math.floor(Math.random() * special.length)];
    
    // Fill rest
    for (let i = 4; i < 12; i++) {
      password += allChars[Math.floor(Math.random() * allChars.length)];
    }
    
    // Shuffle
    password = password.split('').sort(() => Math.random() - 0.5).join('');
    setVirtualData('password', password);
  };

  const getStatusBadge = (status: string) => {
    const colors: Record<string, string> = {
      active: 'bg-green-500',
      suspended: 'bg-yellow-500',
      terminated: 'bg-red-500',
    };
    return colors[status] || 'bg-gray-500';
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('admin.panel-accounts.create'), {
      preserveScroll: true,
      onSuccess: () => {
        setIsModalOpen(false);
        reset();
      },
    });
  };

  const handleOpenModal = () => {
    setIsModalOpen(true);
    reset();
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    reset();
  };

  const handleSubmitVirtual = (e: React.FormEvent) => {
    e.preventDefault();
    postVirtual(route('admin.panel-accounts.create-virtual'), {
      preserveScroll: true,
      onSuccess: () => {
        setIsVirtualModalOpen(false);
        resetVirtual();
      },
    });
  };

  const handleOpenVirtualModal = () => {
    resetVirtual();
    setPackages([]);
    setDisks([]);
    setSelectedPackage(null);
    setConnectionStatus('idle');
    setIsVirtualModalOpen(true);
    // Generate password otomatis
    setTimeout(() => {
      generatePassword();
    }, 100);
  };

  const handleCloseVirtualModal = () => {
    setIsVirtualModalOpen(false);
    resetVirtual();
  };

  const handleLoginToPanel = async (accountId: string) => {
    try {
      const response = await fetch(`/admin/panel-accounts/${accountId}/login-url`);
      const data = await response.json();
      if (data.success && data.login_url) {
        window.open(data.login_url, '_blank');
      } else {
        alert(data.message || 'Failed to get login URL');
      }
    } catch {
      alert('Failed to get login URL');
    }
  };

  const formatBytes = (bytes: number) => {
    if (bytes === 0) return 'Unlimited';
    const gb = bytes / (1024 * 1024 * 1024);
    if (gb >= 1) return `${gb.toFixed(1)} GB`;
    const mb = bytes / (1024 * 1024);
    return `${mb.toFixed(0)} MB`;
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Panel Accounts" />
      <div className="flex flex-col gap-6 p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">Panel Accounts</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">Kelola akun panel hosting</p>
          </div>
          {aapanelServers.length > 0 && (
            <div className="flex gap-2">
              <Button onClick={handleOpenModal} variant="outline">
                <Plus className="w-4 h-4 mr-2" />
                Tambah Website Account
              </Button>
              <Button onClick={handleOpenVirtualModal}>
                <Users className="w-4 h-4 mr-2" />
                Tambah Virtual Account
              </Button>
            </div>
          )}
        </div>

        {accounts.data.length === 0 ? (
          <Card className="bg-white dark:bg-gray-800 shadow-md">
            <CardContent className="p-8 text-center">
              <UserCircle className="w-12 h-12 mx-auto mb-4 text-gray-400" />
              <p className="text-gray-600 dark:text-gray-400">Belum ada panel account.</p>
            </CardContent>
          </Card>
        ) : (
          <div className="space-y-4">
            {accounts.data.map((account) => (
              <Card key={account.id} className="bg-white dark:bg-gray-800 shadow-md hover:shadow-lg transition-shadow">
                <CardContent className="p-6">
                  <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div className="flex-1">
                      <div className="flex items-center gap-3 mb-2">
                        <h3 className="text-lg font-semibold">{account.username}</h3>
                        <Badge className={getStatusBadge(account.status)}>
                          {account.status.toUpperCase()}
                        </Badge>
                      </div>
                      <div className="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        <p>Domain: <span className="font-semibold">{account.domain}</span></p>
                        {account.server && (
                          <p>Server: <span className="font-semibold">{account.server.name} ({account.server.type})</span></p>
                        )}
                        {account.subscription && (
                          <p>
                            Subscription:{' '}
                            <span className="font-semibold">
                              {account.subscription.product.name} - {account.subscription.plan?.code ?? 'Default'}
                            </span>
                          </p>
                        )}
                        {account.last_sync_at && (
                          <p>Last Sync: {dayjs(account.last_sync_at).format('DD MMM YYYY HH:mm')}</p>
                        )}
                      </div>
                    </div>
                    <div className="flex gap-2">
                      <Button variant="outline" size="sm" onClick={() => handleLoginToPanel(account.id)}>
                        <ExternalLink className="w-4 h-4 mr-2" />
                        Login
                      </Button>
                      <Link href={route('admin.panel-accounts.show', account.id)}>
                        <Button variant="outline" size="sm">
                          <Eye className="w-4 h-4 mr-2" />
                          Details
                        </Button>
                      </Link>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}

        {/* Pagination */}
        {accounts.links && accounts.links.length > 3 && (
          <div className="flex justify-center gap-2">
            {accounts.links.map((link: PaginationLink, index: number) => (
              <Link
                key={index}
                href={link.url || '#'}
                className={`px-4 py-2 rounded ${
                  link.active
                    ? 'bg-blue-600 text-white'
                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                } ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}`}
              >
                <span dangerouslySetInnerHTML={{ __html: link.label }} />
              </Link>
            ))}
          </div>
        )}

        {/* Create Website Account Modal */}
        <Dialog open={isModalOpen} onOpenChange={setIsModalOpen}>
          <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
              <DialogTitle>Tambah Website Account (aaPanel)</DialogTitle>
              <DialogDescription>
                Buat website account baru di server aaPanel
              </DialogDescription>
            </DialogHeader>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <Label htmlFor="server_id">Server *</Label>
                <Select
                  value={data.server_id}
                  onValueChange={(value) => setData('server_id', value)}
                >
                  <SelectTrigger className="mt-1">
                    <SelectValue placeholder="Pilih server aaPanel" />
                  </SelectTrigger>
                  <SelectContent>
                    {aapanelServers.map((server) => (
                      <SelectItem key={server.id} value={server.id}>
                        {server.name} ({server.endpoint})
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                <InputError message={errors.server_id} className="mt-2" />
              </div>

              <div>
                <Label htmlFor="domain">Domain *</Label>
                <Input
                  id="domain"
                  value={data.domain}
                  onChange={(e) => setData('domain', e.target.value)}
                  className="mt-1"
                  placeholder="example.com"
                  required
                />
                <InputError message={errors.domain} className="mt-2" />
              </div>

              <div>
                <Label htmlFor="username">Username (Opsional)</Label>
                <Input
                  id="username"
                  value={data.username}
                  onChange={(e) => setData('username', e.target.value)}
                  className="mt-1"
                  placeholder="Akan di-generate otomatis dari domain jika kosong"
                  maxLength={16}
                />
                <InputError message={errors.username} className="mt-2" />
              </div>

              <div>
                <Label htmlFor="password">Password (Opsional)</Label>
                <Input
                  id="password"
                  type="password"
                  value={data.password}
                  onChange={(e) => setData('password', e.target.value)}
                  className="mt-1"
                  placeholder="Akan di-generate otomatis jika kosong"
                />
                <InputError message={errors.password} className="mt-2" />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="php_version">PHP Version</Label>
                  <Select
                    value={data.php_version}
                    onValueChange={(value) => setData('php_version', value)}
                  >
                    <SelectTrigger className="mt-1">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="00">Static</SelectItem>
                      <SelectItem value="74">PHP 7.4</SelectItem>
                      <SelectItem value="80">PHP 8.0</SelectItem>
                      <SelectItem value="81">PHP 8.1</SelectItem>
                      <SelectItem value="82">PHP 8.2</SelectItem>
                      <SelectItem value="83">PHP 8.3</SelectItem>
                      <SelectItem value="84">PHP 8.4</SelectItem>
                    </SelectContent>
                  </Select>
                  <InputError message={errors.php_version} className="mt-2" />
                </div>

                <div>
                  <Label htmlFor="port">Port</Label>
                  <Input
                    id="port"
                    type="number"
                    value={data.port}
                    onChange={(e) => setData('port', e.target.value)}
                    className="mt-1"
                    min="1"
                    max="65535"
                  />
                  <InputError message={errors.port} className="mt-2" />
                </div>
              </div>

              <div>
                <Label htmlFor="description">Deskripsi (Opsional)</Label>
                <Input
                  id="description"
                  value={data.description}
                  onChange={(e) => setData('description', e.target.value)}
                  className="mt-1"
                  placeholder="Manual Account"
                />
                <InputError message={errors.description} className="mt-2" />
              </div>

              {(errors as Record<string, string>).error && (
                <div className="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                  <p className="text-sm text-red-600 dark:text-red-400">{(errors as Record<string, string>).error}</p>
                </div>
              )}

              <div className="flex justify-end gap-2 pt-4">
                <Button type="button" variant="outline" onClick={handleCloseModal}>
                  Batal
                </Button>
                <Button type="submit" disabled={processing}>
                  {processing ? 'Membuat...' : 'Buat Account'}
                </Button>
              </div>
            </form>
          </DialogContent>
        </Dialog>

        {/* Create Virtual Account Modal */}
        <Dialog open={isVirtualModalOpen} onOpenChange={setIsVirtualModalOpen}>
          <DialogContent className="max-w-3xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
              <DialogTitle>Add New Virtual Account</DialogTitle>
              <DialogDescription>
                Buat virtual account/sub user baru di aaPanel
              </DialogDescription>
            </DialogHeader>
            <form onSubmit={handleSubmitVirtual} className="space-y-4">
              {/* Server Selection */}
              <div>
                <Label htmlFor="virtual_server_id">Server *</Label>
                <Select
                  value={virtualData.server_id}
                  onValueChange={(value) => setVirtualData('server_id', value)}
                >
                  <SelectTrigger className="mt-1">
                    <SelectValue placeholder="Pilih server aaPanel" />
                  </SelectTrigger>
                  <SelectContent>
                    {aapanelServers.map((server) => (
                      <SelectItem key={server.id} value={server.id}>
                        {server.name} ({server.endpoint})
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                <InputError message={errorsVirtual.server_id} className="mt-2" />
              </div>

              {/* Connection Status */}
              {virtualData.server_id && (
                <div className={`p-3 rounded-md flex items-center gap-2 ${
                  connectionStatus === 'testing' ? 'bg-blue-50 dark:bg-blue-900/20' :
                  connectionStatus === 'success' ? 'bg-green-50 dark:bg-green-900/20' :
                  connectionStatus === 'failed' ? 'bg-red-50 dark:bg-red-900/20' :
                  'bg-gray-50 dark:bg-gray-900/20'
                }`}>
                  {connectionStatus === 'testing' && <Loader2 className="w-4 h-4 animate-spin text-blue-600" />}
                  {connectionStatus === 'success' && <CheckCircle2 className="w-4 h-4 text-green-600" />}
                  {connectionStatus === 'failed' && <XCircle className="w-4 h-4 text-red-600" />}
                  <span className={`text-sm ${
                    connectionStatus === 'testing' ? 'text-blue-600' :
                    connectionStatus === 'success' ? 'text-green-600' :
                    connectionStatus === 'failed' ? 'text-red-600' :
                    'text-gray-600'
                  }`}>
                    {connectionStatus === 'testing' ? 'Testing connection...' : connectionMessage}
                  </span>
                </div>
              )}

              {/* Username */}
              <div>
                <Label htmlFor="virtual_username">
                  Username <span className="text-red-500">*</span>
                </Label>
                <Input
                  id="virtual_username"
                  value={virtualData.username}
                  onChange={(e) => setVirtualData('username', e.target.value.toLowerCase().replace(/[^a-z0-9_]/g, ''))}
                  className="mt-1"
                  placeholder="username"
                  maxLength={16}
                  required
                />
                <InputError message={errorsVirtual.username} className="mt-2" />
                <p className="text-xs text-gray-500 mt-1">
                  Maksimal 16 karakter, hanya huruf kecil, angka, dan underscore
                </p>
              </div>

              {/* Password */}
              <div>
                <Label htmlFor="virtual_password">
                  Password <span className="text-red-500">*</span>
                </Label>
                <div className="flex gap-2 mt-1">
                  <Input
                    id="virtual_password"
                    type="text"
                    value={virtualData.password}
                    onChange={(e) => setVirtualData('password', e.target.value)}
                    placeholder="Password"
                    required
                    className="flex-1"
                  />
                  <Button
                    type="button"
                    variant="outline"
                    size="icon"
                    onClick={generatePassword}
                    className="shrink-0"
                    title="Generate Password"
                  >
                    <RefreshCw className="w-4 h-4" />
                  </Button>
                </div>
                <InputError message={errorsVirtual.password} className="mt-2" />
              </div>

              {/* Email */}
              <div>
                <Label htmlFor="virtual_email">
                  Email <span className="text-red-500">*</span>
                </Label>
                <Input
                  id="virtual_email"
                  type="email"
                  value={virtualData.email}
                  onChange={(e) => setVirtualData('email', e.target.value)}
                  className="mt-1"
                  placeholder="user@example.com"
                  required
                />
                <InputError message={errorsVirtual.email} className="mt-2" />
              </div>

              {/* Domain (Optional) */}
              <div>
                <Label htmlFor="virtual_domain">Domain (Opsional)</Label>
                <Input
                  id="virtual_domain"
                  value={virtualData.domain}
                  onChange={(e) => setVirtualData('domain', e.target.value)}
                  className="mt-1"
                  placeholder="example.com"
                />
                <InputError message={errorsVirtual.domain} className="mt-2" />
              </div>

              {/* Select Resource Package */}
              <div>
                <Label htmlFor="virtual_package">Select Resource Package</Label>
                <div className="flex gap-2 items-center mt-1">
                  <Select
                    value={virtualData.package_id ? virtualData.package_id.toString() : ''}
                    onValueChange={handlePackageSelect}
                    disabled={loadingPackages || packages.length === 0}
                  >
                    <SelectTrigger className="flex-1">
                      {loadingPackages ? (
                        <span className="flex items-center gap-2">
                          <Loader2 className="w-4 h-4 animate-spin" />
                          Loading...
                        </span>
                      ) : (
                        <SelectValue placeholder="Select package" />
                      )}
                    </SelectTrigger>
                    <SelectContent>
                      {packages.map((pkg) => (
                        <SelectItem key={pkg.package_id} value={pkg.package_id.toString()}>
                          {pkg.package_name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  {selectedPackage && (
                    <Button
                      type="button"
                      variant="link"
                      size="sm"
                      className="text-green-600 hover:text-green-700 px-2"
                      onClick={() => {
                        // Show package details (bisa di-expand)
                      }}
                    >
                      View Options
                    </Button>
                  )}
                </div>
                <InputError message={errorsVirtual.package_name} className="mt-2" />
                {/* Package Info */}
                {selectedPackage && (
                  <div className="mt-2 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-md text-sm border border-gray-200 dark:border-gray-700">
                    <div className="grid grid-cols-2 md:grid-cols-3 gap-3 text-gray-600 dark:text-gray-400">
                      <div className="flex flex-col">
                        <span className="text-xs text-gray-500">Disk Space</span>
                        <span className="font-medium">{selectedPackage.disk_space_quota === 0 ? 'Unlimited' : formatBytes(selectedPackage.disk_space_quota)}</span>
                      </div>
                      <div className="flex flex-col">
                        <span className="text-xs text-gray-500">Bandwidth</span>
                        <span className="font-medium">{selectedPackage.monthly_bandwidth_limit === 0 ? 'Unlimited' : formatBytes(selectedPackage.monthly_bandwidth_limit)}</span>
                      </div>
                      <div className="flex flex-col">
                        <span className="text-xs text-gray-500">Max Sites</span>
                        <span className="font-medium">{selectedPackage.max_site_limit}</span>
                      </div>
                      <div className="flex flex-col">
                        <span className="text-xs text-gray-500">Max Database</span>
                        <span className="font-medium">{selectedPackage.max_database}</span>
                      </div>
                      <div className="flex flex-col">
                        <span className="text-xs text-gray-500">PHP Workers</span>
                        <span className="font-medium">{selectedPackage.php_start_children}-{selectedPackage.php_max_children}</span>
                      </div>
                      {selectedPackage.use_count !== undefined && (
                        <div className="flex flex-col">
                          <span className="text-xs text-gray-500">In Use</span>
                          <span className="font-medium">{selectedPackage.use_count} accounts</span>
                        </div>
                      )}
                    </div>
                  </div>
                )}
              </div>

              {/* Storage Disk */}
              <div>
                <Label htmlFor="virtual_mountpoint">Storage Disk</Label>
                <Select
                  value={virtualData.mountpoint}
                  onValueChange={(value) => setVirtualData('mountpoint', value)}
                  disabled={loadingDisks || disks.length === 0}
                >
                  <SelectTrigger className="mt-1">
                    {loadingDisks ? (
                      <span className="flex items-center gap-2">
                        <Loader2 className="w-4 h-4 animate-spin" />
                        Loading...
                      </span>
                    ) : (
                      <SelectValue placeholder="Select disk" />
                    )}
                  </SelectTrigger>
                  <SelectContent>
                    {disks.map((disk) => {
                      const freeGB = disk.free ? (disk.free / (1024 * 1024 * 1024)).toFixed(2) : '0';
                      const totalGB = disk.total ? (disk.total / (1024 * 1024 * 1024)).toFixed(2) : '0';
                      const quotaStatus = disk.is_user_quota ? 'Quota:Enable' : 'Quota:Disable';
                      return (
                        <SelectItem key={disk.mountpoint} value={disk.mountpoint}>
                          {disk.mountpoint} ({freeGB} GB/{totalGB} GB) {quotaStatus}
                        </SelectItem>
                      );
                    })}
                  </SelectContent>
                </Select>
                <InputError message={errorsVirtual.mountpoint} className="mt-2" />
              </div>

              {/* Expiration Date */}
              <div>
                <Label className="mb-2 block">Expiration Date</Label>
                <div className="flex gap-6 mt-2">
                  <label className="flex items-center gap-2 cursor-pointer">
                    <input
                      type="radio"
                      name="expire_type"
                      value="perpetual"
                      checked={virtualData.expire_type === 'perpetual'}
                      onChange={(e) => {
                        setVirtualData('expire_type', e.target.value);
                        setVirtualData('expire_date', '0000-00-00');
                      }}
                      className="w-4 h-4 text-blue-600"
                    />
                    <span>Perpetual (No Expiration)</span>
                  </label>
                  <label className="flex items-center gap-2 cursor-pointer">
                    <input
                      type="radio"
                      name="expire_type"
                      value="custom"
                      checked={virtualData.expire_type === 'custom'}
                      onChange={(e) => setVirtualData('expire_type', e.target.value)}
                      className="w-4 h-4 text-blue-600"
                    />
                    <span>Custom</span>
                  </label>
                </div>
                {virtualData.expire_type === 'custom' && (
                  <Input
                    type="date"
                    value={virtualData.expire_date === '0000-00-00' ? '' : virtualData.expire_date}
                    onChange={(e) => setVirtualData('expire_date', e.target.value || '0000-00-00')}
                    className="mt-2"
                    min={new Date().toISOString().split('T')[0]}
                  />
                )}
                <InputError message={errorsVirtual.expire_date} className="mt-2" />
              </div>

              {/* Remarks */}
              <div>
                <Label htmlFor="virtual_remark">Remarks</Label>
                <Input
                  id="virtual_remark"
                  value={virtualData.remark}
                  onChange={(e) => setVirtualData('remark', e.target.value)}
                  className="mt-1"
                  placeholder="Optional notes"
                />
                <InputError message={errorsVirtual.remark} className="mt-2" />
              </div>

              {/* Automatic DNS */}
              <div>
                <Label className="mb-2 block">Automatic DNS</Label>
                <div className="flex gap-6 mt-2">
                  <label className="flex items-center gap-2 cursor-pointer">
                    <input
                      type="radio"
                      name="automatic_dns"
                      value="0"
                      checked={virtualData.automatic_dns === '0'}
                      onChange={(e) => setVirtualData('automatic_dns', e.target.value)}
                      className="w-4 h-4 text-blue-600"
                    />
                    <span>Disabled</span>
                  </label>
                  <label className="flex items-center gap-2 cursor-pointer">
                    <input
                      type="radio"
                      name="automatic_dns"
                      value="1"
                      checked={virtualData.automatic_dns === '1'}
                      onChange={(e) => setVirtualData('automatic_dns', e.target.value)}
                      className="w-4 h-4 text-blue-600"
                    />
                    <span>Enabled</span>
                  </label>
                </div>
              </div>

              {(errorsVirtual as Record<string, string>).error && (
                <div className="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                  <p className="text-sm text-red-600 dark:text-red-400">{(errorsVirtual as Record<string, string>).error}</p>
                </div>
              )}

              {/* API Whitelist Note */}
              <div className="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md">
                <p className="text-sm text-blue-700 dark:text-blue-300">
                  <span className="font-semibold">Automatic:</span> If the API is not open or 127.0.0.1 is not added to the API whitelist, 
                  it will not be possible to deploy web certificates and add post office domain names and deploy post office domain name certificates
                </p>
              </div>

              <div className="flex justify-end gap-2 pt-4">
                <Button type="button" variant="outline" onClick={handleCloseVirtualModal}>
                  Cancel
                </Button>
                <Button 
                  type="submit" 
                  disabled={processingVirtual || connectionStatus !== 'success'} 
                  className="bg-green-600 hover:bg-green-700"
                >
                  {processingVirtual ? (
                    <>
                      <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                      Creating...
                    </>
                  ) : (
                    'Create Account'
                  )}
                </Button>
              </div>
            </form>
          </DialogContent>
        </Dialog>
      </div>
    </AppLayout>
  );
}
