@if ($paginator->hasPages())
  <nav aria-label="Page navigation">
    <ul class="pagination pagination-sm mb-0 align-items-center" style="gap: 4px;">
      {{-- Previous Page Link --}}
      @if ($paginator->onFirstPage())
        <li class="page-item disabled" aria-disabled="true">
          <span class="page-link" aria-hidden="true" style="border-radius: 6px; font-weight: bold; min-width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; color: #98a2b3; background-color: #f8f9fa; border: 1px solid #d0d5dd;">&lsaquo;</span>
        </li>
      @else
        <li class="page-item">
          <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')" style="border-radius: 6px; font-weight: bold; min-width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; color: #344054; background-color: #ffffff; border: 1px solid #d0d5dd;">&lsaquo;</a>
        </li>
      @endif

      {{-- Pagination Elements --}}
      @foreach ($elements as $element)
        {{-- "Three Dots" Separator --}}
        @if (is_string($element))
          <li class="page-item disabled" aria-disabled="true">
            <span class="page-link" style="border-radius: 6px; min-width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border: 1px solid #d0d5dd; background-color: #f8f9fa; color: #667085;">{{ $element }}</span>
          </li>
        @endif

        {{-- Array Of Links --}}
        @if (is_array($element))
          @foreach ($element as $page => $url)
            @if ($page == $paginator->currentPage())
              <li class="page-item active" aria-current="page">
                <span class="page-link shadow-sm" style="border-radius: 6px; font-weight: 700; min-width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background-color: #666cff; border-color: #666cff; color: #ffffff;">{{ $page }}</span>
              </li>
            @else
              <li class="page-item">
                <a class="page-link" href="{{ $url }}" style="border-radius: 6px; font-weight: 600; min-width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; color: #344054; background-color: #ffffff; border: 1px solid #d0d5dd;">{{ $page }}</a>
              </li>
            @endif
          @endforeach
        @endif
      @endforeach

      {{-- Next Page Link --}}
      @if ($paginator->hasMorePages())
        <li class="page-item">
          <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')" style="border-radius: 6px; font-weight: bold; min-width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; color: #344054; background-color: #ffffff; border: 1px solid #d0d5dd;">&rsaquo;</a>
        </li>
      @else
        <li class="page-item disabled" aria-disabled="true">
          <span class="page-link" aria-hidden="true" style="border-radius: 6px; font-weight: bold; min-width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; color: #98a2b3; background-color: #f8f9fa; border: 1px solid #d0d5dd;">&rsaquo;</span>
        </li>
      @endif
    </ul>
  </nav>
@endif
