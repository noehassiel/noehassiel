<?php

namespace App\Enums;

/**
 * What a change requested from the preview touches. Only content and fixes are part of a revision round.
 */
enum ChangeCategory: string
{
    case SectionOrder = 'section_order';
    case Text = 'text';
    case Image = 'image';
    case Bug = 'bug';
    case Design = 'design';
    case Feature = 'feature';

    public function label(): string
    {
        return __("portal.preview.categories.{$this->value}");
    }

    /** Reordering, copy and images are covered by the revision rounds; design and new features are quoted apart. */
    public function isRevision(): bool
    {
        return in_array($this, [self::SectionOrder, self::Text, self::Image], true);
    }

    /** Fixing what doesn't work is always on us and never uses up a round. */
    public function countsTowardRounds(): bool
    {
        return $this !== self::Bug;
    }
}
