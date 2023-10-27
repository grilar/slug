<?php

namespace Grilar\Slug\Listeners;

use Grilar\Base\Events\DeletedContentEvent;
use Grilar\Slug\Facades\SlugHelper;
use Grilar\Slug\Models\Slug;

class DeletedContentListener
{
    public function handle(DeletedContentEvent $event): void
    {
        if (SlugHelper::isSupportedModel(get_class($event->data))) {
            Slug::query()->where([
                'reference_id' => $event->data->getKey(),
                'reference_type' => get_class($event->data),
            ])->delete();
        }
    }
}
