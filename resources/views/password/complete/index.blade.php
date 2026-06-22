<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <title>パスワードの再設定が完了しました</title>
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
          <h1 class="p-sub__head01">パスワードの再設定が完了しました</h1>
          <div class="p-sub__lead">
            <p>
              パスワードの再設定が完了しました。<br>
              以下よりログインしてください。
            </p>
          </div>
          <div class="p-top-btn">
            <a href="{{ route('login.show.form') }}" class="c-button--large">
              <img src="/img/common/icon-enter-white.svg" alt="">
              <span>ログインする</span>
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