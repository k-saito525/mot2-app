<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <title>パスワードの再設定</title>
  @include('components.head')
</head>

<body class="p-top">
  <div class="l-container">

    @include('components.l-header-top')

    <div class="l-contents">
      <main class="l-main">
        <section class="p-sub__section">
          <h1 class="p-sub__head01">パスワードの再設定</h1>
          @include('components.form-errors')
          @include('components.flash-messages')
          <form action="{{ route('password.reset.store') }}" method="POST" class="c-form">
            @csrf
            <div class="p-sub__lead">
              <p>
                新しいパスワードを設定してください。
              </p>
            </div>
            <div class="c-form-item">
              <label for="password" class="c-form-item-title">新しいパスワード</label>
              <input type="password" name="password" id="password">
              @error('password')
              <p class="error-text">※{{ $message }}</p>
              @enderror
            </div>
            <div class="c-form-item">
              <label for="password_confirmation" class="c-form-item-title">パスワードを再度入力してください</label>
              <input type="password" name="password_confirmation" id="password_confirmation">
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