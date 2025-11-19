import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Loader2 } from 'lucide-react';

interface SyncToRdashButtonProps {
  userId: number;
  status?: 'pending' | 'synced' | 'failed' | null;
  syncNow?: boolean;
}

export function SyncToRdashButton({ userId, status, syncNow = false }: SyncToRdashButtonProps) {
  const [loading, setLoading] = useState(false);

  const handleSync = () => {
    setLoading(true);
    router.post(
      `/users/${userId}/sync-rdash`,
      { sync_now: syncNow },
      {
        preserveScroll: true,
        onFinish: () => setLoading(false),
      }
    );
  };

  const getButtonText = () => {
    if (status === 'failed') {
      return 'Retry Sync';
    }
    if (status === 'pending') {
      return 'Sync Now';
    }
    return 'Sync to RDASH';
  };

  return (
    <Button
      size="sm"
      variant={status === 'failed' ? 'destructive' : 'outline'}
      onClick={handleSync}
      disabled={loading}
    >
      {loading && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
      {getButtonText()}
    </Button>
  );
}

