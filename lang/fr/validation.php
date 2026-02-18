<?php

return [
    'required' => 'Ce champ est obligatoire.',
    'string' => 'Ce champ doit être un texte.',
    'email' => 'Veuillez saisir une adresse e-mail valide.',
    'boolean' => 'Ce champ doit être vrai ou faux.',
    'array' => 'Ce champ doit être une liste.',
    'file' => 'Veuillez téléverser un fichier valide.',
    'confirmed' => 'La confirmation ne correspond pas.',
    'unique' => 'Cette valeur est déjà utilisée.',
    'in' => 'La valeur sélectionnée est invalide.',
    'exists' => 'La valeur sélectionnée est invalide.',
    'date' => 'Veuillez saisir une date valide.',
    'integer' => 'Ce champ doit être un nombre entier.',
    'numeric' => 'Ce champ doit être numérique.',
    'min' => [
        'string' => 'Ce champ doit contenir au moins :min caractères.',
        'numeric' => 'Ce champ doit être au minimum :min.',
    ],
    'max' => [
        'string' => 'Ce champ ne peut pas dépasser :max caractères.',
        'numeric' => 'Ce champ ne peut pas dépasser :max.',
        'file' => 'Le fichier ne doit pas dépasser :max kilo-octets.',
    ],
    'attributes' => [
        'sector' => 'secteur',
        'maintenance_domain' => 'domaine',
        'failure_mode' => 'mode de défaillance',
        'industrial_machine_id' => 'machine industrielle',
        'technical_asset_id' => 'actif technique',
        'logistic_asset_id' => 'actif logistique',
        'description' => 'description',
    ],
];
