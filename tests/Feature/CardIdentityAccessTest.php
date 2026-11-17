<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

class CardIdentityAccessTest extends TestCase
{
    public function test_non_staff_user_cannot_open_card_printing(): void
    {
        $this->actingAs(new User(['role' => 'user']))
            ->get('/kartu-identitas')
            ->assertForbidden();
    }

    public function test_non_admin_cannot_access_template_management_endpoint(): void
    {
        $this->actingAs(new User(['role' => 'staff']))
            ->post('/kartu-identitas/template/pelajar/depan')
            ->assertRedirect('/404');
    }

    public function test_guest_cannot_access_template_management_endpoint(): void
    {
        $this->post('/kartu-identitas/template/pelajar/depan')
            ->assertRedirect('/404');
    }

    public function test_guest_opening_activation_url_is_sent_to_login(): void
    {
        $this->get('/kartu-identitas/template/12/aktifkan')
            ->assertRedirect('/login');
    }

    public function test_non_admin_opening_activation_url_is_forbidden(): void
    {
        $this->actingAs(new User(['role' => 'staff']))
            ->get('/kartu-identitas/template/12/aktifkan')
            ->assertForbidden();
    }

    public function test_admin_upload_rejects_non_image_content(): void
    {
        $this->actingAs(new User(['role' => 'admin']))
            ->post('/kartu-identitas/template/pelajar/depan', [
                'template' => UploadedFile::fake()->createWithContent('template.jpg', 'This is not an image.'),
            ])
            ->assertSessionHasErrors('template');
    }

    public function test_admin_upload_rejects_image_content_with_mismatched_extension(): void
    {
        $png = file_get_contents(base_path('public/dokumentasi/template-kartu/Kartu guru — depan.png'));

        $this->actingAs(new User(['role' => 'admin']))
            ->post('/kartu-identitas/template/pelajar/depan', [
                'template' => UploadedFile::fake()->createWithContent('template.jpg', $png),
            ])
            ->assertSessionHasErrors('template');
    }

    public function test_selection_above_batch_limit_is_rejected_before_loading_people(): void
    {
        $ids = array_map(fn () => (string) Str::uuid(), range(1, 501));

        $this->actingAs(new User(['role' => 'admin']))
            ->post('/kartu-identitas/pratinjau', ['jenis' => 'pelajar', 'ids' => $ids])
            ->assertSessionHasErrors('ids');
    }

    public function test_non_staff_user_cannot_generate_all_cards(): void
    {
        $this->actingAs(new User(['role' => 'user']))
            ->post('/kartu-identitas/generate-all', ['jenis' => 'pelajar'])
            ->assertForbidden();
    }
}
