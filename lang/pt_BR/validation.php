
<?php

return [
    'required' => 'O campo :attribute é obrigatório.',
    'present' => 'O campo :attribute deve estar presente.',
    'email' => 'O campo :attribute deve conter um e-mail válido.',
    'string' => 'O campo :attribute deve ser um texto.',
    'array' => 'O campo :attribute deve ser uma lista.',
    'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
    'integer' => 'O campo :attribute deve ser um número inteiro.',
    'distinct' => 'O campo :attribute contém valores duplicados.',
    'exists' => 'O valor selecionado para :attribute é inválido.',
    'unique' => 'O valor informado para :attribute já está em uso.',
    'confirmed' => 'A confirmação do campo :attribute não confere.',
    'regex' => 'O formato do campo :attribute é inválido.',
    'prohibited' => 'O campo :attribute não pode ser enviado.',

    'min' => [
        'array' => 'Selecione pelo menos :min item para :attribute.',
        'numeric' => 'O campo :attribute deve ser no mínimo :min.',
        'file' => 'O arquivo :attribute deve ter pelo menos :min KB.',
        'string' => 'O campo :attribute deve ter pelo menos :min caracteres.',
    ],

    'max' => [
        'array' => 'O campo :attribute aceita no máximo :max itens.',
        'numeric' => 'O campo :attribute não pode ser maior que :max.',
        'file' => 'O arquivo :attribute não pode ultrapassar :max KB.',
        'string' => 'O campo :attribute não pode ultrapassar :max caracteres.',
    ],

    'password' => [
        'letters' => 'O campo :attribute deve conter pelo menos uma letra.',
        'mixed' => 'O campo :attribute deve conter letras maiúsculas e minúsculas.',
        'numbers' => 'O campo :attribute deve conter pelo menos um número.',
        'symbols' => 'O campo :attribute deve conter pelo menos um símbolo.',
        'uncompromised' => 'Essa senha apareceu em um vazamento de dados. Escolha outra.',
    ],

    'attributes' => [
        'name' => 'nome',
        'email' => 'e-mail',
        'password' => 'senha',
        'password_confirmation' => 'confirmação da senha',
        'slug' => 'identificador',
        'roles' => 'papéis',
        'roles.*' => 'papel',
        'permissions' => 'permissões',
        'permissions.*' => 'permissão',
        'is_active' => 'situação da conta',
    ],
];
