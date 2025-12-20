<?php

/**
 * Laravel Usage Examples
 * 
 * This file contains examples of how to use the Dropbox SDK in Laravel applications.
 * Copy these examples into your Laravel controllers, commands, or jobs.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tigusigalpa\Dropbox\Facades\Dropbox;
use Tigusigalpa\Dropbox\DropboxClient;
use Tigusigalpa\Dropbox\Exceptions\DropboxException;

class DropboxController extends Controller
{
    /**
     * Upload a file using the facade
     */
    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('file');
            $content = file_get_contents($file->path());
            $filename = $file->getClientOriginalName();
            
            $result = Dropbox::files()->upload(
                '/uploads/' . $filename,
                $content,
                'add',
                true // autorename if exists
            );

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'file' => $result,
            ]);

        } catch (DropboxException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List files using dependency injection
     */
    public function listFiles(DropboxClient $dropbox)
    {
        try {
            $result = $dropbox->files->listFolder('/uploads');
            
            $files = collect($result['entries'])->map(function ($entry) {
                return [
                    'name' => $entry['name'],
                    'type' => $entry['.tag'],
                    'size' => $entry['size'] ?? null,
                    'modified' => $entry['client_modified'] ?? null,
                ];
            });

            return view('dropbox.files', compact('files'));

        } catch (DropboxException $e) {
            return back()->with('error', 'Failed to list files: ' . $e->getMessage());
        }
    }

    /**
     * Download a file
     */
    public function downloadFile(string $path)
    {
        try {
            $file = Dropbox::files()->download($path);
            
            return response($file['content'])
                ->header('Content-Type', $file['metadata']['mime_type'] ?? 'application/octet-stream')
                ->header('Content-Disposition', 'attachment; filename="' . basename($path) . '"');

        } catch (DropboxException $e) {
            abort(404, 'File not found');
        }
    }

    /**
     * Create a shared link
     */
    public function shareFile(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        try {
            $link = Dropbox::sharing()->createSharedLinkWithSettings(
                $request->path,
                [
                    'requested_visibility' => ['.tag' => 'public'],
                    'audience' => ['.tag' => 'public'],
                    'access' => ['.tag' => 'viewer'],
                ]
            );

            return response()->json([
                'success' => true,
                'url' => $link['url'],
            ]);

        } catch (DropboxException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get account information
     */
    public function accountInfo()
    {
        try {
            $account = Dropbox::users()->getCurrentAccount();
            $space = Dropbox::users()->getSpaceUsage();

            return view('dropbox.account', compact('account', 'space'));

        } catch (DropboxException $e) {
            return back()->with('error', 'Failed to get account info: ' . $e->getMessage());
        }
    }

    /**
     * Search files
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1',
        ]);

        try {
            $results = Dropbox::files()->search(
                $request->query,
                '/uploads',
                50
            );

            $files = collect($results['matches'])->map(function ($match) {
                return $match['metadata']['metadata'];
            });

            return response()->json([
                'success' => true,
                'results' => $files,
            ]);

        } catch (DropboxException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

/**
 * Laravel Job Example - Backup files to Dropbox
 */
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Tigusigalpa\Dropbox\Facades\Dropbox;
use Illuminate\Support\Facades\Storage;

class BackupToDropbox implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $backupFolder = '/Backups/' . now()->format('Y-m-d');
        
        // Create backup folder
        Dropbox::files()->createFolder($backupFolder);

        // Backup database
        $dbBackup = Storage::disk('local')->get('backups/database.sql');
        Dropbox::files()->upload($backupFolder . '/database.sql', $dbBackup);

        // Backup files
        $files = Storage::disk('local')->allFiles('backups');
        foreach ($files as $file) {
            $content = Storage::disk('local')->get($file);
            Dropbox::files()->upload($backupFolder . '/' . basename($file), $content);
        }

        \Log::info('Backup to Dropbox completed', ['folder' => $backupFolder]);
    }
}

/**
 * Laravel Command Example
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Tigusigalpa\Dropbox\Facades\Dropbox;

class SyncToDropbox extends Command
{
    protected $signature = 'dropbox:sync {path}';
    protected $description = 'Sync local folder to Dropbox';

    public function handle()
    {
        $localPath = $this->argument('path');
        $remotePath = '/Synced/' . basename($localPath);

        $this->info("Syncing $localPath to Dropbox...");

        $files = \File::allFiles($localPath);
        $bar = $this->output->createProgressBar(count($files));

        foreach ($files as $file) {
            $relativePath = str_replace($localPath, '', $file->getPathname());
            $content = file_get_contents($file->getPathname());
            
            Dropbox::files()->upload(
                $remotePath . $relativePath,
                $content,
                'overwrite'
            );
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Sync completed!');
    }
}
