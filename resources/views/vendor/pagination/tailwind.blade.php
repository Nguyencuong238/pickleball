@if ($paginator->hasPages())
    <ul class="custom-pagination">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="disabled">
                <span class="page-link">← Trước</span>
            </li>
        @else
            <li>
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">← Trước</a>
            </li>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <li class="disabled">
                    <span class="page-link">{{ $element }}</span>
                </li>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="active">
                            <span class="page-link">{{ $page }}</span>
                        </li>
                    @else
                        <li>
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li>
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Sau →</a>
            </li>
        @else
            <li class="disabled">
                <span class="page-link">Sau →</span>
            </li>
        @endif
    </ul>
@endif
