<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\DoctorProfile;
use App\Models\Article;
use App\Models\ChatRoom;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $doctorRole = Role::create(['name' => 'doctor']);
        $userRole = Role::create(['name' => 'user']);

        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@mentalhealth.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // Create a sample doctor
        $doctor = User::create([
            'name' => 'Dr. Sarah Johnson',
            'email' => 'doctor@mentalhealth.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_active' => true,
            'phone' => '+1234567890',
        ]);
        $doctor->assignRole('doctor');

        DoctorProfile::create([
            'user_id' => $doctor->id,
            'specialization' => 'Clinical Psychology',
            'license_number' => 'PSY-12345',
            'bio' => 'Experienced clinical psychologist specializing in anxiety and depression treatment.',
            'experience_years' => 10,
            'hourly_rate' => 150.00,
            'is_verified' => true,
            'verified_at' => now(),
            'languages' => ['English', 'Spanish'],
            'consultation_types' => ['video', 'chat'],
        ]);

        // Create a sample user
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'user@mentalhealth.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole('user');

        // Create sample articles
        Article::create([
            'title' => 'Understanding Anxiety: A Complete Guide',
            'slug' => 'understanding-anxiety-complete-guide',
            'content' => '<p>Anxiety is a natural response to stress...</p>',
            'excerpt' => 'Learn about anxiety, its symptoms, and effective coping strategies.',
            'category' => 'anxiety',
            'author_id' => $admin->id,
            'reading_time' => 10,
            'is_published' => true,
            'published_at' => now(),
        ]);

        Article::create([
            'title' => 'Building Healthy Sleep Habits',
            'slug' => 'building-healthy-sleep-habits',
            'content' => '<p>Quality sleep is essential for mental health...</p>',
            'excerpt' => 'Discover techniques to improve your sleep quality.',
            'category' => 'sleep',
            'author_id' => $admin->id,
            'reading_time' => 8,
            'is_published' => true,
            'published_at' => now(),
        ]);

        // Create sample chat rooms
        ChatRoom::create([
            'name' => 'General Support',
            'description' => 'A safe space to share and support each other.',
            'type' => 'support',
            'is_private' => false,
            'created_by' => $admin->id,
        ]);

        ChatRoom::create([
            'name' => 'Anxiety Support Group',
            'description' => 'For those dealing with anxiety to connect and share experiences.',
            'type' => 'support',
            'is_private' => false,
            'created_by' => $admin->id,
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin login: admin@mentalhealth.com / password');
        $this->command->info('Doctor login: doctor@mentalhealth.com / password');
        $this->command->info('User login: user@mentalhealth.com / password');
    }
}
