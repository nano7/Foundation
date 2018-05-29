<?php namespace Nano7\Foundation\Discover\Console;

use Nano7\Console\Command;
use Nano7\Foundation\Discover\PackageManifest;

class PackageDiscoverCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'package:discover';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild the cached package manifest';

    /**
     * @var PackageManifest
     */
    protected $manifest;

    /**
     * @param PackageManifest $manifest
     */
    public function __construct(PackageManifest $manifest)
    {
        parent::__construct();

        $this->manifest = $manifest;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $discoverred = $this->manifest->build();

        foreach (array_keys($discoverred) as $package) {
            $this->line("Discovered Package: <info>{$package}</info>");
        }

        $this->info('Package manifest generated successfully.');
    }
}
