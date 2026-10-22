<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Old project types => the closest service-based type.
     *
     * @var array<string, string>
     */
    private const MAP = [
        'website' => 'web_design_development',
        'webapp' => 'web_design_development',
        'ecommerce' => 'web_design_development',
        'mobile' => 'mobile_design_development',
        'branding' => 'custom',
    ];

    /**
     * Move existing proposals to the service-based project types.
     */
    public function up(): void
    {
        foreach (self::MAP as $old => $new) {
            DB::table('proposals')->where('project_type', $old)->update(['project_type' => $new]);
        }
    }

    /**
     * Not reversible: several old types collapse into one.
     */
    public function down(): void {}
};
