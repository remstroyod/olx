<?php

namespace Tests\Unit;

use App\Jobs\OlxPriceJob;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OlxPriceJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    /** @test */
    public function it_can_be_dispatched()
    {
        Queue::fake();

        OlxPriceJob::dispatch();

        Queue::assertPushed(OlxPriceJob::class);
    }

    /** @test */
    public function it_has_correct_job_configuration()
    {
        $job = new OlxPriceJob();
        
        $this->assertEquals(5, $job->tries);
        $this->assertEquals(120, $job->timeout);
    }
}