<?php

return [
    'required' => 'This field is required.',
    'string' => 'This field must be text.',
    'email' => 'Please provide a valid email address.',
    'boolean' => 'This field must be true or false.',
    'array' => 'This field must be an array.',
    'file' => 'Please upload a valid file.',
    'confirmed' => 'Confirmation does not match.',
    'unique' => 'This value has already been taken.',
    'in' => 'The selected value is invalid.',
    'exists' => 'The selected value is invalid.',
    'date' => 'Please provide a valid date.',
    'integer' => 'This field must be an integer.',
    'numeric' => 'This field must be a number.',
    'min' => [
        'string' => 'This field must be at least :min characters.',
        'numeric' => 'This field must be at least :min.',
    ],
    'max' => [
        'string' => 'This field may not be greater than :max characters.',
        'numeric' => 'This field may not be greater than :max.',
        'file' => 'The file may not be greater than :max kilobytes.',
    ],
    'attributes' => [
        'sector' => 'sector',
        'maintenance_domain' => 'domain',
        'failure_mode' => 'failure mode',
        'industrial_machine_id' => 'industrial machine',
        'technical_asset_id' => 'technical asset',
        'logistic_asset_id' => 'logistic asset',
        'description' => 'description',
    ],
];
