<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>承認待ちユーザー一覧</title>
    @include('components.head')
</head>

<body class="is-subpage">
    <div class="l-container">

        <div class="l-contents">
            <main class="l-main">
                <section class="p-sub__section">
                    <h1 class="p-sub__head01">承認待ちユーザー一覧</h1>
                    <div class="p-sub__inner">
                        @forelse($users as $user)
                        <div class="c-user">
                            <div class="c-user-icon">
                                <img src="{{ ('/img/common/dummy_icon.png') }}" alt="">
                            </div>
                            <div class="c-user-info">
                                <div class="c-user-name">{{ $user->name }}</div>
                                <!-- <div class="c-user-id">@username</div> -->
                            </div>
                            <div>
                                <button type="submit" onclick="location.href='{{ route('admin.show.detail', ['id' => $user->id]) }}'">確認する</button>
                            </div>
                        </div>
                        @empty
                        <p>現在承認待ちのユーザーはおりません。</p>
                        @endforelse
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