<?php

namespace App\Models;

use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    public const PROJECT_TYPES = ['website', 'web_app', 'ecommerce', 'design', 'other'];

    public const BUDGETS = ['under_30k', '30k_80k', '80k_150k', 'over_150k', 'unsure'];

    public const TIMELINES = ['asap', '1_3_months', '3_6_months', 'flexible'];

    protected $fillable = [
        'name',
        'email',
        'company',
        'project_type',
        'budget',
        'timeline',
        'message',
        'language',
    ];
}
