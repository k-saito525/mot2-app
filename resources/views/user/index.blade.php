<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <title>ユーザー一覧</title>
  @include('components.head')
</head>

<body class="is-subpage">
  <div class="l-container">

    <!-- l-header START -->
    @include('components.l-header')
    <!-- l-header END -->

    <div class="l-contents">
      <main class="l-main">
        <section class="p-sub__section">
          <h1 class="p-sub__head01">ユーザー一覧　(全{{ $total_cnt }}件)</h1>
          @if(empty($users))
          <p>現在表示できるユーザー情報はありません。</p>
          @else
          <div class="p-sub__inner">
            <div class="c-user__list">
              @foreach($users as $user)
              <div class="c-user has-button">
                <a href="{{ route('user.show.detail', ['id' => data_get($user, 'id')]) }}">
                  <div class="c-user-icon">
                    @if(!empty(data_get($user, 'user_icon')))
                    <img src="{{ asset('storage/'. data_get($user, 'user_icon')) }}" alt="">
                    @else
                    <img src="/img/common/dummy_icon.png" alt="">
                    @endif
                  </div>
                  <div class="c-user-info">
                    <div class="c-user-name">{{ data_get($user, 'name') }}</div>
                    <div class="c-user-id">@ {{ data_get($user, 'user_identifier', '') }}</div>
                  </div>
                  <div class="c-user-detail">
                    {!! nl2br(htmlspecialchars(data_get($user, 'introduction_text', ''))) !!}
                  </div>
                </a>
                <div class="c-user-follow-wrap">
                  <a class="c-user-follow not-follow" href="{{ route('user.show.detail', ['id' => data_get($user, 'id')]) }}">もっと見る</a>
                </div>
                <!-- <div class="c-user-follow-wrap">
                  <span class="c-user-follow follow">フォロー中</span>
                </div> -->
              </div>
              @endforeach
            </div>
            @endif
            <div class="c-pagenation">
              @if($page > 1)
              <a href="{{ route('user.show.list', ['page' => $page_previous]) }}" class="c-pagenation-item">＜ 前のページ</a>
              @endif
              @if(!empty($page_next))
              <a href="{{ route('user.show.list', ['page' => $page_next]) }}" class="c-pagenation-item">次のページ ＞</a>
              @endif
            </div>
          </div>
        </section>
      </main>
      <!-- l-footer START -->
      @include('components.l-footer')
      <!-- l-footer END -->
    </div>
  </div>
  @include('components.javascript')
</body>

</html>