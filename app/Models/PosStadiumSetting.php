<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosStadiumSetting extends Model
{
    use HasFactory;

    protected $connection = 'pickleball_pos';
    protected $table = 'pos_stadium_settings';
    protected $fillable = ['stadium_id', 'setting_key', 'setting_value'];
    public $timestamps = false;

    /**
     * Get bank settings for a stadium
     */
    public static function getBankInfo($stadiumId)
    {
        try {
            \Log::info('Fetching bank info for stadium: ' . $stadiumId);
            
            $settings = self::where('stadium_id', $stadiumId)
                ->whereIn('setting_key', ['bank_code', 'bank_account_number', 'bank_account_name'])
                ->get();

            \Log::info('Settings found: ' . $settings->count());

            if ($settings->isEmpty()) {
                \Log::warning('No bank settings found for stadium: ' . $stadiumId);
                return null;
            }

            $bankInfo = [];
            foreach ($settings as $setting) {
                $bankInfo[$setting->setting_key] = $setting->setting_value;
            }

            \Log::info('Bank info: ' . json_encode($bankInfo));

            return [
                'bank_code' => $bankInfo['bank_code'] ?? null,
                'account_number' => $bankInfo['bank_account_number'] ?? null,
                'account_name' => $bankInfo['bank_account_name'] ?? null,
                'bank_name' => null,
            ];
        } catch (\Exception $e) {
            \Log::error('Error fetching bank info: ' . $e->getMessage());
            return null;
        }
    }
}
