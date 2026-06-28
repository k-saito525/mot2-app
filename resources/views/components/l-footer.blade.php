<!--include START-->
<footer class="l-footer">
  <div class="l-footer__btn">
    <div class="l-footer__btn-item">
      <a href="{{ route('home.index') }}">
        <img src="{{ asset('/img/common/icon-earth.svg') }}" width="32" height="32" alt="">
        <span>HOME</span>
      </a>
    </div>
    <div class="l-footer__btn-item">
      <a href="{{ route('topic.show.list') }}">
        <img src="{{ asset('/img/common/icon-search.svg') }}" width="32" height="32" alt="">
        <span>トピック一覧</span>
      </a>
    </div>
    <div class="l-footer__btn-item">
      <a href="{{ route('user.show.list') }}">
        <img src="{{ asset('/img/common/icon-users.svg') }}" width="32" height="32" alt="">
        <span>メンバー</span>
      </a>
    </div>
    <div class="l-footer__btn-item">
      <a href="{{ route('user.show.edit', ['id' => auth()->id()]) }}">
        <span class="l-footer__user-icon">
          <x-user-icon :user="auth()->user()" />
        </span>
        <span>個人設定</span>
      </a>
    </div>
  </div>
</footer>
<!--include END-->