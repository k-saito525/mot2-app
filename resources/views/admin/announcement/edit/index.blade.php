<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>お知らせの編集</title>
    @include('components.head')

</head>

<body class="is-subpage">
    <div class="l-container">

        @include('components.l-header')

        <div class="l-contents">
            <main class="l-main">
                <section class="p-sub__section">
                    @include('components.admin.nav')
                    <h1 class="p-sub__head01">お知らせの編集</h1>
                    @include('components.form-errors')
                    @include('components.flash-messages')
                    <div class="p-sub__inner">
                        <form action="{{ route('admin.announcement.update', ['id' => data_get($announcement, 'id')]) }}" method="POST" class="c-form">
                            @csrf
                            @method('PUT')
                            <div class="c-form-item">
                                <div class="c-form-item-row">
                                    <div class="c-form-item-row__col">
                                        <label for="pub-start" class="c-form-item-title">公開開始日</label>
                                        <input type="date" name="pub-start" id="pub-start" value="{{ data_get($announcement, 'pub_start_at') }}" min="{{ now()->format('Y') . '-01-01' }}" max="{{ (now()->year + 5) . '-12-31' }}" required>
                                        @error('pub-start')
                                        <span class="error-text">※{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="c-form-item-row__col">
                                        <label for="pub-end" class="c-form-item-title">公開終了日</label>
                                        <input type="date" name="pub-end" id="pub-end" value="{{ data_get($announcement, 'pub_end_at') }}" min="{{ now()->format('Y') . '-01-01' }}" max="{{ (now()->year + 5) . '-12-31' }}">
                                        @error('pub-end')
                                        <span class="error-text">※{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                @if(session('pub-start'))
                                <p class="error-text">※{{ session('pub-start') }}</p>
                                @endif
                            </div>
                            <div class="c-form-item">
                                <label for="announcement-title" class="c-form-item-title">お知らせのタイトル</label>
                                <input type="text" name="announcement-title" id="announcement-title" value="{{ data_get($announcement, 'title') }}">
                                @error('announcement-title')
                                <p class="error-text">※{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="c-form-item">
                                <label for="announcement-detail" class="c-form-item-title">お知らせの本文</label>
                                <textarea name="announcement-detail" id="announcement-detail" cols="30" rows="10">{{ data_get($announcement, 'content') }}</textarea>
                                @error('announcement-detail')
                                <p class="error-text">※{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="c-form-submit c-button-wrap">
                                <button type="submit" class="c-button">更新する</button>
                            </div>
                        </form>
                        <form action="{{ route('admin.announcement.destroy', ['id' => data_get($announcement, 'id')]) }}" method="POST" class="c-form c-admin-delete-form" onsubmit="return confirm('このお知らせを削除しますか？');">
                            @csrf
                            @method('DELETE')
                            <div class="c-form-submit c-button-wrap">
                                <button type="submit" class="c-button is-danger">削除する</button>
                            </div>
                        </form>
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