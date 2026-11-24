<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAndPublicBlogTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected Blog $publishedPost;
    protected Blog $draftPost;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->customer = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);

        $this->publishedPost = Blog::create([
            'title' => 'Top 5 Traditional Bengali Spices for Health',
            'slug' => 'top-5-traditional-bengali-spices-for-health',
            'excerpt' => 'A guide to authentic turmeric, mustard, and panch phoron from Bengal farms.',
            'content' => 'Bengali spices have been celebrated for centuries. Panch phoron, mustard seeds, and freshly ground turmeric offer immense health benefits...',
            'category' => 'Spices & Health',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'meta_title' => 'Bengali Spices for Health & Immunity | Dua Re Dokandar',
            'meta_description' => 'Learn how authentic mustard seeds and turmeric boost immunity with recipes.',
            'meta_keywords' => 'bengali spices, panch phoron, mustard oil, health tips',
            'author_id' => $this->admin->id,
            'author_name' => $this->admin->name,
        ]);

        $this->draftPost = Blog::create([
            'title' => 'Upcoming Winter Harvest Specials (Draft)',
            'slug' => 'upcoming-winter-harvest-specials-draft',
            'excerpt' => 'Sneak peek into winter specials.',
            'content' => 'This is a draft post that should not be visible to the public.',
            'category' => 'Farm News',
            'status' => 'draft',
            'published_at' => null,
            'author_id' => $this->admin->id,
        ]);
    }

    public function test_customer_can_view_public_blog_index_without_login(): void
    {
        $response = $this->get(route('blog.index'));

        $response->assertStatus(200);
        $response->assertSee('The Dokandar Blog');
        $response->assertSee('Top 5 Traditional Bengali Spices for Health');
        $response->assertSee('Spices & Health');
        // Draft post should NOT be visible to public
        $response->assertDontSee('Upcoming Winter Harvest Specials (Draft)');
    }

    public function test_customer_can_search_and_filter_blogs(): void
    {
        // Search query matching
        $response = $this->get(route('blog.index', ['q' => 'turmeric']));
        $response->assertStatus(200);
        $response->assertSee('Top 5 Traditional Bengali Spices for Health');

        // Search query with no match
        $responseNoMatch = $this->get(route('blog.index', ['q' => 'nonexistentkeywordxyz']));
        $responseNoMatch->assertStatus(200);
        $responseNoMatch->assertSee('No articles found');

        // Filter by category
        $responseCat = $this->get(route('blog.index', ['category' => 'Spices & Health']));
        $responseCat->assertStatus(200);
        $responseCat->assertSee('Top 5 Traditional Bengali Spices for Health');
    }

    public function test_customer_can_view_single_blog_post_with_seo_meta(): void
    {
        $initialViews = $this->publishedPost->views;

        $response = $this->get(route('blog.show', $this->publishedPost->slug));

        $response->assertStatus(200);
        $response->assertSee('Top 5 Traditional Bengali Spices for Health');
        $response->assertSee('Bengali Spices for Health & Immunity | Dua Re Dokandar'); // Meta Title
        $response->assertSee('Learn how authentic mustard seeds and turmeric boost immunity with recipes.'); // Meta Description
        $response->assertSee('bengali spices, panch phoron, mustard oil, health tips'); // Keywords
        $response->assertSee('og:title', false);
        $response->assertSee('twitter:card', false);
        $response->assertSee('application/ld+json', false);

        // Verify view counter incremented
        $this->assertEquals($initialViews + 1, $this->publishedPost->fresh()->views);
    }

    public function test_draft_blog_post_returns_404_to_public(): void
    {
        $response = $this->get(route('blog.show', $this->draftPost->slug));
        $response->assertStatus(404);
    }

    public function test_non_admin_cannot_access_admin_blog_routes(): void
    {
        // Guest
        $this->get(route('admin.blogs.index'))->assertRedirect(route('login'));

        // Regular user
        $this->actingAs($this->customer)->get(route('admin.blogs.index'))->assertStatus(403);
        $this->actingAs($this->customer)->get(route('admin.blogs.create'))->assertStatus(403);
    }

    public function test_admin_can_view_blog_index_and_create_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.blogs.index'));
        $response->assertStatus(200);
        $response->assertSee('Blog Posts & SEO Content', false);
        $response->assertSee('Top 5 Traditional Bengali Spices for Health');
        $response->assertSee('Upcoming Winter Harvest Specials (Draft)'); // Admin can see drafts!

        $createResponse = $this->actingAs($this->admin)->get(route('admin.blogs.create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('Create New Blog Article');
    }

    public function test_admin_can_create_blog_article_with_seo(): void
    {
        $data = [
            'title' => 'Pure Cold-Pressed Kachi Ghani Mustard Oil Guide',
            'slug' => 'pure-cold-pressed-mustard-oil-guide',
            'excerpt' => 'Everything you need to know about traditional cold pressed mustard oil.',
            'content' => 'Cold pressed mustard oil retains natural aroma, pungency and essential fatty acids...',
            'category' => 'Grocery Tips',
            'status' => 'published',
            'published_at' => now()->format('Y-m-d H:i:s'),
            'meta_title' => 'Authentic Mustard Oil Guide | Dokandar',
            'meta_description' => 'Understand how pure cold pressed oil is made in Deganga.',
            'meta_keywords' => 'mustard oil, cold pressed, kachi ghani, deganga',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.blogs.store'), $data);

        $response->assertRedirect(route('admin.blogs.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('blogs', [
            'title' => 'Pure Cold-Pressed Kachi Ghani Mustard Oil Guide',
            'slug' => 'pure-cold-pressed-mustard-oil-guide',
            'category' => 'Grocery Tips',
            'status' => 'published',
            'meta_title' => 'Authentic Mustard Oil Guide | Dokandar',
        ]);
    }

    public function test_admin_can_update_blog_article(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.blogs.update', $this->publishedPost->id), [
            'title' => 'Updated Top 7 Traditional Bengali Spices',
            'slug' => 'updated-top-7-bengali-spices',
            'excerpt' => 'Updated excerpt content for spices.',
            'content' => 'Updated long form article content with 7 spices...',
            'category' => 'Health & Spices',
            'status' => 'published',
            'meta_title' => 'Updated SEO Title',
            'meta_description' => 'Updated SEO Description',
            'meta_keywords' => 'spices, updated',
        ]);

        $response->assertRedirect(route('admin.blogs.index'))
            ->assertSessionHas('success');

        $fresh = $this->publishedPost->fresh();
        $this->assertEquals('Updated Top 7 Traditional Bengali Spices', $fresh->title);
        $this->assertEquals('updated-top-7-bengali-spices', $fresh->slug);
        $this->assertEquals('Health & Spices', $fresh->category);
    }

    public function test_admin_can_delete_blog_article(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.blogs.destroy', $this->draftPost->id));

        $response->assertRedirect(route('admin.blogs.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('blogs', ['id' => $this->draftPost->id]);
    }

    public function test_navbar_and_homepage_contain_blog_links(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee(route('blog.index'));
        $response->assertSee('Blog Articles');
    }
}
