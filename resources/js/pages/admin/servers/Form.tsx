import React, { FormEventHandler, useState } from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/input-error';
import { Loader2, Wifi, CheckCircle2, XCircle } from 'lucide-react';
import { toast } from 'sonner';

interface Server {
  id?: string;
  name: string;
  type: string;
  endpoint: string;
  auth_secret_ref: string;
  status: string;
  meta?: any;
}

interface ServerFormProps {
  server?: Server;
}

export default function ServerForm({ server }: ServerFormProps) {
  const isEdit = !!server;
  const [testingConnection, setTestingConnection] = useState(false);
  const [connectionStatus, setConnectionStatus] = useState<{
    success: boolean;
    message: string;
  } | null>(null);

  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Servers', href: '/admin/servers' },
    { title: isEdit ? 'Edit Server' : 'Create Server', href: '#' },
  ];

  const { data, setData, post, put, processing, errors } = useForm({
    name: server?.name || '',
    type: server?.type || 'cpanel',
    endpoint: server?.endpoint || '',
    auth_secret_ref: server?.auth_secret_ref || '',
    status: server?.status || 'active',
    meta: {
      ...(server?.meta || {}),
      verify_ssl: server?.meta?.verify_ssl !== false, // Default true jika tidak ada
    },
  });

  const submit: FormEventHandler = (e) => {
    e.preventDefault();
    if (isEdit && server?.id) {
      put(route('admin.servers.update', server.id));
    } else {
      post(route('admin.servers.store'));
    }
  };

  const handleTestConnection = async () => {
    if (!data.endpoint || !data.auth_secret_ref) {
      toast.error('Endpoint dan Auth Secret Reference harus diisi terlebih dahulu');
      return;
    }

    if (!isEdit || !server?.id) {
      toast.error('Simpan server terlebih dahulu sebelum test koneksi');
      return;
    }

    setTestingConnection(true);
    setConnectionStatus(null);

    try {
      const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

      const response = await fetch(route('admin.servers.test-connection', server.id), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken || '',
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/json',
        },
        credentials: 'same-origin',
      });

      const result = await response.json();

      if (result.success) {
        setConnectionStatus({
          success: true,
          message: result.message || 'Koneksi berhasil',
        });
        toast.success(result.message || 'Koneksi berhasil');
      } else {
        setConnectionStatus({
          success: false,
          message: result.message || 'Koneksi gagal',
        });
        toast.error(result.message || 'Koneksi gagal');
      }
    } catch (error: any) {
      const errorMessage = error.message || 'Gagal test koneksi';
      setConnectionStatus({
        success: false,
        message: errorMessage,
      });
      toast.error(errorMessage);
    } finally {
      setTestingConnection(false);
    }
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={isEdit ? 'Edit Server' : 'Create Server'} />
      <div className="flex flex-col gap-6 p-4">
        <div>
          <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
            {isEdit ? 'Edit Server' : 'Create Server'}
          </h1>
          <p className="text-gray-600 dark:text-gray-400 mt-2">
            {isEdit ? 'Update server configuration' : 'Add a new provisioning server'}
          </p>
        </div>

        <Card className="bg-white dark:bg-gray-800 shadow-md">
          <CardHeader>
            <CardTitle>Server Information</CardTitle>
          </CardHeader>
          <CardContent>
            <form onSubmit={submit} className="space-y-4">
              <div>
                <Label htmlFor="name">Server Name</Label>
                <Input
                  id="name"
                  value={data.name}
                  onChange={(e) => setData('name', e.target.value)}
                  className="mt-1"
                  placeholder="e.g., cPanel Server 1"
                />
                <InputError message={errors.name} className="mt-2" />
              </div>

              <div>
                <Label htmlFor="type">Server Type</Label>
                <Select value={data.type} onValueChange={(value) => setData('type', value)}>
                  <SelectTrigger className="mt-1">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="cpanel">cPanel</SelectItem>
                    <SelectItem value="directadmin">DirectAdmin</SelectItem>
                    <SelectItem value="proxmox">Proxmox</SelectItem>
                    <SelectItem value="aapanel">aaPanel</SelectItem>
                  </SelectContent>
                </Select>
                <InputError message={errors.type} className="mt-2" />
              </div>

              <div>
                <Label htmlFor="endpoint">Endpoint URL</Label>
                <Input
                  id="endpoint"
                  type="url"
                  value={data.endpoint}
                  onChange={(e) => setData('endpoint', e.target.value)}
                  className="mt-1"
                  placeholder="https://server.example.com:2087"
                />
                <InputError message={errors.endpoint} className="mt-2" />
              </div>

              <div>
                <Label htmlFor="auth_secret_ref">Auth Secret Reference</Label>
                <Input
                  id="auth_secret_ref"
                  value={data.auth_secret_ref}
                  onChange={(e) => setData('auth_secret_ref', e.target.value)}
                  className="mt-1"
                  placeholder="SECRET_KEY_NAME or env variable name"
                />
                <InputError message={errors.auth_secret_ref} className="mt-2" />
                <p className="text-sm text-gray-500 mt-1">
                  Reference to secret key stored in environment or secret manager
                </p>
              </div>

              <div>
                <Label htmlFor="status">Status</Label>
                <Select value={data.status} onValueChange={(value) => setData('status', value)}>
                  <SelectTrigger className="mt-1">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="active">Active</SelectItem>
                    <SelectItem value="maintenance">Maintenance</SelectItem>
                    <SelectItem value="disabled">Disabled</SelectItem>
                  </SelectContent>
                </Select>
                <InputError message={errors.status} className="mt-2" />
              </div>

              <div className="flex items-center space-x-2 pt-2">
                <Checkbox
                  id="verify_ssl"
                  checked={data.meta?.verify_ssl !== false}
                  onCheckedChange={(checked) => {
                    setData('meta', {
                      ...data.meta,
                      verify_ssl: checked === true,
                    });
                  }}
                />
                <div className="grid gap-1.5 leading-none">
                  <Label
                    htmlFor="verify_ssl"
                    className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                  >
                    Verify SSL Certificate
                  </Label>
                  <p className="text-xs text-gray-500">
                    Nonaktifkan jika server tidak memiliki SSL certificate yang valid
                  </p>
                </div>
              </div>

              {isEdit && server?.id && (
                <div className="border-t pt-4">
                  <div className="flex items-center justify-between mb-2">
                    <Label>Test Connection</Label>
                    {connectionStatus && (
                      <div className="flex items-center gap-2">
                        {connectionStatus.success ? (
                          <>
                            <CheckCircle2 className="w-4 h-4 text-green-500" />
                            <span className="text-sm text-green-600 dark:text-green-400">
                              {connectionStatus.message}
                            </span>
                          </>
                        ) : (
                          <>
                            <XCircle className="w-4 h-4 text-red-500" />
                            <span className="text-sm text-red-600 dark:text-red-400">
                              {connectionStatus.message}
                            </span>
                          </>
                        )}
                      </div>
                    )}
                  </div>
                  <Button
                    type="button"
                    variant="outline"
                    onClick={handleTestConnection}
                    disabled={testingConnection || !data.endpoint || !data.auth_secret_ref}
                    className="w-full"
                  >
                    {testingConnection ? (
                      <>
                        <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                        Testing Connection...
                      </>
                    ) : (
                      <>
                        <Wifi className="w-4 h-4 mr-2" />
                        Test Connection
                      </>
                    )}
                  </Button>
                  <p className="text-xs text-gray-500 mt-2">
                    Test koneksi ke server untuk memastikan endpoint dan API key valid
                  </p>
                </div>
              )}

              <div className="flex gap-2">
                <Button type="submit" disabled={processing}>
                  {processing ? 'Saving...' : isEdit ? 'Update Server' : 'Create Server'}
                </Button>
                <Link href={route('admin.servers.index')}>
                  <Button type="button" variant="outline">Cancel</Button>
                </Link>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}

