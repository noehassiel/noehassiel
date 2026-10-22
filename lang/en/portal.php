<?php

return [

    'project_status' => [
        'brief' => 'Brief',
        'design' => 'Design',
        'development' => 'Development',
        'review' => 'Review',
        'delivered' => 'Delivered',
        'paused' => 'Paused',
    ],

    'requirement_status' => [
        'pending' => 'Pending',
        'client_marked' => 'To review',
        'received' => 'Received',
    ],

    'invitation' => [
        'mail_subject' => ':client invited you to their noehassiel workspace',
        'mail_greeting' => 'Hi,',
        'mail_body' => 'You now have access to the :client workspace, where you can follow project progress, see what is still needed and download invoices.',
        'mail_action' => 'Set up my access',
        'mail_expiry' => 'This link is valid for :days days. If you were not expecting this email, you can ignore it.',
        'title' => 'Set up your access',
        'description' => 'You were invited to the :client workspace. Choose your name and a password to get in.',
        'name' => 'Your name',
        'password' => 'Password',
        'password_confirmation' => 'Confirm your password',
        'submit' => 'Create access',
        'invalid' => 'This invitation was already used or has expired. Ask Hassiel for a new one.',
        'email_taken' => 'An account with this email already exists. Log in with it instead.',
    ],

    'home' => [
        'title' => 'Your projects',
        'greeting' => 'Hi, :name',
        'empty' => 'There are no active projects yet.',
        'outstanding' => '{0} Everything delivered|{1} 1 item still needed|[2,*] :count items still needed',
    ],

];
