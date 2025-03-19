<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $table = 'documents'; // Nome da tabela no banco

    protected $fillable = [
        'name',
        'email',
        'whatsapp',
        'source_language',
        'target_language',
        'file_path',
        'extracted_text',
        'status'
    ];
}
