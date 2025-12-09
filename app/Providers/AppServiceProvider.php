<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use TallStackUi\Facades\TallStackUi;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        TallStackUi::personalize()
            ->card()
            // Wrapper - border yang lebih jelas (border-2)
            ->block('wrapper.first', 'rounded-lg border-2 border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-950 dark:text-gray-50 shadow-sm')
            ->block('wrapper.second', '')

            // Image
            ->block('image.wrapper', '')
            ->block('image.rounded.top', 'rounded-t-lg')
            ->block('image.rounded.bottom', 'rounded-b-lg')
            ->block('image.size', 'w-full h-auto')

            // Header - reduced padding
            ->block('header.wrapper.base', 'flex flex-col space-y-1.5 p-4')
            ->block('header.wrapper.border', 'flex flex-col space-y-1.5 p-4 border-b border-gray-200 dark:border-gray-800')
            ->block('header.text.size', 'text-lg font-semibold leading-none tracking-tight')

            // Buttons
            ->block('button.minimize', 'w-5 h-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200')
            ->block('button.maximize', 'w-5 h-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200')
            ->block('button.close', 'w-5 h-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200')

            // Body - reduced padding: p-4 instead of p-6
            ->block('body', 'p-0 pt-0')

            // Footer - reduced padding
            ->block('footer.wrapper', 'flex items-center p-4 pt-0')
            ->block('footer.text', 'text-sm text-gray-500 dark:text-gray-400');
    }
}
