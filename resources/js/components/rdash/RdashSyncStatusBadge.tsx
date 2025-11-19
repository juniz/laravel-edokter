import React from 'react';
import { Badge } from '@/components/ui/badge';

interface RdashSyncStatusBadgeProps {
  status: 'pending' | 'synced' | 'failed' | null | undefined;
}

export function RdashSyncStatusBadge({ status }: RdashSyncStatusBadgeProps) {
  if (!status) {
    return null;
  }

  const variants: Record<string, 'default' | 'secondary' | 'destructive'> = {
    synced: 'default',
    pending: 'secondary',
    failed: 'destructive',
  };

  const labels: Record<string, string> = {
    synced: 'Synced',
    pending: 'Pending',
    failed: 'Failed',
  };

  return (
    <Badge variant={variants[status] || 'secondary'} className="text-xs">
      RDASH: {labels[status] || status}
    </Badge>
  );
}

