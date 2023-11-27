<?php

namespace Nitsnets\MultiselectField;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\ServiceProvider;
use Outl1ne\NovaTranslationsLoader\LoadsNovaTranslations;

class FieldServiceProvider extends ServiceProvider
{
    use LoadsNovaTranslations;

    public function boot()
    {
        Nova::serving(function (ServingNova $event) {
            Nova::script('nitsnets-multiselect-field', __DIR__ . '/../dist/js/multiselect-field.js');
            Nova::style('nitsnets-multiselect-field', __DIR__ . '/../dist/css/multiselect-field.css');
        });

        $this->loadTranslations(__DIR__ . '/../resources/lang', 'nova-multiselect-field', true);
    }
}
