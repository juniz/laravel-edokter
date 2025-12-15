import React, { useState } from 'react';
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
import { UserCircle, Eye, Plus } from 'lucide-react';
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
    };
  };
}

interface Server {
  id: string;
  name: string;
  endpoint: string;
}

interface PanelAccountsProps {
  accounts: {
    data: PanelAccount[];
    links: any;
    meta: any;
  };
  aapanelServers?: Server[];
}

export default function PanelAccountsIndex({ accounts, aapanelServers = [] }: PanelAccountsProps) {
  const [isModalOpen, setIsModalOpen] = useState(false);

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
            <Button onClick={handleOpenModal}>
              <Plus className="w-4 h-4 mr-2" />
              Tambah Account
            </Button>
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
                          <p>Subscription: <span className="font-semibold">{account.subscription.product.name} - {account.subscription.plan.code}</span></p>
                        )}
                        {account.last_sync_at && (
                          <p>Last Sync: {dayjs(account.last_sync_at).format('DD MMM YYYY HH:mm')}</p>
                        )}
                      </div>
                    </div>
                    <div>
                      <Link href={route('admin.panel-accounts.show', account.id)}>
                        <Button variant="outline">
                          <Eye className="w-4 h-4 mr-2" />
                          View Details
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
            {accounts.links.map((link: any, index: number) => (
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

        {/* Create Account Modal */}
        <Dialog open={isModalOpen} onOpenChange={setIsModalOpen}>
          <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
              <DialogTitle>Tambah Panel Account (aaPanel)</DialogTitle>
              <DialogDescription>
                Buat account baru di server aaPanel melalui API
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
                <p className="text-xs text-gray-500 mt-1">
                  Maksimal 16 karakter, hanya huruf kecil, angka, dan underscore
                </p>
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
                <p className="text-xs text-gray-500 mt-1">Minimal 8 karakter</p>
              </div>

              <div>
                <Label htmlFor="path">Path Website (Opsional)</Label>
                <Input
                  id="path"
                  value={data.path}
                  onChange={(e) => setData('path', e.target.value)}
                  className="mt-1"
                  placeholder="/www/wwwroot/example.com"
                />
                <InputError message={errors.path} className="mt-2" />
                <p className="text-xs text-gray-500 mt-1">
                  Default: /www/wwwroot/[domain]
                </p>
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
                      <SelectItem value="52">PHP 5.2</SelectItem>
                      <SelectItem value="53">PHP 5.3</SelectItem>
                      <SelectItem value="54">PHP 5.4</SelectItem>
                      <SelectItem value="55">PHP 5.5</SelectItem>
                      <SelectItem value="56">PHP 5.6</SelectItem>
                      <SelectItem value="70">PHP 7.0</SelectItem>
                      <SelectItem value="71">PHP 7.1</SelectItem>
                      <SelectItem value="72">PHP 7.2</SelectItem>
                      <SelectItem value="73">PHP 7.3</SelectItem>
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

              {errors.error && (
                <div className="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                  <p className="text-sm text-red-600 dark:text-red-400">{errors.error}</p>
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
      </div>
    </AppLayout>
  );
}

