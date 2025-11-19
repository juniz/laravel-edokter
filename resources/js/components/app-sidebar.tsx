import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarMenuSub,
  SidebarMenuSubItem,
  SidebarMenuSubButton,
} from '@/components/ui/sidebar';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';

import { usePage, Link } from '@inertiajs/react';
import AppLogo from './app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavUser } from '@/components/nav-user';
import { iconMapper } from '@/lib/iconMapper';
import type { LucideIcon } from 'lucide-react';
import { ChevronDown, ChevronRight } from 'lucide-react';
import { cn } from '@/lib/utils';

interface MenuItem {
  id: number;
  title: string;
  route: string | null;
  icon: string;
  children?: MenuItem[];
}

function RenderMenu({ items, level = 0 }: { items: MenuItem[]; level?: number }) {
  const { url: currentUrl } = usePage();

  if (!Array.isArray(items)) return null;

  return (
    <>
      {items.map((menu) => {
        if (!menu) return null;
        const Icon = iconMapper(menu.icon || 'Folder') as LucideIcon;
        const children = Array.isArray(menu.children) ? menu.children.filter(Boolean) : [];
        const hasChildren = children.length > 0;
        const isActive = menu.route && currentUrl.startsWith(menu.route);
        
        // Check if any child is active
        const hasActiveChild = children.some((child) => child.route && currentUrl.startsWith(child.route));
        const shouldDefaultOpen = isActive || hasActiveChild;
        
        // For parent menu with active child, use slightly lighter background
        const activeClass = isActive
          ? 'bg-primary text-primary-foreground font-medium'
          : hasActiveChild
          ? 'bg-primary/10 text-primary font-medium'
          : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground';

        if (!menu.route && !hasChildren) return null;

        return (
          <SidebarMenuItem key={menu.id}>
            {hasChildren ? (
              <Collapsible defaultOpen={shouldDefaultOpen} className="group/collapsible">
                <CollapsibleTrigger asChild>
                  <SidebarMenuButton 
                    className={cn(
                      'group flex w-full items-center justify-between rounded-md transition-colors',
                      activeClass,
                      level === 0 ? 'py-3 px-4 my-1' : 'py-2 px-3'
                    )}
                  >
                    <div className="flex items-center">
                      <Icon className="size-4 mr-3 opacity-80 group-hover:opacity-100" />
                      <span>{menu.title}</span>
                    </div>
                    <ChevronDown className="size-4 opacity-50 group-hover:opacity-70 transition-transform duration-200 group-data-[state=open]/collapsible:rotate-180" />
                  </SidebarMenuButton>
                </CollapsibleTrigger>
                <CollapsibleContent>
                  <SidebarMenuSub>
                    {children.map((child) => {
                      const ChildIcon = iconMapper(child.icon || 'Folder') as LucideIcon;
                      const childIsActive = child.route && currentUrl.startsWith(child.route);
                      
                      return (
                        <SidebarMenuSubItem key={child.id}>
                          <SidebarMenuSubButton 
                            asChild 
                            isActive={childIsActive}
                            className={cn(
                              childIsActive 
                                ? 'bg-primary text-primary-foreground font-medium hover:bg-primary/90' 
                                : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                            )}
                          >
                            <Link href={child.route || '#'}>
                              <ChildIcon className="size-4" />
                              <span>{child.title}</span>
                            </Link>
                          </SidebarMenuSubButton>
                        </SidebarMenuSubItem>
                      );
                    })}
                  </SidebarMenuSub>
                </CollapsibleContent>
              </Collapsible>
            ) : (
              <SidebarMenuButton 
                asChild 
                isActive={isActive}
                className={cn(
                  'group flex items-center rounded-md transition-colors',
                  isActive && 'bg-primary text-primary-foreground font-medium',
                  level === 0 ? 'py-3 px-4 my-1' : 'py-2 px-3'
                )}
              >
                <Link href={menu.route || '#'}>
                  <Icon className="size-4 mr-3 opacity-80 group-hover:opacity-100" />
                  <span>{menu.title}</span>
                  {level > 0 && (
                    <ChevronRight className="ml-auto size-4 opacity-0 group-hover:opacity-50" />
                  )}
                </Link>
              </SidebarMenuButton>
            )}
          </SidebarMenuItem>
        );
      })}
    </>
  );
}

export function AppSidebar() {
  const { menus = [] } = usePage().props as { menus?: MenuItem[] };

  const footerNavItems = [
    {
      title: 'Star this Repo',
      url: 'https://github.com/yogijowo/laravel12-react-starterkit',
      icon: iconMapper('Star') as LucideIcon,
    },
    {
      title: 'Donate via Saweria',
      url: 'https://saweria.co/yogijowo',
      icon: iconMapper('Heart') as LucideIcon,
    },
    {
      title: 'Donate via Ko-fi',
      url: 'https://ko-fi.com/yogijowo',
      icon: iconMapper('Heart') as LucideIcon,
    },
  ];

  return (
    <Sidebar collapsible="icon" variant="inset" className="border-r bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
      <SidebarHeader className="px-4 py-3 border-b">
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton size="lg" asChild className="hover:bg-transparent">
              <Link href="/dashboard" prefetch>
                <AppLogo />
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>

      <SidebarContent className="px-2 py-4">
        <SidebarMenu>
          <RenderMenu items={menus} />
        </SidebarMenu>
      </SidebarContent>
      <SidebarFooter className="px-4 py-3 border-t">
        <NavUser  />
        <NavFooter items={footerNavItems} className="justify-center gap-4" />
      </SidebarFooter>
    </Sidebar>
  );
}