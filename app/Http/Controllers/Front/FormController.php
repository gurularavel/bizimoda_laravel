<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Mail\ContactFormMail;
use App\Models\FormSubmission;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Throwable;

/**
 * journal.js ".module-form" AJAX formaları:
 *   cavab: {status: "success", response: {message}} | {status: "error", response: {errors: {field: msg}}}
 */
class FormController extends Controller
{
    public function send(Request $request, string $type, OrderService $orders)
    {
        $rules = $type === 'one_click'
            ? ['name' => 'required|string|max:64', 'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9 +()\-]{7,}$/'], 'message' => 'nullable|string|max:2000']
            : ['name' => 'required|string|max:64', 'email' => 'required|email|max:120', 'phone' => 'nullable|string|max:30', 'subject' => 'nullable|string|max:120', 'message' => 'required|string|min:5|max:5000'];

        $validator = Validator::make($request->all(), $rules, [], [
            'name' => __('Ad'), 'email' => __('E-mail'), 'phone' => __('Telefon'), 'message' => __('Mətn'),
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'response' => ['errors' => $validator->errors()->map(fn ($m) => $m[0])]]);
        }

        $data = $validator->validated();

        if ($type === 'one_click') {
            $product = $this->resolveProduct($request);
            if (! $product) {
                return response()->json(['status' => 'error', 'response' => ['errors' => ['name' => __('Məhsul tapılmadı.')]]]);
            }

            $order = $orders->placeOneClick($product, $data['name'], $data['phone'], $data['message'] ?? null, auth('web')->user());

            FormSubmission::query()->create([
                'type' => 'one_click', 'name' => $data['name'], 'phone' => $data['phone'], 'message' => $data['message'] ?? null,
                'product_id' => $product->id, 'url' => $request->input('url'), 'locale' => app()->getLocale(),
                'subject' => $order->number,
            ]);

            return response()->json(['status' => 'success', 'response' => [
                'message' => __('Sifarişiniz №:number qəbul edildi! Operatorumuz qısa zamanda sizinlə əlaqə saxlayacaq.', ['number' => $order->number]),
            ]]);
        }

        $submission = FormSubmission::query()->create($data + [
            'type' => 'contact', 'url' => $request->input('url'), 'locale' => app()->getLocale(),
        ]);

        $admins = array_filter(array_map('trim', preg_split('/[,;\s]+/', (string) setting('mail.admin_emails', ''))));
        if ($admins) {
            try {
                Mail::to($admins)->send(new ContactFormMail($submission));
            } catch (Throwable $e) {
                report($e);
            }
        }

        return response()->json(['status' => 'success', 'response' => ['message' => __('Mesajınız göndərildi. Təşəkkür edirik!')]]);
    }

    protected function resolveProduct(Request $request): ?Product
    {
        if ($id = (int) $request->input('product_id')) {
            return Product::query()->visible()->find($id);
        }

        $path = trim((string) parse_url((string) $request->input('url'), PHP_URL_PATH), '/');
        $slug = basename($path);
        if ($slug === '') {
            return null;
        }

        foreach (array_keys(locales()) as $locale) {
            if ($product = Product::query()->visible()->whereSlug($slug, $locale)->first()) {
                return $product;
            }
        }

        return null;
    }
}
