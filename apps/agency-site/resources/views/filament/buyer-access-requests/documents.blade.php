@php
    $state = $getState() ?? [];
    $documents = $state['documents'] ?? [];
@endphp

<style>
    .buyer-access-documents {
        display: grid;
        gap: 14px;
    }

    .buyer-access-document {
        align-items: center;
        background: #ffffff;
        border: 1px solid #d7e0ea;
        border-radius: 14px;
        display: grid;
        gap: 14px;
        grid-template-columns: 96px minmax(0, 1fr) auto;
        padding: 14px;
    }

    .buyer-access-document__thumb {
        align-items: center;
        background: #f3f7f9;
        border: 1px solid #d7e0ea;
        border-radius: 10px;
        color: #526071;
        display: flex;
        font-size: 12px;
        font-weight: 800;
        height: 76px;
        justify-content: center;
        overflow: hidden;
        text-transform: uppercase;
        width: 96px;
    }

    .buyer-access-document__thumb img {
        display: block;
        height: 100%;
        object-fit: cover;
        width: 100%;
    }

    .buyer-access-document__title {
        color: #111827;
        font-size: 14px;
        font-weight: 800;
        line-height: 1.25;
        margin: 0 0 5px;
    }

    .buyer-access-document__meta {
        color: #667085;
        font-size: 12px;
        line-height: 1.35;
        margin: 0;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .buyer-access-document__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-end;
    }

    .buyer-access-document__action {
        align-items: center;
        border-radius: 9px;
        border: 0;
        cursor: pointer;
        display: inline-flex;
        font-family: inherit;
        font-size: 13px;
        font-weight: 800;
        justify-content: center;
        line-height: 1;
        min-height: 36px;
        padding: 0 13px;
        text-decoration: none;
    }

    .buyer-access-document__thumb {
        cursor: pointer;
        padding: 0;
    }

    .buyer-access-document__action--primary {
        background: #0f766e;
        color: #ffffff;
    }

    .buyer-access-document__action--secondary {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #334155;
    }

    .buyer-access-document--missing {
        grid-template-columns: 96px minmax(0, 1fr);
    }

    .buyer-access-document--missing .buyer-access-document__thumb {
        border-style: dashed;
        cursor: default;
    }

    .buyer-access-document-modal {
        background: #ffffff;
        border: 0;
        border-radius: 18px;
        box-shadow: 0 28px 80px rgb(15 23 42 / 0.28);
        box-sizing: border-box;
        left: 50%;
        margin: 0;
        max-height: min(86vh, 900px);
        max-width: min(92vw, 980px);
        padding: 0;
        position: fixed;
        top: 50%;
        transform: translate(-50%, -50%);
        width: 980px;
    }

    .buyer-access-document-modal[open] {
        display: block;
    }

    .buyer-access-document-modal::backdrop {
        background: rgb(15 23 42 / 0.55);
    }

    .buyer-access-document-modal__shell {
        display: grid;
        grid-template-rows: auto minmax(0, 1fr) auto;
        max-height: min(86vh, 900px);
        width: 100%;
    }

    .buyer-access-document-modal__header,
    .buyer-access-document-modal__footer {
        align-items: center;
        display: flex;
        gap: 12px;
        justify-content: space-between;
        padding: 16px 18px;
    }

    .buyer-access-document-modal__header {
        border-bottom: 1px solid #e2e8f0;
    }

    .buyer-access-document-modal__footer {
        border-top: 1px solid #e2e8f0;
    }

    .buyer-access-document-modal__title {
        color: #111827;
        font-size: 16px;
        font-weight: 900;
        line-height: 1.25;
        margin: 0;
    }

    .buyer-access-document-modal__meta {
        color: #667085;
        font-size: 12px;
        margin: 4px 0 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .buyer-access-document-modal__close {
        align-items: center;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        color: #334155;
        cursor: pointer;
        display: inline-flex;
        font-family: inherit;
        font-size: 20px;
        font-weight: 800;
        height: 40px;
        justify-content: center;
        line-height: 1;
        width: 40px;
    }

    .buyer-access-document-modal__body {
        align-items: center;
        background: #f8fafc;
        display: grid;
        justify-items: center;
        min-height: 360px;
        overflow: auto;
        padding: 18px;
    }

    .buyer-access-document-modal__body img,
    .buyer-access-document-modal__body iframe {
        background: #ffffff;
        border: 1px solid #d7e0ea;
        border-radius: 12px;
        display: block;
        margin: 0 auto;
        max-width: 100%;
    }

    .buyer-access-document-modal__body img {
        justify-self: center;
        height: auto;
        max-height: 68vh;
        max-width: min(100%, 900px);
        object-fit: contain;
    }

    .buyer-access-document-modal__body iframe {
        height: min(68vh, 720px);
        justify-self: center;
        width: 100%;
    }

    .buyer-access-document-modal__empty {
        color: #526071;
        font-size: 14px;
        font-weight: 700;
        margin: 0;
        padding: 32px;
        text-align: center;
    }

    @media (max-width: 760px) {
        .buyer-access-document {
            align-items: start;
            grid-template-columns: 76px minmax(0, 1fr);
        }

        .buyer-access-document__thumb {
            height: 62px;
            width: 76px;
        }

        .buyer-access-document__actions {
            grid-column: 1 / -1;
            justify-content: flex-start;
        }

        .buyer-access-document-modal {
            max-height: 92vh;
            max-width: 94vw;
        }

        .buyer-access-document-modal__shell {
            max-height: 92vh;
        }

        .buyer-access-document-modal__header,
        .buyer-access-document-modal__footer {
            padding: 14px;
        }
    }
</style>

<div class="buyer-access-documents" data-buyer-access-documents-root>
    @foreach ($documents as $document)
        @php
            $modalId = 'buyer-access-document-preview-'.$document['key'];
        @endphp

        <article class="buyer-access-document @if (! $document['exists']) buyer-access-document--missing @endif">
            <button
                type="button"
                @if ($document['exists'])
                    data-buyer-access-preview-trigger="{{ $modalId }}"
                @endif
                class="buyer-access-document__thumb"
                aria-label="{{ $document['exists'] ? 'Open '.$document['label'].' preview' : $document['label'].' not uploaded' }}"
            >
                @if (! $document['exists'])
                    Missing
                @elseif ($document['is_image'])
                    <img src="{{ $document['preview_url'] }}" alt="{{ $document['label'] }} thumbnail">
                @elseif ($document['is_pdf'])
                    PDF
                @else
                    File
                @endif
            </button>

            <div>
                <h3 class="buyer-access-document__title">{{ $document['label'] }}</h3>
                <p class="buyer-access-document__meta">
                    @if ($document['exists'])
                        {{ $document['filename'] }} · {{ strtoupper($document['extension'] ?: 'file') }}
                    @else
                        No file uploaded.
                    @endif
                </p>
            </div>

            @if ($document['exists'])
                <div class="buyer-access-document__actions">
                    <button
                        type="button"
                        data-buyer-access-preview-trigger="{{ $modalId }}"
                        class="buyer-access-document__action buyer-access-document__action--primary"
                    >
                        Open preview
                    </button>
                    <a
                        href="{{ $document['download_url'] }}"
                        class="buyer-access-document__action buyer-access-document__action--secondary"
                    >
                        Download
                    </a>
                </div>
            @endif
        </article>

        @if ($document['exists'])
            <dialog id="{{ $modalId }}" class="buyer-access-document-modal" aria-label="{{ $document['label'] }} preview">
                <div class="buyer-access-document-modal__shell">
                    <header class="buyer-access-document-modal__header">
                        <div>
                            <h3 class="buyer-access-document-modal__title">{{ $document['label'] }}</h3>
                            <p class="buyer-access-document-modal__meta">
                                {{ $document['filename'] }} · {{ strtoupper($document['extension'] ?: 'file') }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="buyer-access-document-modal__close"
                            data-buyer-access-preview-close
                            aria-label="Close preview"
                        >
                            ×
                        </button>
                    </header>

                    <div class="buyer-access-document-modal__body">
                        @if ($document['is_image'])
                            <img src="{{ $document['preview_url'] }}" alt="{{ $document['label'] }} preview">
                        @elseif ($document['is_pdf'])
                            <iframe src="{{ $document['preview_url'] }}" title="{{ $document['label'] }} PDF preview"></iframe>
                        @else
                            <p class="buyer-access-document-modal__empty">
                                This file type cannot be previewed inline. Please download it to review.
                            </p>
                        @endif
                    </div>

                    <footer class="buyer-access-document-modal__footer">
                        <button
                            type="button"
                            class="buyer-access-document__action buyer-access-document__action--secondary"
                            data-buyer-access-preview-close
                        >
                            Close
                        </button>

                        <a
                            href="{{ $document['download_url'] }}"
                            class="buyer-access-document__action buyer-access-document__action--primary"
                        >
                            Download
                        </a>
                    </footer>
                </div>
            </dialog>
        @endif
    @endforeach
</div>

<script>
    (() => {
        if (window.__buyerAccessDocumentPreviewBound) {
            return;
        }

        window.__buyerAccessDocumentPreviewBound = true;

        document.addEventListener('click', (event) => {
            if (! (event.target instanceof Element)) {
                return;
            }

            const trigger = event.target.closest('[data-buyer-access-preview-trigger]');

            if (trigger) {
                const dialog = document.getElementById(trigger.dataset.buyerAccessPreviewTrigger);

                if (dialog?.showModal) {
                    dialog.showModal();
                }

                return;
            }

            const close = event.target.closest('[data-buyer-access-preview-close]');

            if (close) {
                close.closest('dialog')?.close();
            }
        });

        document.addEventListener('click', (event) => {
            if (! (event.target instanceof Element)) {
                return;
            }

            if (event.target instanceof HTMLDialogElement && event.target.classList.contains('buyer-access-document-modal')) {
                event.target.close();
            }
        });
    })();
</script>
