<?php

namespace Tests\Feature;

use App\Http\Handlers\Subscribe\SubscribeStoreHandler;
use App\Http\Requests\SubscribeStoreRequest;
use App\Models\Subscribe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SubscribeStoreHandlerTest extends TestCase
{
    use RefreshDatabase;

    private SubscribeStoreHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new SubscribeStoreHandler();
        Notification::fake();
    }

    /** @test */
    public function it_creates_new_user_and_subscription()
    {
        $requestData = [
            'email' => 'test@example.com',
            'url' => 'https://www.olx.ua/item/123',
            'price' => '10000',
            'currency' => 'UAH',
            'title' => 'Test Product'
        ];

        $request = $this->createMockRequest($requestData);
        
        $response = $this->handler->process($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue(Hash::check('test@example.com', $user->password));
        $this->assertNull($user->email_verified_at);

        $this->assertDatabaseHas('subscribes', [
            'user_id' => $user->id,
            'url' => 'https://www.olx.ua/item/123',
            'price' => '10000',
            'currency' => 'UAH',
            'title' => 'Test Product'
        ]);
    }

    /** @test */
    public function it_updates_existing_user_and_creates_new_subscription()
    {
        $existingUser = User::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Old Name'
        ]);

        $requestData = [
            'email' => 'test@example.com',
            'url' => 'https://www.olx.ua/item/456',
            'price' => '20000',
            'currency' => 'UAH',
            'title' => 'Another Product'
        ];

        $request = $this->createMockRequest($requestData);
        
        $this->handler->process($request);

        $this->assertDatabaseCount('users', 1);
        
        $user = User::where('email', 'test@example.com')->first();
        $this->assertEquals('test@example.com', $user->name);

        $this->assertDatabaseHas('subscribes', [
            'user_id' => $user->id,
            'url' => 'https://www.olx.ua/item/456',
        ]);
    }

    /** @test */
    public function it_updates_existing_subscription()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $existingSubscription = Subscribe::factory()->create([
            'user_id' => $user->id,
            'url' => 'https://www.olx.ua/item/123',
            'price' => '10000',
            'title' => 'Old Title'
        ]);

        $requestData = [
            'email' => 'test@example.com',
            'url' => 'https://www.olx.ua/item/123',
            'price' => '15000',
            'currency' => 'UAH',
            'title' => 'Updated Title'
        ];

        $request = $this->createMockRequest($requestData);
        
        $this->handler->process($request);

        $this->assertDatabaseCount('subscribes', 1);
        
        $existingSubscription->refresh();
        $this->assertEquals('15000', $existingSubscription->price);
        $this->assertEquals('Updated Title', $existingSubscription->title);
    }

    /** @test */
    public function it_returns_success_for_verified_user()
    {
        $user = User::factory()->create([
            'email' => 'verified@example.com',
            'email_verified_at' => now()
        ]);

        $requestData = [
            'email' => 'verified@example.com',
            'url' => 'https://www.olx.ua/item/123',
            'price' => '10000',
            'currency' => 'UAH',
            'title' => 'Test Product'
        ];

        $request = $this->createMockRequest($requestData);
        
        $response = $this->handler->process($request);

        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['status']);
        $this->assertEquals('Subscription successfully created', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
    }

    /** @test */
    public function it_returns_error_for_unverified_user()
    {
        $requestData = [
            'email' => 'unverified@example.com',
            'url' => 'https://www.olx.ua/item/123',
            'price' => '10000',
            'currency' => 'UAH',
            'title' => 'Test Product'
        ];

        $request = $this->createMockRequest($requestData);
        
        $response = $this->handler->process($request);

        $this->assertEquals(422, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['status']);
        $this->assertEquals('To subscribe you must confirm your email. Please check your mail.', $responseData['message']);
    }

    /** @test */
    public function it_handles_exception_gracefully()
    {
        $request = $this->createMockRequest([
            'email' => null,
            'url' => 'https://www.olx.ua/item/123'
        ]);

        $response = $this->handler->process($request);

        $this->assertEquals(422, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['status']);
        $this->assertArrayHasKey('message', $responseData);
    }

    /** @test */
    public function it_sends_email_verification_for_new_unverified_user()
    {
        $requestData = [
            'email' => 'newuser@example.com',
            'url' => 'https://www.olx.ua/item/123',
            'price' => '10000',
            'currency' => 'UAH',
            'title' => 'Test Product'
        ];

        $request = $this->createMockRequest($requestData);
        
        $this->handler->process($request);

        $user = User::where('email', 'newuser@example.com')->first();
        
        Notification::assertSentTo(
            $user,
            \Illuminate\Auth\Notifications\VerifyEmail::class
        );
    }

    /** @test */
    public function it_creates_multiple_subscriptions_for_same_user()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $subscriptions = [
            [
                'email' => 'test@example.com',
                'url' => 'https://www.olx.ua/item/123',
                'price' => '10000',
                'currency' => 'UAH',
                'title' => 'Product 1'
            ],
            [
                'email' => 'test@example.com',
                'url' => 'https://www.olx.ua/item/456',
                'price' => '20000',
                'currency' => 'UAH',
                'title' => 'Product 2'
            ]
        ];

        foreach ($subscriptions as $subscriptionData) {
            $request = $this->createMockRequest($subscriptionData);
            $this->handler->process($request);
        }

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('subscribes', 2);
        
        $this->assertDatabaseHas('subscribes', ['url' => 'https://www.olx.ua/item/123']);
        $this->assertDatabaseHas('subscribes', ['url' => 'https://www.olx.ua/item/456']);
    }

    private function createMockRequest(array $data): SubscribeStoreRequest
    {
        $request = $this->mock(SubscribeStoreRequest::class);
        
        $request->shouldReceive('get')
            ->with('email')
            ->andReturn($data['email'] ?? null);
            
        $request->shouldReceive('get')
            ->with('url')
            ->andReturn($data['url'] ?? null);
            
        $request->shouldReceive('validated')
            ->andReturn($data);

        return $request;
    }
}