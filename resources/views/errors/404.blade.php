<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <title>title</title>
  @include('components.head')
</head>

<body class="page">
  <!-- <div class="transitionBg is-show"></div> -->
  <div class="l-container">

    @include('components.l-header')

    <div class="l-contents">
      <main class="l-main">
        <section class="sub">
          <div class="topic-path">
            <div class="path top"><a href="{{ route('top') }}">トップページ</a></div>
            <div class="path current"><span>お探しのページが見つかりません</span></div>
          </div>
          <div class="subpage-head-wrap">
            <h2 class="subpage-title">お探しのページが見つかりません</h2>
          </div>
          <div class="subpage-wrap">
            <div class="notfound-wrap">
              <p class="text">大変申し訳ございませんが、お探しのページは削除されたか、名前が変更されている等、現在ご利用できない可能性がございます。</p>
              <div class="viewmore-btn"><a href="{{ route('top') }}">トップページへ戻る</a></div>
            </div>
          </div>
        </section>
      </main>
      @include('components.l-footer-top')
    </div>
  </div>
  @include('components.javascript')
</body>

</html>