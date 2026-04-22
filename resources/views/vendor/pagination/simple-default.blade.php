@if ($paginator->hasPages())
    <nav class="app-pagination app-pagination--simple" role="navigation" aria-label="Pagination Navigation">
        <div class="app-pagination__summary">
            @if ($paginator->firstItem() && $paginator->lastItem())
                Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }}
            @else
                Page {{ $paginator->currentPage() }}
            @endif
        </div>

        <div class="app-pagination__controls">
            @if ($paginator->onFirstPage())
                <span class="app-pagination__button app-pagination__button--nav app-pagination__button--disabled" aria-disabled="true">
                    <span class="app-pagination__chevron" aria-hidden="true">&lsaquo;</span>
                    <span>Previous</span>
                </span>
            @else
                <a class="app-pagination__button app-pagination__button--nav" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                    <span class="app-pagination__chevron" aria-hidden="true">&lsaquo;</span>
                    <span>Previous</span>
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a class="app-pagination__button app-pagination__button--nav" href="{{ $paginator->nextPageUrl() }}" rel="next">
                    <span>Next</span>
                    <span class="app-pagination__chevron" aria-hidden="true">&rsaquo;</span>
                </a>
            @else
                <span class="app-pagination__button app-pagination__button--nav app-pagination__button--disabled" aria-disabled="true">
                    <span>Next</span>
                    <span class="app-pagination__chevron" aria-hidden="true">&rsaquo;</span>
                </span>
            @endif
        </div>
    </nav>
@endif
