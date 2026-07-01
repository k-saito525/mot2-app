<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <title>このトピックを編集</title>
  @include('components.head')
</head>

<body class="is-subpage">
  <div class="l-container">

    @include('components.l-header')

    <div class="l-contents">
      <main class="l-main">
        <section class="p-sub__section">
          <h1 class="p-sub__head01">このトピックを編集</h1>
          <p class="c-topic-title">{{ data_get($topic, 'title') }}</p>
          <div class="p-sub__inner">
            <form action="{{ route('topic.store') }}" method="POST" class="c-form" style="margin: 0;">
              @csrf
              <div class="c-form-item">
                <div class="c-user no-link">
                  <div class="c-user-icon">
                    <x-user-icon :user="$topic->user" />
                  </div>
                  <div class="c-user-info">
                    <div class="c-user-name">{{ data_get($topic, 'user.name') }}</div>
                    <div class="c-user-id">@ {{ data_get($topic, 'user.user_identifier') }}</div>
                  </div>
                </div>
                <textarea name="topic-detail" id="topic-detail" cols="30" rows="10">{{ data_get($topic, 'content') }}</textarea>
                @error('topic-detail')
                <p class="error-text">※{{ $message }}</p>
                @enderror
                <input type="hidden" name="topic-id" value="{{ data_get($topic, 'id') }}">
                <input type="hidden" name="topic-title" value="{{ data_get($topic, 'title') }}">
              </div>
              <div class="c-form-submit c-button-wrap">
                <button type="submit" class="c-button" name="delete" value="削除">削除する</button>
                <button type="submit" class="c-button">更新する</button>
              </div>
            </form>
          </div>
        </section>
      </main>
      @include('components.l-footer')
    </div>
  </div>
  @include('components.javascript')
</body>

</html>