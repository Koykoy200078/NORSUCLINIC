<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ConsultationMedicine
 *
 * @property int $id
 * @property int $request_document_id
 * @property int $medicine_id
 * @property string|null $dosage
 * @property int $quantity
 * @property string|null $used_for
 * @property string|null $dosage_instructions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\RequestDocuments $requestDocument
 * @property-read \App\Models\Medicine $medicine
 */
class ConsultationMedicine extends Model
{
    protected $table = 'consultation_medicines';

    protected $fillable = [
        'request_document_id',
        'medicine_id',
        'dosage',
        'quantity',
        'used_for',
        'dosage_instructions',
    ];

    protected $casts = [
        'id' => 'integer',
        'request_document_id' => 'integer',
        'medicine_id' => 'integer',
        'quantity' => 'integer',
    ];

    /**
     * Get the request document that owns the consultation medicine.
     */
    public function requestDocument(): BelongsTo
    {
        return $this->belongsTo(RequestDocuments::class, 'request_document_id');
    }

    /**
     * Get the medicine associated with this consultation.
     */
    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
