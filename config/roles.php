<?php

return [
    
    'hod_email' => env('HOD_EMAIL', 'hod@example.com'),
    'admin_email' => env('ADMIN_EMAIL', 'admin@cybersecurity.my'),
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