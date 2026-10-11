@if ($paginator->hasPages())
    <nav class="market-pagination" aria-label="Navigasi halaman">
        @if ($paginator->onFirstPage())
            <span class="market-pagination-disabled" aria-disabled="true">Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>
        @else
            <span class="market-pagination-disabled" aria-disabled="true">Berikutnya</span>
        @endif
    </nav>
@endif
