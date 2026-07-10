<?php

namespace App\Console\Commands;

use App\Models\LedgerTransactionLine;
use App\Models\Party;
use Illuminate\Console\Command;

class BackfillPartyOpeningBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backfill-party-opening-balances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Post an opening_balance ledger transaction for every Party created before the accounting ledger existed (one-off backfill)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $postedIds = LedgerTransactionLine::whereNotNull('party_id')
            ->whereHas('transaction', fn ($q) => $q->where('type', 'opening_balance'))
            ->pluck('party_id')
            ->unique();

        $posted = 0;

        Party::where('opening_balance', '>', 0)
            ->whereNotIn('id', $postedIds)
            ->each(function (Party $party) use (&$posted) {
                $party->postOpeningBalanceToLedger();
                $posted++;
            });

        $this->info("Posted opening balance for {$posted} part".($posted === 1 ? 'y' : 'ies').'.');

        return self::SUCCESS;
    }
}
