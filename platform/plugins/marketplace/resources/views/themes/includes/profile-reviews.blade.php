@if ($store->is_minimal_profile && EcommerceHelper::isReviewEnabled())
    @php
        $profileSummary = $store->publicRatingSummary();
        $profileFeedback = $store->profileReviews()->where('status', 'published')->with('customer:id,name')->latest()->paginate(5, ['*'], 'profile_reviews');
        $profileCustomer = auth('customer')->user();
        $myProfileReview = $profileCustomer ? $store->profileReviews()->where('customer_id', $profileCustomer->id)->first() : null;
        $canWriteProfileReview = $profileCustomer && app(\Botble\Marketplace\Services\ProfileReviewService::class)->canWrite($profileCustomer->id, $store->id);
    @endphp
    @once
        <style>
            .bb-profile-reviews {margin:24px 0;padding:24px;border:1px solid #e8eaee;border-radius:12px;background:#fff;}
            .bb-profile-reviews h2 {font-size:20px;margin:0 0 6px;}
            .bb-profile-review-layout {display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.4fr);gap:28px;}
            .bb-profile-review-muted {font-size:13px;color:#6b7280;line-height:1.6;}
            .bb-profile-review-score {display:flex;align-items:baseline;gap:8px;margin:18px 0 8px;}
            .bb-profile-review-score strong {font-size:32px;line-height:1.2;}
            .bb-profile-review-stars {display:inline-flex;flex-direction:row-reverse;gap:6px;border:0;padding:0;margin:0 0 12px;}
            .bb-profile-review-stars input {position:absolute;opacity:0;width:1px;height:1px;}
            .bb-profile-review-stars label {font-size:29px;color:#d7dce2;cursor:pointer;line-height:1.2;}
            .bb-profile-review-stars input:checked~label,.bb-profile-review-stars label:hover,.bb-profile-review-stars label:hover~label {color:#e9ab33;}
            .bb-profile-review-stars input:focus-visible+label {outline:2px solid #172b35;outline-offset:3px;}
            .bb-profile-review-entry {border-top:1px solid #edf0f3;padding:16px 0;}
            .bb-profile-review-entry p {font-size:14px;white-space:pre-line;overflow-wrap:anywhere;margin:8px 0 0;}
            .bb-profile-review-submit {background:#172b35;color:white;border:0;padding:10px 18px;border-radius:7px;font-size:13px;}
            @media(max-width:575px){.bb-profile-reviews{padding:16px;}.bb-profile-review-layout{grid-template-columns:1fr;gap:16px;}.bb-profile-review-submit{width:100%;}}
        </style>
    @endonce
    <section id="profile-reviews" class="bb-profile-reviews" aria-label="{{ __('Vendor reviews and ratings') }}">
        <div class="bb-profile-review-layout">
            <div>
                <h2>{{ __('Ratings & reviews') }}</h2>
                <p class="bb-profile-review-muted">{{ __('Rate this vendor. Share a review after a completed purchase or service.') }}</p>
                <div class="bb-profile-review-score"><strong>{{ number_format($profileSummary['average'], 1) }}</strong><span class="bb-profile-review-muted">{{ __('/ 5') }} · {{ trans_choice(':count rating|:count ratings', $profileSummary['count'], ['count' => $profileSummary['count']]) }}</span></div>
                @include(EcommerceHelper::viewPath('includes.rating-star'), ['avg' => $profileSummary['average']])
            </div>
            <div>
                @if (session('profile_review_success'))<p class="alert alert-success" role="status">{{ session('profile_review_success') }}</p>@endif
                @if ($errors->any())<p class="text-danger" role="alert">{{ $errors->first() }}</p>@endif
                @if (! $profileCustomer)
                    <a href="{{ route('customer.login') }}" class="bb-profile-review-submit d-inline-block">{{ __('Sign in to rate') }}</a>
                    <p class="bb-profile-review-muted mt-2">{{ __('Star ratings are free. No purchase is required.') }}</p>
                @elseif ($profileCustomer->id != $store->customer_id)
                    @if ($myProfileReview && $myProfileReview->status !== 'published')<p class="bb-profile-review-muted">{{ __('Your last submission is not currently published.') }}</p>@endif
                    <form method="post" action="{{ route('public.store.profile-review', $store->id) }}">
                        @csrf
                        <fieldset class="border-0 p-0 m-0">
                            <legend class="fs-6 mb-2">{{ __('Your rating') }}</legend>
                            <div class="bb-profile-review-stars">
                                @for ($star = 5; $star >= 1; $star--)
                                    <input type="radio" id="profile-rating-{{ $store->id }}-{{ $star }}" name="star" value="{{ $star }}" @checked((int) old('star', $myProfileReview?->star ?? 5) === $star) required>
                                    <label for="profile-rating-{{ $store->id }}-{{ $star }}" aria-label="{{ trans_choice(':count star|:count stars', $star, ['count' => $star]) }}">★</label>
                                @endfor
                            </div>
                        </fieldset>
                        <label for="profile-review-comment" class="form-label">{{ __('Written review (optional)') }}</label>
                        <textarea id="profile-review-comment" name="comment" class="form-control" rows="3" maxlength="5000" @disabled(! $canWriteProfileReview) placeholder="{{ __('Tell others about your experience') }}">{{ old('comment', $myProfileReview?->comment) }}</textarea>
                        <p class="bb-profile-review-muted mt-2">{{ $canWriteProfileReview ? __('Your completed purchase or service is verified. You can add a written review.') : __('You can rate now. A completed purchase or admin-confirmed service is needed to write a review.') }}</p>
                        <button type="submit" class="bb-profile-review-submit">{{ __('Save rating & review') }}</button>
                    </form>
                @endif
            </div>
        </div>
        <div class="mt-3">
            @forelse ($profileFeedback as $feedback)
                <article class="bb-profile-review-entry">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2"><strong>{{ $feedback->display_name }}</strong><span class="bb-profile-review-muted">{{ $feedback->created_at->format('d M Y') }}</span></div>
                    <div class="d-flex align-items-center gap-2 mt-1">@include(EcommerceHelper::viewPath('includes.rating-star'), ['avg' => $feedback->star])<span class="bb-profile-review-muted">{{ $feedback->star }}/5</span></div>
                    @if (filled($feedback->comment))<p>{{ $feedback->comment }}</p>@endif
                </article>
            @empty
                <p class="bb-profile-review-muted mb-0">{{ __('No ratings yet. Be the first to rate this vendor.') }}</p>
            @endforelse
            {{ $profileFeedback->fragment('profile-reviews')->links() }}
        </div>
    </section>
@endif
