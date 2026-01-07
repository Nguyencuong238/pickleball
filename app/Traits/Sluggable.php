<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait Sluggable
{
    public static function bootSluggable(): void
    {
        static::creating(function ($model) {
            $model->generateSlugIfEmpty();
        });

        static::updating(function ($model) {
            $sourceColumn = $model->getSlugSourceColumn();
            if ($model->isDirty($sourceColumn) && empty($model->slug)) {
                $model->generateSlugIfEmpty();
            }
        });
    }

    /**
     * Get the column to use as the slug source.
     * Override this method or set $slugSource property to customize.
     */
    public function getSlugSourceColumn(): string
    {
        return $this->slugSource ?? 'name';
    }

    /**
     * Generate a unique slug if one doesn't exist.
     */
    protected function generateSlugIfEmpty(): void
    {
        if (!empty($this->slug)) {
            return;
        }

        $sourceColumn = $this->getSlugSourceColumn();
        $slug = Str::slug($this->{$sourceColumn});

        // Ensure unique slug
        $originalSlug = $slug;
        $count = 1;
        while (static::where('slug', $slug)
            ->where('id', '!=', $this->id ?? 0)
            ->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        $this->slug = $slug;
    }
}
