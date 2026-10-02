<?php

return [
    'accepted' => ':attribute qəbul edilməlidir.',
    'array' => ':attribute massiv olmalıdır.',
    'between' => [
        'numeric' => ':attribute :min ilə :max arasında olmalıdır.',
        'string' => ':attribute :min ilə :max simvol arasında olmalıdır.',
        'array' => ':attribute :min ilə :max element arasında olmalıdır.',
        'file' => ':attribute :min ilə :max KB arasında olmalıdır.',
    ],
    'boolean' => ':attribute doğru və ya yanlış olmalıdır.',
    'confirmed' => ':attribute təkrarı uyğun gəlmir.',
    'date' => ':attribute düzgün tarix deyil.',
    'email' => ':attribute düzgün e-poçt ünvanı olmalıdır.',
    'exists' => 'Seçilmiş :attribute yanlışdır.',
    'file' => ':attribute fayl olmalıdır.',
    'image' => ':attribute şəkil olmalıdır.',
    'in' => 'Seçilmiş :attribute yanlışdır.',
    'integer' => ':attribute tam ədəd olmalıdır.',
    'max' => [
        'numeric' => ':attribute :max-dan böyük ola bilməz.',
        'string' => ':attribute :max simvoldan çox ola bilməz.',
        'array' => ':attribute :max elementdən çox ola bilməz.',
        'file' => ':attribute :max KB-dan böyük ola bilməz.',
    ],
    'min' => [
        'numeric' => ':attribute ən azı :min olmalıdır.',
        'string' => ':attribute ən azı :min simvol olmalıdır.',
        'array' => ':attribute ən azı :min element olmalıdır.',
        'file' => ':attribute ən azı :min KB olmalıdır.',
    ],
    'numeric' => ':attribute rəqəm olmalıdır.',
    'password' => [
        'letters' => ':attribute ən azı bir hərf içərməlidir.',
        'mixed' => ':attribute böyük və kiçik hərf içərməlidir.',
        'numbers' => ':attribute ən azı bir rəqəm içərməlidir.',
        'symbols' => ':attribute ən azı bir simvol içərməlidir.',
        'uncompromised' => 'Bu :attribute məlumat sızmasında aşkarlanıb. Başqa şifrə seçin.',
    ],
    'regex' => ':attribute formatı yanlışdır.',
    'required' => ':attribute mütləq doldurulmalıdır.',
    'string' => ':attribute mətn olmalıdır.',
    'unique' => 'Bu :attribute artıq istifadə olunur.',
    'uploaded' => ':attribute yüklənmədi.',
    'url' => ':attribute düzgün URL deyil.',

    'custom' => [],

    'attributes' => [
        'email' => 'E-poçt',
        'password' => 'Şifrə',
        'name' => 'Ad',
        'phone' => 'Telefon',
        'message' => 'Mətn',
    ],
];
