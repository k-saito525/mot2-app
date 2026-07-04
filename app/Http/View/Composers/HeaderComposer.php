<?php

namespace App\Http\View\Composers;

use App\Services\AnnouncementService;
use Illuminate\View\View;

class HeaderComposer
{
    public function __construct(private readonly AnnouncementService $announcementService) {}

    public function compose(View $view): void
    {
        $userId = auth()->id();

        $announcementInfo = ['unread_count' => 0, 'announcement' => ''];
        if (!is_null($userId)) {
            $announcementInfo = $this->announcementService->getStatusRead($userId);
        }

        $view->with('announcement_info', $announcementInfo);
    }
}
