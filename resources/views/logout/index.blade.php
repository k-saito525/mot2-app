<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>ログアウトしました</title>
    @include('components.head')
</head>

<body class="p-top">
    <div class="l-container">

        @include('components.l-header-top')

        <div class="l-contents">
            <main class="l-main">
                <section class="p-sub__section">
                    <h1 class="p-sub__head01">ログアウト</h1>
                    <div class="p-sub__lead">
                        <p>ログアウトが完了しました。</p>
                    </div>
                    <div class="p-top-btn">
                        <a href="{{ route('top') }}" class="c-button--large">
                            <img src="/img/common/icon-earth.svg" alt="">
                            <span>トップページへ</span>
                        </a>
                    </div>
                </section>
            </main>
            @include('components.l-footer-top')
        </div>
    </div>
    @include('components.javascript')
</body>

</html>