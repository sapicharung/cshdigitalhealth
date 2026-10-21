@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="csh-pagination-wrapper">
        <style>
            .csh-pagination-wrapper {
                display: inline-flex;
                align-items: center;
            }
            .csh-pagination {
                display: flex !important;
                align-items: center !important;
                list-style: none !important;
                margin: 0 !important;
                padding: 0 !important;
                gap: 6px !important;
                flex-wrap: wrap !important;
            }
            .csh-pagination li {
                list-style: none !important;
                margin: 0 !important;
                padding: 0 !important;
                display: inline-block !important;
            }
            .csh-page-btn {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                min-width: 36px !important;
                height: 36px !important;
                padding: 0 11px !important;
                font-size: 0.875rem !important;
                font-weight: 500 !important;
                line-height: 1 !important;
                color: #475569 !important;
                background-color: #FFFFFF !important;
                border: 1px solid #E2E8F0 !important;
                border-radius: 8px !important;
                text-decoration: none !important;
                transition: all 0.15s ease-in-out !important;
                user-select: none !important;
                box-sizing: border-box !important;
                cursor: pointer !important;
            }
            a.csh-page-btn:hover {
                color: #0284C7 !important;
                background-color: #F0F9FF !important;
                border-color: #7DD3FC !important;
                transform: translateY(-1px);
                box-shadow: 0 2px 4px rgba(2, 132, 199, 0.12);
            }
            .csh-page-btn.active {
                color: #FFFFFF !important;
                background: linear-gradient(135deg, #0284C7 0%, #0369A1 100%) !important;
                border-color: #0284C7 !important;
                font-weight: 600 !important;
                box-shadow: 0 3px 8px rgba(2, 132, 199, 0.35) !important;
                cursor: default !important;
            }
            .csh-page-btn.disabled {
                color: #CBD5E1 !important;
                background-color: #F8FAFC !important;
                border-color: #E2E8F0 !important;
                cursor: not-allowed !important;
                opacity: 0.7 !important;
                box-shadow: none !important;
                transform: none !important;
            }
            .csh-page-dots {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                min-width: 30px !important;
                height: 36px !important;
                color: #94A3B8 !important;
                font-size: 0.9rem !important;
                font-weight: 600 !important;
                user-select: none !important;
            }
            .csh-pagination-icon {
                width: 16px;
                height: 16px;
                display: inline-block;
                vertical-align: middle;
            }
        </style>

        <ul class="csh-pagination">
            {{-- ปุ่มก่อนหน้า (Previous) --}}
            @if ($paginator->onFirstPage())
                <li aria-disabled="true" aria-label="ก่อนหน้า">
                    <span class="csh-page-btn disabled" title="หน้าก่อนหน้า">
                        <svg class="csh-pagination-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </span>
                </li>
            @else
                <li>
                    <a class="csh-page-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="ก่อนหน้า" title="หน้าก่อนหน้า">
                        <svg class="csh-pagination-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </a>
                </li>
            @endif

            {{-- ตัวเลขหน้า (Pagination Elements) --}}
            @foreach ($elements as $element)
                {{-- ตัวคั่นจุดไข่ปลา (...) --}}
                @if (is_string($element))
                    <li aria-disabled="true">
                        <span class="csh-page-dots">{{ $element }}</span>
                    </li>
                @endif

                {{-- ลิงก์ตัวเลขหน้า --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li aria-current="page">
                                <span class="csh-page-btn active">{{ $page }}</span>
                            </li>
                        @else
                            <li>
                                <a class="csh-page-btn" href="{{ $url }}" title="หน้า {{ $page }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- ปุ่มถัดไป (Next) --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a class="csh-page-btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="ถัดไป" title="หน้าถัดไป">
                        <svg class="csh-pagination-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                </li>
            @else
                <li aria-disabled="true" aria-label="ถัดไป">
                    <span class="csh-page-btn disabled" title="หน้าถัดไป">
                        <svg class="csh-pagination-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
