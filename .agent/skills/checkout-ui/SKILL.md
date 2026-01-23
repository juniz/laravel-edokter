# Skill: Premium Hosting Checkout UI (Midtrans Snap)

## 1. Project Context
* **Domain:** Cloud Hosting / SaaS Platform.
* **Goal:** Build a high-conversion checkout page focused on trust and simplicity.
* **Aesthetic:** "Simple Premium" (Vercel/Railway style). High contrast, subtle borders, generous whitespace, and monochromatic colors (Zinc/Slate).
* **Payment Gateway:** **Midtrans Snap** (specifically for Bank Virtual Accounts).

## 2. Tech Stack & Library
* **Framework:** Inertia.js (Laravel).
* **Styling:** Tailwind CSS.
* **UI Library:** Shadcn UI.
* **Icons:** Lucide React.
* **Script:** Midtrans Snap.js (client-side).

## 3. Design Tokens
* **Backgrounds:** `bg-background` (White/Zinc-950).
* **Borders:** Subtle `border-zinc-200` (light) or `border-zinc-800` (dark).
* **Radius:** `rounded-xl` for a modern, approachable feel.
* **Typography:** Inter or Geist Sans. Tight tracking (`tracking-tight`) for headers.
* **Shadows:** `shadow-sm` for cards, `shadow-lg` for the sticky order summary.
* **Accent:** Use a subtle primary color for the "Selected" state of the payment card (e.g., `ring-2 ring-primary bg-primary/5`).

## 4. UI Layout Strategy
**Desktop (lg):**
* **Grid:** 12 columns.
* **Left (Span 8):** Customer Information & Payment Method.
* **Right (Span 4):** Sticky Order Summary.

**Mobile:**
* **Stack:** Vertical layout. Order summary moves to the bottom or inside a collapsible drawer.

## 5. Component Specifications

### A. Order Summary (Sticky Sidebar)
* **Component:** `Card`.
* **Content:**
    * **Plan Name:** e.g., "Cloud VPS - Pro".
    * **Specs:** Small text listing CPU/RAM.
    * **Line Items:** Subtotal, PPN (11%), Total.
    * **Total:** Display prominently in `text-2xl font-bold` with IDR currency (e.g., `Rp 150.000`).
* **Trust:** Add a small "Secure Encrpytion" note with a `ShieldCheck` icon.

### B. Customer Information (Left Column)
* **Component:** `Card`.
* **Inputs:** `Label` + `Input` for Name, Email, WhatsApp Number (common for Indo hosting).
* **Styling:** Clean, standard Shadcn inputs.

### C. Payment Method (Midtrans Focus)
* **Concept:** Since Snap handles the specific bank selection in a modal, the UI should show a **single, reassuring "Virtual Account" card**.
* **Visuals:**
    * A large selectable div styled like a card.
    * **Header:** "Bank Virtual Account".
    * **Subtext:** "Automatic verification. Activates instantly."
    * **Logos:** Display grayscale logos for BCA, Mandiri, BNI, BRI, Permata (turning colored or high-contrast to show support).
* **State:** Pre-selected (active ring) since it is the only/primary method.

### D. The "Pay" Button
* **Logic:** The button **does not** submit a form directly. It triggers the `snap.pay()` popup.
* **State:** Must have a `disabled` and `loading` (spinner) state while fetching the Snap Token from the backend.

## 6. Implementation Blueprint (TSX)

Use the following structure as a reference for generation:

```tsx
import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Separator } from "@/components/ui/separator";
import { Badge } from "@/components/ui/badge";
import { Check, ShieldCheck, Landmark, Loader2 } from "lucide-react";

export default function CheckoutPage() {
  const [loading, setLoading] = useState(false);

  const handlePayment = async () => {
    setLoading(true);
    // 1. Call API to get Snap Token
    // 2. window.snap.pay(token)
    // 3. Handle success/error
    setTimeout(() => setLoading(false), 2000); // Mock
  };

  return (
    <div className="container max-w-6xl py-10">
      <h1 className="text-3xl font-bold tracking-tight mb-8">Checkout</h1>
      
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {/* LEFT COLUMN */}
        <div className="lg:col-span-8 space-y-6">
          
          {/* 1. Account Details */}
          <Card className="shadow-sm border-zinc-200/60">
            <CardHeader>
              <CardTitle className="text-lg">Account Information</CardTitle>
            </CardHeader>
            <CardContent className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label>Full Name</Label>
                <Input placeholder="John Doe" />
              </div>
              <div className="space-y-2">
                <Label>Email Address</Label>
                <Input type="email" placeholder="john@example.com" />
              </div>
            </CardContent>
          </Card>

          {/* 2. Payment Method (Midtrans Visual) */}
          <div className="space-y-3">
            <h3 className="text-lg font-semibold tracking-tight px-1">Payment Method</h3>
            <div className="relative rounded-xl border-2 border-primary bg-primary/5 p-6 shadow-sm transition-all cursor-default">
              <div className="flex items-start justify-between">
                <div className="space-y-1">
                  <div className="flex items-center gap-2">
                    <Landmark className="h-5 w-5 text-primary" />
                    <span className="font-semibold text-foreground">Bank Virtual Account</span>
                  </div>
                  <p className="text-sm text-muted-foreground max-w-md">
                    Pay securely via BCA, Mandiri, BNI, BRI, or Permata. 
                    Your service activates automatically after payment.
                  </p>
                </div>
                <div className="flex h-6 w-6 items-center justify-center rounded-full bg-primary text-primary-foreground">
                  <Check className="h-4 w-4" />
                </div>
              </div>
              
              {/* Trust/Bank Logos Visual */}
              <div className="mt-4 flex gap-2 overflow-hidden opacity-70">
                {['BCA', 'MANDIRI', 'BNI', 'BRI', 'PERMATA'].map((bank) => (
                  <div key={bank} className="px-3 py-1 rounded bg-white border text-[10px] font-bold text-zinc-600 shadow-sm">
                    {bank}
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>

        {/* RIGHT COLUMN: Sticky Summary */}
        <div className="lg:col-span-4">
          <div className="sticky top-10">
            <Card className="shadow-lg border-zinc-200 bg-zinc-50/50 backdrop-blur-xl">
              <CardHeader className="pb-4 border-b border-zinc-100">
                <div className="flex justify-between items-center">
                  <CardTitle className="text-base">Order Summary</CardTitle>
                  <Badge variant="outline" className="bg-white">Monthly</Badge>
                </div>
              </CardHeader>
              <CardContent className="space-y-4 pt-6 text-sm">
                <div className="flex justify-between">
                  <span className="text-muted-foreground">Pro Cloud VPS</span>
                  <span className="font-medium">Rp 150.000</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-muted-foreground">Admin Fee</span>
                  <span className="font-medium">Rp 2.500</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-muted-foreground">PPN (11%)</span>
                  <span className="font-medium">Rp 16.775</span>
                </div>
                <Separator />
                <div className="flex justify-between items-center">
                  <span className="font-bold text-lg">Total</span>
                  <span className="font-bold text-xl text-primary">Rp 169.275</span>
                </div>
              </CardContent>
              <CardFooter className="flex-col gap-3 pt-2">
                <Button 
                  size="lg" 
                  className="w-full font-semibold text-md shadow-md" 
                  onClick={handlePayment}
                  disabled={loading}
                >
                  {loading ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : "Pay Now"}
                </Button>
                <div className="text-center text-xs text-muted-foreground flex items-center justify-center gap-1.5">
                  <ShieldCheck className="h-3.5 w-3.5 text-green-600" />
                  <span>Secured by Midtrans</span>
                </div>
              </CardFooter>
            </Card>
          </div>
        </div>

      </div>
    </div>
  );
}