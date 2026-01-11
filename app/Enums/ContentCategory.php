<?php

namespace App\Enums;

enum ContentCategory: string
{
    case DEPRESSION = 'depression';
    case ANXIETY = 'anxiety';
    case STRESS = 'stress';
    case PTSD = 'ptsd';
    case BIPOLAR = 'bipolar';
    case OCD = 'ocd';
    case SLEEP = 'sleep';
    case RELATIONSHIPS = 'relationships';
    case SELF_CARE = 'self_care';
    case MINDFULNESS = 'mindfulness';
    case GRIEF = 'grief';
    case TRAUMA = 'trauma';
    case GENERAL = 'general';

    public function label(): string
    {
        return match ($this) {
            self::DEPRESSION => 'Depression',
            self::ANXIETY => 'Anxiety',
            self::STRESS => 'Stress Management',
            self::PTSD => 'PTSD',
            self::BIPOLAR => 'Bipolar Disorder',
            self::OCD => 'OCD',
            self::RELATIONSHIPS => 'Relationships',
            self::SELF_CARE => 'Self Care',
            self::MINDFULNESS => 'Mindfulness',
            self::SLEEP => 'Sleep & Rest',
            self::GRIEF => 'Grief & Loss',
            self::TRAUMA => 'Trauma Recovery',
            self::GENERAL => 'General Wellness',
        };
    }
}
