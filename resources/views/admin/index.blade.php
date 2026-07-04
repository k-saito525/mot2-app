<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>管理画面TOP</title>
    @include('components.head')
</head>

<body class="is-subpage">
    <div class="l-container">

        @include('components.l-header')

        <div class="l-contents">
            <main class="l-main">
                <section class="p-sub__section">
                    @include('components.admin.nav')
                    <h1 class="p-sub__head01">管理画面TOP</h1>
                    <div class="p-sub__btns">
                        <a href="{{ route('admin.show.unapproved.list') }}" class="c-button--large">
                            <img src="{{ asset('/img/common/icon-users.svg') }}" alt="">
                            <span>承認待ちユーザー一覧</span>
                        </a>
                        <a href="{{ route('admin.show.support.list') }}" class="c-button--large">
                            <img src="{{ asset('/img/common/icon-reply.svg') }}" alt="">
                            <span>メッセージ一覧</span>
                        </a>
                        <a href="{{ route('admin.show.announcement.list') }}" class="c-button--large">
                            <img src="{{ asset('/img/common/icon-bell.svg') }}" alt="">
                            <span>お知らせ一覧</span>
                        </a>
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