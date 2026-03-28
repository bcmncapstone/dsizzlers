<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DigitalMarketingUpload extends Model
{
    protected $fillable = ['uploaded_by', 'image_path', 'description'];

    public function scopeNotArchived($query)
    {
        $archivedIds = self::getArchivedIds();

        if (! empty($archivedIds)) {
            $query->whereNotIn('id', $archivedIds);
        }

        return $query;
    }

    public static function getArchivedIds(): array
    {
        if (! Storage::disk('local')->exists('archived_digital_marketing.json')) {
            return [];
        }

        $raw = Storage::disk('local')->get('archived_digital_marketing.json');
        $data = json_decode($raw, true);

        return is_array($data) ? array_values(array_unique(array_map('intval', $data))) : [];
    }

    public static function archiveId(int $id): void
    {
        $ids = self::getArchivedIds();

        if (! in_array($id, $ids, true)) {
            $ids[] = $id;
            Storage::disk('local')->put('archived_digital_marketing.json', json_encode(array_values(array_unique($ids))));
        }
    }

    public static function unarchiveId(int $id): void
    {
        $ids = self::getArchivedIds();
        $ids = array_values(array_filter($ids, fn ($archivedId) => (int) $archivedId !== $id));
        Storage::disk('local')->put('archived_digital_marketing.json', json_encode(array_values(array_unique($ids))));
    }
}

