<?php

namespace App\Console\Commands;

use App\Models\PermissionRequest;
use Illuminate\Console\Command;

class CheckPermissionRequests extends Command
{
    protected $signature = 'app:check-permission-requests';
    protected $description = 'Check permission requests in database';

    public function handle()
    {
        $requests = PermissionRequest::with('user')->get();
        
        $this->info('Total Permission Requests: ' . $requests->count());
        
        foreach ($requests as $request) {
            $this->line('---');
            $this->line('ID: ' . $request->id);
            $this->line('User: ' . $request->user->name . ' (' . $request->user->email . ')');
            $this->line('Permissions: ' . json_encode($request->permissions));
            $this->line('Status: ' . $request->status);
            $this->line('Created: ' . $request->created_at);
        }
    }
}
