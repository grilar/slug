<?php

namespace Grilar\Slug\Providers;

use Grilar\Base\Facades\BaseHelper;
use Grilar\Base\Facades\DashboardMenu;
use Grilar\Base\Facades\MacroableModels;
use Grilar\Base\Models\BaseModel;
use Grilar\Base\Supports\ServiceProvider;
use Grilar\Base\Traits\LoadAndPublishDataTrait;
use Grilar\Page\Models\Page;
use Grilar\Slug\Facades\SlugHelper as SlugHelperFacade;
use Grilar\Slug\Models\Slug;
use Grilar\Slug\Repositories\Eloquent\SlugRepository;
use Grilar\Slug\Repositories\Interfaces\SlugInterface;
use Grilar\Slug\SlugCompiler;
use Grilar\Slug\SlugHelper;
use Illuminate\Routing\Events\RouteMatched;

class SlugServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    protected bool $defer = true;

    public function register(): void
    {
        $this
            ->setNamespace('packages/slug')
            ->loadAndPublishTranslations();

        $this->app->bind(SlugInterface::class, function () {
            return new SlugRepository(new Slug());
        });

        $this->app->singleton(SlugHelper::class, function () {
            return new SlugHelper(new SlugCompiler());
        });
    }

    public function boot(): void
    {
        $this
            ->loadAndPublishConfigurations(['general'])
            ->loadHelpers()
            ->loadAndPublishViews()
            ->loadRoutes()
            ->loadMigrations()
            ->publishAssets();

        $this->app->register(EventServiceProvider::class);
        $this->app->register(CommandServiceProvider::class);

        $this->app['events']->listen(RouteMatched::class, function () {
            DashboardMenu::registerItem([
                'id' => 'cms-packages-slug-permalink',
                'priority' => 5,
                'parent_id' => 'cms-core-settings',
                'name' => 'packages/slug::slug.permalink_settings',
                'icon' => null,
                'url' => route('slug.settings'),
                'permissions' => ['settings.options'],
            ]);
        });

        $this->app->booted(function () {
            $this->app->register(FormServiceProvider::class);

            foreach (array_keys($this->app->make(SlugHelper::class)->supportedModels()) as $item) {
                if (! class_exists($item)) {
                    continue;
                }

                /**
                 * @var BaseModel $item
                 */
                $item::resolveRelationUsing('slugable', function ($model) {
                    return $model->morphOne(Slug::class, 'reference')->select([
                        'id',
                        'key',
                        'reference_type',
                        'reference_id',
                        'prefix',
                    ]);
                });

                MacroableModels::addMacro($item, 'getSlugAttribute', function () {
                    /**
                     * @var BaseModel $this
                     */
                    return $this->slugable ? $this->slugable->key : '';
                });

                MacroableModels::addMacro($item, 'getSlugIdAttribute', function () {
                    /**
                     * @var BaseModel $this
                     */
                    return $this->slugable ? $this->slugable->getKey() : '';
                });

                MacroableModels::addMacro(
                    $item,
                    'getUrlAttribute',
                    function () {
                        /**
                         * @var BaseModel $this
                         */
                        $model = $this;

                        $slug = $model->slugable;

                        if (
                            ! $slug ||
                            ! $slug->key ||
                            (get_class($model) == Page::class && BaseHelper::isHomepage($model->getKey()))
                        ) {
                            return route('public.index');
                        }

                        $prefix = SlugHelperFacade::getTranslator()->compile(
                            apply_filters(FILTER_SLUG_PREFIX, $slug->prefix),
                            $model
                        );

                        return apply_filters(
                            'slug_filter_url',
                            url(ltrim($prefix . '/' . $slug->key, '/')) . SlugHelperFacade::getPublicSingleEndingURL()
                        );
                    }
                );
            }

            $this->app->register(HookServiceProvider::class);
        });
    }

    public function provides(): array
    {
        return [
            SlugHelper::class,
        ];
    }
}
