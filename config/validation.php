<?php

return [
    'tenantValidation' => [
        'name' => 'required',
        'email' => 'required|email|unique:users',
        'type' => 'required',
        'status' => 'required',
    ],

    'adminValidation' => [
        'name' => 'required',
        'email' => 'required|email|unique:users',
        'role' => 'required',
        'status' => 'required',
    ],

    'tournamentValidation' => [
        //'name' => 'required|unique:tournaments',
        'image' => 'mimes:jpeg,jpg,png,gif|required|max:10000',
        // 'detail' => 'required|max:100',
    ],

    'tournamentUpdateValidation' => [
//        'name' => 'required',
//        'detail' => 'required|max:100',
    ],

    'sportValidation' => [
    ],
];
