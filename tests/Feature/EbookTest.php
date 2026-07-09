<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ebook;
use App\Models\TeacherSubjectAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class EbookTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_ebook_portal(): void
    {
        $response = $this->get(route('teacher.ebook'));

        $response->assertRedirect(route('teacher.login'));
    }

    public function test_authenticated_teacher_can_view_ebook_portal(): void
    {
        User::create([
            'name' => 'AMIS Teacher',
            'email' => 'teacher@amis.edu.ph',
            'username' => 'teacher',
            'password' => Hash::make('teacher123'),
            'role' => 'teacher',
            'account_status' => 'verified',
        ]);

        $response = $this->withSession([
            'teacher_portal_authenticated' => true,
            'teacher_name' => 'AMIS Teacher',
            'teacher_email' => 'teacher@amis.edu.ph',
        ])->get(route('teacher.ebook'));

        $response->assertStatus(200);
        $response->assertSee('eBook Portal');
    }

    public function test_assigned_teacher_can_upload_ebook(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $user = User::create([
            'name' => 'AMIS Teacher',
            'email' => 'teacher@amis.edu.ph',
            'username' => 'teacher',
            'password' => Hash::make('teacher123'),
            'role' => 'teacher',
            'account_status' => 'verified',
        ]);

        // Create mock subject first
        \App\Models\Subject::create([
            'id' => 1,
            'name' => 'Mathematics',
            'code' => 'MATH101',
            'status' => 'active',
        ]);

        // Assign subject to make teacher eligible
        TeacherSubjectAssignment::create([
            'teacher_key' => 'amis-teacher',
            'teacher_name' => 'AMIS Teacher',
            'teacher_email' => 'teacher@amis.edu.ph',
            'subject_id' => 1,
            'status' => 'active',
        ]);

        $pdf = UploadedFile::fake()->create('textbook.pdf', 100, 'application/pdf');
        $cover = UploadedFile::fake()->create('cover.jpg', 100, 'image/jpeg');

        $response = $this->withSession([
            'teacher_portal_authenticated' => true,
            'teacher_name' => 'AMIS Teacher',
            'teacher_email' => 'teacher@amis.edu.ph',
        ])->post(route('teacher.ebook.store'), [
            'title' => 'Arabic Level 4',
            'description' => 'Grade 4 Arabic language book',
            'grade_level' => 'Grade 4',
            'pdf_file' => $pdf,
            'cover_image' => $cover,
            'is_downloadable' => '1',
            'status' => 'published',
        ]);

        $response->assertRedirect(route('teacher.ebook'));
        $this->assertDatabaseHas('ebooks', [
            'title' => 'Arabic Level 4',
            'grade_level' => 'Grade 4',
            'is_downloadable' => true,
            'status' => 'published',
            'created_by' => $user->id,
        ]);

        $book = Ebook::first();
        Storage::disk('local')->assertExists($book->file_path);
        Storage::disk('public')->assertExists($book->cover_image_path);
    }

    public function test_teacher_can_delete_own_ebook(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $user = User::create([
            'name' => 'AMIS Teacher',
            'email' => 'teacher@amis.edu.ph',
            'username' => 'teacher',
            'password' => Hash::make('teacher123'),
            'role' => 'teacher',
            'account_status' => 'verified',
        ]);

        $pdfPath = Storage::disk('local')->put('private/ebooks/dummy.pdf', 'dummy content');
        $coverPath = Storage::disk('public')->put('covers/dummy.jpg', 'dummy image');

        $book = Ebook::create([
            'title' => 'Arabic Level 4',
            'grade_level' => 'Grade 4',
            'file_path' => $pdfPath,
            'cover_image_path' => $coverPath,
            'is_downloadable' => true,
            'status' => 'published',
            'created_by' => $user->id,
        ]);

        $response = $this->withSession([
            'teacher_portal_authenticated' => true,
            'teacher_name' => 'AMIS Teacher',
            'teacher_email' => 'teacher@amis.edu.ph',
        ])->post(route('teacher.ebook.delete', $book->id));

        $response->assertRedirect(route('teacher.ebook'));
        $this->assertDatabaseMissing('ebooks', ['id' => $book->id]);
        Storage::disk('local')->assertMissing($pdfPath);
        Storage::disk('public')->assertMissing($coverPath);
    }

    public function test_read_ebook_generates_sso_token_and_redirects(): void
    {
        $user = User::create([
            'name' => 'AMIS Teacher',
            'email' => 'teacher@amis.edu.ph',
            'username' => 'teacher',
            'password' => Hash::make('teacher123'),
            'role' => 'teacher',
            'account_status' => 'verified',
        ]);

        $book = Ebook::create([
            'title' => 'Arabic Level 4',
            'grade_level' => 'Grade 4',
            'file_path' => 'private/ebooks/dummy.pdf',
            'cover_image_path' => 'covers/dummy.jpg',
            'is_downloadable' => true,
            'status' => 'published',
            'created_by' => $user->id,
        ]);

        $response = $this->withSession([
            'teacher_portal_authenticated' => true,
            'teacher_name' => 'AMIS Teacher',
            'teacher_email' => 'teacher@amis.edu.ph',
        ])->get(route('teacher.ebook.read', $book->id));

        $this->assertDatabaseHas('sso_tokens', [
            'user_id' => $user->id,
            'source_portal' => 'amis_teacher',
        ]);

        $response->assertRedirect();
        $this->assertStringContainsString('/sso/login?sso_token=', $response->headers->get('Location'));
    }
}
