import React from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { AlertCircle, CheckCircle2, Loader2, Search } from 'lucide-react';
import { Alert, AlertDescription } from '@/components/ui/alert';

interface Step1SearchProps {
  data: any;
  setData: (key: string, value: any) => void;
  availabilityCheck: {
    loading: boolean;
    available: boolean | null;
    error: string | null;
    connectionError: boolean;
    price?: number;
    currency?: string;
  };
  checkAvailability: (e: React.FormEvent) => void;
  onNext: () => void;
}

export default function Step1Search({
  data,
  setData,
  availabilityCheck,
  checkAvailability,
  onNext,
}: Step1SearchProps) {
  return (
    <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
      <Card>
        <CardContent className="p-6 space-y-6">
          <div className="space-y-2">
            <h2 className="text-xl font-semibold">Cari Domain Impianmu</h2>
            <p className="text-sm text-muted-foreground">
              Mulai dengan mencari nama domain yang ingin Anda daftarkan.
            </p>
          </div>

          <form onSubmit={checkAvailability} className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="domain_name">Nama Domain *</Label>
              <div className="flex gap-2">
                <div className="relative flex-1">
                  <Input
                    id="domain_name"
                    placeholder="contoh.com"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    className={
                      availabilityCheck.available === true
                        ? 'border-green-500 focus-visible:ring-green-500'
                        : availabilityCheck.available === false
                        ? 'border-red-500 focus-visible:ring-red-500'
                        : ''
                    }
                  />
                  {availabilityCheck.loading && (
                    <div className="absolute right-3 top-2.5">
                      <Loader2 className="h-5 w-5 animate-spin text-muted-foreground" />
                    </div>
                  )}
                </div>
                <Button
                  type="submit"
                  disabled={availabilityCheck.loading || !data.name}
                  variant="secondary"
                >
                  <Search className="h-4 w-4 mr-2" />
                  Cek Ketersediaan
                </Button>
              </div>
              <p className="text-xs text-muted-foreground">
                Masukkan nama domain yang ingin Anda daftarkan (contoh: contoh.com)
              </p>
            </div>
          </form>

          {/* Availability Status */}
          {availabilityCheck.available === true && (
            <div className="rounded-lg border border-green-200 bg-green-50 p-4">
              <div className="flex items-center gap-3">
                <CheckCircle2 className="h-5 w-5 text-green-600" />
                <div className="flex-1">
                  <h3 className="font-medium text-green-900">Domain Tersedia!</h3>
                  <p className="text-sm text-green-700">
                    Domain <strong>{data.name}</strong> tersedia untuk didaftarkan.
                  </p>
                </div>
                <Button onClick={onNext} className="bg-green-600 hover:bg-green-700">
                  Lanjut ke Konfigurasi
                </Button>
              </div>
            </div>
          )}

          {availabilityCheck.available === false && (
            <Alert variant="destructive">
              <AlertCircle className="h-4 w-4" />
              <AlertDescription>
                <strong>Maaf, domain tidak tersedia.</strong> Domain {data.name} sudah terdaftar
                atau tidak tersedia. Silakan coba nama domain lain.
              </AlertDescription>
            </Alert>
          )}

          {availabilityCheck.error && (
            <Alert variant="destructive">
              <AlertCircle className="h-4 w-4" />
              <AlertDescription>
                {availabilityCheck.error}
              </AlertDescription>
            </Alert>
          )}
          
          {availabilityCheck.connectionError && (
             <Alert className="border-yellow-200 bg-yellow-50 text-yellow-800">
              <AlertCircle className="h-4 w-4 text-yellow-600" />
              <AlertDescription>
                <strong>Masalah Koneksi:</strong> Gagal terhubung ke server pengecekan domain. Silakan coba lagi.
              </AlertDescription>
            </Alert>
          )}
        </CardContent>
      </Card>
      
      {/* Suggestions could act better here if needed */}
    </div>
  );
}
