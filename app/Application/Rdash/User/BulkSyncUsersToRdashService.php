<?php

namespace App\Application\Rdash\User;

use App\Models\User;
use Illuminate\Support\Collection;

class BulkSyncUsersToRdashService
{
    public function __construct(
        private SyncUserToRdashService $syncUserToRdashService
    ) {
    }

    /**
     * Sync multiple users ke RDASH dalam batch
     * 
     * @param array<int>|Collection<int, User> $userIds
     * @return array{success: int, failed: int, results: array}
     */
    public function execute(array|Collection $userIds): array
    {
        $success = 0;
        $failed = 0;
        $results = [];

        if ($userIds instanceof Collection) {
            $users = $userIds;
        } else {
            $users = User::whereIn('id', $userIds)->get();
        }

        foreach ($users as $user) {
            $result = $this->syncUserToRdashService->execute($user, true);
            
            $results[] = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                ...$result,
            ];

            if ($result['success']) {
                $success++;
            } else {
                $failed++;
            }
        }

        return [
            'success_count' => $success,
            'failed_count' => $failed,
            'results' => $results,
        ];
    }
}

