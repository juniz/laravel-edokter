import React, { useState, useMemo } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { type BreadcrumbItem } from '@/types';
import { RdashSyncStatusBadge } from '@/components/rdash/RdashSyncStatusBadge';
import { SyncToRdashButton } from '@/components/rdash/SyncToRdashButton';
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
import { Search, Users, CheckCircle2, Clock, XCircle } from 'lucide-react';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import 'dayjs/locale/id';

dayjs.extend(relativeTime);
dayjs.locale('id');

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'User Management',
    href: '/users',
  },
];

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
}

interface Props {
  users: {
    data: User[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
  };
  filters?: {
    search?: string;
    role?: string;
    rdash_status?: string;
    per_page?: number;
  };
  stats?: {
    total: number;
    synced: number;
    pending: number;
    failed: number;
  };
  roles?: string[];
}

function getInitials(name: string) {
  return name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
}

export default function UserIndex({ users, filters = {}, stats, roles = [] }: Props) {
  const { delete: destroy, processing } = useForm();
  const [searchQuery, setSearchQuery] = useState(filters.search || '');
  const [debounceTimer, setDebounceTimer] = useState<NodeJS.Timeout | null>(null);

  const handleSearch = (value: string) => {
    setSearchQuery(value);
    
    if (debounceTimer) {
      clearTimeout(debounceTimer);
    }

    const timer = setTimeout(() => {
      const params = new URLSearchParams(window.location.search);
      if (value) {
        params.set('search', value);
      } else {
        params.delete('search');
      }
      params.delete('page'); // Reset to first page on new search
      router.get(`/users?${params.toString()}`, {}, { preserveState: true, preserveScroll: false });
    }, 500);

    setDebounceTimer(timer);
  };

  const handleFilterChange = (key: string, value: string) => {
    const params = new URLSearchParams(window.location.search);
    if (value && value !== 'all') {
      params.set(key, value);
    } else {
      params.delete(key);
    }
    params.delete('page'); // Reset to first page on filter change
    router.get(`/users?${params.toString()}`, {}, { preserveState: true, preserveScroll: false });
  };

  const handleDelete = (id: number) => {
    destroy(`/users/${id}`, {
      preserveScroll: true,
      onSuccess: () => {
        // Data akan otomatis terupdate karena Inertia.js
      },
      onError: (errors) => {
        console.error('Delete failed:', errors);
      }
    });
  };

  const handleResetPassword = (id: number) => {
    router.put(`/users/${id}/reset-password`, {}, { preserveScroll: true });
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="User Management" />
      <div className="p-4 md:p-6 space-y-6">
        {/* Header */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">User Management</h1>
            <p className="text-muted-foreground mt-1">Manage user data and their roles within the system.</p>
          </div>
          <Link href="/users/create">
            <Button className="w-full md:w-auto">
              <Users className="w-4 h-4 mr-2" />
              Add User
            </Button>
          </Link>
        </div>

        {/* Stats Cards */}
        {stats && (
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            <Card>
              <CardContent className="p-4">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm font-medium text-muted-foreground">Total Users</p>
                    <p className="text-2xl font-bold mt-1">{stats.total}</p>
                  </div>
                  <div className="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center">
                    <Users className="h-6 w-6 text-primary" />
                  </div>
                </div>
              </CardContent>
            </Card>
            <Card>
              <CardContent className="p-4">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm font-medium text-muted-foreground">Synced</p>
                    <p className="text-2xl font-bold mt-1 text-green-600">{stats.synced}</p>
                  </div>
                  <div className="h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/20 flex items-center justify-center">
                    <CheckCircle2 className="h-6 w-6 text-green-600" />
                  </div>
                </div>
              </CardContent>
            </Card>
            <Card>
              <CardContent className="p-4">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm font-medium text-muted-foreground">Pending</p>
                    <p className="text-2xl font-bold mt-1 text-yellow-600">{stats.pending}</p>
                  </div>
                  <div className="h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900/20 flex items-center justify-center">
                    <Clock className="h-6 w-6 text-yellow-600" />
                  </div>
                </div>
              </CardContent>
            </Card>
            <Card>
              <CardContent className="p-4">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm font-medium text-muted-foreground">Failed</p>
                    <p className="text-2xl font-bold mt-1 text-red-600">{stats.failed}</p>
                  </div>
                  <div className="h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/20 flex items-center justify-center">
                    <XCircle className="h-6 w-6 text-red-600" />
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        )}

        {/* Filters */}
        <Card>
          <CardContent className="p-4">
            <div className="flex flex-col md:flex-row gap-4">
              {/* Search */}
              <div className="flex-1">
                <div className="relative">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                  <Input
                    placeholder="Search by name or email..."
                    value={searchQuery}
                    onChange={(e) => handleSearch(e.target.value)}
                    className="pl-10"
                  />
                </div>
              </div>

              {/* Role Filter */}
              <div className="w-full md:w-48">
                <Select
                  value={filters.role || 'all'}
                  onValueChange={(value) => handleFilterChange('role', value)}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Filter by role" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Roles</SelectItem>
                    {roles.map((role) => (
                      <SelectItem key={role} value={role}>
                        {role}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>

              {/* RDASH Status Filter */}
              <div className="w-full md:w-48">
                <Select
                  value={filters.rdash_status || 'all'}
                  onValueChange={(value) => handleFilterChange('rdash_status', value)}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="RDASH Status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Status</SelectItem>
                    <SelectItem value="synced">Synced</SelectItem>
                    <SelectItem value="pending">Pending</SelectItem>
                    <SelectItem value="failed">Failed</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Users List */}
        {users.data.length === 0 ? (
          <Card>
            <CardContent className="p-12 text-center">
              <Users className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
              <h3 className="text-lg font-semibold mb-2">No users found</h3>
              <p className="text-muted-foreground mb-4">
                {searchQuery || filters.role || filters.rdash_status
                  ? 'Try adjusting your filters'
                  : 'Get started by creating a new user'}
              </p>
              {!searchQuery && !filters.role && !filters.rdash_status && (
                <Link href="/users/create">
                  <Button>Add User</Button>
                </Link>
              )}
            </CardContent>
          </Card>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {users.data.map((user) => (
              <Card key={user.id} className="hover:shadow-lg transition-shadow">
                <CardContent className="p-5">
                  <div className="flex items-start justify-between mb-4">
                    <div className="flex items-center gap-3">
                      <div className="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-primary/20 to-primary/10 text-lg font-semibold text-primary">
                        {getInitials(user.name)}
                      </div>
                      <div className="flex-1 min-w-0">
                        <h3 className="font-semibold text-base truncate">{user.name}</h3>
                        <p className="text-sm text-muted-foreground truncate">{user.email}</p>
                      </div>
                    </div>
                  </div>

                  {/* Roles */}
                  {user.roles.length > 0 && (
                    <div className="mb-3 flex flex-wrap gap-1">
                      {user.roles.map((role) => (
                        <Badge key={role.id} variant="secondary" className="text-xs">
                          {role.name}
                        </Badge>
                      ))}
                    </div>
                  )}

                  {/* RDASH Status */}
                  <div className="mb-3 flex flex-wrap gap-2 items-center">
                    <RdashSyncStatusBadge status={user.rdash_sync_status} />
                    {user.rdash_customer_id && (
                      <Badge variant="outline" className="text-xs font-mono">
                        ID: {user.rdash_customer_id}
                      </Badge>
                    )}
                  </div>

                  {/* Metadata */}
                  <div className="text-xs text-muted-foreground mb-4">
                    Registered {dayjs(user.created_at).fromNow()}
                  </div>

                  {/* Actions */}
                  <div className="flex flex-wrap gap-2 pt-4 border-t">
                    {user.rdash_sync_status && (
                      <SyncToRdashButton
                        userId={user.id}
                        status={user.rdash_sync_status}
                      />
                    )}
                    <Link href={`/users/${user.id}`} className="flex-1">
                      <Button size="sm" variant="ghost" className="w-full">
                        View
                      </Button>
                    </Link>
                    <Link href={`/users/${user.id}/edit`} className="flex-1">
                      <Button size="sm" variant="outline" className="w-full">
                        Edit
                      </Button>
                    </Link>

                    <AlertDialog>
                      <AlertDialogTrigger asChild>
                        <Button size="sm" variant="secondary">Reset</Button>
                      </AlertDialogTrigger>
                      <AlertDialogContent>
                        <AlertDialogHeader>
                          <AlertDialogTitle>Reset Password?</AlertDialogTitle>
                          <AlertDialogDescription>
                            Password for <strong>{user.name}</strong> will be reset to:
                            <br />
                            <code className="bg-muted rounded px-2 py-1 text-sm">ResetPasswordNya</code>
                          </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                          <AlertDialogCancel>Cancel</AlertDialogCancel>
                          <AlertDialogAction
                            onClick={() => handleResetPassword(user.id)}
                            disabled={processing}
                          >
                            Yes, Reset
                          </AlertDialogAction>
                        </AlertDialogFooter>
                      </AlertDialogContent>
                    </AlertDialog>

                    <AlertDialog>
                      <AlertDialogTrigger asChild>
                        <Button size="sm" variant="destructive">Delete</Button>
                      </AlertDialogTrigger>
                      <AlertDialogContent>
                        <AlertDialogHeader>
                          <AlertDialogTitle>Delete User?</AlertDialogTitle>
                          <AlertDialogDescription>
                            User <strong>{user.name}</strong> will be permanently deleted.
                          </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                          <AlertDialogCancel>Cancel</AlertDialogCancel>
                          <AlertDialogAction
                            onClick={() => handleDelete(user.id)}
                            disabled={processing}
                          >
                            Yes, Delete
                          </AlertDialogAction>
                        </AlertDialogFooter>
                      </AlertDialogContent>
                    </AlertDialog>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}

        {/* Pagination */}
        {users.links && users.links.length > 3 && (
          <Card>
            <CardContent className="p-4">
              <div className="flex flex-col md:flex-row items-center justify-between gap-4">
                <div className="text-sm text-muted-foreground">
                  Showing {((users.current_page - 1) * users.per_page) + 1} to{' '}
                  {Math.min(users.current_page * users.per_page, users.total)} of {users.total} users
                </div>
                <div className="flex flex-wrap gap-2 justify-center">
                  {users.links.map((link, index) => (
                    <Link
                      key={index}
                      href={link.url || '#'}
                      className={`px-3 py-2 rounded-md text-sm font-medium transition-colors ${
                        link.active
                          ? 'bg-primary text-primary-foreground'
                          : 'bg-background hover:bg-muted text-foreground'
                      } ${!link.url ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''}`}
                      dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                  ))}
                </div>
              </div>
            </CardContent>
          </Card>
        )}
      </div>
    </AppLayout>
  );
}
