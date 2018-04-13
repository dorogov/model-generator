<?php

return [
    'namespace'           => 'App\Models',
    'base_class_name'     => \Illuminate\Database\Eloquent\Model::class,
    'sofa_eloquence_name' => \Sofa\Eloquence\Eloquence::class,
    'sofa_mappable_name'  => \Sofa\Eloquence\Mappable::class,
    'sofa_mutable_name'   => \Sofa\Eloquence\Mutable::class,
    'output_path'         => 'Models',
    'no_timestamps'       => null,
    'date_format'         => null,
    'connection'          => null,
    'db_types'            => [
        'enum' => 'string',
        'json' => 'string'
    ]
];
