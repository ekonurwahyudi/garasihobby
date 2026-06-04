<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'bank_name', 'account_name', 'account_number', 'opening_balance', 'balance', 'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(FinanceTransaction::class);
    }

    public function getLogoTextAttribute(): string
    {
        return collect(explode(' ', preg_replace('/[^A-Za-z0-9 ]/', '', $this->bank_name ?? 'Bank')))
            ->filter()
            ->map(fn ($word) => substr($word, 0, 1))
            ->take(3)
            ->implode('') ?: 'BNK';
    }

    public function getLogoUrlAttribute(): ?string
    {
        $bankName = strtoupper($this->bank_name ?? 'BANK');
        $logoMap = [
            'BCA DIGITAL' => 'BCA Digital logo.svg',
            'BCA SYARIAH' => 'BCA Syariah.svg',
            'BCA' => 'Bank Central Asia.svg',
            'BRI' => 'BRI 2020.svg',
            'BNI' => 'Bank Negara Indonesia logo (2004).svg',
            'MANDIRI' => 'Bank Mandiri logo 2016.svg',
            'BTN' => 'Bank BTN logo.svg',
            'CIMB' => 'CIMB Niaga logo.svg',
            'DANAMON' => 'Danamon.svg',
            'MEGA' => 'Bank Mega 2013.svg',
            'PERMATA' => 'Permata Bank (2024).svg',
            'PANIN' => 'Logo Panin Bank.svg',
            'JAGO' => 'Logo-jago.svg',
            'UOB' => 'UOB Logo (2022).svg',
            'DKI' => 'Bank DKI.svg',
        ];

        foreach ($logoMap as $needle => $file) {
            if (str_contains($bankName, $needle)) {
                return 'https://commons.wikimedia.org/wiki/Special:FilePath/' . rawurlencode($file) . '?width=160';
            }
        }

        if (str_contains($bankName, 'BSI') || str_contains($bankName, 'SYARIAH INDONESIA')) {
            return 'https://commons.wikimedia.org/wiki/Special:FilePath/' . rawurlencode('Bank Syariah Indonesia.svg') . '?width=160';
        }

        if (str_contains($bankName, 'SEABANK') || str_contains($bankName, 'SEA BANK')) {
            return 'https://commons.wikimedia.org/wiki/Special:FilePath/' . rawurlencode('SeaBank.svg') . '?width=160';
        }

        return str_contains($bankName, 'CASH') ? asset('assets/media/logos.png') : null;
    }
}
