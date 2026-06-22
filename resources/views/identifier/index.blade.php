<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>ユーザーIDの設定</title>
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
                    <h1 class="p-sub__head01">ユーザーIDの設定</h1>
                    <div class="p-sub__lead">
                        <p>続いて、ユーザーIDの設定をしてください。</p>
                        <p>※半角英数アンダースコア(_)を使用して、8文字以上24文字以内で設定してください。</p>
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
                    @if(session('flash_failed'))
                    <div class="form-error">
                        <p class="error-text">{{ session('flash_failed') }}</p>
                    </div>
                    @endif
                    <form action="{{ route('identifier.store') }}" method="POST" class="c-form">
                        @csrf
                        <div class="c-form-item">
                            <label for="user_identifier" class="c-form-item-title">ユーザーID</label>
                            <input type="text" name="user_identifier" id="user_identifier" required>
                            @error('user_identifier')
                            <p class="error-text">※{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="c-form-submit c-button-wrap">
                            <button type="submit" class="c-button">設定する</button>
                        </div>
                    </form>
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