<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>承認待ちユーザー一覧</title>
    @include('components.head')
</head>

<body class="is-subpage">
    <div class="l-container">

        @include('components.l-header')

        <div class="l-contents">
            <main class="l-main">
                <section class="p-sub__section">
                    @include('components.admin.nav')
                    <h1 class="p-sub__head01">承認待ちユーザー一覧</h1>
                    <div class="p-sub__inner">
                        <div class="c-user__list">
                            @forelse($users as $user)
                            <div class="c-user has-button">
                                <a href="{{ route('admin.show.detail', ['id' => $user->id]) }}">
                                    <div class="c-user-icon">
                                        <x-user-icon :user="$user" />
                                    </div>
                                    <div class="c-user-info">
                                        <div class="c-user-name">{{ $user->name }}</div>
                                    </div>
                                </a>
                                <div class="c-user-follow-wrap">
                                    <a href="{{ route('admin.show.detail', ['id' => $user->id]) }}" class="c-button--mini">確認する</a>
                                </div>
                            </div>
                            @empty
                            <p class="c-empty-message">現在承認待ちのユーザーはおりません。</p>
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