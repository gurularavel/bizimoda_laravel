@php $customer = auth('web')->user(); @endphp
<aside id="column-right" class="side-column">
  <div class="grid-rows">
    <div class="grid-row grid-row-column-right-1">
      <div class="grid-cols">
        <div class="grid-col grid-col-column-right-1-1">
          <div class="grid-items">
            <div class="grid-item grid-item-column-right-1-1-1">
              <div class="accordion-menu accordion-menu-126">
                <ul class="j-menu">
                  @php
                    $links = $customer ? [
                        [__('Hesabım'), lroute('front.account')],
                        [__('Şəxsi məlumatlar'), lroute('front.account.edit')],
                        [__('Şifrəni dəyiş'), lroute('front.account.password')],
                        [__('Ünvanlarım'), lroute('front.account.addresses')],
                        [__('Arzu siyahısı'), lroute('front.wishlist')],
                        [__('Sifariş tarixçəsi'), lroute('front.account.orders')],
                    ] : [
                        [__('Daxil ol'), lroute('front.login')],
                        [__('Qeydiyyat'), lroute('front.register')],
                        [__('Şifrənizi unutmusunuz?'), lroute('front.password.request')],
                        [__('Arzu siyahısı'), lroute('front.wishlist')],
                    ];
                  @endphp
                  @foreach($links as [$label, $href])
                    <li class="menu-item accordion-menu-item accordion-menu-item-{{ $loop->iteration }} {{ url()->current() === $href ? 'active' : '' }}">
                      <a href="{{ $href }}"><span class="links-text">{{ $label }}</span></a>
                    </li>
                  @endforeach
                  @if($customer)
                    <li class="menu-item accordion-menu-item">
                      <a href="javascript:void(0)" onclick="document.getElementById('form-logout').submit()"><span class="links-text">{{ __('Çıxış') }}</span></a>
                    </li>
                  @endif
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</aside>
