import React from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { type BreadcrumbItem } from '@/types';
import { FileText } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
	{
		title: 'Log Viewer',
		href: '/utilities/log-viewer',
	},
];

export default function LogViewerIndex() {
	const logViewerUrl = '/log-viewer';

	return (
		<AppLayout breadcrumbs={breadcrumbs}>
			<Head title="Log Viewer" />
			<div className="flex-1 p-4 md:p-6">
				<Card className="h-full flex flex-col">
					<CardHeader className="pb-3">
						<div className="flex items-center gap-3">
							<FileText className="w-6 h-6 text-primary" />
							<div>
								<CardTitle className="text-2xl font-bold">Log Viewer</CardTitle>
								<p className="text-muted-foreground text-sm">
									View and analyze Laravel application logs
								</p>
							</div>
						</div>
					</CardHeader>

					<Separator />

					<CardContent className="pt-6 flex-1 flex flex-col min-h-0">
						<div className="flex-1 border rounded-lg overflow-hidden bg-muted/30">
							<iframe
								src={logViewerUrl}
								className="w-full h-full min-h-[600px] border-0"
								title="Log Viewer"
								allowFullScreen
							/>
						</div>
					</CardContent>
				</Card>
			</div>
		</AppLayout>
	);
}
