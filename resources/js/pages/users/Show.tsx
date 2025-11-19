import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';
import { RdashSyncStatusBadge } from '@/components/rdash/RdashSyncStatusBadge';
import { SyncToRdashButton } from '@/components/rdash/SyncToRdashButton';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import 'dayjs/locale/id';

dayjs.extend(relativeTime);
dayjs.locale('id');

interface User {
  id: number;
  name: string;
  email: string;
  created_at: string;
  rdash_sync_status?: 'pending' | 'synced' | 'failed' | null;
  rdash_customer_id?: number | null;
  rdash_synced_at?: string | null;
  roles: {
    id: number;
    name: string;
  }[];
  customer?: {
    id: string;
    name: string;
    email: string;
    phone?: string;
  } | null;
}

interface RdashCustomer {
  rdash_customer?: {
    id: number;
    name: string;
    email: string;
    organization: string;
    street_1?: string;
    street_2?: string | null;
    city?: string;
    state?: string | null;
    country?: string | null;
    country_code?: string;
    postal_code?: string;
    voice?: string | null;
    fax?: string | null;
    reg_id?: string | null;
    is_2fa_enabled?: boolean;
    created_at?: string;
    updated_at?: string;
  };
  sync_status?: string;
  synced_at?: string;
  sync_error?: string;
}

interface Props {
  user: User;
  rdashCustomer?: RdashCustomer | null;
}

export default function UserShow({ user, rdashCustomer }: Props) {
  const [isEditingRdash, setIsEditingRdash] = useState(false);
  
  const { data, setData, put, processing, errors, reset } = useForm({
    name: rdashCustomer?.rdash_customer?.name || '',
    email: rdashCustomer?.rdash_customer?.email || '',
    organization: rdashCustomer?.rdash_customer?.organization || '',
    street_1: rdashCustomer?.rdash_customer?.street_1 || '',
    street_2: rdashCustomer?.rdash_customer?.street_2 || '',
    city: rdashCustomer?.rdash_customer?.city || '',
    state: rdashCustomer?.rdash_customer?.state || '',
    country_code: rdashCustomer?.rdash_customer?.country_code || 'ID',
    postal_code: rdashCustomer?.rdash_customer?.postal_code || '',
    voice: rdashCustomer?.rdash_customer?.voice || '',
    fax: rdashCustomer?.rdash_customer?.fax || '',
  });

  const handleEditRdash = () => {
    setIsEditingRdash(true);
  };

  const handleCancelEdit = () => {
    setIsEditingRdash(false);
    reset();
  };

  const handleSubmitRdash = (e: React.FormEvent) => {
    e.preventDefault();
    put(`/users/${user.id}/rdash-customer`, {
      preserveScroll: true,
      onSuccess: () => {
        setIsEditingRdash(false);
      },
    });
  };

  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'User Management', href: '/users' },
    { title: user.name, href: '#' },
  ];

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`User: ${user.name}`} />
      <div className="p-4 md:p-6 space-y-6">
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h1 className="text-2xl font-bold tracking-tight">{user.name}</h1>
            <p className="text-muted-foreground">{user.email}</p>
          </div>
          <div className="flex gap-2">
            <Link href={`/users/${user.id}/edit`}>
              <Button variant="outline">Edit</Button>
            </Link>
            <Link href="/users">
              <Button variant="secondary">Back</Button>
            </Link>
          </div>
        </div>

        <Tabs defaultValue="details" className="space-y-4">
          <TabsList>
            <TabsTrigger value="details">Details</TabsTrigger>
            <TabsTrigger value="rdash">RDASH Integration</TabsTrigger>
          </TabsList>

          <TabsContent value="details" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>User Information</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <label className="text-sm font-medium text-muted-foreground">Name</label>
                  <p className="text-base">{user.name}</p>
                </div>
                <Separator />
                <div>
                  <label className="text-sm font-medium text-muted-foreground">Email</label>
                  <p className="text-base">{user.email}</p>
                </div>
                <Separator />
                <div>
                  <label className="text-sm font-medium text-muted-foreground">Roles</label>
                  <div className="mt-2 flex flex-wrap gap-2">
                    {user.roles.map((role) => (
                      <Badge key={role.id} variant="secondary">
                        {role.name}
                      </Badge>
                    ))}
                  </div>
                </div>
                <Separator />
                <div>
                  <label className="text-sm font-medium text-muted-foreground">Created At</label>
                  <p className="text-base">{dayjs(user.created_at).format('DD MMMM YYYY HH:mm')}</p>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="rdash" className="space-y-4">
            <Card>
              <CardHeader>
                <div className="flex items-center justify-between">
                  <CardTitle>RDASH Integration</CardTitle>
                  {user.rdash_sync_status && (
                    <SyncToRdashButton
                      userId={user.id}
                      status={user.rdash_sync_status}
                      syncNow={true}
                    />
                  )}
                </div>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <label className="text-sm font-medium text-muted-foreground">Sync Status</label>
                  <div className="mt-2">
                    <RdashSyncStatusBadge status={user.rdash_sync_status} />
                  </div>
                </div>
                <Separator />
                {user.rdash_customer_id && (
                  <>
                    <div>
                      <label className="text-sm font-medium text-muted-foreground">RDASH Customer ID</label>
                      <p className="text-base font-mono">{user.rdash_customer_id}</p>
                    </div>
                    <Separator />
                  </>
                )}
                {user.rdash_synced_at && (
                  <>
                    <div>
                      <label className="text-sm font-medium text-muted-foreground">Last Synced</label>
                      <p className="text-base">{dayjs(user.rdash_synced_at).format('DD MMMM YYYY HH:mm')}</p>
                      <p className="text-sm text-muted-foreground">{dayjs(user.rdash_synced_at).fromNow()}</p>
                    </div>
                    <Separator />
                  </>
                )}
                {rdashCustomer?.rdash_customer && (
                  <>
                    <div className="flex items-center justify-between mb-4">
                      <label className="text-sm font-medium text-muted-foreground">RDASH Customer Details</label>
                      {!isEditingRdash && (
                        <Button type="button" variant="outline" size="sm" onClick={handleEditRdash}>
                          Edit Customer
                        </Button>
                      )}
                    </div>
                    
                    {isEditingRdash ? (
                      <form onSubmit={handleSubmitRdash} className="space-y-4">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                          <div>
                            <Label htmlFor="rdash_name">Name *</Label>
                            <Input
                              id="rdash_name"
                              value={data.name}
                              onChange={(e) => setData('name', e.target.value)}
                              className={errors.name ? 'border-red-500' : ''}
                            />
                            {errors.name && <p className="text-xs text-red-500 mt-1">{errors.name}</p>}
                          </div>
                          <div>
                            <Label htmlFor="rdash_email">Email *</Label>
                            <Input
                              id="rdash_email"
                              type="email"
                              value={data.email}
                              onChange={(e) => setData('email', e.target.value)}
                              className={errors.email ? 'border-red-500' : ''}
                            />
                            {errors.email && <p className="text-xs text-red-500 mt-1">{errors.email}</p>}
                          </div>
                          <div>
                            <Label htmlFor="rdash_organization">Organization *</Label>
                            <Input
                              id="rdash_organization"
                              value={data.organization}
                              onChange={(e) => setData('organization', e.target.value)}
                              className={errors.organization ? 'border-red-500' : ''}
                            />
                            {errors.organization && <p className="text-xs text-red-500 mt-1">{errors.organization}</p>}
                          </div>
                          <div>
                            <Label htmlFor="rdash_voice">Phone *</Label>
                            <Input
                              id="rdash_voice"
                              value={data.voice}
                              onChange={(e) => setData('voice', e.target.value)}
                              className={errors.voice ? 'border-red-500' : ''}
                              placeholder="Min 9, Max 20 characters"
                            />
                            {errors.voice && <p className="text-xs text-red-500 mt-1">{errors.voice}</p>}
                          </div>
                          <div>
                            <Label htmlFor="rdash_fax">Fax</Label>
                            <Input
                              id="rdash_fax"
                              value={data.fax}
                              onChange={(e) => setData('fax', e.target.value)}
                              className={errors.fax ? 'border-red-500' : ''}
                            />
                            {errors.fax && <p className="text-xs text-red-500 mt-1">{errors.fax}</p>}
                          </div>
                        </div>
                        <Separator />
                        <div>
                          <Label className="text-sm font-medium mb-4 block">Address</Label>
                          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                              <Label htmlFor="rdash_street_1">Street 1 *</Label>
                              <Input
                                id="rdash_street_1"
                                value={data.street_1}
                                onChange={(e) => setData('street_1', e.target.value)}
                                className={errors.street_1 ? 'border-red-500' : ''}
                              />
                              {errors.street_1 && <p className="text-xs text-red-500 mt-1">{errors.street_1}</p>}
                            </div>
                            <div>
                              <Label htmlFor="rdash_street_2">Street 2</Label>
                              <Input
                                id="rdash_street_2"
                                value={data.street_2}
                                onChange={(e) => setData('street_2', e.target.value)}
                                className={errors.street_2 ? 'border-red-500' : ''}
                              />
                              {errors.street_2 && <p className="text-xs text-red-500 mt-1">{errors.street_2}</p>}
                            </div>
                            <div>
                              <Label htmlFor="rdash_city">City *</Label>
                              <Input
                                id="rdash_city"
                                value={data.city}
                                onChange={(e) => setData('city', e.target.value)}
                                className={errors.city ? 'border-red-500' : ''}
                              />
                              {errors.city && <p className="text-xs text-red-500 mt-1">{errors.city}</p>}
                            </div>
                            <div>
                              <Label htmlFor="rdash_state">State</Label>
                              <Input
                                id="rdash_state"
                                value={data.state}
                                onChange={(e) => setData('state', e.target.value)}
                                className={errors.state ? 'border-red-500' : ''}
                              />
                              {errors.state && <p className="text-xs text-red-500 mt-1">{errors.state}</p>}
                            </div>
                            <div>
                              <Label htmlFor="rdash_country_code">Country Code *</Label>
                              <Input
                                id="rdash_country_code"
                                value={data.country_code}
                                onChange={(e) => setData('country_code', e.target.value.toUpperCase())}
                                maxLength={2}
                                className={errors.country_code ? 'border-red-500' : ''}
                              />
                              <p className="text-xs text-muted-foreground mt-1">ISO 3166-1 alpha-2 (e.g., ID, US, SG)</p>
                              {errors.country_code && <p className="text-xs text-red-500 mt-1">{errors.country_code}</p>}
                            </div>
                            <div>
                              <Label htmlFor="rdash_postal_code">Postal Code *</Label>
                              <Input
                                id="rdash_postal_code"
                                value={data.postal_code}
                                onChange={(e) => setData('postal_code', e.target.value)}
                                className={errors.postal_code ? 'border-red-500' : ''}
                              />
                              {errors.postal_code && <p className="text-xs text-red-500 mt-1">{errors.postal_code}</p>}
                            </div>
                          </div>
                        </div>
                        <div className="flex gap-2 justify-end pt-4">
                          <Button type="button" variant="outline" onClick={handleCancelEdit} disabled={processing}>
                            Cancel
                          </Button>
                          <Button type="submit" disabled={processing}>
                            {processing ? 'Saving...' : 'Save Changes'}
                          </Button>
                        </div>
                      </form>
                    ) : (
                      <>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                          <div>
                            <label className="text-xs font-medium text-muted-foreground">Customer ID</label>
                            <p className="text-base font-mono">{rdashCustomer.rdash_customer.id}</p>
                          </div>
                          <div>
                            <label className="text-xs font-medium text-muted-foreground">Name</label>
                            <p className="text-base">{rdashCustomer.rdash_customer.name}</p>
                          </div>
                          <div>
                            <label className="text-xs font-medium text-muted-foreground">Email</label>
                            <p className="text-base">{rdashCustomer.rdash_customer.email}</p>
                          </div>
                          <div>
                            <label className="text-xs font-medium text-muted-foreground">Organization</label>
                            <p className="text-base">{rdashCustomer.rdash_customer.organization}</p>
                          </div>
                          {rdashCustomer.rdash_customer.voice && (
                            <div>
                              <label className="text-xs font-medium text-muted-foreground">Phone</label>
                              <p className="text-base">{rdashCustomer.rdash_customer.voice}</p>
                            </div>
                          )}
                          {rdashCustomer.rdash_customer.fax && (
                            <div>
                              <label className="text-xs font-medium text-muted-foreground">Fax</label>
                              <p className="text-base">{rdashCustomer.rdash_customer.fax}</p>
                            </div>
                          )}
                        </div>
                        <Separator />
                        <div>
                          <label className="text-sm font-medium text-muted-foreground mb-4 block">Address</label>
                          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                              <label className="text-xs font-medium text-muted-foreground">Street 1</label>
                              <p className="text-base">{rdashCustomer.rdash_customer.street_1 || '-'}</p>
                            </div>
                            {rdashCustomer.rdash_customer.street_2 && (
                              <div>
                                <label className="text-xs font-medium text-muted-foreground">Street 2</label>
                                <p className="text-base">{rdashCustomer.rdash_customer.street_2}</p>
                              </div>
                            )}
                            <div>
                              <label className="text-xs font-medium text-muted-foreground">City</label>
                              <p className="text-base">{rdashCustomer.rdash_customer.city || '-'}</p>
                            </div>
                            {rdashCustomer.rdash_customer.state && (
                              <div>
                                <label className="text-xs font-medium text-muted-foreground">State</label>
                                <p className="text-base">{rdashCustomer.rdash_customer.state}</p>
                              </div>
                            )}
                            <div>
                              <label className="text-xs font-medium text-muted-foreground">Country</label>
                              <p className="text-base">
                                {rdashCustomer.rdash_customer.country || rdashCustomer.rdash_customer.country_code || '-'}
                              </p>
                            </div>
                            <div>
                              <label className="text-xs font-medium text-muted-foreground">Postal Code</label>
                              <p className="text-base">{rdashCustomer.rdash_customer.postal_code || '-'}</p>
                            </div>
                          </div>
                        </div>
                      </>
                    )}
                    <Separator />
                    {(rdashCustomer.rdash_customer.reg_id || 
                      rdashCustomer.rdash_customer.is_2fa_enabled !== undefined ||
                      rdashCustomer.rdash_customer.created_at ||
                      rdashCustomer.rdash_customer.updated_at) && (
                      <>
                        <Separator />
                        <div>
                          <label className="text-sm font-medium text-muted-foreground mb-4 block">Additional Information</label>
                          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {rdashCustomer.rdash_customer.reg_id && (
                              <div>
                                <label className="text-xs font-medium text-muted-foreground">Registration ID</label>
                                <p className="text-base">{rdashCustomer.rdash_customer.reg_id}</p>
                              </div>
                            )}
                            <div>
                              <label className="text-xs font-medium text-muted-foreground">2FA Enabled</label>
                              <p className="text-base">
                                <Badge variant={rdashCustomer.rdash_customer.is_2fa_enabled ? 'default' : 'secondary'}>
                                  {rdashCustomer.rdash_customer.is_2fa_enabled ? 'Yes' : 'No'}
                                </Badge>
                              </p>
                            </div>
                            {rdashCustomer.rdash_customer.created_at && (
                              <div>
                                <label className="text-xs font-medium text-muted-foreground">Created At</label>
                                <p className="text-base">{dayjs(rdashCustomer.rdash_customer.created_at).format('DD MMMM YYYY HH:mm')}</p>
                                <p className="text-xs text-muted-foreground">{dayjs(rdashCustomer.rdash_customer.created_at).fromNow()}</p>
                              </div>
                            )}
                            {rdashCustomer.rdash_customer.updated_at && (
                              <div>
                                <label className="text-xs font-medium text-muted-foreground">Updated At</label>
                                <p className="text-base">{dayjs(rdashCustomer.rdash_customer.updated_at).format('DD MMMM YYYY HH:mm')}</p>
                                <p className="text-xs text-muted-foreground">{dayjs(rdashCustomer.rdash_customer.updated_at).fromNow()}</p>
                              </div>
                            )}
                          </div>
                        </div>
                      </>
                    )}
                  </>
                )}
                {rdashCustomer?.sync_error && (
                  <div className="p-3 bg-destructive/10 border border-destructive/20 rounded-md">
                    <label className="text-sm font-medium text-destructive">Sync Error</label>
                    <p className="text-sm text-destructive mt-1">{rdashCustomer.sync_error}</p>
                  </div>
                )}
                {!user.rdash_sync_status && (
                  <div className="p-4 bg-muted rounded-md text-center">
                    <p className="text-sm text-muted-foreground">
                      User belum di-sync ke RDASH. Klik tombol "Sync to RDASH" untuk memulai sync.
                    </p>
                  </div>
                )}
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </div>
    </AppLayout>
  );
}

