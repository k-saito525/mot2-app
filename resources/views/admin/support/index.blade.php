<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>メッセージ一覧</title>
    @include('components.head')
</head>

<body class="is-subpage">
    <div class="l-container">

        @include('components.l-header')

        <div class="l-contents">
            <main class="l-main">
                <section class="p-sub__section">
                    @include('components.admin.nav')
                    <h1 class="p-sub__head01">メッセージ一覧</h1>
                    <div class="p-sub__inner">
                        <div class="c-admin-list">
                            @forelse($messages as $message)
                            <div class="c-admin-card">
                                <div class="c-admin-card__head">
                                    <span class="c-admin-card__period">{{ data_get($message, 'created_at') }}</span>
                                </div>
                                <p class="c-admin-card__title">{{ data_get($message, 'user.name') }} さんからのメッセージ</p>
                                <p class="c-admin-card__body">{!! nl2br(htmlspecialchars(data_get($message, 'message'))) !!}</p>
                            </div>
                            @empty
                            <p class="c-empty-message">現在表示できるメッセージはありません。</p>
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