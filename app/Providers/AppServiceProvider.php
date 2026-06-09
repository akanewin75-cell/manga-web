<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::directive('proxy', function ($expression) {
            return "<?php 
                \$url = $expression;
                if (!\$url) {
                    echo '';
                } else {
                    \$isExternal = false;
                    
                    if (str_starts_with(\$url, '//')) {
                        \$isExternal = true;
                    }
                    elseif (str_starts_with(\$url, '/') && !file_exists(public_path(ltrim(\$url, '/')))) {
                        \$isExternal = true;
                    } 
                    elseif (str_starts_with(\$url, 'http')) {
                        \$host = parse_url(\$url, PHP_URL_HOST);
                        \$localHost = request()->getHost();

                        if (\$host && !in_array(\$host, [\$localHost, '127.0.0.1', 'localhost', 'via.placeholder.com'])) {
                            \$isExternal = true;
                        }
                    }

                    if (\$isExternal) {
                        echo route('proxy.image', ['url' => \$url]);
                    } else {
                        // Handle local assets
                        if (str_starts_with(\$url, 'http')) {
                            echo \$url;
                        } else {
                            echo asset(ltrim(\$url, '/'));
                        }
                    }
                }
            ?>";
        });
    }
}
