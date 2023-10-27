<?php

namespace Grilar\Slug\Http\Controllers;

use Grilar\Base\Facades\PageTitle;
use Grilar\Base\Http\Controllers\BaseController;
use Grilar\Base\Http\Responses\BaseHttpResponse;
use Grilar\Menu\Facades\Menu;
use Grilar\Setting\Supports\SettingStore;
use Grilar\Slug\Http\Requests\SlugRequest;
use Grilar\Slug\Http\Requests\SlugSettingsRequest;
use Grilar\Slug\Models\Slug;
use Grilar\Slug\Services\SlugService;
use Illuminate\Support\Str;

class SlugController extends BaseController
{
    public function store(SlugRequest $request, SlugService $slugService)
    {
        return $slugService->create(
            $request->input('value'),
            $request->input('slug_id'),
            $request->input('model')
        );
    }

    public function getSettings()
    {
        PageTitle::setTitle(trans('packages/slug::slug.settings.title'));

        return view('packages/slug::settings');
    }

    public function postSettings(
        SlugSettingsRequest $request,
        BaseHttpResponse $response,
        SettingStore $settingStore
    ) {
        $hasChangedEndingUrl = false;

        foreach ($request->except(['_token']) as $settingKey => $settingValue) {
            if (Str::contains($settingKey, '-model-key')) {
                continue;
            }

            if ($settingKey == 'public_single_ending_url') {
                $settingValue = ltrim($settingValue, '.');

                if ($settingStore->get($settingKey) !== $settingValue) {
                    $hasChangedEndingUrl = true;
                }
            }

            if ($settingStore->get($settingKey) !== (string)$settingValue) {
                Slug::query()
                    ->where('reference_type', $request->input($settingKey . '-model-key'))
                    ->update(['prefix' => (string)$settingValue]);

                Menu::clearCacheMenuItems();
            }

            $settingStore->set($settingKey, (string)$settingValue);
        }

        $settingStore->save();

        if ($hasChangedEndingUrl) {
            Menu::clearCacheMenuItems();
        }

        return $response
            ->setPreviousUrl(route('slug.settings'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }
}
