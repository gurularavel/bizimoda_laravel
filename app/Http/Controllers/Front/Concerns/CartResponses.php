<?php

namespace App\Http\Controllers\Front\Concerns;

use App\Models\Product;
use App\Services\CartService;

/**
 * Journal3 cart.add/remove/update JS-inin gözlədiyi JSON formatı.
 */
trait CartResponses
{
    protected function cartAddedResponse(Product $product, CartService $cart): array
    {
        $cartUrl = lroute('front.cart');
        $message = __('Siz <a href=":product">:name</a> məhsulunu <a href=":cart">səbətinizə</a> müvəffəqiyyətlə əlavə etdiniz!', [
            'product' => $product->url(),
            'name' => e($product->name),
            'cart' => $cartUrl,
        ]);
        $image = $product->mainImage();
        $count = $cart->count();

        // Journal show_notification title/message-i HTML kimi daxil edir — bütün dəyərlər escape olunur
        $title = '<span class="bz-toast__status"><svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>'
            .e(__('Səbətə əlavə olundu')).'</span>';
        $body = '<a class="bz-toast__name" href="'.e($product->url()).'">'.e($product->name).'</a>'
            .'<span class="bz-toast__price">'.e(money($product->computed_price)).'</span>'
            .'<span class="bz-toast__meta">'.e(__('Səbətdə: :count məhsul', ['count' => $count])).' · '.e(__('Cəm: :total', ['total' => money($cart->total())])).'</span>';

        return [
            'success' => $message,
            'notification' => [
                'className' => 'notification-cart bz-toast',
                'position' => 'tr',
                'title' => $title,
                'image' => thumb($image, 80, 80),
                'image2x' => thumb($image, 160, 160),
                'message' => $body,
                'buttons' => [
                    ['className' => 'bz-btn bz-btn--outline notification-view-cart', 'name' => e(__('Səbətə bax')), 'href' => $cartUrl],
                    ['className' => 'bz-btn bz-btn--primary notification-checkout', 'name' => e(__('Sifarişi rəsmiləşdir')), 'href' => lroute('front.checkout')],
                ],
            ],
        ] + $this->cartTotals($cart);
    }

    protected function cartTotals(CartService $cart): array
    {
        $count = $cart->count();

        return [
            'total' => __(':count ədəd - :total', ['count' => $count, 'total' => money($cart->total())]),
            'items_count' => $count,
            'items_price' => money($cart->total()),
        ];
    }
}
