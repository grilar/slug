<?php

namespace Grilar\Slug\Providers;

use Grilar\Base\Events\CreatedContentEvent;
use Grilar\Base\Events\DeletedContentEvent;
use Grilar\Base\Events\UpdatedContentEvent;
use Grilar\Slug\Listeners\CreatedContentListener;
use Grilar\Slug\Listeners\DeletedContentListener;
use Grilar\Slug\Listeners\UpdatedContentListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UpdatedContentEvent::class => [
            UpdatedContentListener::class,
        ],
        CreatedContentEvent::class => [
            CreatedContentListener::class,
        ],
        DeletedContentEvent::class => [
            DeletedContentListener::class,
        ],
    ];
}
