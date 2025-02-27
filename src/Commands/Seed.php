<?php

declare(strict_types=1);

namespace Stancl\Tenancy\Commands;

use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Console\Seeds\SeedCommand;
use Stancl\Tenancy\DatabaseManager;
use Stancl\Tenancy\Traits\HasATenantsOption;

class Seed extends SeedCommand
{
    use HasATenantsOption;

    protected $database;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed tenant database(s).';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ConnectionResolverInterface $resolver, DatabaseManager $database)
    {
        parent::__construct($resolver);
        $this->database = $database;

        $this->setName('tenants:seed');
        $this->specifyParameters();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        $this->input->setOption('database', 'tenant');

        $originalTenant = tenancy()->getTenant();
        tenancy()->all($this->option('tenants'))->each(function ($tenant) {
            $this->line("Tenant: {$tenant['id']}");
            tenancy()->initialize($tenant);

            // Seed
            parent::handle();
        });

        if ($originalTenant) {
            tenancy()->initialize($originalTenant);
        } else {
            tenancy()->endTenancy();
        }
    }
}
