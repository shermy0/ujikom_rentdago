@if ($paginator->hasPages())
<nav class="pagination-wrapper">
    <ul class="pagination pagination-modern">

        {{-- PREVIOUS --}}
        <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
            <a class="page-link"
               href="{{ $paginator->previousPageUrl() }}"
               aria-label="Previous">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>

        {{-- PAGE NUMBERS --}}
        @foreach ($elements as $element)
            {{-- "..." --}}
            @if (is_string($element))
                <li class="page-item disabled">
                    <span class="page-link">{{ $element }}</span>
                </li>
            @endif

            {{-- ARRAY OF LINKS --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url }}">
                            {{ $page }}
                        </a>
                    </li>
                @endforeach
            @endif
        @endforeach

        {{-- NEXT --}}
        <li class="page-item {{ !$paginator->hasMorePages() ? 'disabled' : '' }}">
            <a class="page-link"
               href="{{ $paginator->nextPageUrl() }}"
               aria-label="Next">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>

    </ul>
</nav>
@endif
