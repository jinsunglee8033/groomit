<?php

return [

    'twilio' => [

        'default' => 'twilio',

        'connections' => [

            'twilio' => [

                /*
                |--------------------------------------------------------------------------
                | SID
                |--------------------------------------------------------------------------
                |
                | Your Twilio Account SID #
                |
                */

                'sid' => getenv('TWILIO_SID') ?: 'AC8f1af8519c6374fe3ad941bbf5fe54b5',

                /*
                |--------------------------------------------------------------------------
                | Access Token
                |--------------------------------------------------------------------------
                |
                | Access token that can be found in your Twilio dashboard
                |
                */

                'token' => getenv('TWILIO_TOKEN') ?: '4478fb6776fd6042e06b7029d4b75d3c',

                /*
                |--------------------------------------------------------------------------
                | From Number
                |--------------------------------------------------------------------------
                |
                | The Phone number registered with Twilio that your SMS & Calls will come from
                |
                */

                'from' => getenv('TWILIO_FROM') ?: '16466667790',

                /*
                |--------------------------------------------------------------------------
                | Verify Twilio's SSL Certificates
                |--------------------------------------------------------------------------
                |
                | Allows the client to bypass verifying Twilio's SSL certificates.
                | It is STRONGLY advised to leave this set to true for production environments.
                |
                */

                'ssl_verify' => true,
            ],

            'twilio_gc' => [

              /*
              |--------------------------------------------------------------------------
              | SID
              |--------------------------------------------------------------------------
              |
              | Your Twilio Account SID #
              |
              */

            'sid' => getenv('TWILIO_SID') ?: 'AC8f1af8519c6374fe3ad941bbf5fe54b5',

              /*
              |--------------------------------------------------------------------------
              | Access Token
              |--------------------------------------------------------------------------
              |
              | Access token that can be found in your Twilio dashboard
              |
              */

            'token' => getenv('TWILIO_TOKEN') ?: '4478fb6776fd6042e06b7029d4b75d3c',

              /*
              |--------------------------------------------------------------------------
              | From Number
              |--------------------------------------------------------------------------
              |
              | The Phone number registered with Twilio that your SMS & Calls will come from
              |
              */

            'from' => '16466669749',

              /*
              |--------------------------------------------------------------------------
              | Verify Twilio's SSL Certificates
              |--------------------------------------------------------------------------
              |
              | Allows the client to bypass verifying Twilio's SSL certificates.
              | It is STRONGLY advised to leave this set to true for production environments.
              |
              */

            'ssl_verify' => true,
          ],
        ],
    ],
];
