<?php

namespace App\Console\Commands;

use App\Models\Menu;
use Illuminate\Console\Command;

class CleanDuplicateMenus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'menus:clean-duplicates {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicate menu entries based on route and parent_id';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        $deletedCount = 0;
        $keptCount = 0;

        // Get all menus grouped by route and parent_id
        $menus = Menu::orderBy('created_at')->get();

        // Group menus by identifier (route + parent_id)
        $groups = [];

        foreach ($menus as $menu) {
            // For parent menus (route = '#'), use route + title as identifier
            // For child menus, use route + parent_id as identifier
            if ($menu->route === '#') {
                $key = "{$menu->route}|{$menu->title}|{$menu->parent_id}";
            } else {
                $key = "{$menu->route}|{$menu->parent_id}";
            }

            if (! isset($groups[$key])) {
                $groups[$key] = [];
            }

            $groups[$key][] = $menu;
        }

        // Collect IDs of menus to delete
        $idsToDelete = [];

        // Process each group
        foreach ($groups as $key => $groupMenus) {
            if (count($groupMenus) > 1) {
                // Keep the first one (oldest), mark the rest for deletion
                $keep = $groupMenus[0];
                $duplicates = array_slice($groupMenus, 1);

                $keptCount++;

                $this->info("📌 Keeping menu: {$keep->title} (ID: {$keep->id}, Route: {$keep->route})");

                foreach ($duplicates as $duplicate) {
                    $deletedCount++;
                    $idsToDelete[] = $duplicate->id;

                    if ($dryRun) {
                        $this->warn("  ⚠️  Would delete: {$duplicate->title} (ID: {$duplicate->id}, Route: {$duplicate->route}, Created: {$duplicate->created_at})");
                    } else {
                        $this->warn("  🗑️  Deleting: {$duplicate->title} (ID: {$duplicate->id}, Route: {$duplicate->route})");
                    }
                }

                $this->newLine();
            }
        }

        // Also find and delete child menus of deleted parents
        if (! empty($idsToDelete) && ! $dryRun) {
            $childMenus = Menu::whereIn('parent_id', $idsToDelete)->get();

            foreach ($childMenus as $child) {
                $this->warn("  🗑️  Deleting child menu: {$child->title} (ID: {$child->id}, Parent ID: {$child->parent_id})");
                $child->delete();
                $deletedCount++;
            }
        }

        // Actually delete the duplicate menus
        if (! empty($idsToDelete) && ! $dryRun) {
            Menu::whereIn('id', $idsToDelete)->delete();
        }

        // Summary
        $this->newLine();
        if ($dryRun) {
            $this->info('✅ Summary:');
            $this->info("   - Menus to keep: {$keptCount}");
            $this->info("   - Duplicates found: {$deletedCount}");
            $this->warn('   Run without --dry-run to actually delete duplicates');
        } else {
            $this->info('✅ Cleanup completed!');
            $this->info("   - Menus kept: {$keptCount}");
            $this->info("   - Duplicates deleted: {$deletedCount}");
        }

        return Command::SUCCESS;
    }
}
