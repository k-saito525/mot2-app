<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <title>ユーザー登録の申請が完了しました</title>
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
          <h1 class="p-sub__head01">ユーザー登録の申請が完了しました</h1>
          <div class="p-sub__lead">
            <p>
              ユーザー登録の申請が完了しました。<br>
              自動返信メールが届いているかご確認ください。<br>
              運営が申請内容を確認でき次第、メールにてお知らせいたしますので、しばらくお待ちください。
            </p>
          </div>
          <div class="p-top-btn">
            <a href="{{ route('top') }}" class="c-button--large">
              <img src="/img/common/icon-earth.svg" alt="">
              <span>トップページへ</span>
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