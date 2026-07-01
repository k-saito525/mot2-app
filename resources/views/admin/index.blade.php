<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>管理画面TOP</title>
    @include('components.head')
    {{-- 暫定対応のためスタイル直書き --}}
    <style>
        .p-sub__inner {
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>

<body class="is-subpage">
    <div class="l-container">

        <div class="l-contents">
            <main class="l-main">
                <section class="p-sub__section">
                    <h1 class="p-sub__head01">管理画面TOP</h1>
                    <div class="p-sub__inner ">
                        <p>
                            <a href="{{ route('admin.show.unapproved.list') }}">▪️承認待ちユーザー一覧</a>
                        </p>
                    </div>
                    <div class="p-sub__inner">
                        <p>
                            <a href="{{ route('admin.show.support.list') }}">▪️メッセージ一覧</a>
                        </p>
                    </div>
                    <div class="p-sub__inner">
                        <p>
                            <a href="{{ route('admin.show.announcement.list') }}">▪️お知らせ一覧</a>
                        </p>
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