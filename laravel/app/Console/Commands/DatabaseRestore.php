<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DatabaseRestore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:database-restore {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restaura um backup da base de dados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = $this->argument('filename');
        $zipPath = storage_path("app/backups/$filename");

        if (!file_exists($zipPath)) {
            $this->error("Erro: O arquivo não foi encontrado ($filename)");
            return 1;
        }

        $extractPath = storage_path('app/backups/temp');
        if (!is_dir($extractPath)) {
            mkdir($extractPath, 0755, true);
        }

        $zip = new \ZipArchive;
        if ($zip->open($zipPath) === true) {
            $zip->extractTo($extractPath);
            $zip->close();
        } else {
            $this->error("Erro ao extrair o backup.");
            return 1;
        }

        $sqlFile = collect(scandir($extractPath))->first(fn($file) => str_ends_with($file, '.sql'));
        if (!$sqlFile) {
            $this->error("Erro: Nenhum ficheiro SQL encontrado no backup.");
            return 1;
        }

        $sqlFilePath = "$extractPath/$sqlFile";

        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPass = env('DB_PASSWORD');
        $dbHost = env('DB_HOST');

        $mysqlPath = 'C:\\xampp\\mysql\\bin\\mysql.exe';

        $command = sprintf(
            '"%s" --host=%s --user=%s --password=%s %s < "%s"',
            $mysqlPath,
            $dbHost,
            $dbUser,
            $dbPass,
            $dbName,
            $sqlFilePath
        );

        shell_exec($command);

        unlink($sqlFilePath);
        rmdir($extractPath);

        $this->info("Backup restaurado com sucesso!");
        return 0;
    }
}
