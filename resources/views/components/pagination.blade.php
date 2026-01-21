@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="Pagination">
        <div class="pagination-summary">
            Menampilkan {{ $paginator->firstItem() ?? 0 }} - {{ $paginator->lastItem() ?? 0 }}
            dari {{ $paginator->total() }} data
        </div>
        <div class="pagination-links">
            @if ($paginator->onFirstPage())
                <span class="page disabled">Previous</span>
            @else
                <a class="page" href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="page dots">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="page current">{{ $page }}</span>
                        @else
                            <a class="page" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="page" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
            @else
                <span class="page disabled">Next</span>
            @endif
        </div>
    </nav>
@endif
