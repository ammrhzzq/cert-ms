<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HOD Email
    |--------------------------------------------------------------------------
    |
    | This value is the email address that will be automatically assigned
    | the Head of Department (HOD) role upon registration. There can only
    | be one HOD in the system.
    |
    */
    'hod_email' => env('HOD_EMAIL', 'hod@example.com'),
    
    /*
    |--------------------------------------------------------------------------
    | Default Role
    |--------------------------------------------------------------------------
    |
    | This value is the default role assigned to new users upon registration.
    | Options are: 'staff', 'manager', 'hod'
    |
    */
    'default_role' => env('DEFAULT_ROLE', 'staff'),
    
    /*
    |--------------------------------------------------------------------------
    | Auto-approve Users
    |--------------------------------------------------------------------------
    |
    | This value determines whether new users are automatically approved
    | upon registration. If false, they need to be approved by an admin.
    |
    */
    'auto_approve_users' => env('AUTO_APPROVE_USERS', false),
];