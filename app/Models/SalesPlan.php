<?php

namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\SoftDeletes;

    class SalesPlan extends Model
    {
        use HasFactory;
        use SoftDeletes; // Tambahkan ini
        protected $table = 'salesplans'; // Specify the table name if it differs from the model name
        protected $fillable = [
            'data_id',
            'fu1_hasil',
            'fu1_tindak_lanjut',
            'fu2_hasil',
            'fu2_tindak_lanjut',
            'fu3_hasil',
            'fu3_tindak_lanjut',
            'fu4_hasil',
            'fu4_tindak_lanjut',
            'fu5_hasil',
            'fu5_tindak_lanjut',
            'fu6_hasil',
            'fu6_tindak_lanjut',
            'fu7_hasil',
            'fu7_tindak_lanjut',
            'fu8_hasil',
            'fu8_tindak_lanjut',
            'fu9_hasil',
            'fu9_tindak_lanjut',
            'fu10_hasil',
            'fu10_tindak_lanjut',
            'fu11_hasil',
            'fu11_tindak_lanjut',
            'fu12_hasil',
            'fu12_tindak_lanjut',
            'fu1_done', 'fu2_done', 'fu3_done', 'fu4_done', 'fu5_done', 'fu6_done', 
            'fu7_done', 'fu8_done', 'fu9_done', 'fu10_done', 'fu11_done', 'fu12_done',
            'keterangan',
            'nominal',
            'kebutuhan',
            'created_by',
            'level',
            'komentar_atasan',
            'fu_history',
            'tanggal_closing',
            'selected_months'
        ];
        protected $casts = [
            'status' => 'string', // Cast status to string
            'closing_paket' => 'boolean',
            'fu_history' => 'array',
            'fu1_at' => 'datetime',
            'fu2_at' => 'datetime',
            'fu3_at' => 'datetime',
            'fu4_at' => 'datetime',
            'fu5_at' => 'datetime',
            'fu6_at' => 'datetime',
            'fu7_at' => 'datetime',
            'fu8_at' => 'datetime',
            'fu9_at' => 'datetime',
            'fu10_at' => 'datetime',
            'fu11_at' => 'datetime',
            'fu12_at' => 'datetime',
            'fu1_rtl_at' => 'datetime',
            'fu2_rtl_at' => 'datetime',
            'fu3_rtl_at' => 'datetime',
            'fu4_rtl_at' => 'datetime',
            'fu5_rtl_at' => 'datetime',
            'fu6_rtl_at' => 'datetime',
            'fu7_rtl_at' => 'datetime',
            'fu8_rtl_at' => 'datetime',
            'fu9_rtl_at' => 'datetime',
            'fu10_rtl_at' => 'datetime',
            'fu11_rtl_at' => 'datetime',
            'fu12_rtl_at' => 'datetime',
            'fu1_done' => 'boolean',
            'fu2_done' => 'boolean',
            'fu3_done' => 'boolean',
            'fu4_done' => 'boolean',
            'fu5_done' => 'boolean',
            'fu6_done' => 'boolean',
            'fu7_done' => 'boolean',
            'fu8_done' => 'boolean',
            'fu9_done' => 'boolean',
            'fu10_done' => 'boolean',
            'fu11_done' => 'boolean',
            'fu12_done' => 'boolean',
            'tanggal_closing' => 'date',
            'selected_months' => 'array',
        ];


        /**
         * Relasi dengan model data.
         */
        public function data()
        {
            return $this->belongsTo(Data::class, 'data_id');
        }
        public function kelas()
        {
            return $this->belongsTo(Kelas::class, 'kelas_id');
        }
        
        // app/Models/SalesPlan.php
    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }


        public function getCreatedByNameAttribute()
        {
            if ($this->createdBy) {
                return $this->createdBy->name;
            }

            $names = [
                1 => 'Administrator',
                2 => 'Linda',
                3 => 'Yasmin',
                4 => 'Tursia',
                5 => 'Livia',
                6 => 'Shafa',
            ];

            return $names[$this->created_by] ?? '-';
        }

        public function pesertaSmi()
        {
            return $this->hasOne(PesertaSmi::class, 'sales_plan_id');
        }
    }
