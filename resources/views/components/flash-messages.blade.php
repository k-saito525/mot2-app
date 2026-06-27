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
