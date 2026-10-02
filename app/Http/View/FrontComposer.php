<?php

namespace App\Http\View;

use App\Services\CartService;
use App\Services\MenuBuilder;
use App\Support\Locales;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Front layout-un ümumi məlumatları: menyular, səbət, dil keçidləri, Journal JS konfiqurasiyası.
 */
class FrontComposer
{
    public function __construct(protected MenuBuilder $menus, protected CartService $cart) {}

    public function compose(View $view): void
    {
        $locale = app()->getLocale();
        $data = $view->getData();

        $view->with([
            'menus' => [
                'main' => $this->menus->tree('main'),
                'top' => $this->menus->tree('top'),
                'account' => $this->menus->tree('account'),
                'mobile_top' => $this->menus->tree('mobile_top'),
                'footer_1' => $this->menus->tree('footer_1'),
                'footer_2' => $this->menus->tree('footer_2'),
                'footer_3' => $this->menus->tree('footer_3'),
                'social' => $this->menus->tree('social'),
            ],
            'cart' => $this->cart,
            'customer' => Auth::guard('web')->user(),
            'languageLinks' => $this->languageLinks($data['alternates'] ?? []),
            'currentLocale' => $locale,
            'journalConfig' => $journal = $this->journalConfig($data),
            'deviceClass' => $this->deviceClass($journal),
        ]);
    }

    /**
     * <html> üçün ilkin cihaz sinifləri. Desktop UA-da journal-init.js enə görə yeniləyir;
     * telefon/planşet UA-da isə JS bu sinifləri dəyişmir — əvvəl planşet də "desktop-header-active"
     * alırdı və sıxılmış desktop header göstərilirdi.
     */
    protected function deviceClass(array $journal): string
    {
        return match (true) {
            $journal['isPhone'] => 'mobile phone mobile-header-active',
            $journal['isTablet'] => 'mobile tablet mobile-header-active',
            default => 'desktop desktop-header-active',
        };
    }

    /** Hər dil üçün cari səhifənin ekvivalent URL-i */
    protected function languageLinks(array $alternates): array
    {
        $links = [];
        $request = request();
        foreach (Locales::all() as $code => $lang) {
            if (isset($alternates[$code])) {
                $url = $alternates[$code];
            } else {
                $segments = $request->segments();
                if ($segments && array_key_exists($segments[0], config('locales.supported'))) {
                    $segments[0] = $code;
                } else {
                    $segments = [$code];
                }
                $url = url(implode('/', $segments)).($request->getQueryString() ? '?'.$request->getQueryString() : '');
            }
            $links[$code] = ['name' => $lang['name'], 'url' => $url, 'flag' => config("locales.flags.{$code}")];
        }

        return $links;
    }

    protected function journalConfig(array $data): array
    {
        $agent = (string) request()->userAgent();
        $isPhone = (bool) preg_match('/iPhone|Android.+Mobile|Windows Phone/i', $agent);
        $isTablet = ! $isPhone && (bool) preg_match('/iPad|Android|Tablet/i', $agent);

        return array_merge([
            'isPopup' => false,
            'isPhone' => $isPhone,
            'isTablet' => $isTablet,
            'isDesktop' => ! $isPhone && ! $isTablet,
            'filterScrollTop' => true,
            'filterUrlValuesSeparator' => ',',
            'countdownDay' => __('Gün'), 'countdownHour' => __('Saat'), 'countdownMin' => __('Dəq'), 'countdownSec' => __('San'),
            'globalPageColumnLeftTabletStatus' => false,
            'globalPageColumnRightTabletStatus' => false,
            'scrollTop' => true,
            'scrollToTop' => false,
            'notificationHideAfter' => '2000',
            'quickviewPageStyleCloudZoomStatus' => true,
            'quickviewPageStyleAdditionalImagesCarousel' => true,
            'quickviewPageStyleAdditionalImagesCarouselStyleSpeed' => '500',
            'quickviewPageStyleAdditionalImagesCarouselStyleAutoPlay' => false,
            'quickviewPageStyleAdditionalImagesCarouselStylePauseOnHover' => true,
            'quickviewPageStyleAdditionalImagesCarouselStyleDelay' => '3000',
            'quickviewPageStyleAdditionalImagesCarouselStyleLoop' => false,
            'quickviewPageStyleAdditionalImagesHeightAdjustment' => '5',
            'quickviewPageStyleProductStockUpdate' => false,
            'quickviewPageStylePriceUpdate' => false,
            'quickviewPageStyleOptionsSelect' => 'none',
            'quickviewText' => __('Cəld baxış'),
            'mobileHeaderOn' => 'tablet',
            'subcategoriesCarouselStyleSpeed' => '500',
            'subcategoriesCarouselStyleAutoPlay' => false,
            'subcategoriesCarouselStylePauseOnHover' => true,
            'subcategoriesCarouselStyleDelay' => '3000',
            'subcategoriesCarouselStyleLoop' => false,
            'productPageStyleImageCarouselStyleSpeed' => '500',
            'productPageStyleImageCarouselStyleAutoPlay' => true,
            'productPageStyleImageCarouselStylePauseOnHover' => true,
            'productPageStyleImageCarouselStyleDelay' => '3000',
            'productPageStyleImageCarouselStyleLoop' => false,
            'productPageStyleCloudZoomStatus' => true,
            'productPageStyleCloudZoomPosition' => 'inner',
            'productPageStyleAdditionalImagesCarousel' => true,
            'productPageStyleAdditionalImagesCarouselStyleSpeed' => '500',
            'productPageStyleAdditionalImagesCarouselStyleAutoPlay' => false,
            'productPageStyleAdditionalImagesCarouselStylePauseOnHover' => true,
            'productPageStyleAdditionalImagesCarouselStyleDelay' => '3000',
            'productPageStyleAdditionalImagesCarouselStyleLoop' => false,
            'productPageStyleAdditionalImagesHeightAdjustment' => '',
            'productPageStyleProductStockUpdate' => false,
            // Qiymət yenilənməsini öz JS-imiz (bizimoda-shop.js) edir
            'productPageStylePriceUpdate' => false,
            'productPageStyleOptionsSelect' => 'none',
            'infiniteScrollStatus' => false,
            'infiniteScrollOffset' => '4',
            'infiniteScrollLoadPrev' => __('Əvvəlki məhsullar'),
            'infiniteScrollLoadNext' => __('Növbəti məhsullar'),
            'infiniteScrollLoading' => __('Yüklənir...'),
            'infiniteScrollNoneLeft' => __('Siyahının sonuna çatdınız.'),
            'checkoutUrl' => route('front.checkout'),
            'headerHeight' => '100',
            'headerCompactHeight' => '50',
            'mobileMenuOn' => '',
            'searchStyleSearchAutoSuggestStatus' => true,
            'searchStyleSearchAutoSuggestDescription' => true,
            'searchStyleSearchAutoSuggestSubCategories' => true,
            'headerMiniSearchDisplay' => 'default',
            'stickyStatus' => false,
            'stickyFullHomePadding' => false,
            'stickyFullwidth' => true,
            'stickyAt' => '',
            'stickyHeight' => '',
            'headerTopBarHeight' => '35',
            'topBarStatus' => true,
            'headerType' => 'classic',
            'headerMobileHeight' => '60',
            'headerMobileStickyStatus' => true,
            'headerMobileTopBarVisibility' => true,
            'headerMobileTopBarHeight' => '40',
            'notification' => setting_t('site.notification_text') ? [['m' => 137, 'c' => substr(md5((string) setting_t('site.notification_text')), 0, 8)]] : [],
            'columnsCount' => 0,
            'currency_left' => '',
            'currency_right' => ' ₼',
            'currency_decimal' => '.',
            'currency_thousand' => ',',
            'mobileFilterButtonText' => __('Filtr'),
            'locale' => app()->getLocale(),
        ], $data['journalExtra'] ?? []);
    }
}
