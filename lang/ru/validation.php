<?php

return [
    'accepted' => 'Необходимо принять :attribute.',
    'array' => 'Поле :attribute должно быть массивом.',
    'between' => [
        'numeric' => 'Поле :attribute должно быть между :min и :max.',
        'string' => 'Поле :attribute должно содержать от :min до :max символов.',
        'array' => 'Поле :attribute должно содержать от :min до :max элементов.',
        'file' => 'Размер файла :attribute должен быть от :min до :max КБ.',
    ],
    'boolean' => 'Поле :attribute должно быть логическим.',
    'confirmed' => 'Подтверждение поля :attribute не совпадает.',
    'date' => 'Поле :attribute не является датой.',
    'email' => 'Поле :attribute должно быть корректным e-mail адресом.',
    'exists' => 'Выбранное значение :attribute некорректно.',
    'file' => 'Поле :attribute должно быть файлом.',
    'image' => 'Поле :attribute должно быть изображением.',
    'in' => 'Выбранное значение :attribute некорректно.',
    'integer' => 'Поле :attribute должно быть целым числом.',
    'max' => [
        'numeric' => 'Поле :attribute не может быть больше :max.',
        'string' => 'Поле :attribute не может быть длиннее :max символов.',
        'array' => 'Поле :attribute не может содержать больше :max элементов.',
        'file' => 'Размер файла :attribute не может быть больше :max КБ.',
    ],
    'min' => [
        'numeric' => 'Поле :attribute должно быть не меньше :min.',
        'string' => 'Поле :attribute должно содержать не менее :min символов.',
        'array' => 'Поле :attribute должно содержать не менее :min элементов.',
        'file' => 'Размер файла :attribute должен быть не меньше :min КБ.',
    ],
    'numeric' => 'Поле :attribute должно быть числом.',
    'password' => [
        'letters' => 'Поле :attribute должно содержать хотя бы одну букву.',
        'mixed' => 'Поле :attribute должно содержать заглавные и строчные буквы.',
        'numbers' => 'Поле :attribute должно содержать хотя бы одну цифру.',
        'symbols' => 'Поле :attribute должно содержать хотя бы один символ.',
        'uncompromised' => 'Этот :attribute обнаружен в утечке данных. Выберите другой.',
    ],
    'regex' => 'Поле :attribute имеет неверный формат.',
    'required' => 'Поле :attribute обязательно для заполнения.',
    'string' => 'Поле :attribute должно быть строкой.',
    'unique' => 'Такое значение поля :attribute уже существует.',
    'uploaded' => 'Не удалось загрузить :attribute.',
    'url' => 'Поле :attribute должно быть корректным URL.',

    'custom' => [],

    'attributes' => [
        'email' => 'E-mail',
        'password' => 'Пароль',
        'name' => 'Имя',
        'phone' => 'Телефон',
        'message' => 'Текст',
    ],
];
