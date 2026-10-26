<?php

namespace App\Console\Commands;

use App\Services\Linear;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('linear:teams')]
#[Description('List the Linear teams the API key can see, to pick LINEAR_TEAM_ID')]
class LinearTeams extends Command
{
    public function handle(Linear $linear): int
    {
        if (blank(config('services.linear.api_key'))) {
            $this->error('Set LINEAR_API_KEY in .env first.');

            return self::FAILURE;
        }

        $this->table(['LINEAR_TEAM_ID', 'Key', 'Name'], array_map(
            fn (array $team): array => [$team['id'], $team['key'], $team['name']],
            $linear->teams(),
        ));

        return self::SUCCESS;
    }
}
