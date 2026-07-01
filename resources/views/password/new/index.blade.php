<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <title>パスワードの設定</title>
  @include('components.head')
</head>

<body class="p-top">
  <div class="l-container">

    @include('components.l-header-top')

    <div class="l-contents">
      <main class="l-main">
        <section class="p-sub__section">
          <h1 class="p-sub__head01">パスワードの設定</h1>
          <div class="p-sub__lead">
            <p>
              {{ $user->name }}さん、MOT2へようこそ！
              まず、MOT2へログインするためのパスワードを設定してください。
            </p>
          </div>
          @include('components.form-errors')
          @include('components.flash-messages')
          <form action="{{ route('password.new.store') }}" method="POST" class="c-form">
            @csrf
            <div class="c-form-item">
              <label for="password" class="c-form-item-title">パスワード</label>
              <input type="password" name="password" id="password" required>
              @error('password')
              <p class="error-text">※{{ $message }}</p>
              @enderror
            </div>
            <div class="c-form-item">
              <label for="password_confirmation" class="c-form-item-title">パスワードを再度入力してください</label>
              <input type="password" name="password_confirmation" id="password_confirmation" required>
              @error('password_confirmation')
              <p class="error-text">※{{ $message }}</p>
              @enderror
            </div>
            <div class="c-form-submit c-button-wrap">
              <button type="submit" class="c-button">設定する</button>
            </div>
          </form>
        </section>
      </main>
      @include('components.l-footer-top')
    </div>
  </div>
  @include('components.javascript')
</body>

</html>