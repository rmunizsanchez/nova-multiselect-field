<?php

namespace Nitsnets\MultiselectField;

use Exception;
use RuntimeException;
use Laravel\Nova\Fields\Field;
use Illuminate\Support\Collection;
use Laravel\Nova\Http\Requests\NovaRequest;

class Multiselect extends Field
{
    public $component = 'nitsnets-multiselect-field';

    protected $pageResponseResolveCallback;
    protected $saveAsJSON = false;

    /* *
     * Enable import from CSV files.
     *
     * @ param bool $activeImport
     * @ return \Nitsnets\MultiselectField\Multiselect
     **/

    public function activeImport($activeImport = true)
    {
        return $this->withMeta(['activeImport' => $activeImport]);
    }


    /**
     * Sets the options available for select.
     *
     * @param  array|callable
     * @return \Nitsnets\MultiselectField\Multiselect
     **/
    public function options($options = [], $grouped = true)
    {
        if (is_callable($options)) {
            $options = call_user_func($options);
        }
        $options = collect($options ?? []);

        if ($grouped) {
            $isOptionGroup = $options->contains(function ($label, $value) {
                return is_array($label);
            });
        } else {
            $isOptionGroup = false;
        }

        if ($isOptionGroup) {
            $_options = $options
                ->map(function ($value, $key) {
                    return collect($value + ['value' => $key]);
                })
                ->groupBy('group')
                ->map(function ($value, $key) {
                    return ['label' => $key, 'values' => $value->map->only(['label', 'value'])->toArray()];
                })
                ->values()
                ->toArray();

            return $this->withMeta(['options' => $_options]);
        }

        return $this->withMeta([
            'options' => $options->map(function ($label, $value) {
                return ['label' => $label, 'value' => $value];
            })->values()->all(),
        ]);
    }

    /**
     * @param      $path
     * @param      $resourceClass
     * @param bool $resolving
     *
     * @return \Nitsnets\MultiselectField\Multiselect
     * @throws \Exception
     */
    public function api($path, $resourceClass, $resolving = true)
    {
        if (empty($resourceClass)) throw new Exception('Multiselect requires resourceClass, none provided.');
        if (empty($path)) throw new Exception('Multiselect requires apiUrl, none provided.');

        if ($resolving) {
            $this->resolveUsing(function ($value) use ($resourceClass) {
                $this->options([]);
                $value = array_values((array)$value);
                if (empty($value)) {
                    return $value;
                }

                // Handle translatable/collection where values are an array of arrays
                if (is_array($value) && is_array($value[0] ?? null)) {
                    $value = collect($value)->flatten(1)->toArray();
                }

                try {
                    $modelObj = $resourceClass::newModel();
                    $models = $modelObj::whereIn($modelObj->getKeyName(), $value)->get();

                    $this->setOptionsFromModels($models, $resourceClass);
                } catch (Exception $e) {
                    \Log::error($e->getMessage());
                }

                return $value;
            });
        }

        return $this->withMeta(['apiUrl' => $path, 'labelKey' => $resourceClass::$title]);
    }

    /**
     * @ param      $path
     *
     * @ return \Nitsnets\MultiselectField\Multiselect
     * @ throws \Exception
     * **/
    public function apiImport($path)
    {
        if (empty($path)) throw new Exception('Multiselect requires apiImportUrl, none provided.');
        return $this->withMeta(['apiImportUrl' => $path]);
    }

    /**
     * @param      $resourceClass
     * @param bool $resolving
     *
     * @return \Nitsnets\MultiselectField\Multiselect
     * @throws \Exception
     */
    public function asyncResource($resourceClass, $resolving = true)
    {
        $apiUrl = "/nova-api/{$resourceClass::uriKey()}";
        return $this->api($apiUrl, $resourceClass, $resolving);
    }

    protected function resolveAttribute($resource, $attribute)
    {
        $singleSelect = $this->meta['singleSelect'] ?? false;
        $value = data_get($resource, str_replace('->', '.', $attribute));

        if ($value instanceof Collection) return $value;
        if ($this->saveAsJSON || $singleSelect) return $value;
        return is_array($value) || is_object($value) ? (array) $value : json_decode($value);
    }

    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        $singleSelect = $this->meta['singleSelect'] ?? false;
        $value = $request->input($requestAttribute) ?? null;

        if ($singleSelect) {
            $model->{$attribute} = $value;
        } else {
            $value = is_null($value) ? ($this->nullable ? $value : $value = []) : $value;
            $model->{$attribute} = ($this->saveAsJSON || is_null($value)) ? $value : json_encode($value);
        }
    }

    /**
     * Allows the field to save an actual JSON array to a SQL JSON column.
     *
     * @param bool $saveAsJSON
     * @return \Nitsnets\MultiselectField\Multiselect
     **/
    public function saveAsJSON($saveAsJSON = true)
    {
        $this->saveAsJSON = $saveAsJSON;
        return $this;
    }

    /**
     * Sets the max number of options the user can select.
     *
     * @param int $max
     * @return \Nitsnets\MultiselectField\Multiselect
     **/
    public function max($max)
    {
        return $this->withMeta(['max' => $max]);
    }

    /**
     * Sets the placeholder value displayed on the field.
     *
     * @param string $placeholder
     * @return \Nitsnets\MultiselectField\Multiselect
     **/
    public function placeholder($placeholder)
    {
        return $this->withMeta(['placeholder' => $placeholder]);
    }

    /**
     * Sets the maximum number of options displayed at once.
     *
     * @param int $optionsLimit
     * @return \Nitsnets\MultiselectField\Multiselect
     **/
    public function optionsLimit($optionsLimit)
    {
        return $this->withMeta(['optionsLimit' => $optionsLimit]);
    }

    /**
     * Enables or disables reordering of the field values.
     *
     * @param bool $reorderable
     * @return \Nitsnets\MultiselectField\Multiselect
     **/
    public function reorderable($reorderable = true)
    {
        return $this->withMeta(['reorderable' => $reorderable]);
    }

    /**
     * Enables the field to be used as a single select.
     *
     * This forces the value saved to be a single value and not an array.
     *
     * @param bool $singleSelect
     * @return \Nitsnets\MultiselectField\Multiselect
     **/
    public function singleSelect($singleSelect = true)
    {
        return $this->withMeta(['singleSelect' => $singleSelect]);
    }

    /**
     * Enables vue-multiselect's group-select feature which allows the
     * user to select the whole group at once.
     *
     * @param bool $groupSelect
     * @return \Nitsnets\MultiselectField\Multiselect
     **/
    public function groupSelect($groupSelect = true)
    {
        return $this->withMeta(['groupSelect' => $groupSelect]);
    }

    /**
     * Enable other-field dependency.
     *
     * @param string $otherFieldName
     * @return \Nitsnets\MultiselectField\Multiselect
     **/
    public function dependsOn($otherFieldName)
    {
        return $this->withMeta(['dependsOn' => $otherFieldName]);
    }

    /**
     * Set dependency options map as a keyed array of options.
     *
     * @param array $options
     * @return \Nitsnets\MultiselectField\Multiselect
     **/
    public function dependsOnOptions(array $options)
    {
        $this->withMeta(['dependsOnOptions' => $options]);
        return $this;
    }

    /**
     * Set max selectable value count as a keyed array of numbers.
     *
     * @param array $maxOptions
     * @return \Nitsnets\MultiselectField\Multiselect
     **/
    public function dependsOnMax(array $maxOptions)
    {
        $this->withMeta(['dependsOnMax' => $maxOptions]);
        return $this;
    }

    /**
     * Sets group name for selects that need to have their values distinct.
     *
     * @param string $group
     * @return \Nitsnets\MultiselectField\Multiselect
     **/
    public function distinct($group = "")
    {
        if (empty($group)) $group = $this->attribute;
        return $this->withMeta(['distinct' => $group]);
    }

    /**
     * @param $value
     * @param $templateModel
     *
     * @return false|mixed|null
     */
    public function resolveResponseValue($value, $templateModel)
    {
        $parsedValue = isset($value) ? ($this->saveAsJSON ? $value : json_decode($value)) : null;
        return is_callable($this->pageResponseResolveCallback)
            ? call_user_func($this->pageResponseResolveCallback, $parsedValue, $templateModel)
            : $parsedValue;
    }

    /**
     * @param callable $resolveCallback
     *
     * @return \Nitsnets\MultiselectField\Multiselect
     */
    public function resolveForPageResponseUsing(callable $resolveCallback)
    {
        $this->pageResponseResolveCallback = $resolveCallback;
        return $this;
    }


    /**
     * Makes the field to manage a BelongsToMany relationship.
     *
     * @param string $resourceClass The Nova Resource class for the other model.
     * @return \Nitsnets\MultiselectField\Multiselect
     **/
    public function belongsToMany($resourceClass, $async = true)
    {
        $this->resolveUsing(function ($value) use ($async, $resourceClass) {
            if ($async) $this->asyncResource($resourceClass);

            $models = $async ? $value : $resourceClass::newModel()::all();

            $this->setOptionsFromModels($models, $resourceClass);

            return $value->map(function ($model) {
                return $model[$model->getKeyName()];
            })->toArray();
        });

        $this->fillUsing(function ($request, $model, $requestAttribute, $attribute) {
            $model::saved(function ($model) use ($attribute, $request) {
                // Validate
                if (!method_exists($model, $attribute)) {
                    throw new RuntimeException("{$model}::{$attribute} must be a relation method.");
                }

                $relation = $model->{$attribute}();

                if (!method_exists($relation, 'sync')) {
                    throw new RuntimeException("{$model}::{$attribute} does not appear to model a BelongsToMany or MorphsToMany.");
                }

                // Sync
                $relation->sync($request->get($attribute) ?? []);
            });
        });

        return $this;
    }

    /**
     * Makes the field to manage a BelongsTo relationship.
     *
     * @param string $resourceClass The Nova Resource class for the other model.
     * @return \Nitsnets\MultiselectField\Multiselect
     **/
    public function belongsTo($resourceClass, $async = true)
    {
        $this->singleSelect();
        $primaryKey =  $resourceClass::newModel()->getKeyName();

        $this->resolveUsing(function ($value) use ($async, $primaryKey, $resourceClass) {
            if ($async) $this->asyncResource($resourceClass);

            $value = $value->{$primaryKey} ?? null;
            $model = $resourceClass::newModel();
            $models = $async && isset($value) ? collect([$model::find($value)]) : $model::all();

            $this->setOptionsFromModels($models, $resourceClass);

            return $value;
        });

        $this->fillUsing(function ($request, $model, $requestAttribute, $attribute) use ($resourceClass) {
            $modelClass = get_class($model);

            // Validate
            if (!method_exists($model, $attribute)) {
                throw new RuntimeException("{$modelClass}::{$attribute} must be a relation method.");
            }

            $relation = $model->{$attribute}();

            if (!method_exists($relation, 'associate')) {
                throw new RuntimeException("{$modelClass}::{$attribute} does not appear to model a BelongsTo relationship.");
            }

            // Sync
            $relation->associate($resourceClass::newModel()::find($request->get($attribute)));
        });

        return $this;
    }

    /**
     * @param bool $clearOnSelect
     *
     * @return \Nitsnets\MultiselectField\Multiselect
     */
    public function clearOnSelect($clearOnSelect = true)
    {
        return $this->withMeta(['clearOnSelect' => $clearOnSelect]);
    }

    /**
     * Set the options from a collection of models.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @param  string  $resourceClass
     * @return void
     */
    public function setOptionsFromModels(Collection $models, $resourceClass)
    {
        $options = $models->mapInto($resourceClass)->mapWithKeys(function ($associatedResource) {
            return [$associatedResource->getKey() => $associatedResource->title()];
        });
        $this->options($options);
    }

    /**
     * @return \Nitsnets\MultiselectField\Multiselect
     **/
    public function showListed()
    {
        $this->withMeta(['listed' => true]);
        return $this;
    }

    /**
     * Allow to add some html after the main input
     *
     * @param $html
     * @return mixed
     */
    public function setHtmlAfterInput($html)
    {
        return $this->withMeta(['htmlAfterInput' => $html]);
    }

    /**
     * Sort Async Options By the given value and order.
     *
     * @param $html
     * @return mixed
     */
    public function sortAsyncOptionsBy(string $value, string $order = 'asc')
    {
        return $this->withMeta([
            'sortAsyncOptionsByValue' => $value,
            'sortAsyncOptionsByOrder' => $order
        ]);
    }


    public function __construct($name, $attribute = null, callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);
        $this->withMeta(['listed' => false, 'reorderable' => false, 'activeImport' => false]);
    }
}
