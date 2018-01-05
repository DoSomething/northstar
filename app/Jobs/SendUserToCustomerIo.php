<?php

namespace Northstar\Jobs;

use Northstar\Models\User;
use Illuminate\Bus\Queueable;
use Northstar\Services\CustomerIo;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendUserToCustomerIo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The serialized user model.
     *
     * @var User
     */
    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(CustomerIo $customerIo)
    {
        // Rate limit Customer.io API requests to 10/s.
        $throttler = Redis::throttle('customerio')->allow(10)->every(1);
        $throttler->then(function () use ($customerIo) {
            $success = $customerIo->updateProfile($this->user);

            if (! $success) {
                throw new \Exception('Failed to backfill user '.$this->user->id);
            }

            // @NOTE: Queue runner does not register model events, so this will
            // not hit Blink. See 'AppServiceProvider' for disabled model event.
            $this->user->cio_full_backfill = true;
            $this->user->save(['timestamps' => false]);
        }, function () {
            // Could not obtain lock... release to the queue.
            return $this->release(10);
        });
    }
}