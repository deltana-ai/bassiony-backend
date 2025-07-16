<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option defines the default authentication "guard" and password
    | reset "broker" for your application. You may change these values
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => 'web-owner',
        'passwords' => 'owners',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | which utilizes session storage plus the Eloquent user provider.
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | Supported: "session"
    |
    */

    'guards' => [
      //web guards
        'web-owner' => [
            'driver' => 'sanctum',
            'provider' => 'owners',
        ],
        'web-manager' => [
            'driver' => 'sanctum',
            'provider' => 'web_managers',
        ],
        // 'web-pharmacist' => [
        //     'driver' => 'sanctum',
        //     'provider' => 'web_pharmacist',
        // ],
        // 'web-driver' => [
        //     'driver' => 'sanctum',
        //     'provider' => 'web_driver',
        // ],

        //mobile guards
        'client' => [
            'driver' => 'sanctum',
            'provider' => 'clients',
        ],
        'pharmacist' => [
            'driver' => 'sanctum',
            'provider' => 'pharmacists',
        ],
        'driver' => [
            'driver' => 'sanctum',
            'provider' => 'drivers',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | If you have multiple user tables or models you may configure multiple
    | providers to represent the model / table. These providers may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
      //web guards
        'owners' => [
            'driver' => 'eloquent',
            'model' => App\Models\Owner::class,
        ],
        'web_managers' => [
            'driver' => 'eloquent',
            'model' => App\Models\CompanyManager::class,
        ],
        'web_pharmacist' => [
            'driver' => 'eloquent',
            'model' => App\Models\Pharmacist::class,
        ],
        'web_driver' => [
            'driver' => 'eloquent',
            'model' => App\Models\Driver::class,
        ],

         //mobile guards
        'clients' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
        'pharmacists' => [
            'driver' => 'eloquent',
            'model' => App\Models\Pharmacist::class,
        ],
        'drivers' => [
            'driver' => 'eloquent',
            'model' => App\Models\Driver::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | These configuration options specify the behavior of Laravel's password
    | reset functionality, including the table utilized for token storage
    | and the user provider that is invoked to actually retrieve users.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

    'passwords' => [

        'owner' => [
            'provider' => 'owners',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        'web_manager' => [
             'provider' => 'web_managers',
             'table' =>  'password_reset_tokens',
             'expire' => 60,
             'throttle' => 60,
         ],

         'web_pharmacist' => [
             'provider' => 'web_pharmacist',
             'table' =>  'password_reset_tokens',
             'expire' => 60,
             'throttle' => 60,
         ],
         'web_driver' => [
             'provider' => 'web_driver',
             'table' =>  'password_reset_tokens',
             'expire' => 60,
             'throttle' => 60,
         ],

         // 'drivers' => [
         //     'provider' => 'drivers',
         //     'table' =>  'password_reset_tokens',
         //     'expire' => 60,
         //     'throttle' => 60,
         // ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | window expires and users are asked to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
