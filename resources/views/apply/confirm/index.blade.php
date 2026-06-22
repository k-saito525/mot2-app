<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <title>ユーザー登録の申請（確認）</title>
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
          <h1 class="p-sub__head01">ユーザー登録の申請（確認）</h1>
          <div class="p-sub__lead">
            <p>
              申請内容にお間違いがないか、ご確認ください。
            </p>
          </div>
          <form action="{{ route('apply.store') }}" method="POST" class="c-form">
            @csrf
            <div class="c-form-item c-form-item--register">
              <p class="c-form-item-title">お名前<span class="c-form-require">必須</span></p>
              <p>{{ Arr::get($form_input, 'name', '') }}</p>
            </div>
            <div class="c-form-item c-form-item--register">
              <p class="c-form-item-title">メールアドレス<span class="c-form-require">必須</span></p>
              <p>{{ Arr::get($form_input, 'email', '') }}</p>
              <span class="c-form-note">※ユーザー登録後、このメールアドレスをログイン時に使用します。</span>
            </div>
            <div class="c-form-item c-form-item--register">
              <p class="c-form-item-title">過去に参加された多文化交流</p>
              @if(!empty($form_input['past-join']))
              @foreach($form_input['past-join'] as $key => $val)
              <p>・{{ $val }}</p>
              @endforeach
              @else
              <p>未選択</p>
              @endif
            </div>
            <div class="c-form-submit c-button-wrap">
              <button type="submit" class="c-button">申請する</button>
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