<?php

namespace Grilar\Slug\Events;

use Grilar\Base\Events\Event;
use Grilar\Slug\Models\Slug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class UpdatedSlugEvent extends Event
{
    use SerializesModels;

    public function __construct(public bool|Model|null $data, public Slug $slug)
    {
    }
}
