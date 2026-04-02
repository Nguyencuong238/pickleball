@if ($paginator->hasPages())
    <nav style="display: flex; justify-content: center; margin-top: 30px; margin-bottom: 30px;">
        <div style="display: flex; gap: 8px; flex-wrap: wrap; justify-content: center;">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; color: #999; cursor: not-allowed; opacity: 0.5;">← Trước</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; color: #006646; text-decoration: none; display: inline-block; transition: all 0.3s ease;">← Trước</a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span style="padding: 8px 12px; color: #999;">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span style="padding: 8px 12px; border: 1px solid #006646; border-radius: 4px; background-color: #006646; color: white; display: inline-block;">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; color: #006646; text-decoration: none; display: inline-block; transition: all 0.3s ease;">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; color: #006646; text-decoration: none; display: inline-block; transition: all 0.3s ease;">Sau →</a>
            @else
                <span style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; color: #999; cursor: not-allowed; opacity: 0.5;">Sau →</span>
            @endif
        </div>
    </nav>

    <style>
        nav a:hover {
            background-color: #006646 !important;
            color: white !important;
            border-color: #006646 !important;
        }
    </style>
@endif
