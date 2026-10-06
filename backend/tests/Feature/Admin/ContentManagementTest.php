<?php

namespace Tests\Feature\Admin;

use App\Enums\MessageStatus;
use App\Enums\Role;
use App\Filament\Pages\SiteSettingsPage;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Jobs\RevalidateFrontend;
use App\Models\Book;
use App\Models\ContactMessage;
use App\Models\Page;
use App\Models\SiteSetting;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ContentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_settings_page_updates_public_site_data(): void
    {
        $this->actingAs($this->userWithRole(Role::Admin));

        Livewire::test(SiteSettingsPage::class)
            ->fillForm([
                'contact' => ['phone' => '+1 404 555 0199', 'email' => 'info@csinc91.com', 'notification_email' => 'team@csinc91.com'],
                'social' => ['links' => [['platform' => 'linkedin', 'url' => 'https://www.linkedin.com/company/csinc91']]],
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertSame('team@csinc91.com', SiteSetting::get('contact.notification_email'));
        $this->getJson('/api/v1/site')
            ->assertJsonPath('data.contact.phone', '+1 404 555 0199')
            ->assertJsonPath('data.social.0.platform', 'linkedin');
    }

    public function test_editor_cannot_open_general_settings(): void
    {
        $this->actingAs($this->userWithRole(Role::Editor));

        // Editors hold the SEO permission only, so they see the page but not the protected tabs.
        Livewire::test(SiteSettingsPage::class)
            ->fillForm(['seo' => ['default_title' => 'New default title'], 'contact' => ['notification_email' => 'attacker@example.com']])
            ->call('save');

        $this->assertSame('New default title', SiteSetting::get('seo.default_title'));
        $this->assertNotSame('attacker@example.com', SiteSetting::get('contact.notification_email'));
    }

    public function test_viewing_an_inquiry_marks_it_read(): void
    {
        $admin = $this->userWithRole(Role::Admin);
        $this->actingAs($admin);
        $message = ContactMessage::factory()->create();

        Livewire::test(ViewContactMessage::class, ['record' => $message->getRouteKey()])
            ->assertSee($message->email)
            ->callAction('mark_replied');

        $message->refresh();
        $this->assertSame(MessageStatus::Replied, $message->status);
        $this->assertSame($admin->id, $message->handled_by);
    }

    public function test_system_pages_cannot_be_deleted_but_custom_pages_can(): void
    {
        $this->actingAs($this->userWithRole(Role::Admin));
        $system = Page::factory()->create(['is_system' => true]);
        $custom = Page::factory()->create();

        Livewire::test(EditPage::class, ['record' => $system->getRouteKey()])->assertActionHidden(DeleteAction::class);
        Livewire::test(EditPage::class, ['record' => $custom->getRouteKey()])->callAction(DeleteAction::class);

        $this->assertNotSoftDeleted($system);
        $this->assertSoftDeleted($custom);
    }

    public function test_editing_page_blocks_updates_the_public_api(): void
    {
        $this->actingAs($this->userWithRole(Role::Admin));
        $page = Page::factory()->create(['slug' => 'about']);

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->fillForm(['title' => 'About CSinc91', 'seo_description' => 'Who we are.'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->getJson('/api/v1/pages/about')
            ->assertJsonPath('data.title', 'About CSinc91')
            ->assertJsonPath('data.seo.description', 'Who we are.');

        Livewire::test(ListPages::class)->assertCanSeeTableRecords([$page]);
    }

    public function test_content_changes_trigger_frontend_revalidation(): void
    {
        Bus::fake([RevalidateFrontend::class]);

        $book = Book::factory()->published()->create(['slug' => 'box-truck-business']);
        $book->update(['title' => 'Box Truck Business Blueprint']);

        Bus::assertDispatched(RevalidateFrontend::class, fn (RevalidateFrontend $job): bool => in_array('book:box-truck-business', $job->tags, true));
    }

    public function test_private_product_files_are_not_publicly_reachable(): void
    {
        Storage::disk('local')->put('books/files/secret.pdf', 'pdf');

        $this->get('/storage/books/files/secret.pdf')->assertForbidden();
    }
}
