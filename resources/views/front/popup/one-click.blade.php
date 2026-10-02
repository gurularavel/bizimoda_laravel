@extends('front.layouts.popup', ['popupClass' => 'popup-module module-popup-22', 'journalPopup' => ['modulePopupId' => 22]])

@section('content')
<div class="grid-rows">
  <div class="grid-row grid-row-1">
    <div class="grid-cols">
      <div class="grid-col grid-col-1">
        <div class="grid-items">
          <div class="grid-item grid-item-1">
            <div class="module module-form module-form-198">
              <h3 class="title module-title">{{ __('Bir kliklə al') }}</h3>
              <div class="module-body">
                <form action="{{ lroute('front.form.send', ['type' => 'one_click']) }}" method="post" enctype="multipart/form-data" class="form-horizontal" data-language="{{ app()->getLocale() }}">
                  @csrf
                  <input type="hidden" name="product_id" value=""/>
                  <fieldset>
                    <div class="form-group custom-field required">
                      <label class="col-sm-2 control-label" for="oc-name">{{ __('Ad') }}</label>
                      <div class="col-sm-10"><input type="text" name="name" value="{{ auth('web')->user()?->name }}" placeholder="{{ __('Ad') }}" id="oc-name" class="form-control"/></div>
                    </div>
                    <div class="form-group custom-field required">
                      <label class="col-sm-2 control-label" for="oc-phone">{{ __('Telefon nömrəsi') }}</label>
                      <div class="col-sm-10"><input type="tel" name="phone" value="{{ auth('web')->user()?->phone }}" placeholder="{{ __('Telefon nömrəsi') }}" id="oc-phone" class="form-control"/></div>
                    </div>
                    <div class="form-group custom-field">
                      <label class="col-sm-2 control-label" for="oc-product">{{ __('Məhsulun adı') }}</label>
                      <div class="col-sm-10"><input type="text" value="" placeholder="{{ __('Məhsulun adı') }}" id="oc-product" class="form-control" readonly/></div>
                    </div>
                    <div class="form-group custom-field">
                      <label class="col-sm-2 control-label" for="oc-message">{{ __('Sualınız') }}</label>
                      <div class="col-sm-10"><textarea name="message" rows="5" placeholder="{{ __('Sualınız') }}" id="oc-message" class="form-control"></textarea></div>
                    </div>
                  </fieldset>
                  <div class="buttons">
                    <div class="pull-right">
                      <button type="submit" class="btn btn-primary" data-loading-text="<span>{{ __('Təsdiqlə') }}</span>"><span>{{ __('Təsdiqlə') }}</span></button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (function () {
    try {
      var p = parent.window.__popup_product || {};
      $('input[name="product_id"]').val(p.id || '');
      $('#oc-product').val(p.name || '');
    } catch (e) {}
  })();
</script>
@endpush
