<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>お知らせ一覧</title>
    @include('components.head')
    <style>
        .edit-announcement {
            font-weight: bold;
            margin-top: 10px;
            padding: 3px;
            background-color: #FFB000;
            border: solid 1px black;
            border-radius: 15px;
        }
    </style>
</head>

<body class="is-subpage">
    <div class="l-container">

        <div class="l-contents">
            <main class="l-main">
                <section class="p-sub__section">
                    <h1 class="p-sub__head01">お知らせ一覧</h1>
                    <a href="{{ route('admin.show.announcement.create') }}">★新規作成はこちら★</a>
                    <div class="p-sub__inner">
                        @forelse($announcement_list as $announcement)
                        <div class="c-announcement">
                            <p>【タイトル】<br>
                                {{ $announcement->title }}
                            </p>
                            <p>【本文】<br>
                                {!! nl2br(htmlspecialchars($announcement->content)) !!}
                            </p>
                            <p>【公開開始日】{{ $announcement->pub_start_at }}</p>
                            <p>【公開終了日】{{ $announcement->pub_end_at }}</p>
                            <p>【公開状況】{{ $announcement->pub_status }}</p>
                            <a href="{{ route('admin.show.announcement.edit', ['id' => $announcement->id]) }}" class="edit-announcement">編集する</a>
                            <p>---------------------------------------</p>
                        </div>
                        @empty
                        <p>現在表示できるお知らせはありません。</p>
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