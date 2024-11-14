<?php

namespace Mitoop\LaravelTools\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Grammars\MySqlGrammar;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class GenerateFillable extends Command
{
    protected $signature = 'tools:gen-fillable 
                            {table : The table name} 
                            {connection? : The database connection name}';

    protected $description = 'Generate fillable array for a table and copy to clipboard if on macOS';

    public function handle(): void
    {
        $table = $this->argument('table');
        $connection = $this->argument('connection') ?? Config::get('database.default');

        $database = DB::connection($connection);
        if ($database->getDriverName() === 'mysql'
            &&
            version_compare($this->getLaravel()->version(), '10.30.0', '<')) {
            $grammar = new class extends MysqlGrammar
            {
                public function compileColumnListing($table): string
                {
                    return parent::compileColumnListing($table).' ORDER BY ordinal_position';
                }
            };

            $database->setSchemaGrammar($grammar->setConnection($database)->setTablePrefix($database->getTablePrefix()));
        }

        $columns = $database->getSchemaBuilder()->getColumnListing($table);
        $columns = array_diff($columns, ['id', 'created_at', 'updated_at', 'deleted_at']);

        $fillable = "protected \$fillable = [\n    '".implode("',\n    '", $columns)."'\n];";

        $this->info($fillable);

        if (PHP_OS === 'Darwin') {
            exec('echo '.escapeshellarg($fillable).' | pbcopy');
            $this->info('The fillable array has been copied to your clipboard.');
        }
    }
}
