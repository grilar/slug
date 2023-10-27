<?php

namespace Grilar\Slug\Facades;

use Grilar\Slug\SlugHelper as BaseSlugHelper;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Grilar\Slug\SlugHelper registerModule(array|string $model, string|null $name = null)
 * @method static \Grilar\Slug\SlugHelper removeModule(array|string $model)
 * @method static array supportedModels()
 * @method static \Grilar\Slug\SlugHelper setPrefix(string $model, string|null $prefix, bool $canEmptyPrefix = false)
 * @method static \Grilar\Slug\SlugHelper setColumnUsedForSlugGenerator(string $model, string $column)
 * @method static bool isSupportedModel(string $model)
 * @method static \Grilar\Slug\SlugHelper disablePreview(array|string $model)
 * @method static bool canPreview(string $model)
 * @method static mixed getSlug(string|null $key, string|null $prefix = null, string|null $model = null, $referenceId = null)
 * @method static string|null getPrefix(string $model, string $default = '', bool $translate = true)
 * @method static string|null getColumnNameToGenerateSlug(object|string $model)
 * @method static string getPermalinkSettingKey(string $model)
 * @method static bool turnOffAutomaticUrlTranslationIntoLatin()
 * @method static string|null getPublicSingleEndingURL()
 * @method static array getCanEmptyPrefixes()
 * @method static \Grilar\Slug\SlugCompiler getTranslator()
 *
 * @see \Grilar\Slug\SlugHelper
 */
class SlugHelper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BaseSlugHelper::class;
    }
}
