<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <!-- ↓「メールを送信しました」のほうがいいかも -->
  <title>パスワードのリセットが完了しました</title>
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
          <h1 class="p-sub__head01">パスワード再設定用のメール送信が完了しました</h1>
          <div class="p-sub__lead">
            <p>
              入力いただいたメールアドレスにパスワードリセット用のメールを送信しました。<br>
              メール内に記載されているリンクをクリックして、パスワードの再設定を行ってください。
            </p>
            <p>
              メールが届かない場合は、ご入力されたメールアドレスに誤りがあるか、該当のメールアドレスで登録がされていない可能性がございます。お手数ですが<a href="{{ route('apply.form') }}">こちら</a>よりユーザー登録の申請をし直してください。
            </p>
          </div>
          <div class="p-top-btn">
            <a href="{{ route('top') }}" class="c-button--large">
              <img src="/img/common/icon-enter-white.svg" alt="">
              <span>トップページへ戻る</span>
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