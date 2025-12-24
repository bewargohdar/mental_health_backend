<?php

namespace App\Enums;

enum MoodType: string
{
    case HAPPY = 'happy';
    case SAD = 'sad';
    case ANXIOUS = 'anxious';
    case CALM = 'calm';
    case ANGRY = 'angry';
    case STRESSED = 'stressed';
    case HOPEFUL = 'hopeful';
    case NEUTRAL = 'neutral';
    case DEPRESSED = 'depressed';
    case EXCITED = 'excited';

    public function label(): string
    {
        return match ($this) {
            self::HAPPY => '😊 Happy',
            self::SAD => '😢 Sad',
            self::ANXIOUS => '😰 Anxious',
            self::CALM => '😌 Calm',
            self::ANGRY => '😠 Angry',
            self::STRESSED => '😫 Stressed',
            self::HOPEFUL => '🌟 Hopeful',
            self::NEUTRAL => '😐 Neutral',
            self::DEPRESSED => '😔 Depressed',
            self::EXCITED => '🎉 Excited',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::HAPPY => '#FFD700',
            self::SAD => '#4169E1',
            self::ANXIOUS => '#FF6347',
            self::CALM => '#98FB98',
            self::ANGRY => '#DC143C',
            self::STRESSED => '#FF8C00',
            self::HOPEFUL => '#9370DB',
            self::NEUTRAL => '#808080',
            self::DEPRESSED => '#2F4F4F',
            self::EXCITED => '#FF1493',
        };
    }
}
