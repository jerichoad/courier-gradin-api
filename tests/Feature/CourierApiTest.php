<?php

use App\Models\Courier;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function courierPayload(array $overrides = []): array
{
    return array_merge([
        'courier_code' => 'CR001',
        'courier_name' => 'Budiono Hadi Agung',
        'courier_phone' => '081234567890',
        'courier_email' => 'budi@example.test',
        'courier_level' => 2,
        'courier_address' => 'Jl. Merdeka 10',
        'is_active' => true,
    ], $overrides);
}

describe('store', function () {
    it('creates a courier', function () {
        $response = $this->postJson('/api/couriers', courierPayload());

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Courier created successfully.',
                'data' => [
                    'courier_code' => 'CR001',
                    'courier_name' => 'Budiono Hadi Agung',
                    'courier_level' => 2,
                ],
            ]);

        $this->assertDatabaseHas('m_courier', ['courier_code' => 'CR001']);
    });

    it('rejects invalid input', function () {
        $response = $this->postJson('/api/couriers', courierPayload([
            'courier_code' => '',
            'courier_name' => 'ab',
            'courier_email' => 'not-an-email',
            'courier_level' => 9,
        ]));

        $response->assertStatus(422)
            ->assertJson(['success' => false, 'message' => 'Validation failed.'])
            ->assertJsonValidationErrors(['courier_code', 'courier_name', 'courier_email', 'courier_level']);
    });

    it('rejects duplicate courier_code', function () {
        Courier::factory()->create(['courier_code' => 'CR001']);

        $this->postJson('/api/couriers', courierPayload())
            ->assertStatus(422)
            ->assertJsonValidationErrors('courier_code');
    });
});

describe('index', function () {
    it('paginates 15 items per page', function () {
        Courier::factory()->count(20)->create();

        $response = $this->getJson('/api/couriers');

        $response->assertOk()
            ->assertJsonPath('data.per_page', 15)
            ->assertJsonPath('data.total', 20)
            ->assertJsonCount(15, 'data.data');

        $this->getJson('/api/couriers?page=2')
            ->assertOk()
            ->assertJsonPath('data.current_page', 2)
            ->assertJsonCount(5, 'data.data');
    });

    it('accepts a custom per_page', function () {
        Courier::factory()->count(10)->create();

        $this->getJson('/api/couriers?per_page=5')
            ->assertOk()
            ->assertJsonPath('data.per_page', 5)
            ->assertJsonCount(5, 'data.data');
    });

    it('rejects an out of range per_page', function () {
        $this->getJson('/api/couriers?per_page=500')
            ->assertStatus(422)
            ->assertJsonPath('message', 'Validation failed.')
            ->assertJsonValidationErrors('per_page');
    });

    it('rejects an invalid level filter', function () {
        $this->getJson('/api/couriers?level=9')
            ->assertStatus(422)
            ->assertJsonValidationErrors('level');
    });

    it('exposes only the courier resource fields', function () {
        Courier::factory()->create();

        $courier = $this->getJson('/api/couriers')->json('data.data.0');

        expect(array_keys($courier))->toBe([
            'courier_id',
            'courier_code',
            'courier_name',
            'courier_phone',
            'courier_email',
            'courier_level',
            'courier_address',
            'is_active',
            'created_at',
            'updated_at',
        ]);
    });

    it('sorts by courier_name ascending by default', function () {
        Courier::factory()->create(['courier_name' => 'Charlie']);
        Courier::factory()->create(['courier_name' => 'Alpha']);
        Courier::factory()->create(['courier_name' => 'Bravo']);

        $names = $this->getJson('/api/couriers')->json('data.data.*.courier_name');

        expect($names)->toBe(['Alpha', 'Bravo', 'Charlie']);
    });

    it('sorts by created_at ascending and descending', function () {
        $old = Courier::factory()->create(['courier_name' => 'Zulu', 'created_at' => now()->subDays(2)]);
        $new = Courier::factory()->create(['courier_name' => 'Alpha', 'created_at' => now()]);

        $ascending = $this->getJson('/api/couriers?sort=created_at')->json('data.data.*.courier_id');
        expect($ascending)->toBe([$old->courier_id, $new->courier_id]);

        $descending = $this->getJson('/api/couriers?sort=-created_at')->json('data.data.*.courier_id');
        expect($descending)->toBe([$new->courier_id, $old->courier_id]);
    });

    it('searches courier_name by every keyword', function () {
        $target = Courier::factory()->create(['courier_name' => 'Budiono Hadi Agung']);
        Courier::factory()->create(['courier_name' => 'Budiono Santoso']);
        Courier::factory()->create(['courier_name' => 'Agung Pratama']);

        $response = $this->getJson('/api/couriers?search=budi+agung');

        $response->assertOk()
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.data.0.courier_id', $target->courier_id);
    });

    it('filters by multiple levels', function () {
        Courier::factory()->create(['courier_level' => 1]);
        Courier::factory()->create(['courier_level' => 2]);
        Courier::factory()->create(['courier_level' => 3]);
        Courier::factory()->create(['courier_level' => 5]);

        $levels = $this->getJson('/api/couriers?level=2,3')->json('data.data.*.courier_level');

        expect($levels)->toHaveCount(2)
            ->and($levels)->toEqualCanonicalizing([2, 3]);
    });
});

describe('show', function () {
    it('returns an existing courier', function () {
        $courier = Courier::factory()->create();

        $this->getJson("/api/couriers/{$courier->courier_id}")
            ->assertOk()
            ->assertJsonPath('data.courier_id', $courier->courier_id)
            ->assertJsonPath('success', true);
    });

    it('returns 404 for a missing courier', function () {
        $this->getJson('/api/couriers/999')
            ->assertNotFound()
            ->assertExactJson(['success' => false, 'message' => 'Courier not found.']);
    });
});

describe('update', function () {
    it('updates a courier', function () {
        $courier = Courier::factory()->create(['courier_code' => 'CR001']);

        $this->putJson("/api/couriers/{$courier->courier_id}", courierPayload([
            'courier_name' => 'Sinta Dewi',
            'courier_level' => 4,
        ]))
            ->assertOk()
            ->assertJsonPath('message', 'Courier updated successfully.')
            ->assertJsonPath('data.courier_name', 'Sinta Dewi')
            ->assertJsonPath('data.courier_level', 4);

        $this->assertDatabaseHas('m_courier', [
            'courier_id' => $courier->courier_id,
            'courier_name' => 'Sinta Dewi',
            'courier_level' => 4,
        ]);
    });

    it('rejects invalid input', function () {
        $courier = Courier::factory()->create();

        $this->putJson("/api/couriers/{$courier->courier_id}", courierPayload([
            'courier_name' => 'ab',
            'courier_level' => 0,
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['courier_name', 'courier_level']);
    });

    it('keeps courier_code unique but ignores its own record', function () {
        $first = Courier::factory()->create(['courier_code' => 'CR001']);
        $second = Courier::factory()->create(['courier_code' => 'CR002']);

        $this->putJson("/api/couriers/{$second->courier_id}", courierPayload(['courier_code' => 'CR001']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('courier_code');

        $this->putJson("/api/couriers/{$first->courier_id}", courierPayload(['courier_code' => 'CR001']))
            ->assertOk();
    });

    it('returns 404 for a missing courier', function () {
        $this->putJson('/api/couriers/999', courierPayload())
            ->assertNotFound()
            ->assertExactJson(['success' => false, 'message' => 'Courier not found.']);
    });
});

describe('destroy', function () {
    it('deletes a courier and removes it from the database', function () {
        $courier = Courier::factory()->create();

        $this->deleteJson("/api/couriers/{$courier->courier_id}")
            ->assertOk()
            ->assertJsonPath('message', 'Courier deleted successfully.');

        $this->assertDatabaseMissing('m_courier', ['courier_id' => $courier->courier_id]);
    });

    it('returns 404 for a missing courier', function () {
        $this->deleteJson('/api/couriers/999')
            ->assertNotFound()
            ->assertExactJson(['success' => false, 'message' => 'Courier not found.']);
    });
});
