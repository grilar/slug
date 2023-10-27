<?php

namespace Grilar\Slug\Listeners;

use Grilar\Base\Events\CreatedContentEvent;
use Grilar\Slug\Facades\SlugHelper;
use Grilar\Slug\Models\Slug;
use Grilar\Slug\Services\SlugService;
use Exception;
use Illuminate\Support\Str;

class CreatedContentListener
{
    public function handle(CreatedContentEvent $event): void
    {
        if (SlugHelper::isSupportedModel($class = get_class($event->data)) && $event->request->input('is_slug_editable', 0)) {
            try {
                $slug = $event->request->input('slug');

                $fieldNameToGenerateSlug = SlugHelper::getColumnNameToGenerateSlug($event->data);

                if (! $slug) {
                    $slug = $event->request->input($fieldNameToGenerateSlug);
                }

                if (! $slug && $event->data->{$fieldNameToGenerateSlug}) {
                    if (! SlugHelper::turnOffAutomaticUrlTranslationIntoLatin()) {
                        $slug = Str::slug($event->data->{$fieldNameToGenerateSlug});
                    } else {
                        $slug = $event->data->{$fieldNameToGenerateSlug};
                    }
                }

                if (! $slug) {
                    $slug = time();
                }

                $slugService = new SlugService();

                Slug::query()->create([
                    'key' => $slugService->create($slug, (int)$event->data->slug_id, $class),
                    'reference_type' => $class,
                    'reference_id' => $event->data->getKey(),
                    'prefix' => SlugHelper::getPrefix($class),
                ]);
            } catch (Exception $exception) {
                info($exception->getMessage());
            }
        }
    }
}
