<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>お知らせの編集</title>
    @include('components.head')

</head>

<body class="is-subpage">
    <div class="l-container">

        <!-- l-header START -->
        <!-- l-header END -->

        <div class="l-contents">
            <main class="l-main">
                <section class="p-sub__section">
                    <h1 class="p-sub__head01">お知らせの編集</h1>
                    <div class="p-sub__inner">
                        <form action="{{ route('admin.announcement.store') }}" method="POST" class="c-form">
                            @csrf
                            <input type="hidden" name="announcement_id" value="{{ data_get($announcement, 'id') }}">
                            <div class="c-form-item">
                                <?php
                                // 日付選択範囲
                                $min = date('Y') . '-01-01';
                                $max = date('Y') + 5 . '-12-31';
                                ?>
                                {{-- 公開開始日 --}}
                                <input type="date" name="pub-start" id="pub-start" value="{{ data_get($announcement, 'pub_start_at') }}" min="{{ $min }}" max="{{ $max }}" required>
                                @error('pub-start')
                                <span class="error-text">※{{ $message }}</span>
                                @enderror
                                {{-- 公開終了日 --}}
                                <input type="date" name="pub-end" id="pub-end" value="{{ data_get($announcement, 'pub_end_at') }}" min="{{ $min }}" max="{{ $max }}">
                                @error('pub-end')
                                <span class="error-text">※{{ $message }}</span>
                                @enderror
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
                                <button type="submit" class="c-button" name="delete" value="削除">削除する</button>
                            </div>
                        </form>
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