<?php

namespace App\Http\View\Composers;

use App\Models\Announcement;
use Illuminate\View\View;

class HeaderComposer
{
    public function compose(View $view): void
    {
        $user_id = auth()->id();

        $announcement_info = ['unread_count' => 0, 'announcement' => ''];
        if (!is_null($user_id)) {
            $announcement_info = (new Announcement())->getStatusRead($user_id);
        }

        $view->with('announcement_info', $announcement_info);
    }
}
