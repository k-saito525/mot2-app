<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <title>トピックの一覧</title>
  @include('components.head')
</head>

<body class="is-subpage">
  <div class="l-container">

    @include('components.l-header')

    <div class="l-contents">
      <main class="l-main">
        <section class="p-sub__section">
          <div class="p-sub__btns" style="margin-bottom: 20px;">
            <a href="{{ route('topic.show.create') }}" class="c-button--large">
              <img src="{{ ('/img/common/icon-topic.svg') }}" alt="">
              <span>トピックを新規作成する</span>
            </a>
          </div>
          <h1 class="p-sub__head01">トピック一覧　(全{{ $topics->total() }}件)</h1>
          @include('components.flash-messages')
          @forelse($topics as $topic)
          <div class="c-topic-wrap">
            <a href="{{ route('topic.show.detail', ['id' => data_get($topic, 'id')]) }}" class="c-topic-title js-accordion-topic">{{ data_get($topic, 'title') }}</a>
            <div class="p-sub__inner">
              <div class="c-user">
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
              <div class="c-button-wrap">
                <a href="{{ route('topic.show.create.comment', ['id' => data_get($topic, 'id')]) }}/#comment" class="c-button">
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
          </div>
          @empty
          <p>現在表示できるトピックはありません。</p>
          @endforelse
          <div class="c-pagenation">
            @if($topics->currentPage() > 1)
            <a href="{{ route('topic.show.list', ['page' => $topics->currentPage() - 1]) }}" class="c-pagenation-item">＜ 前のページ</a>
            @endif
            @if($topics->hasMorePages())
            <a href="{{ route('topic.show.list', ['page' => $topics->currentPage() + 1]) }}" class="c-pagenation-item">次のページ ＞</a>
            @endif
          </div>
        </section>
      </main>
      @include('components.l-footer')
    </div>
  </div>
  @include('components.javascript')
</body>

</html>