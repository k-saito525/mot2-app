<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <title>{{ config('app.name') }} ー 繋がりを、もっともっと。</title>
  @include('components.head')
</head>

<body class="p-top">
  <div class="l-container">

    <!-- l-header START -->
    @include('components.l-header-top')
    <!-- l-header END -->

    <div class="l-contents">
      <main class="l-main">
        <section>
          <div class="p-top-mv">
            <h1 class="p-top-mv-logo">
              <img src="{{ ('/img/common/mot2_logo.png') }}" width="330" height="332" alt="mot2ロゴ">
            </h1>
            <h2 class="p-top-mv-text">
              繋がりを、<br>もっともっと。
            </h2>
          </div>
          <div class="p-top-lead">
            <p>
              国際比較文化研究所が主催する多文化交流では、今までたくさんの国内外の人々が国境という垣根を超えて「友人」「仲間」として出会いを果たしてきました。
            </p>
            <p>
              <strong>多文化交流を通して得た絆はとても強い。</strong><br>
              それは、多文化交流に参加したことがある方はきっとわかってくださると思います。
            </p>
            <p>
              しかし、<br>
              「あいつ、今何しているんだろう？」<br>
              「元気にしているのかな？」<br>
              疎遠になってしまっている人が少なからずいるのも事実かと思います。
            </p>
            <p>
              Webという場を通して、<strong>住む場所が離れていても、またあの時のようにコミュニケーションが取れないだろうか？</strong><br>
              それがこの{{ config('app.name') }}が生まれた背景です。
            </p>
            <p>
              {{ config('app.name') }}を通して、多文化交流で生まれた絆が、もっともっと大きくなることを、私たちは願ってやみません。
            </p>
          </div>
          <div class="p-top-btn">
            <a href="{{ route('login.show.form') }}" class="c-button--large">
              <img src="{{ ('/img/common/icon-enter-white.svg') }}" alt="">
              <span>{{ config('app.name') }}にログインする</span>
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