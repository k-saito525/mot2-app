<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <title>HOME</title>
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
          <h1 class="p-sub__head01">HOME</h1>
          <div class="p-sub__btns">
            <a href="{{ route('topic.show.create') }}" class="c-button--large">
              <img src="{{ ('/img/common/icon-topic.svg') }}" alt="">
              <span>トピックを新規作成する</span>
            </a>
          </div>
        </section>
        <section class="p-sub__section">
          <h2 class="p-sub__head02">今盛り上がっているおすすめトピック</h2>
          <div class="c-topic-wrap">
            @if(empty($recc_topic))
            <p>現在表示できるトピックはありません。</p>
            @else
            <a href="{{ route('topic.show.detail', ['id' => data_get($recc_topic, 'id')]) }}" class="c-topic-title">{{ data_get($recc_topic, 'title') }}</a>
            <div class="p-sub__inner">
              <div class="c-user">
                <a href="{{ route('user.show.detail', ['id' => data_get($recc_topic, 'user_id')]) }}">
                  <div class="c-user-icon">
                    @if(!empty(data_get($recc_topic, 'user_icon')))
                    <img src="{{ asset('storage/'. data_get($recc_topic, 'user_icon')) }}" alt="">
                    @else
                    <img src="/img/common/dummy_icon.png" alt="">
                    @endif
                  </div>
                  <div class="c-user-info">
                    <div class="c-user-name">{{ data_get($recc_topic, 'name') }}</div>
                    <div class="c-user-id">@ {{ data_get($recc_topic, 'user_identifier') }}</div>
                  </div>
                </a>
              </div>
              <div class="c-topic-detail">
                <p>
                  {!! nl2br(data_get($recc_topic, 'content')) !!}
                </p>
                <time class="c-topic-date" datetime="{{ data_get($recc_topic, 'created_at') }}">{{ data_get($recc_topic, 'created_at') }}</time>
                @if(strtotime(data_get($recc_topic, 'created_at')) !== strtotime(data_get($recc_topic, 'updated_at')))
                <time class="c-topic-date" datetime="{{ data_get($recc_topic, 'updated_at') }}">（更新：{{ data_get($recc_topic, 'updated_at') }}）</time>
                @endif
              </div>
              <div class="c-button-wrap">
                <a href="{{ route('topic.show.create.comment', ['id' => data_get($recc_topic, 'id')]) }}/#comment" class="c-button">
                  <img src="{{ ('/img/common/icon-reply.svg') }}" alt="">
                  <span>このトピックに回答する</span>
                </a>
                {{-- 編集できるのは作成者のみ --}}
                @if(data_get($recc_topic, 'user_id') === Auth::id())
                <a href="{{ route('topic.show.edit', ['id' => data_get($recc_topic, 'id')]) }}" class="c-button">
                  <img src="{{ ('/img/common/icon-pencil.svg') }}" alt="">
                  <span>このトピックを編集する</span>
                </a>
                @endif
              </div>
              <div class="c-reply-wrap">
                @if(!empty($comment_recc_topics))
                @foreach($comment_recc_topics as $comment_recc_topic)
                <div class="c-reply c-reply--has-detail">
                  <div class="c-user-icon">
                    @if(!empty(data_get($comment_recc_topic, 'user_icon')))
                    <img src="{{ asset('storage/'. data_get($comment_recc_topic, 'user_icon')) }}" alt="">
                    @else
                    <img src="/img/common/dummy_icon.png" alt="">
                    @endif
                  </div>
                  <div class="c-user-info">
                    <div class="c-user-name">{{ data_get($comment_recc_topic, 'username') }}</div>
                    <div class="c-user-id">@ {{ data_get($comment_recc_topic, 'user_identifier') }}</div>
                  </div>
                  <div class="c-reply-detail">
                    <p>
                      {!! nl2br(data_get($comment_recc_topic, 'comment_formatted')) !!}
                    </p>
                    <time class="c-reply-date" datetime="{{ data_get($comment_recc_topic, 'created_at') }}">{{ data_get($comment_recc_topic, 'created_at') }}</time>
                    @if(strtotime(data_get($comment_recc_topic, 'created_at')) !== strtotime(data_get($comment_recc_topic, 'updated_at')))
                    <time class="c-reply-date" datetime="{{ data_get($comment_recc_topic, 'updated_at') }}">（更新：{{ data_get($comment_recc_topic, 'updated_at') }}）</time>
                    @endif
                  </div>
                </div>
                @endforeach
                @endif
              </div>
              <div class="c-button-wrap">
                <a href="{{ route('topic.show.detail', ['id' => data_get($recc_topic, 'id')]) }}" class="c-button">
                  <img src="{{ ('/img/common/icon-show-topic.svg') }}" alt="">
                  <span>このトピックを見る</span>
                </a>
              </div>
            </div>
            @endif
          </div>
        </section>
        {{-- 最新のトピックを5件表示 --}}
        <section class="p-sub__section">
          <h2 class="p-sub__head02">その他のトピック</h2>
          @forelse($topics as $topic)
          <div class="c-topic-wrap">
            <a href="{{ route('topic.show.detail', ['id' => data_get($topic, 'id')]) }}" class="c-topic-title js-accordion-topic">{{ $topic->title }}</a>
            <div class="p-sub__inner">
              <div class="c-user">
                <a href="{{ route('user.show.detail', ['id' => $topic->user_id]) }}">
                  <div class="c-user-icon">
                    @if(!empty(data_get($topic, 'user_icon')))
                    <img src="{{ asset('storage/'. data_get($topic, 'user_icon')) }}" alt="">
                    @else
                    <img src="/img/common/dummy_icon.png" alt="">
                    @endif
                  </div>
                  <div class="c-user-info">
                    <div class="c-user-name">{{ $topic->name }}</div>
                  </div>
                </a>
              </div>
              <div class="c-topic-detail">
                <p>{!! nl2br($topic->content_formatted) !!}</p>
                <time class="c-topic-date" datetime="{{ $topic->created_at }}">{{ $topic->created_at }}</time>
                <time class="c-topic-date" datetime="{{ $topic->updated_at }}">（更新：{{ $topic->updated_at }}）</time>
              </div>
              <div class="c-button-wrap">
                <a href="{{ route('topic.show.create.comment', ['id' => data_get($topic, 'id')]) }}/#comment" class="c-button">
                  <img src="{{ ('/img/common/icon-reply.svg') }}" alt="">
                  <span>このトピックに回答する</span>
                </a>
                <a href="{{ route('topic.show.detail', ['id' => $topic->id]) }}" class="c-button">
                  <img src="{{ ('/img/common/icon-show-topic.svg') }}" alt="">
                  <span>このトピックを見る</span>
                </a>
                @if($user_id === $topic->user_id)
                <a href="{{ route('topic.show.edit', ['id' => $topic->id]) }}" class="c-button">
                  <img src="{{ ('/img/common/icon-pencil.svg') }}" alt="">
                  <span>このトピックを編集する</span>
                </a>
                @endif
              </div>
            </div>
          </div>
          @empty
          <p>現在表示できるトピックはありません。</p>
          @endforelse
        </section>
        <section class="p-sub__section">
          <h2 class="p-sub__head02">運営へのメッセージはこちらから</h2>
          <div class="p-sub__inner">
            <div class="p-sub__lead">
              <p>
                MOT2を使っていただいてお気付きの点、改善してほしい点などございましたら以下より送信ください。<br>
                MOT2は無償のプロジェクトです。あなたからのご感想や応援のメッセージがとても励みになります！<br>
              </p>
              @if(session('flash_success'))
              <div class="flash-complete">
                <p class="flash-text">{{ session('flash_success') }}</p>
              </div>
              @endif
              @if(session('flash_failed'))
              <div class="form-error">
                <p class="error-text">{{ session('flash_failed') }}</p>
              </div>
              @endif
            </div>
            <form action="{{ route('support.store') }}" method="POST" class="c-form">
              @csrf
              <div class="c-form-item">
                <textarea name="message" id="message" required cols="30" rows="10"></textarea>
                <input type="hidden" name="user_id" value="{{ $user_id }}">
              </div>
              <div class="c-form-submit c-button-wrap">
                <button type="submit" class="c-button">送信する</button>
              </div>
            </form>
          </div>
        </section>
        @if(!empty($user_info->is_admin))
        <a href="{{ route('admin.show.index') }}">管理者画面はこちら</a>
        @endif
      </main>
      <!-- l-footer START -->
      @include('components.l-footer')
      <!-- l-footer END -->
    </div>
  </div>
  @include('components.javascript')
  <!-- <script>
    // メッセージ送信完了時にフォーム部分まで移動させる
    window.onload = function() {
      if ($("#contact").hasClass("required")) { //#contactとrequired は任意です。
        window.location.hash = "message"
      }
    };
  </script> -->
</body>

</html>