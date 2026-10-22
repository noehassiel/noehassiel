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

    'nav' => [
        'projects' => 'Projects',
        'invoices' => 'Invoices',
        'security' => 'Security',
        'logout' => 'Log out',
        'label' => 'Portal sections',
    ],

    'home' => [
        'title' => 'Your projects',
        'greeting' => 'Hi, :name',
        'empty' => 'There are no active projects yet.',
        'outstanding' => '{0} Everything delivered|{1} 1 item still needed|[2,*] :count items still needed',
        'due' => 'Estimated delivery :date',
        'recent_invoices' => 'Recent invoices',
        'all_invoices' => 'See all',
    ],

    'project' => [
        'back' => 'Your projects',
        'progress' => 'Progress',
        'starts' => 'Start',
        'due' => 'Estimated delivery',
        'checklist' => 'What we need from you',
        'checklist_hint' => 'Upload it to your shared folder and mark it here. I will check it and confirm.',
        'checklist_empty' => 'We do not need anything else from you right now.',
        'all_received' => 'We have everything. Thank you.',
        'mark_uploaded' => 'I uploaded it',
        'marked_at' => 'Marked :time · I am reviewing it',
        'received_at' => 'Received :time',
        'due_on' => 'Due :date',
        'folders' => 'Shared folders',
        'folders_hint' => 'This is where you upload the project resources.',
        'folders_empty' => 'No folders yet. Share one with me and I will add it here.',
        'open_folder' => 'Open',
    ],

    'invoices' => [
        'title' => 'Invoices',
        'empty' => 'There are no invoices yet.',
        'download' => 'Download ZIP',
        'unavailable' => 'File unavailable',
        'issued' => 'Issued :date',
    ],

    'security' => [
        'title' => 'Security',
    ],

    'reminder' => [
        'subject' => '{1} 1 item needed to move your project forward|[2,*] :count items needed to move your project forward',
        'greeting' => 'Hi,',
        'body' => 'To keep moving we need the following. Upload it to your shared folder and mark it as "I uploaded it" in your portal.',
        'action' => 'Go to my portal',
        'footnote' => 'We write every few days while items are pending. If you already sent it, mark it in the portal and the reminders stop.',
    ],

];
