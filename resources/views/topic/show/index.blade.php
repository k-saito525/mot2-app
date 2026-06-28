<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <title>このトピックの詳細</title>
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
          <h1 class="p-sub__head01">このトピックの詳細</h1>
          @include('components.flash-messages')
          <p class="c-topic-title">{{ data_get($topic, 'title') }}</p>
          <div class="p-sub__inner">
            <div class="c-user no-link">
              <a href="{{ route('user.show.detail', ['id' => data_get($topic, 'user_id')]) }}">
                <div class="c-user-icon">
                  <x-user-icon :user="$topic->user" />
                </div>
                <div class="c-user-info">
                  <div class="c-user-name">{{ data_get($topic, 'user.name') }}</div>
                  <div class="c-user-id">@ {{ data_get($topic, 'user.user_identifier') }}</div>
                </div>
              </a>
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

            <?php // ここからコメント 
            ?>
            <div class="c-reply-wrap">
              @if($comments->isNotEmpty())
              @foreach($comments as $comment)
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
              @endforeach
              @endif
            </div>
            <div class="c-button-wrap">
              <a href="{{ route('topic.show.create.comment', ['id' => data_get($topic, 'id')]) }}/#comment" class="c-button">
                <img src="/img/common/icon-reply.svg" alt="">
                <span>このトピックに回答する</span>
              </a>
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