@php
    $state = $getState() ?? [];
    $status = (string) ($state['status'] ?? '');
    $statusLabel = str($status)->replace('_', ' ')->headline();
    $isApproved = $status === 'approved';
    $isRejected = $status === 'rejected';
@endphp

<style>
    .buyer-access-review {
        background: linear-gradient(135deg, #ecfdf5, #ffffff);
        border: 1px solid #b9ddd8;
        border-left: 5px solid #0f766e;
        border-radius: 14px;
        display: grid;
        gap: 16px;
        grid-template-columns: minmax(0, 1fr) auto;
        padding: 16px 18px;
    }

    .buyer-access-review__eyebrow {
        color: #0f766e;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .08em;
        margin: 0 0 5px;
        text-transform: uppercase;
    }

    .buyer-access-review__title {
        color: #111827;
        font-size: 16px;
        font-weight: 900;
        line-height: 1.25;
        margin: 0 0 5px;
    }

    .buyer-access-review__copy {
        color: #526071;
        font-size: 13px;
        line-height: 1.45;
        margin: 0;
    }

    .buyer-access-review__actions {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
    }

    .buyer-access-review__button {
        border: 0;
        border-radius: 10px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 900;
        min-height: 42px;
        min-width: 112px;
        padding: 0 18px;
    }

    .buyer-access-review__button--approve {
        background: #059669;
        color: #ffffff;
    }

    .buyer-access-review__button--reject {
        background: #dc2626;
        color: #ffffff;
    }

    .buyer-access-review__button:disabled {
        background: #d1d5db;
        color: #6b7280;
        cursor: not-allowed;
    }

    .buyer-access-review__notice {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        border-radius: 12px;
        color: #047857;
        font-size: 13px;
        font-weight: 800;
        grid-column: 1 / -1;
        padding: 10px 12px;
    }

    @media (max-width: 760px) {
        .buyer-access-review {
            grid-template-columns: 1fr;
        }

        .buyer-access-review__actions {
            justify-content: flex-start;
        }
    }
</style>

<div class="buyer-access-review">
    @if (session('buyer_access_review_status'))
        <div class="buyer-access-review__notice">
            {{ session('buyer_access_review_status') }}
        </div>
    @endif

    <div>
        <p class="buyer-access-review__eyebrow">Agent decision</p>
        <h3 class="buyer-access-review__title">Approve or reject this buyer access request</h3>
        <p class="buyer-access-review__copy">
            Current status: <strong>{{ $statusLabel }}</strong>. Approving the request unlocks buyer bidding for this property.
        </p>
    </div>

    <div class="buyer-access-review__actions">
        <form method="POST" action="{{ $state['approve_url'] }}">
            @csrf
            <button
                type="submit"
                @disabled($isApproved)
                class="buyer-access-review__button buyer-access-review__button--approve"
            >
                Approve
            </button>
        </form>

        <form method="POST" action="{{ $state['reject_url'] }}" onsubmit="return confirm('Reject this buyer access request?');">
            @csrf
            <button
                type="submit"
                @disabled($isRejected)
                class="buyer-access-review__button buyer-access-review__button--reject"
            >
                Reject
            </button>
        </form>
    </div>
</div>
