<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>お知らせ一覧</title>
    @include('components.head')
</head>

<body class="is-subpage">
    <div class="l-container">

        @include('components.l-header')

        <div class="l-contents">
            <main class="l-main">
                <section class="p-sub__section">
                    @include('components.admin.nav')
                    <div class="p-sub__btns c-admin-create-btn">
                        <a href="{{ route('admin.show.announcement.create') }}" class="c-button--large">
                            <img src="{{ asset('/img/common/icon-bell.svg') }}" alt="">
                            <span>お知らせを新規作成する</span>
                        </a>
                    </div>
                    <h1 class="p-sub__head01">お知らせ一覧</h1>
                    @include('components.flash-messages')
                    <div class="p-sub__inner">
                        <div class="c-admin-list">
                            @forelse($announcement_list as $announcement)
                            @php
                            $badge_class = match ($announcement->pub_status) {
                                '公開中' => 'c-badge--active',
                                '公開前' => 'c-badge--pending',
                                default => 'c-badge--ended',
                            };
                            @endphp
                            <div class="c-admin-card">
                                <div class="c-admin-card__head">
                                    <span class="c-badge {{ $badge_class }}">{{ $announcement->pub_status }}</span>
                                    <span class="c-admin-card__period">
                                        公開期間：{{ $announcement->pub_start_at->format('Y/m/d') }} 〜 {{ $announcement->pub_end_at?->format('Y/m/d') ?? '期限なし' }}
                                    </span>
                                </div>
                                <p class="c-admin-card__title">{{ $announcement->title }}</p>
                                <p class="c-admin-card__body">{!! nl2br(htmlspecialchars($announcement->content)) !!}</p>
                                <div class="c-admin-card__actions">
                                    <a href="{{ route('admin.show.announcement.edit', ['id' => $announcement->id]) }}" class="c-button--mini">
                                        <img src="{{ asset('/img/common/icon-pencil.svg') }}" alt="">
                                        <span>編集する</span>
                                    </a>
                                    <form action="{{ route('admin.announcement.destroy', ['id' => $announcement->id]) }}" method="POST" onsubmit="return confirm('このお知らせを削除しますか？');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="c-button--mini is-danger">削除する</button>
                                    </form>
                                </div>
                            </div>
                            @empty
                            <p class="c-empty-message">現在表示できるお知らせはありません。</p>
                            @endforelse
                        </div>
                    </div>
                    @include('components.admin.footer')
                </section>
            </main>
            @include('components.l-footer-top')
        </div>
    </div>
    @include('components.javascript')
</body>

</html>