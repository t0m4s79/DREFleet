<?php

namespace App\Http\Controllers;

use App\Enums\Roles;
use App\Models\Backup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class BackupController extends Controller
{
    public function index()
    {
        if (!Gate::allows('view-backups')) {
            abort(403);
        }

        $user = Auth::user();

        if ($user->user_type !== Roles::ADMIN->value) {
            return redirect()->route('dashboard.index')->with('error', 'Erro: Não tem permissões para aceder a esta página');
        }

        $backups = Backup::with('user')
            ->get()
            ->map(function ($backup) {
                $path = storage_path("app/backups/{$backup->filename}");
                $size = file_exists($path) ? filesize($path) : 0;

                return [
                    'id' => $backup->id,
                    'user' => $backup->user->name,
                    'filename' => $backup->filename,
                    'created_at' => $backup->created_at->format('d-m-Y H:i:s'),
                    'size' => $size ? $this->formatSize($size) : 'N/A',
                    'url' => route('backups.show', ['filename' => $backup->filename]),
                    'restore' => $backup->id
                ];
            });

        return Inertia::render('Backups/AllBackups', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'backups' => $backups,
        ]);
    }

    public function createBackup()
    {
        if (!Gate::allows('create-backups')) {
            abort(403);
        }

        Log::channel('user')->info('User accessed create backup page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        $user = Auth::user();

        if ($user->user_type !== Roles::ADMIN->value) {
            return redirect()->route('dashboard.index')->with('error', 'Erro: Não tem permissões para aceder a esta página');
        }

        try {
            DB::beginTransaction();

            Artisan::call('app:database-backup');

            $files = Storage::files('backups');
            $latestBackup = collect($files)->sortDesc()->first();

            if (!$latestBackup) {
                throw new \Exception('Erro ao gerar backup');
            }

            Backup::create([
                'user_id' => auth()->id(),
                'filename' => basename($latestBackup),
            ]);

            DB::commit();

            return redirect()->route('backups.index')->with('message', 'Backup da Base de Dados feita com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('usererror')->error('Error while attempting backup', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('backups.index')->with('error', 'Houve um problema ao realizar um Backup da Base de Dados. Tente novamente.');
        }
    }

    public function downloadBackup($filename)
    {
        if (!Gate::allows('download-backups')) {
            abort(403);
        }

        $user = Auth::user();

        if ($user->user_type !== Roles::ADMIN->value) {
            return redirect()->route('dashboard.index')->with('error', 'Erro: Não tem permissões para aceder a esta página');
        }

        $path = storage_path("app/backups/$filename");

        if (!file_exists($path)) {
            return response()->json(['error' => 'Arquivo não encontrado'], 404);
        }

        return response()->download($path);
    }

    public function deleteBackup($id)
    {
        if (!Gate::allows('delete-backups')) {
            abort(403);
        }

        $user = Auth::user();

        if ($user->user_type !== Roles::ADMIN->value) {
            return redirect()->route('dashboard.index')->with('error', 'Erro: Não tem permissões para aceder a esta página');
        }

        $backup = Backup::find($id);

        if (!$backup) {
            return response()->json(['error' => 'Backup não encontrado'], 404);
        }

        $path = storage_path("app/backups/{$backup->filename}");

        try {
            if (file_exists($path)) {
                unlink($path);
            }

            $backup->delete();

            return redirect()->route('backups.index')->with('message', 'Backup excluído com sucesso!');
        } catch (\Exception $e) {
            Log::channel('usererror')->error('Erro ao excluir backup', [
                'auth_user_id' => auth()->id(),
                'exception' => $e->getMessage(),
            ]);

            return redirect()->route('backups.index')->with('error', 'Erro ao excluir o backup.');
        }
    }

    public function restoreBackup($id)
    {
        if (!Gate::allows('restore-backups')) {
            abort(403);
        }

        $user = Auth::user();
        if ($user->user_type !== Roles::ADMIN->value) {
            return redirect()->route('dashboard.index')->with('error', 'Erro: Não tem permissões para aceder a esta página');
        }

        Log::channel('user')->info('User accessed restore backup page', [
            'auth_user_id' => $user->id,
        ]);

        try {
            $backup = Backup::find($id);
            if (!$backup) {
                return redirect()->route('backups.index')->with('error', 'Erro: Backup não encontrado.');
            }

            $exitCode = Artisan::call('app:database-restore', [
                'filename' => $backup->filename,
            ]);

            if ($exitCode === 0) {
                Log::channel('user')->info('User made a backup restore successfully', [
                    'auth_user_id' => $user->id,
                ]);

                return redirect()->route('backups.index')->with('message', 'Backup restaurado com sucesso!');
            } else {
                Log::channel('usererror')->error('Error restoring backup', [
                    'auth_user_id' => $user->id,
                    'backup_id' => $id,
                ]);

                return redirect()->route('backups.index')->with('error', 'Erro ao restaurar o backup.');
            }
        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error restoring backup', [
                'auth_user_id' => $user->id,
                'backup_id' => $id,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('backups.index')->with('error', 'Erro ao restaurar o backup. Verifique o ficheiro e tente novamente.');
        }
    }

    private function formatSize($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$precision}f", $bytes / pow(1024, $factor)) . ' ' . $units[$factor];
    }

}
