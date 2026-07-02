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
            <form action="{{ route('topic.update', ['id' => data_get($topic, 'id')]) }}" method="POST" class="c-form" style="margin: 0;">
              @csrf
              @method('PUT')
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
              </div>
              <div class="c-form-submit c-button-wrap">
                <button type="submit" class="c-button">更新する</button>
              </div>
            </form>
            <form action="{{ route('topic.destroy', ['id' => data_get($topic, 'id')]) }}" method="POST" class="c-form" style="margin: 10px 0 0;">
              @csrf
              @method('DELETE')
              <div class="c-form-submit c-button-wrap">
                <button type="submit" class="c-button">削除する</button>
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