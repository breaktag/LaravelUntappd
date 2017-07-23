<?php

namespace Breaktag\LaravelUntappd\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Container\Container;
use Laravel\Lumen\Application as LumenApplication;
use Illuminate\Foundation\Application as LaravelApplication;

class LaravelUntappdServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$source = realpath($raw = __DIR__.'/../config/untappd.php') ?: $raw;

		if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
			$this->publishes([$source => config_path('untappd.php')]);
		} elseif ($this->app instanceof LumenApplication) {
			$this->app->configure('untappd');
		}

		$this->mergeConfigFrom($source, 'untappd');
	}

	public function register()
	{
		$this->app->bind('Breaktag\LaravelUntappd', function (Container $app) {
			return new LaravelUntappd($app->config->get('untappd', []));
		});

		$this->app->alias('breaktag.untappd', LaravelUntappd::class);
	}
}
