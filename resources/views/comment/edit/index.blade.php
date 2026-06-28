<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <title>この回答を編集</title>
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
          <h1 class="p-sub__head01">この回答を編集</h1>
          <p class="c-topic-title">{{ data_get($topic, 'title') }}</p>
          <div class="p-sub__inner">
            <div class="c-user no-link">
              <div class="c-user-icon">
                <x-user-icon :user="$topic->user" />
              </div>
              <div class="c-user-info">
                <div class="c-user-name">{{ data_get($topic, 'user.name') }}</div>
                <div class="c-user-id">@ {{ data_get($topic, 'user.user_identifier') }}</div>
              </div>
            </div>
            <div class="c-topic-detail">
              <p>
                {!! nl2br(data_get($topic, 'content_formatted')) !!}
              </p>
              <time class="c-topic-date" datetime="{{ data_get($topic, 'created_at') }}">{{ data_get($topic, 'created_at') }}</time>
              @if(strtotime(data_get($topic, 'created_at')) !== strtotime(data_get($topic, 'updated_at')))
              <time class="c-topic-date" datetime="{{ data_get($topic, 'updated_at') }}">（更新：{{ data_get($topic, 'updated_at') }}）</time>
              @endif
            </div>
            {{-- ここからコメント --}}
            <div class="c-reply-wrap">
              @if($comments->isNotEmpty())
              @foreach($comments as $comment)
              @if(data_get($comment, 'id') === data_get($target_comment, 'id'))
              {{-- 編集対象のコメントの場合はコメント欄最下部に配置するためスキップ --}}
              @else
              <div class="c-reply c-reply--has-detail">
                <div class="c-user no-link">
                  <div class="c-user-icon">
                    <x-user-icon :user="$comment->user" />
                  </div>
                  <div class="c-user-info">
                    <div class="c-user-name">{{ data_get($comment, 'user.name') }}</div>
                    <div class="c-user-id">@ {{ data_get($comment, 'user.user_identifier') }}</div>
                  </div>
                </div>
                <div class="c-reply-detail">
                  <p>
                    {!! nl2br(data_get($comment, 'comment_formatted')) !!}
                  </p>
                  <time class="c-reply-date" datetime="{{ data_get($comment, 'created_at') }}">{{ data_get($comment, 'created_at') }}</time>
                  @if(strtotime(data_get($comment, 'created_at')) !== strtotime(data_get($comment, 'updated_at')))
                  <time class="c-reply-date" datetime="{{ data_get($comment, 'updated_at') }}">（更新：{{ data_get($comment, 'updated_at') }}）</time>
                  @endif
                  @if(data_get($comment, 'user_id') === $user_id)
                  <div class="c-reply-edit">
                    <a href="{{ route('comment.show.edit', ['id' => data_get($comment, 'id')]) }}" class="c-button--mini">
                      <img src="/img/common/icon-pencil.svg" alt="">
                      <span>回答を編集</span>
                    </a>
                  </div>
                  @endif
                </div>
              </div>
              @endif
              @endforeach
              @endif
              {{-- 編集対象のコメント --}}
              <div class="c-reply c-reply--has-detail">
                <form action="{{ route('comment.store') }}" method="POST" class="c-form" style="width: 100%;">
                  @csrf
                  <div class="c-form-item">
                    <div class="c-user no-link">
                      <div class="c-user-icon">
                        <x-user-icon :user="$target_comment->user" />
                      </div>
                      <div class="c-user-info">
                        <div class="c-user-name">{{ data_get($target_comment, 'user.name') }}</div>
                        <div class="c-user-id">@ {{ data_get($target_comment, 'user.user_identifier') }}</div>
                      </div>
                    </div>
                    <!-- <textarea name="comment" id="comment" cols="30" rows="10">{!! nl2br(htmlspecialchars(data_get($target_comment, 'comment'))) !!}</textarea> -->
                    <textarea name="comment" id="comment" cols="30" rows="10">{{ data_get($target_comment, 'comment') }}</textarea>
                    @error('comment')
                    <p class="error-text">※{{ $message }}</p>
                    @enderror
                    <input type="hidden" name="topic_id" value="{{ data_get($topic, 'id') }}">
                    <input type="hidden" name="comment_id" value="{{ data_get($target_comment, 'id') }}">
                  </div>
                  <div class="c-form-submit c-button-wrap">
                    <button type="submit" class="c-button">更新する</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
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