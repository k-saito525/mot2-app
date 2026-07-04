@php
$admin_path = request()->path();
@endphp
<nav class="c-admin-nav">
    <a href="{{ route('admin.show.index') }}" @class(['c-admin-nav__item', 'is-current' => $admin_path === 'admin'])>管理画面TOP</a>
    <a href="{{ route('admin.show.unapproved.list') }}" @class(['c-admin-nav__item', 'is-current' => str_starts_with($admin_path, 'admin/user/unapproved')])>承認待ちユーザー</a>
    <a href="{{ route('admin.show.support.list') }}" @class(['c-admin-nav__item', 'is-current' => str_starts_with($admin_path, 'admin/support')])>メッセージ</a>
    <a href="{{ route('admin.show.announcement.list') }}" @class(['c-admin-nav__item', 'is-current' => str_starts_with($admin_path, 'admin/announcement')])>お知らせ</a>
</nav>
