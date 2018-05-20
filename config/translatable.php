<?php

return [
    /*
     |
     | Putting this to false disables the saving service,
     | and lets you define your own saving mechanism,
     |
     | For times when you want to save multiple translations at a time.
     |
     */
    'use_saving_service' => true,

    /*
     |
     | The locale key name, used in the translation tables.
     |
     */
    'locale_key_name' => 'locale',

    /*
     |
     | You may set this to "true" when you need the saved translations in the newly created model directly.
     |
     */
    'refresh_after_save' => false,
];
