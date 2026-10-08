@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="custom-pagination" style="display:inline-flex;align-items:center;gap:4px;font-family:'Kalpurush',sans-serif">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="page-link-btn disabled" aria-disabled="true" style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid #e2e8f0;background:#f8fafc;color:#94a3b8;border-radius:6px;font-size:12.5px;font-weight:600;cursor:not-allowed">
                <i class="fa-solid fa-chevron-left" style="font-size:10px"></i> পূর্ববর্তী
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="page-link-btn" style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid #cbd5e1;background:#ffffff;color:#1e293b;border-radius:6px;font-size:12.5px;font-weight:600;text-decoration:none;transition:all 0.15s ease">
                <i class="fa-solid fa-chevron-left" style="font-size:10px"></i> পূর্ববর্তী
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="page-link-dots" style="padding:6px 10px;color:#94a3b8;font-size:13px">...</span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="page-link-btn active" aria-current="page" style="display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:32px;padding:0 8px;border:1px solid #047857;background:#047857;color:#ffffff;border-radius:6px;font-size:13px;font-weight:700">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" class="page-link-btn" style="display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:32px;padding:0 8px;border:1px solid #e2e8f0;background:#ffffff;color:#334155;border-radius:6px;font-size:13px;font-weight:600;text-decoration:none;transition:all 0.15s ease">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="page-link-btn" style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid #cbd5e1;background:#ffffff;color:#1e293b;border-radius:6px;font-size:12.5px;font-weight:600;text-decoration:none;transition:all 0.15s ease">
                পরবর্তী <i class="fa-solid fa-chevron-right" style="font-size:10px"></i>
            </a>
        @else
            <span class="page-link-btn disabled" aria-disabled="true" style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid #e2e8f0;background:#f8fafc;color:#94a3b8;border-radius:6px;font-size:12.5px;font-weight:600;cursor:not-allowed">
                পরবর্তী <i class="fa-solid fa-chevron-right" style="font-size:10px"></i>
            </span>
        @endif
    </nav>
@endif
