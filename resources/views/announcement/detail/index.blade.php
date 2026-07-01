<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>お知らせの詳細</title>
    @include('components.head')
</head>

<body class="is-subpage">
    <div class="l-container">

        @include('components.l-header')

        <div class="l-contents">
            <main class="l-main">
                <section class="p-sub__section">
                    <h1 class="p-sub__head01">お知らせの詳細</h1>
                    <div class="p-sub__inner">
                        <div class="c-announcement-detail">
                            <p>【お知らせのタイトル】</p>
                            <p>{{ data_get($announcement, 'title') }}</p>
                            <p>【お知らせの本文】</p>
                            <p>{!! nl2br(htmlspecialchars(data_get($announcement, 'content'))) !!}</p>
                        </div>
                    </div>
                </section>
            </main>
            @include('components.l-footer')
        </div>
    </div>
    @include('components.javascript')
</body>

</html>