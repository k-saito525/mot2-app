<?php
// ユーザーID取得
$user_id = Auth::id();
// お知らせ取得（未ログイン時はスキップ）
$announcement_info = ['unread_count' => 0, 'announcement' => ''];
if (!is_null($user_id)) {
    $m_announcement = new App\Models\Announcement();
    $announcement_info = $m_announcement->getStatusRead($user_id);
}

?>
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
          @foreach(data_get($announcement_info, 'announcement', []) as $key => $val)
          <?php
          // 未読の場合のclass属性
          $unread = '';
          if (!isset($val->pub_status)) {
            $unread = 'unread';
          }
          ?>
          <div class="l-header__info-list-item {{ $unread }}">
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