<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EstimateControllerTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected $customer;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Initialize tenant and user for tests
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id
        ]);
        $this->service = Service::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    /** @test */
    public function it_can_list_estimates()
    {
        Estimate::factory(3)->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/estimates');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'estimate_number',
                        'title',
                        'status',
                        'total_amount',
                        'customer',
                        'created_at',
                    ]
                ],
                'pagination'
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function it_can_create_an_estimate()
    {
        $estimateData = [
            'customer_id' => $this->customer->id,
            'title' => 'Test Estimate',
            'description' => 'Test description',
            'status' => 'draft',
            'tax_rate' => 8.5,
            'discount_amount' => 50,
            'notes' => 'Test notes',
            'terms_conditions' => 'Test terms',
            'estimate_items' => [
                [
                    'service_id' => $this->service->id,
                    'description' => 'Test Service',
                    'quantity' => 2,
                    'unit_price' => 100,
                    'total_price' => 200,
                    'notes' => 'Test item notes',
                    'sort_order' => 1,
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/estimates', $estimateData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'estimate_number',
                    'title',
                    'status',
                    'customer',
                    'estimate_items',
                ]
            ]);

        $this->assertDatabaseHas('estimates', [
            'title' => 'Test Estimate',
            'customer_id' => $this->customer->id,
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('estimate_items', [
            'description' => 'Test Service',
            'quantity' => 2,
            'unit_price' => 100,
        ]);
    }

    /** @test */
    public function it_can_show_an_estimate()
    {
        $estimate = Estimate::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/estimates/{$estimate->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'estimate_number',
                    'title',
                    'status',
                    'customer',
                    'estimate_items',
                ]
            ]);

        $this->assertEquals($estimate->id, $response->json('data.id'));
    }

    /** @test */
    public function it_can_update_an_estimate()
    {
        $estimate = Estimate::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        $updateData = [
            'title' => 'Updated Estimate',
            'description' => 'Updated description',
            'status' => 'pending',
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/estimates/{$estimate->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'title',
                    'status',
                ]
            ]);

        $this->assertDatabaseHas('estimates', [
            'id' => $estimate->id,
            'title' => 'Updated Estimate',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function it_can_delete_an_estimate()
    {
        $estimate = Estimate::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/estimates/{$estimate->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Estimate deleted successfully']);

        $this->assertDatabaseMissing('estimates', ['id' => $estimate->id]);
    }

    /** @test */
    public function it_can_search_estimates()
    {
        Estimate::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'title' => 'Plumbing Repair Estimate',
        ]);

        Estimate::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'title' => 'Electrical Work Estimate',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/estimates/search?query=Plumbing');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'status',
                    ]
                ]
            ]);

        $this->assertCount(1, $response->json('data'));
        $this->assertStringContainsString('Plumbing', $response->json('data.0.title'));
    }

    /** @test */
    public function it_can_get_estimates_by_status()
    {
        Estimate::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'status' => 'draft',
        ]);

        Estimate::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'status' => 'sent',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/estimates/status/draft');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('draft', $response->json('data.0.status'));
    }

    /** @test */
    public function it_can_get_estimates_by_customer()
    {
        $customer2 = Customer::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'created_by' => $this->user->id
        ]);

        Estimate::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        Estimate::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $customer2->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/estimates/customer/{$this->customer->id}");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($this->customer->id, $response->json('data.0.customer.id'));
    }

    /** @test */
    public function it_can_mark_estimate_as_sent()
    {
        $estimate = Estimate::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/estimates/{$estimate->id}/mark-sent");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'status',
                    'sent_at',
                ]
            ]);

        $this->assertDatabaseHas('estimates', [
            'id' => $estimate->id,
            'status' => 'sent',
        ]);
    }

    /** @test */
    public function it_can_mark_estimate_as_accepted()
    {
        $estimate = Estimate::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'status' => 'sent',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/estimates/{$estimate->id}/mark-accepted");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'status',
                    'accepted_at',
                ]
            ]);

        $this->assertDatabaseHas('estimates', [
            'id' => $estimate->id,
            'status' => 'accepted',
        ]);
    }

    /** @test */
    public function it_can_mark_estimate_as_rejected()
    {
        $estimate = Estimate::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'status' => 'sent',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/estimates/{$estimate->id}/mark-rejected");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'status',
                    'rejected_at',
                ]
            ]);

        $this->assertDatabaseHas('estimates', [
            'id' => $estimate->id,
            'status' => 'rejected',
        ]);
    }

    /** @test */
    public function it_can_mark_estimate_as_expired()
    {
        $estimate = Estimate::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'status' => 'sent',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/estimates/{$estimate->id}/mark-expired");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'status',
                    'expired_at',
                ]
            ]);

        $this->assertDatabaseHas('estimates', [
            'id' => $estimate->id,
            'status' => 'expired',
        ]);
    }

    /** @test */
    public function it_can_generate_pdf_for_estimate()
    {
        $estimate = Estimate::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/estimates/{$estimate->id}/pdf");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'pdf_content',
            ]);
    }

    /** @test */
    public function it_can_get_expired_estimates()
    {
        Estimate::factory()->expired()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        Estimate::factory()->sent()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/estimates/status/expired');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('expired', $response->json('data.0.status'));
    }

    /** @test */
    public function it_can_get_pending_estimates()
    {
        Estimate::factory()->pending()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        Estimate::factory()->sent()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/estimates/status/pending');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('pending', $response->json('data.0.status'));
    }

    /** @test */
    public function it_can_get_sent_estimates()
    {
        Estimate::factory()->sent()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        Estimate::factory()->draft()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/estimates/status/sent');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('sent', $response->json('data.0.status'));
    }

    /** @test */
    public function it_can_get_accepted_estimates()
    {
        Estimate::factory()->accepted()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        Estimate::factory()->sent()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/estimates/status/accepted');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('accepted', $response->json('data.0.status'));
    }

    /** @test */
    public function it_can_get_rejected_estimates()
    {
        Estimate::factory()->rejected()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        Estimate::factory()->sent()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/estimates/status/rejected');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('rejected', $response->json('data.0.status'));
    }

    /** @test */
    public function it_validates_required_fields_when_creating_estimate()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/estimates', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_id', 'title', 'status']);
    }

    /** @test */
    public function it_validates_customer_exists_when_creating_estimate()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/estimates', [
                'customer_id' => 99999,
                'title' => 'Test Estimate',
                'status' => 'draft',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_id']);
    }

    /** @test */
    public function it_validates_status_is_valid_when_creating_estimate()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/estimates', [
                'customer_id' => $this->customer->id,
                'title' => 'Test Estimate',
                'status' => 'invalid_status',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_estimate()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/estimates/99999');

        $response->assertStatus(404);
    }

            /** @test */
    public function it_prevents_access_to_estimates_from_different_tenant()
    {
        $otherTenant = Tenant::factory()->create();
        $otherUser = User::factory()->forTenant($otherTenant)->create();
        $otherCustomer = Customer::factory()->create([
            'tenant_id' => $otherTenant->id,
            'created_by' => $otherUser->id
        ]);

        $estimate = Estimate::create([
            'tenant_id' => $otherTenant->id,
            'customer_id' => $otherCustomer->id,
            'created_by' => $otherUser->id,
            'title' => 'Test Estimate',
            'description' => 'Test description',
            'status' => 'draft',
            'subtotal' => 100,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 100,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/estimates/{$estimate->id}");

        $response->assertStatus(404);
    }

    /** @test */
    public function it_requires_authentication_for_all_endpoints()
    {
        $estimate = Estimate::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
        ]);

        // Test list endpoint
        $this->getJson('/api/estimates')->assertStatus(401);

        // Test show endpoint
        $this->getJson("/api/estimates/{$estimate->id}")->assertStatus(401);

        // Test create endpoint
        $this->postJson('/api/estimates', [])->assertStatus(401);

        // Test update endpoint
        $this->putJson("/api/estimates/{$estimate->id}", [])->assertStatus(401);

        // Test delete endpoint
        $this->deleteJson("/api/estimates/{$estimate->id}")->assertStatus(401);
    }
}
