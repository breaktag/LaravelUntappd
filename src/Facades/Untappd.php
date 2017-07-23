<?php

namespace Breaktag\LaravelUntappd\Facades;

use Illuminate\Support\Facades\Facade;

class Untappd extends Facade
{
	protected static getFacadeAccessor()
	{
		return 'breaktag.laraveluntappd';
	}
}