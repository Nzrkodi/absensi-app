<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateDefaultAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create-default 
                            {--reset : Reset password to default}
                            {--force : Force create even if user exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or reset default super admin account (Aditya Wahyu)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = 'aditya.wahyu@smaitpersis.sch.id';
        $defaultPassword = 'admin123456';
        
        $this->info('🔧 Creating/Updating Default Super Admin Account...');
        $this->newLine();
        
        // Check if user exists
        $existingUser = User::where('email', $email)->first();
        
        if ($existingUser && !$this->option('force') && !$this->option('reset')) {
            $this->warn('⚠️  User already exists!');
            $this->info('📧 Email: ' . $existingUser->email);
            $this->info('👤 Name: ' . $existingUser->name);
            $this->newLine();
            
            if ($this->confirm('Do you want to reset the password?')) {
                $existingUser->update([
                    'password' => Hash::make($defaultPassword)
                ]);
                $this->info('✅ Password reset successfully!');
            } else {
                $this->info('ℹ️  No changes made.');
                return;
            }
        } else {
            // Create or update user
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => 'Aditya Wahyu',
                    'email' => $email,
                    'password' => Hash::make($defaultPassword),
                    'role' => 'admin',
                    'position' => 'Super Administrator',
                    'subject' => 'Sistem Administrator',
                    'email_verified_at' => now(),
                ]
            );
            
            $action = $existingUser ? 'updated' : 'created';
            $this->info("✅ Default Super Admin {$action} successfully!");
        }
        
        $this->newLine();
        $this->info('📋 Login Credentials:');
        $this->table(
            ['Field', 'Value'],
            [
                ['Email', $email],
                ['Password', $defaultPassword],
                ['Role', 'Super Admin'],
                ['School', 'SMA IT Persis Palu'],
            ]
        );
        
        $this->newLine();
        $this->warn('🔐 SECURITY REMINDER:');
        $this->warn('   • Change the default password immediately after first login');
        $this->warn('   • This account has full system access');
        $this->warn('   • Keep these credentials secure');
        
        $this->newLine();
        $this->info('🚀 You can now login to the system!');
        
        return Command::SUCCESS;
    }
}