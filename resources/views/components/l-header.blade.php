<!--include START-->
<header class="l-header">
  <div class="l-header__logo">
    <a href="{{ route('home.index') }}">
      <img src="{{ asset('/img/common/mot2_simple_logo.svg') }}" alt="">
    </a>
  </div>
  <div class="l-header__btn">
    <div class="l-header__btn-item">
      <button type="button" class="l-header__info-btn">
        <img src="{{ asset('/img/common/icon-bell.svg') }}" alt="">
        <span>お知らせ</span>
        @if($announcement_info['unread_count'] > 0)
        {{-- 未読が1件以上ある場合のみ未読数のバッジを表示 --}}
        <span class="attention-num">{{ data_get($announcement_info, 'unread_count') }}</span>
        @endif
      </button>
      <div class="l-header__info">
        <div class="l-header__info-list">
          @if(!empty($announcement_info['announcement']))
          @foreach(data_get($announcement_info, 'announcement', []) as $val)
          <div @class(['l-header__info-list-item', 'unread' => !isset($val->pub_status)])>
            <a href="{{ route('show.detail.announcement', ['id' => data_get($val, 'id')]) }}">{{ data_get($val, 'title') }}</a>
          </div>
          @endforeach
          @else
          <div class="l-header__info-list-item unread">
            ※現在表示できるお知らせはありません。
          </div>
          @endif
        </div>
      </div>
    </div>
    <form action="{{ route('logout') }}" method="POST" name="a_form_logout">
      @csrf
      <div class="l-header__btn-item">
        <a href="#" onclick="document.a_form_logout.submit();">
          <img src="{{ asset('/img/common/icon-exit.svg') }}" alt="">
          <span>ログアウト</span>
        </a>
      </div>
    </form>
  </div>
</header>
<!--include END-->