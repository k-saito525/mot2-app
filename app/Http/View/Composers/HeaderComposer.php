<?php

namespace App\Http\View\Composers;

use App\Services\AnnouncementService;
use Illuminate\View\View;

class HeaderComposer
{
    public function __construct(private readonly AnnouncementService $announcementService) {}

    public function compose(View $view): void
    {
        $user_id = auth()->id();

        $announcement_info = ['unread_count' => 0, 'announcement' => ''];
        if (!is_null($user_id)) {
            $announcement_info = $this->announcementService->getStatusRead($user_id);
        }

        $view->with('announcement_info', $announcement_info);
    }
}
