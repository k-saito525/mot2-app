<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <title>{{ data_get($user, 'name') }}さんのページ</title>
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
          <h1 class="p-sub__head01">{{ data_get($user, 'name') }}さんのページ</h1>
          @if(session('flash_success'))
          <div class="flash-complete">
            <p class="flash-text">・{{ session('flash_success') }}</p>
          </div>
          @endif
          <div class="p-sub__inner is-user-info">
            <div class="c-user-info__cover">
              @if(!empty(data_get($user, 'user_cover_image')))
              <img src="{{ asset('storage/'. data_get($user, 'user_cover_image')) }}" alt="">
              @else
              <img src="/img/common/dummy.png" alt="">
              @endif
            </div>
            <div class="c-user-info__head">
              <div class="c-user-icon">
                @if(!empty(data_get($user, 'user_icon')))
                <img src="{{ asset('storage/'. data_get($user, 'user_icon')) }}" alt="">
                @else
                <img src="/img/common/dummy_icon.png" alt="">
                @endif
              </div>
              <div class="c-user-info">
                <div class="c-user-name">{{ data_get($user, 'name') }}</div>
                <div class="c-user-id">{{ data_get($user, 'user_identifier', '') }}</div>
              </div>
            </div>
            <div class="c-user-info__body">
              {!! nl2br(htmlspecialchars(data_get($user, 'introduction_text', ''))) !!}
            </div>
            <div class="c-user-info__foot">
              <div class="c-user__sns">
                @if(!empty(data_get($user, 'sns_x')))
                <div class="c-user__sns-item">
                  <a href="{{ data_get($user, 'sns_x') }}" target="_blank">
                    <img src="/img/common/icon_circle_x.svg" alt="X">
                  </a>
                </div>
                @endif
                @if(!empty(data_get($user, 'sns_facebook')))
                <div class="c-user__sns-item">
                  <a href="{{ data_get($user, 'sns_facebook') }}" target="_blank">
                    <img src="/img/common/icon_circle_facebook.svg" alt="X">
                  </a>
                </div>
                @endif
                @if(!empty(data_get($user, 'sns_instagram')))
                <div class="c-user__sns-item">
                  <a href="{{ data_get($user, 'sns_instagram') }}" target="_blank">
                    <img src="/img/common/icon_circle_instagram.svg" alt="X">
                  </a>
                </div>
                @endif
              </div>
            </div>
          </div>
        </section>

        <section class="p-sub__section">
          <h2 class="p-sub__head02">{{ data_get($user, 'name') }}さんのトピック一覧</h2>
          <div class="c-topic-wrap">
            @if($topics->isEmpty())
            <p>現在表示できるトピックはありません。</p>
            @else
            @foreach($topics as $topic)
            <a href="/topic/show/topicID/" class="c-topic-title js-accordion-topic">{{ data_get($topic, 'title') }}</a>
            <div class="p-sub__inner user-topic">
              <div class="c-user">
                <a href="{{ route('user.show.detail', ['id' => data_get($topic, 'user_id')]) }}">
                  <div class="c-user-icon">
                    @if(!empty($topic->user_icon))
                    <img src="{{ asset('storage/'. $topic->user_icon) }}" alt="">
                    @else
                    <img src="/img/common/dummy_icon.png" alt="">
                    @endif
                  </div>
                  <div class="c-user-info">
                    <div class="c-user-name">{{ data_get($topic, 'name') }}</div>
                    <div class="c-user-id">@ {{ data_get($topic, 'user_identifier', '') }}</div>
                  </div>
                </a>
              </div>
              <div class="c-topic-detail">
                <p>
                  {!! nl2br(data_get($topic, 'content')) !!}
                </p>
                <time class="c-topic-date" datetime="{{ data_get($topic, 'created_at') }}">{{ data_get($topic, 'created_at') }}</time>
              </div>
              <div class="c-button-wrap">
                <a href="{{ route('topic.show.create.comment', ['topic_id' => data_get($topic, 'id')]) }}/#comment" class="c-button">
                  <img src="/img/common/icon-reply.svg" alt="">
                  <span>このトピックに回答する</span>
                </a>
                <a href="{{ route('topic.show.detail', ['id' => data_get($topic, 'id')]) }}" class="c-button">
                  <img src="/img/common/icon-show-topic.svg" alt="">
                  <span>このトピックを見る</span>
                </a>
                {{-- 編集できるのは作成者のみ --}}
                @if(data_get($topic, 'user_id') === $user_id)
                <a href="{{ route('topic.show.edit', ['id' => data_get($topic, 'id')]) }}" class="c-button">
                  <img src="/img/common/icon-pencil.svg" alt="">
                  <span>このトピックを編集する</span>
                </a>
                @endif
              </div>
            </div>
            @endforeach
            @endif
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