<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar',
        'aadhaar_number',
        'pan_number',
        'address',
        'nominee_name',
        'nominee_relation',
        'kyc_status',
        'kyc_notes',
    ];

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function creditAccount()
    {
        return $this->hasOne(CreditAccount::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function referralRecord()
    {
        return $this->hasOne(Referral::class, 'user_id');
    }

    public function referredUsers()
    {
        // users who have this user as parent_id in referrals table
        return $this->hasManyThrough(
            User::class,
            Referral::class,
            'parent_id', // Foreign key on referrals table...
            'id',        // Foreign key on users table...
            'id',        // Local key on users table...
            'user_id'    // Local key on referrals table...
        );
    }

    public function paymentRequests()
    {
        return $this->hasMany(PaymentRequest::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function bvCommissions()
    {
        return $this->hasMany(BvCommission::class);
    }

    public function emis()
    {
        return $this->hasMany(EmiSchedule::class);
    }

    public function walletLedgers()
    {
        return $this->hasMany(WalletLedger::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Return a usable avatar URL for the user.
     */
    public function getAvatarUrlAttribute(): string
    {
        $a = $this->avatar;
        if (empty($a)) {
            return asset('/images/user/default.png');
        }
        // If stored as a full URL, return as-is
        if (str_starts_with($a, 'http://') || str_starts_with($a, 'https://')) {
            return $a;
        }
        // Otherwise assume it's a public path and use asset()
        return asset($a);
    }

    /**
     * Mutator to ensure phone is always normalized and cleaned of spaces on assignment.
     */
    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone'] = !empty($value) ? \App\Helpers\PhoneHelper::normalize($value) : null;
    }

    /**
     * Scope query to find user matching a phone number flexibly.
     * Matches across various formats (+91, without +91, with/without spaces, etc.)
     */
    public function scopeWherePhone($query, ?string $phone)
    {
        if (empty($phone)) {
            return $query->whereRaw('1 = 0');
        }

        $variants = \App\Helpers\PhoneHelper::getPossibleVariants($phone);
        $digits = preg_replace('/[^0-9]/', '', $phone);
        $table = $this->getTable();

        return $query->where(function($q) use ($variants, $digits, $table) {
            $q->whereIn($table . '.phone', $variants);
            if (strlen($digits) >= 10) {
                $last10 = substr($digits, -10);
                $q->orWhere($table . '.phone', 'LIKE', '%' . $last10);
            }
        });
    }

    /**
     * Scope a query to search name/email/phone.
     */
    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) return $query;
        $termSearch = "%{$term}%";
        $table = $this->getTable();
        $cleanPhone = preg_replace('/[^0-9]/', '', $term);
        return $query->where(function($q) use ($termSearch, $term, $table, $cleanPhone) {
            $q->where($table.'.id', $term)
              ->orWhere($table.'.name', 'like', $termSearch)
              ->orWhere($table.'.email', 'like', $termSearch)
              ->orWhere($table.'.phone', 'like', $termSearch);
            if (!empty($cleanPhone) && strlen($cleanPhone) >= 4) {
                $q->orWhere($table.'.phone', 'like', "%{$cleanPhone}%");
                if (strlen($cleanPhone) >= 10) {
                    $last10 = substr($cleanPhone, -10);
                    $q->orWhere($table.'.phone', 'like', "%{$last10}%");
                }
            }
            $q->orWhereExists(function($q2) use ($term, $table) {
                  $q2->select(\Illuminate\Support\Facades\DB::raw(1))
                     ->from('referrals')
                     ->whereColumn('referrals.user_id', $table.'.id')
                     ->where('referrals.referral_code', $term);
            });
        });
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
