<?php

namespace Botble\Marketplace\Models;

use Botble\ACL\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Base\Supports\Avatar;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Discount;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\QueryBuilders\StoreQueryBuilder;
use Botble\Ecommerce\Traits\LocationTrait;
use Botble\Marketplace\Enums\StoreStatusEnum;
use Botble\Media\Facades\RvMedia;
use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Store extends BaseModel
{
    use LocationTrait;

    protected $table = 'mp_stores';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'country',
        'state',
        'city',
        'customer_id',
        'logo',
        'logo_square',
        'cover_image',
        'description',
        'content',
        'status',
        'company',
        'zip_code',
        'certificate_file',
        'government_id_file',
        'pan_card_file',
        'aadhar_card_file',
        'gst_certificate_file',
        'udyam_aadhar_file',
        'tax_id',
        'is_verified',
        'is_key_account',
        'verified_at',
        'verified_by',
        'verification_note',
        'sponsored_video_url',
        'sponsored_video_thumbnail',
        'sponsored_video_expires_at',
    ];

    protected $casts = [
        'status' => StoreStatusEnum::class,
        'name' => SafeContent::class,
        'description' => SafeContent::class,
        'content' => SafeContent::class,
        'address' => SafeContent::class,
        'company' => SafeContent::class,
        'is_verified' => 'boolean',
        'is_key_account' => 'boolean',
        'verified_at' => 'datetime',
        'verification_note' => SafeContent::class,
        'sponsored_video_expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleted(function (Store $store): void {
            $store->products()->each(fn (Product $product) => $product->delete());
            $store->discounts()->delete();
            $store->orders()->update(['store_id' => null]);

            $folder = Storage::path($store->upload_folder);
            if (File::isDirectory($folder) && Str::endsWith($store->upload_folder, '/' . ($store->slug ?: $store->id))) {
                File::deleteDirectory($folder);
            }
        });

        static::updating(function (Store $store): void {
            if ($store->getOriginal('status') != $store->status) {
                $status = $store->status;

                if ($status == StoreStatusEnum::BLOCKED) {
                    $store
                        ->products()
                        ->where('status', BaseStatusEnum::PUBLISHED)
                        ->update(['status' => $status]);
                } elseif ($status == StoreStatusEnum::PUBLISHED) {
                    $store
                        ->products()
                        ->where('status', 'blocked')
                        ->update(['status' => $status]);
                }
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withDefault();
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->where('is_finished', 1);
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class, 'store_id');
    }

    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo) {
            return RvMedia::getImageUrl($this->logo);
        }

        try {
            return (new Avatar())->create($this->name)->toBase64();
        } catch (Exception) {
            return RvMedia::getDefaultImage();
        }
    }

    public function reviews(): HasMany
    {
        return $this
            ->hasMany(Product::class)
            ->join('ec_reviews', 'ec_products.id', '=', 'ec_reviews.product_id');
    }

    protected function uploadFolder(): Attribute
    {
        return Attribute::make(
            get: function () {
                $folder = $this->id ? 'stores/' . ($this->slug ?: $this->id) : 'stores';

                return apply_filters('marketplace_store_upload_folder', $folder, $this);
            }
        );
    }

    protected function badge(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->is_verified) {
                    return '';
                }

                return view('plugins/marketplace::partials.verified-badge', ['size' => 'sm'])->render();
            }
        );
    }

    public static function handleCommissionEachCategory(array $data): array
    {
        $commissions = [];
        CategoryCommission::query()->truncate();
        foreach ($data as $datum) {
            if (! $datum['categories']) {
                continue;
            }

            $categories = json_decode($datum['categories'], true);

            if (! is_array($categories) || ! count($categories)) {
                continue;
            }

            foreach ($categories as $category) {
                $commission = CategoryCommission::query()->firstOrNew([
                    'product_category_id' => $category['id'],
                ]);

                if (! $commission) {
                    continue;
                }

                $commission->commission_percentage = $datum['commission_fee'];
                $commission->save();
                $commissions[] = $commission;
            }
        }

        return $commissions;
    }

    public static function getCommissionEachCategory(): array
    {
        $commissions = CategoryCommission::query()->with(['category'])->get();
        $data = [];
        foreach ($commissions as $commission) {
            if (! $commission->category) {
                continue;
            }

            $data[$commission->commission_percentage]['commission_fee'] = $commission->commission_percentage;
            $data[$commission->commission_percentage]['categories'][] = [
                'id' => $commission->product_category_id,
                'value' => $commission->category->name,
            ];
        }

        return $data;
    }

    public function newEloquentBuilder($query): StoreQueryBuilder
    {
        return new StoreQueryBuilder($query);
    }

    public function getMetaData(string $key, bool $single = false): array|string|null
    {
        if (in_array($key, ['cover_image', 'background'])) {
            return $this->cover_image;
        }

        return parent::getMetaData($key, $single);
    }

    public function hasActiveSponsoredVideo(): bool
    {
        if (empty($this->sponsored_video_url)) {
            return false;
        }

        if ($this->sponsored_video_expires_at && $this->sponsored_video_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(VendorSubscription::class)
            ->where('status', VendorSubscription::STATUS_ACTIVE)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(VendorSubscription::class);
    }

    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription()->exists();
    }

    public function canCreateProduct(): bool
    {
        $subscription = VendorSubscription::getActiveForStore($this->id);

        if (!$subscription) {
            return false;
        }

        return $subscription->canCreateProduct($this->products()->where('is_variation', 0)->count());
    }

    public function getSubscriptionStatus(): array
    {
        $subscription = VendorSubscription::getActiveForStore($this->id);

        if (!$subscription) {
            return [
                'has_subscription' => false,
                'plan_name' => null,
                'products_used' => $this->products()->where('is_variation', 0)->count(),
                'products_limit' => 0,
                'products_remaining' => 0,
                'days_remaining' => 0,
                'expires_at' => null,
                'is_expired' => true,
            ];
        }

        $productsUsed = $this->products()->where('is_variation', 0)->count();
        $productsLimit = $subscription->plan->max_products ?? 0;

        return [
            'has_subscription' => true,
            'plan_name' => $subscription->plan->name ?? __('Unknown'),
            'products_used' => $productsUsed,
            'products_limit' => $productsLimit,
            'products_remaining' => $productsLimit == 0 ? -1 : max(0, $productsLimit - $productsUsed),
            'days_remaining' => $subscription->days_remaining,
            'expires_at' => $subscription->expires_at,
            'is_expired' => $subscription->isExpired(),
        ];
    }
}
