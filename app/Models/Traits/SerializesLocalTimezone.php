<?php

namespace App\Models\Traits;

use DateTimeInterface;

trait SerializesLocalTimezone
{
    /**
     * Prepare a date for array / JSON serialization.
     * Returns date in the app's configured timezone instead of UTC.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
    }
}
