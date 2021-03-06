<?php
namespace SmoothPhp\LaravelAdapter\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Schema\Blueprint;
use Schema;

/**
 * Class BuildLaravelEventStore
 * @package SmoothPhp\LaravelAdapter\Console
 * @author Simon Bennett <simon@bennett.im>
 */
final class BuildLaravelEventStore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smoothphp:buildeventstore';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build the Laravel Event Store';

    /** @var Repository */
    private $config;

    /**
     * BuildLaravelEventStore constructor.
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        parent::__construct();
        $this->config = $config;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!$this->confirm("Are you sure you want to make a new table '{$this->config->get('cqrses.eventstore_table')}'"
                            . " on connection '{$this->config->get('cqrses.eventstore_connection')}'"
                            . " Do you wish to continue?")
        ) {
            $this->line("Stopping");

            return;
        }

        Schema::connection($this->config->get('cqrses.eventstore_connection'))
              ->create($this->config->get('cqrses.eventstore_table'),
                  function (Blueprint $table) {
                      $table->increments('id');
                      $table->string('uuid', 56);
                      $table->integer('playhead')->unsigned();
                      $table->text('metadata');
                      $table->text('payload');
                      $table->string('recorded_on', 32);
                      $table->text('type');
                      $table->unique(['uuid', 'playhead']);
                  });
    }
}