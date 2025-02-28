<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:database-backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria um backup da base de dados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = 'backup-' . now()->format('Y-m-d_H-i-s') . '.sql';
        $path = storage_path("app/backups/$filename");
        $zipPath = storage_path("app/backups/$filename.zip");

        if (!is_dir(storage_path("app/backups"))) {
            mkdir(storage_path("app/backups"), 0755, true);
        }

        $mysqldumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';

        $command = sprintf(
            '"%s" --user=%s --password=%s --host=%s %s > %s',
            $mysqldumpPath,
            env('DB_USERNAME'),
            env('DB_PASSWORD'),
            env('DB_HOST'),
            env('DB_DATABASE'),
            $path
        );

        shell_exec($command);

        if (!file_exists($path)) {
            $this->error('Erro ao criar backup');
            return 1;
        }

        shell_exec("powershell Compress-Archive -Path $path -DestinationPath $zipPath");

        if (!file_exists($zipPath)) {
            $this->error('Erro ao comprimir backup');
            return 1;
        }

        unlink($path);

        $this->info("Backup criado e comprimido com sucesso: " . basename($zipPath));
        return 0;
    }
}
