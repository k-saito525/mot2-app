<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>ユーザーIDの設定が完了しました</title>
    @include('components.head')
</head>

<body class="p-top">
    <div class="l-container">

        <!-- l-header START -->
        @include('components.l-header-top')
        <!-- l-header END -->

        <div class="l-contents">
            <main class="l-main">
                <section class="p-sub__section">
                    <h1 class="p-sub__head01">ユーザーIDの設定が完了しました。</h1>
                    <div class="p-sub__lead">
                        <p>ユーザーIDの設定が完了しました。以下よりログインできます。</p>
                    </div>
                    @if($errors->any())
                    <div class="form-error">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li class="error-text">・{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <div class="p-top-btn">
                        <a href="{{ route('login.show.form') }}" class="c-button--large">
                            <img src="{{ ('/img/common/icon-enter-white.svg') }}" alt="">
                            <span>{{ config('app.name') }}にログインする</span>
                        </a>
                    </div>
                </section>
            </main>
            <!-- l-footer START -->
            @include('components.l-footer-top')
            <!-- l-footer END -->
        </div>
    </div>
    @include('components.javascript')
</body>

</html>