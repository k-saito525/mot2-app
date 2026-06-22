<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <title>ログイン</title>
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
          <h1 class="p-sub__head01">ログイン</h1>
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
          {{-- ユーザーIDが登録だった場合、フラッシュメッセージを表示する --}}
          <div class="form-error">
            <p class="error-text">{{ session('flash_failed') }}</p>
          </div>
          @endif
          @if(session('complete_regist'))
          <div class="form-error">
            <p class="error-text">{{ session('complete_regist') }}</p>
          </div>
          @endif
          <form action="{{ route('login') }}" method='POST' class="c-form">
            @csrf
            <div class="c-form-item">
              <label for="email" class="c-form-item-title">登録メールアドレス</label>
              <input type="email" name="email" id="email" value="{{ old('email') }}" required>
              @error('email')
              <p class="error-text">※{{ $message }}</p>
              @enderror
            </div>
            <div class="c-form-item">
              <label for="password" class="c-form-item-title">パスワード</label>
              <input type="password" name="password" id="password" required>
              @error('password')
              <p class="error-text">※{{ $message }}</p>
              @enderror
            </div>
            <div class="c-form-submit c-button-wrap">
              <button type="submit" class="c-button">ログイン</button>
            </div>
          </form>
        </section>
        <section class="p-sub__section">
          <h2 class="p-sub__head02">お困りですか？</h2>
          <div class="c-form-link">
            <a href="{{ route('password.reset.show.form-mail') }}">パスワードを忘れた方はこちら</a>
            <a href="{{ route('apply.form') }}">ユーザー登録の申請はこちら</a>
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